<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>My Rules!Discount rule settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --p-surface: #ffffff;
            --p-surface-subdued: #f9fafb;
            --p-border: #e1e3e5;
            --p-border-subdued: #e1e3e5;
            --p-shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.05);
            --p-space-4: 1rem;
            --p-space-5: 1.25rem;
            --p-text: #212b36;
            --p-text-subdued: #637381;
            --p-interactive: #5c6ac4;
        }

        body {
            background-color: #f6f6f7;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            color: var(--p-text);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        h2.page-title {
            font-weight: 600;
            font-size: 1.75rem;
            margin: 0;
        }

        /* Card styling to match image */
        .card {
            background: var(--p-surface);
            border: 1px solid var(--p-border);
            border-radius: 8px;
            box-shadow: var(--p-shadow-xs);
            margin-bottom: var(--p-space-5);
        }

        .card-header {
            padding: var(--p-space-4) var(--p-space-5);
            background-color: var(--p-surface);
            border-bottom: 1px solid var(--p-border);
            font-weight: 600;
            font-size: 1rem;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .card-body {
            padding: var(--p-space-5);
        }

        label.form-label,
        .form-check-label {
            font-weight: 600;
            color: var(--p-text);
            margin-bottom: 0.4rem;
        }

        /* Smaller font-weight for radio/check labels */
        .form-check-label {
            font-weight: 400;
        }

        .form-control,
        .form-select,
        .select2-selection {
            border-radius: 6px !important;
            border-color: #c4cdd5 !important;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus,
        .select2-selection--multiple:focus-within {
            border-color: var(--p-interactive) !important;
            box-shadow: 0 0 0 2px rgba(92, 106, 196, 0.2) !important;
        }

        .btn-primary {
            background-color: #008060;
            /* Shopify Green */
            border-color: #008060;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
        }

        .btn-primary:hover {
            background-color: #006e52;
            border-color: #006e52;
        }

        .btn-secondary {
            background-color: var(--p-surface);
            color: var(--p-text);
            border: 1px solid #c4cdd5;
        }

        .btn-secondary:hover {
            background-color: #f1f2f3;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        /* Summary Box on the right */
        .summary-card ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .summary-card li {
            padding: 8px 0;
            border-bottom: 1px solid var(--p-border-subdued);
            font-size: 0.9rem;
            color: var(--p-text-subdued);
        }

        .summary-card li:last-child {
            border-bottom: none;
        }

        .summary-card li .summary-value {
            color: var(--p-text);
            font-weight: 500;
        }

        /* Hide sections by default */
        .target-section {
            display: none;
        }

        .target-section.active {
            display: block;
        }

        #end-date-wrapper {
            display: none;
            /* Hide end date by default */
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <form action="{{ route('rules.store') }}?host={{ request()->get('host') }}&shop={{ request()->get('shop') }}"
        method="POST">
        @csrf
        <div class="container-fluid mt-4">
            <div class="page-header">
                <div>
                    <a href="{{ route('rules.index') }}?host={{ request()->get('host') }}&shop={{ request()->get('shop') }}"
                        class="btn btn-secondary mb-3 me-3">← My Rules</a>
                    <h2 class="page-title d-inline-block align-middle">Discount rule settings</h2>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>

            <div class="row">

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name" class="form-label">Discount Rule Name</label>
                                <input type="text" name="name" id="name" class="form-control" required
                                    placeholder="Hello world">
                                <div class="form-text">For internal reference only. Customers cannot see this.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-8">
                                    <label for="discount_value" class="form-label">Discount value</label>
                                    <input type="number" name="discount_value" id="discount_value" class="form-control"
                                        step="0.01" min="0" required placeholder="50">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="discount_type" class="form-label invisible">Type</label>
                                    <select name="discount_type" id="discount_type" class="form-select" required>
                                        <option value="percentage">%</option>
                                        <option value="fixed_amount">Fixed amount</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label d-block">Set discount based on</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="discount_base" value="current_price" id="current_price"
                                        class="form-check-input" checked>
                                    <label for="current_price" class="form-check-label">Current price</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="discount_base" value="compare_at_price"
                                        id="compare_at_price" class="form-check-input">
                                    <label for="compare_at_price" class="form-check-label">Compare at price</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="apply_to_type" class="form-label">Applies to</label>
                                <select name="apply_to_type" id="apply_to_type" class="form-select" required>
                                    <option value="products">Products and variants</option>
                                    <option value="collections">Collections</option>
                                    <option value="tags">Tags</option>
                                    <option value="vendors">Vendors</option>
                                    <option value="whole_store">Whole store</option>
                                </select>
                            </div>

                            <div class="target-section" id="products-section">
                                <label class="form-label">Search products</label>
                                <select name="apply_to_targets[]" id="products-select" class="form-control select2"
                                    multiple="multiple" style="width: 100%;"></select>
                            </div>
                            <div class="target-section" id="collections-section">
                                <label class="form-label">Search collections</label>
                                <select name="apply_to_targets[]" id="collections-select" class="form-control select2"
                                    multiple="multiple" style="width: 100%;"></select>
                            </div>
                            <div class="target-section" id="tags-section">
                                <label class="form-label">Search tags</label>
                                <select name="apply_to_targets[]" id="tags-select" class="form-control select2"
                                    multiple="multiple" style="width: 100%;"></select>
                            </div>
                            <div class="target-section" id="vendors-section">
                                <label class="form-label">Search vendors</label>
                                <select name="apply_to_targets[]" id="vendors-select" class="form-control select2"
                                    multiple="multiple" style="width: 100%;"></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card summary-card">
                        <div class="card-header">SALE</div>
                        <div class="card-body">
                            <ul>
                                <li>Rule Name: <span id="summary-rule-name" class="summary-value">Not set</span></li>
                                <li>
                                    <span id="summary-discount" class="summary-value">0%</span> Discount for
                                    <span id="summary-target-count" class="summary-value">0</span>
                                    <span id="summary-target-type">Products</span>
                                </li>
                                <li>Discount based on <span id="summary-discount-base" class="summary-value">Current
                                        price</span></li>
                                <li>Starting from <span id="summary-start-date" class="summary-value">not set</span>
                                </li>
                                <li>Tags Added: <span id="summary-tags" class="summary-value">None</span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Custom Tags</div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label for="tags_to_add" class="form-label">Add a tag</label>
                                <input type="text" name="tags_to_add" id="tags_to_add" class="form-control"
                                    placeholder="e.g., flash-sale, 24hr-deal">
                                <div class="form-text">Tags are added when the rule is active and removed when it's not.
                                    Separate tags with a comma.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Start Date</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-7">
                                    <label for="start_at" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control">
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="time" name="start_time" id="start_time" class="form-control">
                                </div>
                            </div>
                            <input type="datetime-local" name="start_at" id="start_at" class="d-none">

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="set-end-date-checkbox">
                                <label class="form-check-label" for="set-end-date-checkbox">
                                    Set End Date
                                </label>
                            </div>

                            <div id="end-date-wrapper" class="row">
                                <div class="form-group col-md-7">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control">
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="time" name="end_time" id="end_time" class="form-control">
                                </div>
                            </div>
                            <input type="datetime-local" name="end_at" id="end_at" class="d-none">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // =================================================================
            // ORIGINAL SCRIPT (UNCHANGED FUNCTIONALITY)
            // =================================================================

            var urlParams = new URLSearchParams(window.location.search);
            var hostParam = urlParams.get('host');
            var shopParam = urlParams.get('shop');

            $('.select2').select2({
                ajax: {
                    url: function () {
                        var baseUrl;
                        var selectId = $(this).attr('id');
                        if (selectId.includes('products')) {
                            baseUrl = '{{ route('api.products.search') }}';
                        } else if (selectId.includes('collections')) {
                            baseUrl = '{{ route('api.collections.search') }}';
                        } else if (selectId.includes('tags')) {
                            baseUrl = '{{ route('api.tags.search') }}';
                        } else if (selectId.includes('vendors')) {
                            baseUrl = '{{ route('api.vendors.search') }}';
                        }

                        if (hostParam && shopParam) {
                            baseUrl += (baseUrl.includes('?') ? '&' : '?') + 'host=' + encodeURIComponent(hostParam) + '&shop=' + encodeURIComponent(shopParam);
                        }
                        return baseUrl;
                    },
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page,
                            host: hostParam,
                            shop: shopParam
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.map(item => ({ id: item.id || item.node, text: item.text || item.title || item.node })),
                            pagination: { more: data.length === 250 }
                        };
                    },
                    cache: true
                },
                placeholder: 'Search and select...',
                allowClear: true,
                minimumInputLength: 2
            });

            // Show/hide target sections based on dropdown
            $('#apply_to_type').on('change', function () {
                var selectedType = $(this).val();
                $('.target-section').removeClass('active');
                if (selectedType !== 'whole_store') {
                    $('#' + selectedType + '-section').addClass('active');
                }
                // Also update summary when type changes
                updateSummary();
            }).trigger('change');

            // Form submission via AJAX
            $('form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const data = form.serialize() + (hostParam && shopParam ? '&host=' + encodeURIComponent(hostParam) + '&shop=' + encodeURIComponent(shopParam) : '');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(response.message || "Rule saved successfully!");
                            form.trigger("reset");
                            $('.select2').val(null).trigger('change');
                        } else {
                            alert(response.message || 'Failed to save rule!');
                        }
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Please try again'));
                    }
                });
            });

            // =================================================================
            // NEW SCRIPT FOR UI ENHANCEMENTS & SUMMARY
            // =================================================================

            // Function to combine date and time into a single datetime-local input
            function combineDateTime(dateInput, timeInput, combinedInput) {
                const dateVal = $(dateInput).val();
                const timeVal = $(timeInput).val();
                if (dateVal && timeVal) {
                    $(combinedInput).val(`${dateVal}T${timeVal}`);
                } else if (dateVal) {
                    $(combinedInput).val(`${dateVal}T00:00`); // Default time if only date is set
                } else {
                    $(combinedInput).val('');
                }
                $(combinedInput).trigger('change'); // Ensure change event fires for validation
            }

            // Combine date/time for start and end dates
            $('#start_date, #start_time').on('change', function () {
                combineDateTime('#start_date', '#start_time', '#start_at');
            });
            $('#end_date, #end_time').on('change', function () {
                combineDateTime('#end_date', '#end_time', '#end_at');
            });
            

            // Toggle end date visibility
            $('#set-end-date-checkbox').on('change', function () {
                $('#end-date-wrapper').toggle(this.checked);
                if (!this.checked) {
                    $('#end_date, #end_time, #end_at').val('').trigger('change');
                }
            });

            // Date validation: start must be before end
            $('#start_at, #end_at').on('change', function () {
                const start = $('#start_at').val();
                const end = $('#end_at').val();
                if (start && end && new Date(start) >= new Date(end)) {
                    alert('Start date must be before the end date!');
                    $('#end_date, #end_time, #end_at').val('');
                }
                updateSummary();
            });

            // --- Summary Panel Logic ---
            function updateSummary() {
                // Rule Name
                const ruleName = $('#name').val();
                $('#summary-rule-name').text(ruleName || 'Not set');

                // Discount Value & Type
                const discountValue = $('#discount_value').val() || 0;
                const discountType = $('#discount_type').val() === 'percentage' ? '%' : ' (fixed)';
                $('#summary-discount').text(`${discountValue}${discountType}`);

                // Applied Target
                const applyToType = $('#apply_to_type').val();
                const targetSelectId = '#' + applyToType + '-select';
                let targetCount = 0;
                if (applyToType !== 'whole_store') {
                    targetCount = $(targetSelectId).select2('data').length;
                } else {
                    targetCount = 1; // Representing the whole store
                }

                let targetTypeName = applyToType.charAt(0).toUpperCase() + applyToType.slice(1);
                if (targetCount !== 1) {
                    // simple pluralization
                    targetTypeName = targetTypeName.endsWith('s') ? targetTypeName : targetTypeName + 's';
                }
                if (applyToType === 'whole_store') targetTypeName = "Store";

                $('#summary-target-count').text(targetCount);
                $('#summary-target-type').text(targetTypeName);

                // Discount Base
                const discountBase = $('input[name="discount_base"]:checked').parent().find('label').text();
                $('#summary-discount-base').text(discountBase);

                // Start Date
                const startDateVal = $('#start_at').val();
                if (startDateVal) {
                    const date = new Date(startDateVal);
                    const formattedDate = date.toLocaleDateString('en-GB', { month: 'long', day: 'numeric', year: 'numeric' }) + ' (' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) + ')';
                    $('#summary-start-date').text(formattedDate);
                } else {
                    $('#summary-start-date').text('now');
                }

                // Tags
                const tags = $('#tags_to_add').val();
                $('#summary-tags').text(tags || 'None');
            }

            // Attach event listeners to update summary on input change
            $('form').on('input change', 'input, select', updateSummary);
            $('.select2').on('change', updateSummary);

            // Initial call to populate summary on page load
            updateSummary();
        });
    </script>
</body>

</html>