<?php

namespace App\Http\Controllers;

use App\Jobs\BulkApplyDiscountJob;
use App\Jobs\BulkRestoreDiscountJob;
use App\Jobs\BulkProductActionJob;
use App\Models\Rule;
use App\Models\User;
use App\Services\ProductGraphQLService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class RulesGraphQLController extends Controller
{
    protected ProductGraphQLService $service;

    public function __construct(ProductGraphQLService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $query = Rule::query();
        $tab = $request->tab ?? 'main';
        if ($tab === 'archived') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        // Bộ lọc
        if ($search = $request->search) $query->where('name', 'like', "%$search%");
        if ($status = $request->status) {
            // Logic tùy chỉnh, ví dụ: nếu ($status === 'active') $query->where('active', true); v.v.
            // Đối với nâng cao, ánh xạ sang các truy vấn (ví dụ: tương lai: where('start_at', '>', now()))
        }
        if ($applyTo = $request->apply_to) $query->where('apply_to_type', $applyTo);
        if ($discountType = $request->discount_type) $query->where('discount_type', $discountType);
        if ($minValue = $request->min_discount) $query->where('discount_value', '>=', $minValue);
        if ($maxValue = $request->max_discount) $query->where('discount_value', '<=', $maxValue);
        if ($startFrom = $request->start_from) $query->where('start_at', '>=', $startFrom);
        if ($startTo = $request->start_to) $query->where('start_at', '<=', $startTo);
        // Tương tự cho end_at

        // Sắp xếp
        $sort = $request->sort ?? 'name';
        $dir = $request->dir ?? 'asc';
        $query->orderBy($sort, $dir);

        $rules = $query->paginate(10);

        Log::info('Query conditions: ', ['tab' => $tab, 'archived_at' => $query->toSql()]);

        return view('rules.index', compact('rules', 'tab'));
    }

    public function create()
    {
        $shop = $this->service->getFirstShop();
        $products = $this->service->getProductType($shop, ''); // Truy vấn tất cả sản phẩm, điều chỉnh query nếu cần
        $tags = $this->service->getTags($shop);
        $vendors = $this->service->getVendors($shop);
        $collections = $this->service->getCollections($shop); // Giả sử phương thức hiện có
        // dd($products, $collections); // Kiểm tra cấu trúc mảng
        // Đối với sản phẩm, sử dụng tìm kiếm AJAX
        return view('rules.create', compact('products', 'tags', 'vendors', 'collections'));
    }

    public function store(Request $request)
    {
        // Xác thực: $request->validate([...])
        Log::info('Request data: ', $request->all());
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_base' => 'required|in:current_price,compare_at_price',
            'apply_to_type' => 'required|in:products,collections,tags,vendors,whole_store',
            'apply_to_targets' => 'present|array',
            'exclude_products' => 'present|array',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
            'tags_to_add' => 'nullable|string',
        ]);
        $rule = Rule::create($validatedData);
        $lastPage = ceil(Rule::count() / 10); // Tính trang cuối
        Log::info('Created rule: ', ['id' => $rule->id, 'name' => $rule->name]);
        return redirect()->route('rules.index', ['page' => $lastPage])->with('success', 'Quy tắc đã được tạo');
    }

    public function edit(Rule $rule)
    {
        $shop = $this->service->getFirstShop();
        $products = $this->service->getProductType($shop, ''); // Truy vấn tất cả sản phẩm
        $collections = $this->service->getCollections($shop);
        $tags = $this->service->getTags($shop);
        $vendors = $this->service->getVendors($shop);
        return view('rules.edit', compact('rules', 'products', 'collections', 'tags', 'vendors'));
    }

    public function update(Request $request, Rule $rule)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_base' => 'required|in:current_price,compare_at_price',
            'apply_to_type' => 'required|in:products,collections,tags,vendors,whole_store',
            'apply_to_targets' => 'present|array',
            'exclude_products' => 'present|array',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
            'tags_to_add' => 'nullable|string',
        ]);

        $rule->update($validatedData);
        return redirect()->route('rules.index')->with('success', 'Quy tắc đã được cập nhật');
    }

    public function toggle(Rule $rule)
    {
        $shop = $this->service->getFirstShop(); // Hoặc từ phiên
        if ($rule->active) {
            $rule->active = false;
            $rule->save();
            $this->disableRule($shop, $rule);
        } else {
            $rule->active = true;
            $rule->save();
            $this->applyRule($shop, $rule);
        }
        return back()->with('success', 'Quy tắc đã được chuyển đổi');
    }

    public function archive(Rule $rule)
    {
        $rule->update(['archived_at' => now(), 'active' => false]);
        $this->disableRule($this->service->getFirstShop(), $rule); // Vô hiệu hóa nếu lưu trữ
        return back();
    }

    public function restore(Rule $rule)
    {
        $rule->update(['archived_at' => null]);
        return back();
    }

    public function destroy(Rule $rule)
    {
        $this->disableRule($this->service->getFirstShop(), $rule);
        $rule->delete();
        return back();
    }

    public function duplicate(Rule $rule)
    {
        $dup = $rule->replicate();
        $dup->name = 'Bản sao của ' . $rule->name;
        $dup->active = false;
        $dup->save();
        return redirect()->route('rules.edit', $dup);
    }

    protected function applyRule(User $shop, Rule $rule): void
    {
        if ($rule->ruleVariants()->exists()) return; // Đã áp dụng
        $variants = $this->service->getMatchingVariants($shop, $rule);
        $batch = Bus::batch([])->name('Áp dụng Quy tắc ' . $rule->id)->dispatch();
        $chunks = array_chunk($variants, 100);
        foreach ($chunks as $chunk) {
            $batch->add(new BulkApplyDiscountJob($shop, $rule, $chunk));
        }
        $productIds = array_unique(array_column($variants, 'product_id'));
        if ($rule->tags_to_add) {
            $batch->add(new BulkProductActionJob($shop, 'add_tags', $productIds, ['tags' => $rule->tags_to_add]));
        }
        Log::info('Áp dụng quy tắc', ['rule_id' => $rule->id]);
    }

    protected function disableRule(User $shop, Rule $rule): void
    {
        if (!$rule->ruleVariants()->exists()) return; // Chưa áp dụng
        $batch = Bus::batch([])->name('Vô hiệu hóa Quy tắc ' . $rule->id)->dispatch();
        $ruleVariants = $rule->ruleVariants;
        $chunks = $ruleVariants->chunk(100);
        foreach ($chunks as $chunk) {
            $batch->add(new BulkRestoreDiscountJob($shop, $rule, $chunk));
        }
        $productIds = $ruleVariants->pluck('product_id')->unique()->toArray();
        if ($rule->tags_to_add) {
            $batch->add(new BulkProductActionJob($shop, 'remove_tags', $productIds, ['tags' => $rule->tags_to_add]));
        }
        Log::info('Vô hiệu hóa quy tắc', ['rule_id' => $rule->id]);
    }

    // API cho tìm kiếm (ví dụ: sản phẩm)
    public function searchProducts(Request $request)
    {
        $shop = $this->service->getFirstShop();
        $queryStr = $request->q ? 'title:*' . $request->q . '*' : '';

        // Sử dụng truy vấn tương tự như getMatchingVariants
        $products = $this->service->getProducts($shop, 20, null, null, $queryStr);

        // Chuẩn bị dữ liệu cho Select2
        $results = [];
        if ($products) {
            foreach ($products['products'] as $edge) {
                $node = $edge['node'];
                $results[] = [
                    'id' => $node['id'],
                    'title' => $node['title'],
                ];
            }
        }

        return response()->json($results);
    }
    // Tương tự cho collections, v.v.
}
