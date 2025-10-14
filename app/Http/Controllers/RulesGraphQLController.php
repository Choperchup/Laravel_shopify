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

        // Tab: main / archived
        if ($tab === 'archived') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        // Filter: search by name
        if ($search = $request->search) {
            $query->where('name', 'like', "%$search%");
        }

        // Filter: status
        if ($status = $request->status) {
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        }

        // Filter: apply_to_type
        if ($applyTo = $request->apply_to) {
            $query->where('apply_to_type', $applyTo);
        }

        // Filter: discount_type
        if ($discountType = $request->discount_type) {
            $query->where('discount_type', $discountType);
        }

        // Filter: start / end date
        if ($startDate = $request->start_date) {
            $query->whereDate('start_at', '>=', $startDate);
        }
        if ($endDate = $request->end_date) {
            $query->whereDate('end_at', '<=', $endDate);
        }

        // Sort
        $sort = $request->sort ?? 'name_asc';
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'start_asc':
                $query->orderBy('start_at', 'asc');
                break;
            case 'start_desc':
                $query->orderBy('start_at', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        // Pagination giữ query string để filter vẫn còn khi chuyển trang
        $rules = $query->paginate(10)->withQueryString();

        Log::info('Query conditions: ', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'tab' => $tab
        ]);

        return view('rules.index', compact('rules', 'tab'));
    }


    public function create()
    {
        $shop = $this->service->getFirstShop();
        $products = $this->service->getProductType($shop, '');
        $tags = $this->service->getTags($shop);
        $vendors = $this->service->getVendors($shop);
        $collections = $this->service->getCollections($shop);

        return view('rules.create', compact('products', 'tags', 'vendors', 'collections'));
    }

    public function store(Request $request)
    {
        Log::info('Request data: ', $request->all());

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_base' => 'required|in:current_price,compare_at_price',
            'apply_to_type' => 'required|in:products,collections,tags,vendors,whole_store',
            'apply_to_targets' => 'nullable|array',
            'exclude_products' => 'nullable|array',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
            'tags_to_add' => 'nullable|string',
        ]);

        // Convert array to JSON string nếu DB cột là text
        if (isset($validatedData['apply_to_targets'])) {
            $validatedData['apply_to_targets'] = json_encode($validatedData['apply_to_targets']);
        }
        if (isset($validatedData['exclude_products'])) {
            $validatedData['exclude_products'] = json_encode($validatedData['exclude_products']);
        }

        $rule = Rule::create($validatedData);
        $lastPage = ceil(Rule::count() / 10);

        Log::info('Created rule: ', ['id' => $rule->id, 'name' => $rule->name]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quy tắc đã được tạo thành công!',
                'rule' => $rule,
                'last_page' => $lastPage
            ]);
        }
        // Lấy host/shop từ request hoặc session
        $host = $request->get('host') ?? session('shopify_host');
        $shop = $request->get('shop') ?? session('shopify_shop');

        return redirect()->route('rules.index', [
            'page' => $lastPage,
            'host' => $host,
            'shop' => $shop,
        ])->with('success', 'Quy tắc đã được tạo');
    }

    public function edit(Rule $rule)
    {
        $shop = $this->service->getFirstShop();
        $products = $this->service->getProductType($shop, '');
        $collections = $this->service->getCollections($shop);
        $tags = $this->service->getTags($shop);
        $vendors = $this->service->getVendors($shop);

        // dd($collections);

        return view('rules.edit', compact('rule', 'products', 'collections', 'tags', 'vendors'));
    }

    public function update(Request $request, Rule $rule)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_base' => 'required|in:current_price,compare_at_price',
            'apply_to_type' => 'required|in:products,collections,tags,vendors,whole_store',
            'apply_to_targets' => 'nullable|array',
            'exclude_products' => 'nullable|array',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
            'tags_to_add' => 'nullable|string',
        ]);

        // Convert arrays to JSON string nếu DB cột là text
        if (isset($validatedData['apply_to_targets'])) {
            $validatedData['apply_to_targets'] = json_encode($validatedData['apply_to_targets']);
        }
        if (isset($validatedData['exclude_products'])) {
            $validatedData['exclude_products'] = json_encode($validatedData['exclude_products']);
        }

        $rule->update($validatedData);

        // Lấy host/shop từ request hoặc session
        $host = $request->get('host') ?? session('shopify_host');
        $shop = $request->get('shop') ?? session('shopify_shop');

        // URL quay lại trang index
        $redirectUrl = route('rules.index', ['tab' => 'main', 'host' => $host, 'shop' => $shop]);

        // Nếu request là AJAX → trả JSON để JS xử lý
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quy tắc đã được cập nhật',
                'redirect' => $redirectUrl,
            ]);
        }

        // Nếu có host → render view redirect trong iframe (giống restore)
        if ($host && $shop) {
            return response()
                ->view('shared.app-bridge-redirect', [
                    'redirect' => $redirectUrl,
                    'host' => $host
                ])
                ->header('X-Frame-Options', 'ALLOWALL');
        }

        // Fallback ngoài Shopify iframe
        return redirect($redirectUrl)->with('success', 'Quy tắc đã được cập nhật');
    }


    public function toggle(Rule $rule)
    {
        $shop = $this->service->getFirstShop();
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
        $this->disableRule($this->service->getFirstShop(), $rule);
        return back();
    }

    public function restore(Request $request, Rule $rule)
    {
        Log::debug('🧠 Restore request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'host' => $request->get('host'),
            'shop' => $request->get('shop'),
        ]);

        $rule->update(['archived_at' => null]);

        $host = $request->get('host') ?? session('shopify_host');
        $shop = $request->get('shop') ?? session('shopify_shop');
        $redirectUrl = route('rules.index', ['tab' => 'archived', 'host' => $host, 'shop' => $shop]);

        // Nếu request là AJAX → trả JSON
        if ($request->ajax()) {
            return response()->json(['success' => true, 'redirect' => $redirectUrl]);
        }

        // Nếu có host → render view redirect trong iframe
        if ($host && $shop) {
            return response()
                ->view('shared.app-bridge-redirect', [
                    'redirect' => $redirectUrl,
                    'host' => $host
                ])
                ->header('X-Frame-Options', 'ALLOWALL'); // ⚡ Cho phép nhúng trong iframe Shopify
        }

        // Trường hợp fallback
        return redirect($redirectUrl)->with('success', 'Đã khôi phục quy tắc');
    }


    public function destroy(Rule $rule)
    {
        $this->disableRule($this->service->getFirstShop(), $rule);
        $rule->delete();
        return back();
    }

    public function duplicate(Request $request, Rule $rule)
    {
        $dup = $rule->replicate();
        $dup->name = 'Bản sao của ' . $rule->name;
        $dup->active = false;
        $dup->save();

        // Lấy host và shop từ request hoặc session
        $host = $request->get('host') ?? session('shopify_host');
        $shop = $request->get('shop') ?? session('shopify_shop');

        return redirect()->route('rules.edit', [
            'rule' => $dup->id,
            'host' => $host,
            'shop' => $shop,
        ]);
    }

    protected function applyRule(User $shop, Rule $rule): void
    {
        if ($rule->ruleVariants()->exists()) return;

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
        if (!$rule->ruleVariants()->exists()) return;

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

    // API cho tìm kiếm sản phẩm
    public function searchProducts(Request $request)
    {
        $shop = $this->service->getFirstShop();
        $queryStr = $request->q ? 'title:*' . $request->q . '*' : '';

        $products = $this->service->getProducts($shop, 20, null, null, $queryStr);

        $results = [];
        if ($products) {
            foreach ($products['products'] as $edge) {
                $node = $edge['node'];
                $results[] = [
                    'id' => $node['id'],
                    'text' => $node['title'],
                ];
            }
        }

        return response()->json($results);
    }

    // API cho tìm kiếm collections
    public function searchCollections(Request $request)
    {
        $shop = $this->service->getFirstShop();
        $collections = $this->service->getCollections($shop);

        $results = [];
        foreach ($collections as $edge) { // ✅ Bỏ ['edges']
            $node = $edge['node'];
            if (!$request->q || stripos($node['title'], $request->q) !== false) {
                $results[] = [
                    'id' => $node['id'],
                    'text' => $node['title'],
                ];
            }
        }

        return response()->json($results);
    }

    // API cho tìm kiếm tags
    public function searchTags(Request $request)
    {
        $shop = $this->service->getFirstShop();
        $tags = $this->service->getTags($shop);

        $results = [];
        foreach ($tags as $edge) { // ✅ Bỏ phần ['shop']['productTags']['edges']
            $node = $edge['node'];
            if (!$request->q || stripos($node, $request->q) !== false) {
                $results[] = [
                    'id' => $node,
                    'text' => $node,
                ];
            }
        }
        return response()->json($results);
    }

    // API cho tìm kiếm vendors
    public function searchVendors(Request $request)
    {
        $shop = $this->service->getFirstShop();
        $vendors = $this->service->getVendors($shop);

        $results = [];
        if ($vendors && isset($vendors['shop']['productVendors']['edges'])) {
            foreach ($vendors['shop']['productVendors']['edges'] as $edge) {
                $node = $edge['node'];
                if (!$request->q || stripos($node, $request->q) !== false) {
                    $results[] = [
                        'id' => $node,
                        'text' => $node,
                    ];
                }
            }
        }

        return response()->json($results);
    }
}
