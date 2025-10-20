<?php

namespace App\Http\Controllers;

use App\Jobs\BulkApplyDiscountJob;
use App\Jobs\BulkRestoreDiscountJob;
use App\Jobs\BulkProductActionJob;
use App\Models\Rule;
use App\Models\User;
use Carbon\Carbon;
use App\Models\RuleVariant;
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
                // 'active' giờ đây bao gồm nhiều trạng thái
                $query->whereIn('status', ['ACTIVE', 'ACTIVATING', 'PENDING_DEACTIVATION']);
            } elseif ($status === 'inactive') {
                // 'inactive' cũng bao gồm nhiều trạng thái
                $query->whereIn('status', ['INACTIVE', 'SCHEDULED', 'PENDING_ACTIVATION']);
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
        $now = Carbon::now();

        // Cập nhật trạng thái người dùng mong muốn (bật hoặc tắt)
        $rule->is_enabled = !$rule->is_enabled;

        $rule->save();

        // === LOGIC KHI BẬT RULE (is_enabled = true) 
        if ($rule->is_enabled) {
            // Trường hợp 1: Rule đang trong thời gian hiệu lực -> Kích hoạt ngay
            if ($rule->start_at <= $now && (!$rule->end_at || $rule->end_at >= $now)) {
                $this->dispatchActivationJobs($shop, $rule);
            }
            // Trường hợp 2: Rule được hẹn giờ cho tương lai -> Chỉ đổi trạng thái
            else if ($rule->start_at > $now) {
                $rule->status = 'SCHEDULED';
                $rule->save();
            }
            // Trường hợp 3: Rule đã hết hạn nhưng người dùng cố bật
            else {
                $rule->is_enabled = false; // Trả lại trạng thái tắt
                $rule->status = 'INACTIVE';
                $rule->save();
                return back()->with('error', 'Quy tắc này đã hết hạn.');
            }
        }
        // === LOGIC KHI TẮT RULE (is_enabled = false)
        else {
            $this->dispatchDeactivationJobs($shop, $rule);
        }

        return back()->with('success', 'Trạng thái quy tắc đang được cập nhật...');
    }

    /**
     * MỚI: Chuẩn bị và đẩy một batch job để ÁP DỤNG GIẢM GIÁ.
     */

    public function dispatchActivationJobs(User $shop, Rule $rule): void
    {
        $rule->update(['status' => 'PENDING_ACTIVATION']);

        $allVariants = $this->service->getMatchingVariants($shop, $rule);
        $totalVariants = count($allVariants);

        if ($totalVariants === 0) {
            $rule->update(['status' => 'ACTIVE', 'total_products' => 0, 'activated_at' => now()]);
            return;
        }

        $jobs = [];
        $chunkSize = 50;
        $variantChunks = array_chunk($allVariants, $chunkSize);

        foreach ($variantChunks as $chunk) {
            $jobs[] = new BulkApplyDiscountJob($shop, $rule, $chunk);
        }

        $ruleId = $rule->id; // Lấy ID ra một biến riêng

        $batch = Bus::batch($jobs)
            ->then(function () use ($ruleId) { // Chỉ sử dụng $ruleId ở đây
                // Tìm lại Rule mới nhất từ DB bằng ID
                $ruleToUpdate = Rule::find($ruleId);
                if ($ruleToUpdate) {
                    $ruleToUpdate->update([
                        'status' => 'ACTIVE',
                        'job_batch_id' => null,
                        'activated_at' => now(),
                    ]);
                }
            })
            ->catch(function () use ($ruleId) { // Tương tự cho catch
                $ruleToUpdate = Rule::find($ruleId);
                if ($ruleToUpdate) {
                    $ruleToUpdate->update(['status' => 'FAILED', 'job_batch_id' => null]);
                }
            })
            ->name("Activate Rule ID: {$ruleId}")
            ->dispatch();

        $rule->update([
            'job_batch_id' => $batch->id,
            'total_products' => $totalVariants,
            'processed_products' => 0,
        ]);
    }

    /**
     * MỚI: Chuẩn bị và đẩy một batch job để GỠ BỎ GIẢM GIÁ (revert).
     */
    public function dispatchDeactivationJobs(User $shop, Rule $rule, bool $deleteAfter = false): void
    {
        if ($rule->status !== 'DELETING') {
            $rule->update(['status' => 'PENDING_DEACTIVATION']);
        }

        $appliedVariants = RuleVariant::where('rule_id', $rule->id)->get();
        $totalVariants = $appliedVariants->count();

        if ($totalVariants === 0) {
            if ($deleteAfter) {
                $rule->delete();
            } else {
                $rule->update(['status' => 'INACTIVE']);
            }
            return;
        }

        $jobs = [];
        $chunkSize = 50;
        $variantChunks = $appliedVariants->chunk($chunkSize);

        foreach ($variantChunks as $chunk) {
            $jobs[] = new \App\Jobs\BulkRestoreDiscountJob($shop, $rule, $chunk);
        }

        // === THAY ĐỔI QUAN TRỌNG: CHỈ TRUYỀN ID ===
        $ruleId = $rule->id; // Lấy ID ra một biến riêng

        $batch = Bus::batch($jobs)
            ->then(function () use ($ruleId, $deleteAfter) { // Chỉ sử dụng $ruleId
                // Tìm lại Rule mới nhất từ DB bằng ID
                $ruleToUpdate = Rule::find($ruleId);
                if ($ruleToUpdate) {
                    if ($deleteAfter) {
                        $ruleToUpdate->delete();
                        Log::info("Rule ID: {$ruleToUpdate->id} đã được xóa sau khi revert giá thành công.");
                    } else {
                        $ruleToUpdate->update(['status' => 'INACTIVE', 'job_batch_id' => null]);
                    }
                }
            })
            ->catch(function () use ($ruleId) { // Tương tự cho catch
                $ruleToUpdate = Rule::find($ruleId);
                if ($ruleToUpdate) {
                    $ruleToUpdate->update(['status' => 'FAILED', 'job_batch_id' => null]);
                }
            })
            ->name("Deactivate Rule ID: {$ruleId} (Delete After: " . ($deleteAfter ? 'Yes' : 'No') . ")")
            ->dispatch();

        $rule->update([
            'job_batch_id' => $batch->id,
            'total_products' => $totalVariants,
            'processed_products' => 0,
        ]);
    }

    /**
     * archive, restore, destroy, duplicate
     */

    public function archive(Rule $rule)
    {
        // 1. Cập nhật trạng thái lưu trữ
        $rule->update([
            'is_enabled' => false,      // Tắt ý định của người dùng
            'archived_at' => now()
        ]);

        // 2. Nếu rule đang active, đẩy job vào queue để revert giá
        if (in_array($rule->status, ['ACTIVE', 'ACTIVATING', 'PENDING_DEACTIVATION'])) {
            $shop = $this->service->getFirstShop();
            $this->dispatchDeactivationJobs($shop, $rule);
        }
        return back()->with('success', 'Quy tắc đã được lưu trữ.');
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
        // Giữ nguyên logic redirect của bạn
        $host = $request->get('host') ?? session('shopify_host');
        $shop = $request->get('shop') ?? session('shopify_shop');
        $redirectUrl = route('rules.index', ['tab' => 'main', 'host' => $host, 'shop' => $shop]);
        return redirect($redirectUrl)->with('success', 'Đã khôi phục quy tắc');
    }


    public function destroy(Rule $rule)
    {
        // Nếu rule đang active → revert giá trước khi xóa
        if (in_array($rule->status, ['ACTIVE', 'ACTIVATING', 'PENDING_DEACTIVATION'])) {
            $shop = $this->service->getFirstShop();

            // Đẩy job với cờ deleteAfter = true
            $this->dispatchDeactivationJobs($shop, $rule, deleteAfter: true);

            return back()->with('success', 'Đang khôi phục giá sản phẩm trước khi xóa quy tắc...');
        }

        // Nếu rule đã inactive → xóa ngay
        $rule->delete();
        return back()->with('success', 'Quy tắc đã được xóa.');
    }

    public function duplicate(Request $request, Rule $rule)
    {
        $dup = $rule->replicate();
        $dup->name = 'Bản sao của ' . $rule->name;
        // Đặt trạng thái mặc định cho rule mới
        $dup->is_enabled = false;
        $dup->status = 'INACTIVE';
        $dup->job_batch_id = null;
        $dup->save();

        $host = $request->get('host') ?? session('shopify_host');
        $shop = $request->get('shop') ?? session('shopify_shop');
        return redirect()->route('rules.edit', [
            'rule' => $dup->id,
            'host' => $host,
            'shop' => $shop,
        ]);
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
