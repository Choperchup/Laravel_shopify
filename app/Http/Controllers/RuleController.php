<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use App\Jobs\BulkApplyDiscountJob;
use App\Jobs\BulkRemoveDiscountJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\ShopifyGraphQLService;

class RuleController extends Controller
{
    protected $shopifyService;

    public function __construct(ShopifyGraphQLService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'main');
        $query = Rule::query();

        if ($tab === 'archived') {
            $query->where('archived', true);
        } else {
            $query->where('archived', false);
        }

        // Filters
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%$search%");
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($applyTo = $request->get('apply_to')) {
            $query->where('apply_to', $applyTo);
        }
        if ($discountType = $request->get('discount_type')) {
            $query->where('discount_type', $discountType);
        }
        if ($minValue = $request->get('min_value')) {
            $query->where('discount_value', '>=', $minValue);
        }
        if ($maxValue = $request->get('max_value')) {
            $query->where('discount_value', '<=', $maxValue);
        }
        if ($startFrom = $request->get('start_from')) {
            $query->where('start_time', '>=', Carbon::parse($startFrom));
        }
        if ($startTo = $request->get('start_to')) {
            $query->where('start_time', '<=', Carbon::parse($startTo));
        }
        if ($endFrom = $request->get('end_from')) {
            $query->where('end_time', '>=', Carbon::parse($endFrom));
        }
        if ($endTo = $request->get('end_to')) {
            $query->where('end_time', '<=', Carbon::parse($endTo));
        }

        // Sort
        $sortField = $request->get('sort_field', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortField, $sortDir);

        $rules = $query->paginate(20);

        return view('rules.index', [
            'rules' => $rules,
            'tab' => $tab,
            'filters' => $request->all(),
        ]);
    }

    public function create()
    {
        return view('rules.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,fix_amount',
            'discount_on' => 'required|in:current_price,compare_at_price',
            'apply_to' => 'required|in:product_variant,tag,vendor,collection,whole_store',
            'targets' => 'nullable|json', // Lưu dưới dạng json
            'summary' => 'nullable|string',
            'add_tag' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'excluded_ids' => 'nullable|json',
            'status' => 'required|in:on,off',
        ]);

        $rule = Rule::create($validated);

        if ($rule->status === 'on' && Carbon::parse($rule->start_time) <= Carbon::now()) {
            BulkApplyDiscountJob::dispatch($rule);
        }

        return redirect()->route('rules.index')->with('success', 'Rule đã được tạo.');
    }

    public function edit(Rule $rule)
    {
        return view('rules.edit', compact('rule'));
    }

    public function update(Request $request, Rule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'discount_value' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,fix_amount',
            'discount_on' => 'required|in:current_price,compare_at_price',
            'apply_to' => 'required|in:product_variant,tag,vendor,collection,whole_store',
            'targets' => 'nullable|json',
            'summary' => 'nullable|string',
            'add_tag' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'excluded_ids' => 'nullable|json',
            'status' => 'required|in:on,off',
        ]);

        $oldStatus = $rule->status;
        $rule->update($validated);

        $now = Carbon::now();
        if ($rule->status === 'on' && $rule->start_time <= $now && $oldStatus === 'off') {
            BulkApplyDiscountJob::dispatch($rule);
        } elseif ($rule->status === 'off') {
            BulkRemoveDiscountJob::dispatch($rule);
        }

        return redirect()->route('rules.index')->with('success', 'Rule đã được cập nhật.');
    }

    public function destroy(Rule $rule)
    {
        BulkRemoveDiscountJob::dispatch($rule);
        $rule->delete();
        return redirect()->route('rules.index')->with('success', 'Rule đã được xóa.');
    }

    public function toggleStatus(Rule $rule)
    {
        $rule->status = $rule->status === 'on' ? 'off' : 'on';
        $rule->save();

        $now = Carbon::now();
        if ($rule->status === 'on' && $rule->start_time <= $now && ($rule->end_time === null || $rule->end_time > $now)) {
            BulkApplyDiscountJob::dispatch($rule);
        } else {
            BulkRemoveDiscountJob::dispatch($rule);
        }

        return back()->with('success', 'Trạng thái đã được thay đổi.');
    }

    public function archive(Rule $rule)
    {
        $rule->archived = true;
        $rule->status = 'off';
        $rule->save();
        BulkRemoveDiscountJob::dispatch($rule);
        return back()->with('success', 'Rule đã được lưu trữ.');
    }

    public function restore(Rule $rule)
    {
        $rule->archived = false;
        $rule->save();
        return back()->with('success', 'Rule đã được khôi phục.');
    }

    public function duplicate(Rule $rule)
    {
        $duplicate = $rule->replicate();
        $duplicate->name = $rule->name . ' (Copy)';
        $duplicate->save();
        return redirect()->route('rules.edit', $duplicate)->with('success', 'Rule đã được nhân bản.');
    }
}
