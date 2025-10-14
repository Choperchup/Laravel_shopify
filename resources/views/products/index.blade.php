<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products / Rules</title>
    <link rel="stylesheet" href="{{ secure_asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/style.css') }}">
    <!-- Load Shopify App Bridge and Utils from CDN -->
    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
    <script src="https://unpkg.com/@shopify/app-bridge-utils@3"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-right">
                <button>Export</button>
                <button>Import</button>
                <button class="add-btn">Add product</button>
            </div>
        </div>
        <div class="tabs-bar">
            <div class="tabs">
                <button class="active">All</button>
                <button>Active</button>
                <button>Draft</button>
                <button>Archived</button>
                <button>+</button>
            </div>
            <div class="tabs-actions">
                <button class="icon-btn" id="toggleSearch">🔍</button>
                <div class="dropdown sort-dropdown">
                    <button class="icon-btn" id="sortToggle">⇅</button>
                    @php
                        $currentSort = request('sort', 'title');
                        $currentOrder = request('order', 'asc');
                    @endphp
                    <div class="dropdown-content sort-menu">
                        <p class="dropdown-title">Sort by</p>
                        <label>
                            <input type="radio" name="sort" value="title" {{ $currentSort === 'title' ? 'checked' : '' }}>
                            Product title
                        </label>
                        <label>
                            <input type="radio" name="sort" value="created" {{ $currentSort === 'created' ? 'checked' : '' }}> Created At
                        </label>
                        <label>
                            <input type="radio" name="sort" value="updated" {{ $currentSort === 'updated' ? 'checked' : '' }}> Updated At
                        </label>
                        <label>
                            <input type="radio" name="sort" value="productType" {{ $currentSort === 'productType' ? 'checked' : '' }}> Product type
                        </label>
                        <label>
                            <input type="radio" name="sort" value="vendor" {{ $currentSort === 'vendor' ? 'checked' : '' }}> Vendor
                        </label>
                        <hr>
                        <p class="dropdown-title">Order</p>
                        <label>
                            <input type="radio" name="order" value="asc" {{ $currentOrder === 'asc' ? 'checked' : '' }}> ↑
                            Oldest first
                        </label>
                        <label>
                            <input type="radio" name="order" value="desc" {{ $currentOrder === 'desc' ? 'checked' : '' }}>
                            ↓ Newest first
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div id="searchBar" style="display:flex; flex-direction:column">
            <div class="search-row">
                <input type="text" id="searchInput" name="title" placeholder="Search...">
                <div class="search-actions">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="button" class="apply-filter">Apply</button>
                </div>
            </div>
            <div class="active-filters-bar">
                <div id="activeFilters" class="active-filters"></div>
                <button type="button" id="clearAll" class="clear-all">Clear all</button>
            </div>
            <div class="filters">
                <div class="dropdown vendors-dropdown">
                    <button class="dropdown-btn">Vendors ▾</button>
                    <div class="dropdown-content">
                        @foreach($vendors ?? [] as $vendor)
                            @php
                                $vendorName = is_array($vendor) ? ($vendor['node'] ?? '') : $vendor;
                            @endphp
                            <div>
                                <input type="checkbox" name="vendors[]" value="{{ $vendorName }}"> {{ $vendorName }}
                            </div>
                        @endforeach
                        <div class="dropdown-actions">
                            <button type="button" class="clear">Clear</button>
                        </div>
                    </div>
                </div>
                <div class="dropdown tags-dropdown">
                    <button class="dropdown-btn">Tags ▾</button>
                    <div class="dropdown-content">
                        @foreach($tags ?? [] as $tag)
                            @php
                                $tagName = is_array($tag) ? ($tag['node'] ?? '') : $tag;
                            @endphp
                            <div>
                                <input type="radio" name="tag" value="{{ $tagName }}"> {{ $tagName }}
                            </div>
                        @endforeach
                        <div class="dropdown-actions">
                            <button type="button" class="clear">Clear</button>
                        </div>
                    </div>
                </div>
                <div class="dropdown statuses-dropdown">
                    <button class="dropdown-btn">Statuses ▾</button>
                    <div class="dropdown-content">
                        <label><input type="checkbox" name="status[]" value="ACTIVE"> Active</label>
                        <label><input type="checkbox" name="status[]" value="DRAFT"> Draft</label>
                        <label><input type="checkbox" name="status[]" value="ARCHIVED"> Archived</label>
                        <div class="dropdown-actions">
                            <button type="button" class="clear">Clear</button>
                        </div>
                    </div>
                </div>
                <div class="dropdown productTypes-dropdown">
                    <button class="dropdown-btn">Product Types ▾</button>
                    <div class="dropdown-content">
                        @foreach($productTypes ?? [] as $type)
                            @php
                                $typeName = is_array($type) ? ($type['node'] ?? '') : $type;
                            @endphp
                            <div>
                                <input type="checkbox" name="types[]" value="{{ $typeName }}"> {{ $typeName }}
                            </div>
                        @endforeach
                        <div class="dropdown-actions">
                            <button type="button" class="clear">Clear</button>
                        </div>
                    </div>
                </div>
                <div class="dropdown collections-dropdown">
                    <button class="dropdown-btn">Collections ▾</button>
                    <div class="dropdown-content">
                        @foreach($collections ?? [] as $collection)
                            @php
                                $collectionId = is_array($collection) ? ($collection['node']['id'] ?? '') : $collection;
                                $collectionName = is_array($collection) ? ($collection['node']['title'] ?? '') : $collection;
                            @endphp
                            <div>
                                <input type="radio" name="collection" value="{{ $collectionId }}"> {{ $collectionName }}
                            </div>
                        @endforeach
                        <div class="dropdown-actions">
                            <button type="button" class="clear">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="bulkBar" class="bulk-action-bar" style="display:none;">
            <span id="selectedCount">0 selected</span>
            <button>Bulk edit</button>
            <button type="button" data-action="draft">Set as Draft</button>
            <button type="button" data-action="active">Set as Active</button>
            <button type="button" data-action="archive">Set as Archive</button>
            <div class="dropdown">
                <button class="dropdown-btn">⋮</button>
                <div class="dropdown-content">
                    <button type="button" onclick="openModal('tags','add')">Add tags</button>
                    <button type="button" onclick="openModal('tags','remove')">Remove tags</button>
                    <button type="button" onclick="openModal('collections','add')">Add to collections</button>
                    <button type="button" onclick="openModal('collections','remove')">Remove from collections</button>
                </div>
            </div>
        </div>

        <div id="actionModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3 id="modalTitle"></h3>
                <input type="text" id="modalSearchInput" placeholder="Search..." onkeyup="filterItems()">
                <div id="itemList" class="tag-list">
                    <!-- Danh sách sẽ được inject bằng JS -->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button class="btn btn-primary" id="saveBtn" onclick="saveAction()">Save</button>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Inventory</th>
                    <th>Vendor</th>
                    <th>Type</th>
                    <th>Tags</th>
                    <th>Collections</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                @include('products.partials.table_body', ['products' => $products ?? []])
            </tbody>
        </table>

        @include('products.partials.pagination', ['pageInfo' => $pageInfo ?? null])

    </div>

    <script>
        (function () {
            // Đảm bảo App Bridge được load
            const host = new URLSearchParams(window.location.search).get("host");
            const shop = new URLSearchParams(window.location.search).get("shop");
            const apiKey = "{{ config('shopify-app.api_key') }}";


            // Initialize AppBridge
            const AppBridge = window["app-bridge"];
            const AppBridgeUtils = window["app-bridge-utils"];
            const createApp = AppBridge.default;
            const { actions } = AppBridge;

            // Khởi tạo Shopify App Bridge
            const app = createApp({
                apiKey: "{{ config('shopify-app.api_key') }}",
                host: new URLSearchParams(location.search).get('host'),
                forceRedirect: true,
            });



            // Hàm lấy session token từ App Bridge
            async function getToken() {
                try {
                    const AppBridgeUtils = window['app-bridge-utils'];
                    if (!AppBridgeUtils || !app) {
                        throw new Error('App Bridge is not properly initialized.');
                    }
                    return await AppBridgeUtils.getSessionToken(app);
                } catch (err) {
                    console.error('❌ Error getting token:', err);
                    throw err;
                }
            }

            // Hàm hiển thị toast (thay thế nếu bạn có thư viện toast riêng)
            function showToast(message, isError = false) {
                console.log((isError ? '❌' : '✅') + ' ' + message);
                // Nếu bạn dùng thư viện như Polaris Toast, thêm logic ở đây
                const toastContainer = document.createElement('div');
                toastContainer.className = isError ? 'toast toast-error' : 'toast toast-success';
                toastContainer.textContent = message;
                document.body.appendChild(toastContainer);
                setTimeout(() => toastContainer.remove(), 3000);
            }

            // Bind checkbox cho các row
            function bindRowCheckboxes() {
                const checkboxes = document.querySelectorAll('.row-check');
                const rows = document.querySelectorAll('tbody tr');
                console.log('🔄 Binding row checkboxes...');
                console.log('Found checkboxes:', checkboxes.length);
                console.log('Found rows:', rows.length);

                checkboxes.forEach((cb, index) => {
                    cb.addEventListener('change', updateBulkBar);
                    rows[index].addEventListener('click', (e) => {
                        if (!e.target.closest('input, button, a')) {
                            cb.checked = !cb.checked;
                            updateBulkBar();
                        }
                    });
                });
                console.log('✅ Row checkboxes bound successfully');
            }

            // Bind các nút bulk action
            function bindBulkActionButtons() {
                document.querySelectorAll('[data-action]').forEach(btn => {
                    btn.addEventListener('click', handleBulkActionClick);
                });
                console.log('✅ Bulk action buttons bound');
            }

            // Xử lý click nút bulk action
            function handleBulkActionClick(e) {
                const action = e.target.dataset.action.toUpperCase();
                console.log('Action button clicked:', action);
                sendBulkStatus(action);
            }

            // Cập nhật thanh bulk action
            function updateBulkBar() {
                const selected = document.querySelectorAll('.row-check:checked').length;
                document.getElementById('selectedCount').textContent = `${selected} selected`;
                document.getElementById('bulkBar').style.display = selected > 0 ? 'flex' : 'none';
                document.getElementById('selectAll').checked = selected === document.querySelectorAll('.row-check').length;
            }

            // Xử lý chọn tất cả
            function handleSelectAll(e) {
                console.log('Select all clicked:', e.target.checked);
                document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
                updateBulkBar();
            }

            // Gửi bulk status
            async function sendBulkStatus(status) {
                console.log('⚡ sendBulkStatus CALLED with status:', status);
                try {
                    const token = await getToken();
                    const selectedIds = Array.from(document.querySelectorAll('.row-check:checked'))
                        .map(cb => cb.dataset.productId);
                    console.log('Selected products:', selectedIds);

                    if (selectedIds.length === 0) {
                        showToast('Please select at least one product', true);
                        return;
                    }

                    const payload = {
                        product_ids: selectedIds,
                        action: 'status',
                        payload: { status },
                        shop: SHOP
                    };

                    const res = await fetch('/products/bulk-action', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`,
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    if (!res.ok) {
                        showToast('❌ Failed to update status', true);
                        return;
                    }

                    const data = await res.json();
                    if (data.batch_id) {
                        showToast('⏳ Processing bulk action...');
                        let finished = false;
                        while (!finished) {
                            const statusRes = await fetch(`/products/bulk-action/status/${data.batch_id}`);
                            const statusData = await statusRes.json();
                            if (statusData.finished) {
                                finished = true;
                                showToast('✅ Bulk status updated!');
                            } else if (statusData.failed) {
                                finished = true;
                                showToast('❌ Some jobs failed!', true);
                            } else {
                                await new Promise(r => setTimeout(r, 1200));
                            }
                        }
                    } else {
                        showToast('✅ Bulk status updated!');
                    }

                    applyFilters();
                } catch (err) {
                    console.error('❌ Error in sendBulkStatus:', err);
                    showToast('⚠️ Error: ' + err.message, true);
                }
            }

            // Modal actions
            let currentType = null;
            let currentAction = null;
            const tagsData = @json($tags ?? []);
            const collectionsData = @json($collections ?? []);

            window.openModal = function (type, action) {
                currentType = type;
                currentAction = action;
                const count = document.querySelectorAll('.row-check:checked').length;
                document.getElementById('modalTitle').textContent =
                    `${action.charAt(0).toUpperCase() + action.slice(1)} ${type} for ${count} product(s)`;
                document.getElementById('saveBtn').textContent =
                    `${action.charAt(0).toUpperCase() + action.slice(1)} ${type}`;

                const listContainer = document.getElementById('itemList');
                listContainer.innerHTML = '';
                const data = type === 'tags' ? tagsData : collectionsData;

                data.forEach(item => {
                    const name = (typeof item === 'object') ?
                        (type === 'tags' ? (item.node ?? '') : (item.node?.title ?? '')) : item;
                    const id = (typeof item === 'object' && type === 'collections') ?
                        (item.node?.id ?? '') : name;
                    if (!name || !id) return;

                    const label = document.createElement('label');
                    label.className = 'tag-item';
                    if (type === 'collections') {
                        label.innerHTML = `<input type="radio" name="modal_collection" value="${id}"><span>${name}</span>`;
                    } else {
                        label.innerHTML = `<input type="checkbox" name="modal_tags[]" value="${id}"><span>${name}</span>`;
                    }
                    listContainer.appendChild(label);
                });

                document.getElementById('actionModal').style.display = 'flex';
            };

            window.closeModal = function () {
                document.getElementById('actionModal').style.display = 'none';
            };

            window.filterItems = function () {
                const q = document.getElementById('modalSearchInput').value.toLowerCase();
                document.querySelectorAll('#itemList label').forEach(label => {
                    const v = label.querySelector('input').value.toLowerCase();
                    label.style.display = v.includes(q) ? '' : 'none';
                });
            };

            window.saveAction = async function () {
                try {
                    const token = await getToken();
                    let selectedProducts = Array.from(document.querySelectorAll('.row-check:checked'))
                        .map(cb => cb.dataset.productId);

                    if (selectedProducts.length === 0) {
                        showToast('Please select at least one product', true);
                        return;
                    }

                    let selectedItems = [];
                    if (currentType === 'collections') {
                        const radio = document.querySelector('#itemList input[type="radio"]:checked');
                        if (radio) selectedItems.push(radio.value);
                    } else {
                        selectedItems = Array.from(document.querySelectorAll('#itemList input[type="checkbox"]:checked'))
                            .map(i => i.value);
                    }

                    if (selectedItems.length === 0) {
                        showToast(`Please select at least one ${currentType}`, true);
                        return;
                    }

                    let actionName = '';
                    if (currentType === 'tags') actionName = (currentAction === 'add') ? 'add_tags' : 'remove_tags';
                    else if (currentType === 'collections') actionName = (currentAction === 'add') ? 'add_collection' : 'remove_collection';

                    const payload = {};
                    if (currentType === 'tags') payload.tags = selectedItems;
                    else if (currentType === 'collections') payload.collection_ids = selectedItems;

                    const res = await fetch('/products/bulk-action', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`,
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            product_ids: selectedProducts,
                            action: actionName,
                            payload,
                            shop: SHOP
                        })
                    });

                    if (!res.ok) {
                        showToast('❌ Failed to apply action', true);
                        return;
                    }

                    const data = await res.json();
                    if (data.batch_id) {
                        showToast('⏳ Processing...');
                        let finished = false;
                        while (!finished) {
                            const statusRes = await fetch(`/products/bulk-action/status/${data.batch_id}`);
                            const statusData = await statusRes.json();
                            if (statusData.finished) {
                                finished = true;
                                showToast('✅ Action completed successfully!');
                            } else if (statusData.failed) {
                                finished = true;
                                showToast('❌ Some jobs failed!', true);
                            } else {
                                await new Promise(r => setTimeout(r, 1200));
                            }
                        }
                    } else {
                        showToast('✅ Action completed successfully!');
                    }

                    closeModal();
                    document.querySelectorAll("input[name='collection']").forEach(i => i.checked = false);
                    applyFilters();
                } catch (err) {
                    console.error('❌ Error in saveAction:', err);
                    showToast('⚠️ Error: ' + err.message, true);
                }
            };

            // Hàm applyFilters (giả sử bạn có)
            function applyFilters() {
                const formData = new FormData();
                document.querySelectorAll('#searchBar input, #searchBar select').forEach(input => {
                    if (input.type === 'checkbox' && input.checked) {
                        formData.append(input.name, input.value);
                    } else if (input.type === 'radio' && input.checked) {
                        formData.append(input.name, input.value);
                    } else if (input.type === 'text' && input.value) {
                        formData.append(input.name, input.value);
                    }
                });

                fetch('/products?' + new URLSearchParams(formData).toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('productTableBody').innerHTML = data.table;
                        document.querySelector('.pagination-container').innerHTML = data.pagination;
                        bindRowCheckboxes();
                        bindBulkActionButtons();
                    })
                    .catch(err => showToast('⚠️ Error loading products: ' + err.message, true));
            }

            // Bind sự kiện
            document.getElementById('selectAll')?.addEventListener('change', handleSelectAll);
            document.getElementById('toggleSearch')?.addEventListener('click', () => {
                document.getElementById('searchBar').style.display = document.getElementById('searchBar').style.display === 'none' ? 'flex' : 'none';
            });
            document.getElementById('sortToggle')?.addEventListener('click', () => {
                document.querySelector('.sort-menu').classList.toggle('show');
            });
            document.querySelector('.apply-filter')?.addEventListener('click', applyFilters);
            document.getElementById('clearAll')?.addEventListener('click', () => {
                document.querySelectorAll('#searchBar input, #searchBar select').forEach(i => {
                    if (i.type === 'checkbox' || i.type === 'radio') i.checked = false;
                    else if (i.type === 'text' || i.type === 'search') i.value = '';
                    else if (i.tagName === 'SELECT') i.selectedIndex = 0;
                });
                document.getElementById('activeFilters').innerHTML = '';
                applyFilters();
            });

            // MutationObserver để rebind khi table thay đổi
            let observerTimeout;
            const tableObserver = new MutationObserver(() => {
                clearTimeout(observerTimeout);
                observerTimeout = setTimeout(() => {
                    console.log('🔄 Table DOM changed, rebinding checkboxes...');
                    bindRowCheckboxes();
                    bindBulkActionButtons();
                }, 150);
            });

            const tbody = document.getElementById('productTableBody');
            if (tbody) {
                tableObserver.observe(tbody, { childList: true, subtree: true });
            }

            // === Init ===
            window.addEventListener("DOMContentLoaded", () => {
                bindRowCheckboxes();
                bindBulkActionButtons();
            });
        })();
    </script>
</body>

</html>