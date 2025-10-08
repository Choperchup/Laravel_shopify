<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ secure_asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/style.css') }}">
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
                        $currentSort = request('sort', 'title'); // fallback mặc định
                        $currentOrder = request('order', 'asc'); // fallback mặc định
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
        <div id="searchBar">
            <!-- Search row -->
            <div class="search-row">
                <input type="text" id="searchInput" name="title" placeholder="Search...">
                <div class="search-actions">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="button" class="apply-filter">Apply</button>
                </div>
            </div>
            <!-- Active filters -->
            <div class="active-filters-bar">
                <div id="activeFilters" class="active-filters"></div>
                <button type="button" id="clearAll" class="clear-all">Clear all</button>
            </div>
            <!-- Filters group -->
            <div class="filters">
                <!-- Vendors -->
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
                <!-- Tags -->
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
                <!-- Statuses -->
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
                <!-- Product Types -->
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
                <!-- Collections -->
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
        <div class="bulk-action-bar" id="bulkBar">
            <span id="selectedCount">0 selected</span>
            <button>Bulk edit</button>
            <button type="button" onclick="sendBulkStatus('DRAFT')">Set as Draft</button>
            <button type="button" onclick="sendBulkStatus('ACTIVE')">Set as Active</button>
            <button type="button" onclick="sendBulkStatus('ARCHIVED')">Set as Archive</button>
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
        <!-- Modal -->
        <div id="actionModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3 id="modalTitle"></h3>
                <input type="text" id="searchInput" placeholder="Search..." onkeyup="filterItems()">
                <div id="itemList" class="tag-list">
                    <!-- Danh sách sẽ được inject bằng JS -->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button class="btn btn-primary" id="saveBtn" onclick="saveAction()">Save</button>
                </div>
            </div>
        </div>
        <!-- Products table -->
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
                @foreach ($products ?? [] as $product)
                    @php
                        $node = $product['node'];
                        $image = $node['media']['edges'][0]['node']['image']['url'] ?? 'https://via.placeholder.com/40';
                        $tablecollections = [];
                        if (!empty($node['collections']['edges']) && is_array($node['collections']['edges'])) {
                            foreach ($node['collections']['edges'] as $cEdge) {
                                $cNode = $cEdge['node'] ?? null;
                                if ($cNode) {
                                    $tablecollections[] = [
                                        'title' => $cNode['title'] ?? '-',
                                        'handle' => $cNode['handle'] ?? null,
                                        'id' => $cNode['id'] ?? null,
                                    ];
                                }
                            }
                        }
                    @endphp
                    <tr>
                        <td><input type="checkbox" class="row-check" data-product-id="{{ $node['id'] }}"></td>
                        <td>
                            <img src="{{ $image }}" alt="" width="40">
                            {{ $node['title'] }}
                        </td>
                        <td>
                            <span class="status {{ strtolower($node['status']) }}">
                                {{ $node['status'] }}
                            </span>
                        </td>
                        <td>
                            {{ $node['totalInventory'] ?? 0 }} in stock
                            for {{ $node['variantsCount']['count'] ?? 0 }} variants
                        </td>
                        <td>{{ $node['vendor'] ?? '-' }}</td>
                        <td>{{ $node['productType'] ?? '-' }}</td>
                        <td>{{ !empty($node['tags']) ? implode(', ', $node['tags']) : '-' }}</td>
                        <td>
                            @if (!empty($tablecollections))
                                @foreach ($tablecollections as $c)
                                    <span data-collection-id="{{ $c['id'] }}">{{ $c['title'] }}</span>
                                    @if (!$loop->last), @endif
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <!-- Pagination -->
        <div id="paginationContainer">
            @if(isset($pageInfo) && (($pageInfo['hasNextPage'] ?? false) || ($pageInfo['hasPreviousPage'] ?? false)))
                <div class="pagination-container">
                    @if($pageInfo['hasPreviousPage'])
                        <button class="Polaris-Button Polaris-Button--sizeSlim" data-cursor="{{ $pageInfo['startCursor'] }}"
                            data-direction="prev" onclick="loadPageWithData(this)">
                            ← Previous
                        </button>
                    @endif
                    @if($pageInfo['hasNextPage'])
                        <button class="Polaris-Button Polaris-Button--sizeSlim" data-cursor="{{ $pageInfo['endCursor'] }}"
                            data-direction="next" onclick="loadPageWithData(this)">
                            Next →
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>


    <script src="https://unpkg.com/@shopify/app-bridge@3.7.4"></script>
    <script src="https://unpkg.com/@shopify/app-bridge-utils@3.7.4"></script>
    <script src="https://unpkg.com/@shopify/app-bridge-actions@3.7.4"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            const host = new URLSearchParams(window.location.search).get("host");
            const apiKey = "{{ config('shopify-app.api_key') }}";

            if (!apiKey || apiKey.trim() === "") {
                console.error("❌ Missing SHOPIFY_API_KEY in .env or config/shopify-app.php");
                return;
            }


            // ✅ Chuẩn hóa AppBridge
            var AppBridge = window['app-bridge'];
            var AppBridgeUtils = window['app-bridge-utils'];
            var actions = AppBridge.actions;
            const { Toast } = AppBridge.actions;

            // ✅ Chuẩn hóa AppBridge Utils
            const utils = window['appBridgeUtils'] || window['ShopifyAppBridgeUtils'] || window['app-bridge-utils'];
            const getSessionToken = utils?.getSessionToken;

            if (!getSessionToken) {
                console.error("❌ AppBridgeUtils not found or getSessionToken undefined!");
                console.log("window keys:", Object.keys(window));
            }

            let app = AppBridge.createApp({
                apiKey: "{{ config('shopify-app.api_key') }}",
                host: new URLSearchParams(window.location.search).get("host"),
                forceRedirect: true
            });

            // Gán toàn cục
            window.app = app;
            window.getSessionToken = getSessionToken; // <== quan trọng
            window.AppBridgeUtils = utils;

            // // Import các actions
            // const { actions, utilities } = AppBridge;//window['app-bridge']
            // const { Toast } = actions;
            // const AppBridgeUtils = utilities;

            // Hàm toast helper
            window.showToast = function (message, isError = false) {
                try {
                    const toast = Toast.create(app, {
                        message: message,
                        duration: 3000,
                        isError: isError
                    });
                    toast.dispatch(Toast.Action.SHOW);
                } catch (err) {
                    console.warn("⚠️ Toast fallback:", err);
                    alert(message);
                }
            };

            console.log("✅ Shopify AppBridge initialized!");

            // ✅ Hàm gọi từ HTML (global scope)
            window.sendBulkStatus = async function (status) {
                try {
                    const token = await getSessionToken(app); // ✅ dùng hàm từ app-bridge-utils
                    const selectedIds = Array.from(document.querySelectorAll('.row-check:checked'))
                        .map(cb => cb.dataset.productId);

                    if (selectedIds.length === 0) {
                        showToast("Please select at least one product", true);
                        return;
                    }

                    const res = await fetch("/products/bulk-action", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "Authorization": `Bearer ${token}`,
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            product_ids: selectedIds,
                            action: "status",
                            payload: { status },
                            shop: window.SHOP
                        })
                    });

                    if (!res.ok) {
                        showToast("❌ Failed to update status", true);
                        console.error(await res.text());
                        return;
                    }

                    const data = await res.json();
                    showToast("✅ Bulk status updated!");
                } catch (err) {
                    console.error("❌ Error in sendBulkStatus:", err);
                    showToast("⚠️ " + err.message, true);
                }
            };

            console.log("✅ Shopify AppBridge initialized!");

            // ✅ 3. Các hàm và sự kiện khác
            // Checkbox and bulk bar logic
            const bulkBar = document.getElementById("bulkBar");
            const checkboxes = document.querySelectorAll(".row-check");
            const selectAll = document.getElementById("selectAll");
            const selectedCount = document.getElementById("selectedCount");

            function updateBulkBar() {
                const checked = document.querySelectorAll(".row-check:checked").length;
                bulkBar.style.display = checked > 0 ? "flex" : "none";
                selectedCount.textContent = `${checked} selected`;
            }

            checkboxes.forEach((cb) => cb.addEventListener("change", updateBulkBar));
            if (selectAll) {
                selectAll.addEventListener("change", function () {
                    checkboxes.forEach((cb) => (cb.checked = this.checked));
                    updateBulkBar();
                });
            }

            // Toggle search bar
            const toggleSearch = document.getElementById("toggleSearch");
            const searchBar = document.getElementById("searchBar");
            const cancelBtn = document.querySelector(".cancel-btn");

            toggleSearch.addEventListener("click", () => {
                searchBar.style.display = searchBar.style.display === "flex" ? "none" : "flex";
            });
            cancelBtn.addEventListener("click", () => (searchBar.style.display = "none"));

            // Dropdown toggle logic
            document.querySelectorAll(".dropdown-btn, #sortToggle").forEach((btn) => {
                btn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const dropdown = btn.parentElement;
                    dropdown.classList.toggle("show");
                });
            });

            document.querySelectorAll(".dropdown-content, .sort-menu").forEach((content) => {
                content.addEventListener("click", (e) => e.stopPropagation());
            });

            window.addEventListener("click", (e) => {
                document.querySelectorAll(".dropdown, .sort-dropdown").forEach((dd) => {
                    if (!dd.contains(e.target)) dd.classList.remove("show");
                });
            });

            document.querySelectorAll(".clear").forEach((clearBtn) => {
                clearBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    const dropdown = clearBtn.closest(".dropdown-content");
                    dropdown.querySelectorAll("input").forEach((input) => (input.checked = false));
                });
            });

            // Filter chips logic
            const activeFilters = document.getElementById("activeFilters");
            const clearAllBtn = document.getElementById("clearAll");

            document.querySelectorAll(".dropdown-content input").forEach((input) => {
                input.addEventListener("change", () => {
                    const type = input.closest(".dropdown").querySelector(".dropdown-btn").textContent.split(" ")[0];
                    const value = input.value;

                    if (input.type === "checkbox" && input.checked) {
                        addFilterChip(type, value, input);
                    } else if (input.type === "radio") {
                        document.querySelectorAll(`.filter-chip[data-type="${type}"]`).forEach((chip) => chip.remove());
                        if (input.checked) addFilterChip(type, value, input);
                    } else {
                        const chip = document.querySelector(`.filter-chip[data-type="${type}"][data-value="${value}"]`);
                        if (chip) chip.remove();
                    }
                });
            });

            function addFilterChip(type, value, inputEl) {
                const chip = document.createElement("div");
                chip.className = "filter-chip";
                chip.dataset.type = type;
                chip.dataset.value = value;
                chip.innerHTML = `${type}: ${value} <span class="remove">×</span>`;

                chip.querySelector(".remove").addEventListener("click", () => {
                    chip.remove();
                    inputEl.checked = false;
                });

                activeFilters.appendChild(chip);
            }

            clearAllBtn.addEventListener("click", () => {
                activeFilters.innerHTML = "";
                document.querySelectorAll(".dropdown-content input").forEach((i) => (i.checked = false));
                applyFilters();
            });

            // Sort and collection change
            document.querySelectorAll(".sort-menu input").forEach((input) => {
                input.addEventListener("change", () => applyFilters());
            });

            document.querySelectorAll("input[name='collection']").forEach((input) => {
                input.addEventListener("change", () => {
                    if (input.checked) {
                        document.querySelectorAll("#searchBar input").forEach((i) => {
                            if (i.name !== "collection") {
                                i.checked = false;
                                if (i.type === 'text') i.value = "";
                            }
                        });
                        document.getElementById("activeFilters").innerHTML = "";
                        applyFilters();
                    }
                });
            });

            // Pagination and apply filters
            async function loadPageWithData(button) {
                const cursor = button.dataset.cursor || "";
                const direction = button.dataset.direction || "";
                applyFilters(cursor, direction);
            }

            async function applyFilters(cursor = "", direction = "") {
                try {
                    const token = await AppBridgeUtils.getSessionToken(app);
                    const url = new URL("/products", window.location.origin);
                    if (HOST) url.searchParams.set("host", HOST);
                    if (SHOP) url.searchParams.set("shop", SHOP);
                    url.searchParams.set("embedded", "1");

                    document.querySelectorAll("#searchBar input").forEach((input) => {
                        if ((input.type === "checkbox" || input.type === "radio") && !input.checked) return;
                        if (input.value) url.searchParams.set(input.name, input.value);
                    });

                    const collection = document.querySelector("input[name='collection']:checked");
                    if (collection) url.searchParams.set("collection", collection.value);

                    const sort = document.querySelector(".sort-menu input[name='sort']:checked");
                    const order = document.querySelector(".sort-menu input[name='order']:checked");
                    if (sort) url.searchParams.set("sort", sort.value);
                    if (order) url.searchParams.set("order", order.value);

                    if (cursor) {
                        if (direction === "prev") {
                            url.searchParams.set("before", cursor);
                            url.searchParams.delete("after");
                        } else if (direction === "next") {
                            url.searchParams.set("after", cursor);
                            url.searchParams.delete("before");
                        }
                    } else {
                        url.searchParams.delete("after");
                        url.searchParams.delete("before");
                    }

                    const response = await fetch(url.toString(), {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                            "Authorization": `Bearer ${token}`,
                        },
                    });
                    if (!response.ok) throw new Error(`Status ${response.status}`);
                    const data = await response.json();

                    const tableContainer = document.querySelector("#productTableBody");
                    if (tableContainer && data.table) tableContainer.outerHTML = data.table;

                    const paginationContainer = document.querySelector("#paginationContainer");
                    if (paginationContainer && data.pagination) paginationContainer.innerHTML = data.pagination;

                    window.history.pushState({}, "", url.pathname + "?" + url.searchParams.toString());

                    bindRowCheckboxes();
                } catch (err) {
                    console.error("Error fetching products:", err);
                    showToast("⚠️ Error loading products: " + err.message, true);
                }
            }

            function bindRowCheckboxes() {
                const checkboxes = document.querySelectorAll(".row-check");
                function updateBulkBar() {
                    const checked = document.querySelectorAll(".row-check:checked").length;
                    if (bulkBar) bulkBar.style.display = checked > 0 ? "flex" : "none";
                    if (selectedCount) selectedCount.textContent = `${checked} selected`;
                }
                checkboxes.forEach((cb) => cb.addEventListener("change", updateBulkBar));
                if (selectAll) {
                    const newSelectAll = selectAll.cloneNode(true);
                    selectAll.parentNode.replaceChild(newSelectAll, selectAll);
                    newSelectAll.addEventListener("change", function () {
                        document.querySelectorAll(".row-check").forEach((cb) => (cb.checked = this.checked));
                        updateBulkBar();
                    });
                }

                // ✅ Thêm đoạn này: click vào hàng thì chọn sản phẩm
                rows.forEach((row) => {
                    row.addEventListener("click", (e) => {
                        // Không click trúng checkbox (để tránh double toggle)
                        if (e.target.tagName.toLowerCase() === "input") return;
                        const checkbox = row.querySelector(".row-check");
                        if (checkbox) {
                            checkbox.checked = !checkbox.checked;
                            updateBulkBar();
                        }
                    });
                });
            }

            // Search and apply filters
            const searchInput = document.getElementById("searchInput");
            if (searchInput) searchInput.addEventListener("keyup", (e) => { if (e.key === "Enter") applyFilters(); });
            const applyBtn = document.querySelector(".apply-filter");
            if (applyBtn) applyBtn.addEventListener("click", () => applyFilters());

            document.querySelectorAll('.sort-menu input[name="sort"], .sort-menu input[name="order"]').forEach((el) =>
                el.addEventListener("change", () => applyFilters()));

            const clearAllInputsBtn = document.getElementById("clearAll");
            if (clearAllInputsBtn) clearAllInputsBtn.addEventListener("click", () => {
                document.querySelectorAll("input, select").forEach((el) => {
                    if (el.type === "checkbox" || el.type === "radio") el.checked = false;
                    else if (el.type === "text" || el.type === "search") el.value = "";
                    else if (el.tagName === "SELECT") el.selectedIndex = 0;
                });
                applyFilters();
            });

            // Bulk status actions
            const btnSetDraft = document.querySelector('button[onclick="sendBulkStatus(\'DRAFT\')"]');
            const btnSetActive = document.querySelector('button[onclick="sendBulkStatus(\'ACTIVE\')"]');
            const btnSetArchive = document.querySelector('button[onclick="sendBulkStatus(\'ARCHIVED\')"]');

            if (btnSetDraft) btnSetDraft.addEventListener('click', () => sendBulkStatus('DRAFT'));
            if (btnSetActive) btnSetActive.addEventListener('click', () => sendBulkStatus('ACTIVE'));
            if (btnSetArchive) btnSetArchive.addEventListener('click', () => sendBulkStatus('ARCHIVED'));


            // Modal and tags/collections actions
            let currentType = null;
            let currentAction = null;
            const tagsData = @json($tags ?? []);
            const collectionsData = @json($collections ?? []);

            window.openModal = function (type, action) {
                currentType = type;
                currentAction = action;
                const count = document.querySelectorAll(".row-check:checked").length;
                document.getElementById("modalTitle").textContent = `${capitalize(action)} ${type} for ${count} product(s)`;
                document.getElementById("saveBtn").textContent = capitalize(action) + ' ' + capitalize(type);
                const listContainer = document.getElementById("itemList");
                listContainer.innerHTML = "";
                const data = type === 'tags' ? tagsData : collectionsData;
                data.forEach(item => {
                    const name = typeof item === 'object' ? (type === 'tags' ? (item.node ?? '') : (item.node?.title ?? '')) : item;
                    const id = typeof item === 'object' && type === 'collections' ? (item.node?.id ?? '') : name;
                    const label = document.createElement("label");
                    label.className = "tag-item";
                    if (type === "collections") {
                        label.innerHTML = `<input type="radio" name="modal_collection" value="${id}"><span>${name}</span>`;
                    } else {
                        label.innerHTML = `<input type="checkbox" name="modal_tags[]" value="${id}"><span>${name}</span>`;
                    }
                    listContainer.appendChild(label);
                });
                document.getElementById("actionModal").style.display = "flex";
            };

            window.closeModal = function () {
                document.getElementById("actionModal").style.display = "none";
            };

            window.filterItems = function () {
                const input = document.getElementById("searchInput").value.toLowerCase();
                document.querySelectorAll("#itemList label").forEach(label => {
                    const value = label.querySelector("input").value.toLowerCase();
                    label.style.display = value.includes(input) ? "" : "none";
                });
            };

            window.saveAction = async function () {
                try {
                    const token = await AppBridgeUtils.getSessionToken(app);
                    let selectedProducts = Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.dataset.productId);
                    if (selectedProducts.length === 0) { alert("Please select at least one product"); return; }
                    let selectedItems = [];
                    if (currentType === "collections") {
                        const radio = document.querySelector(`#itemList input[type=radio]:checked`);
                        if (radio) selectedItems.push(radio.value);
                    } else {
                        selectedItems = Array.from(document.querySelectorAll(`#itemList input[type=checkbox]:checked`)).map(el => el.value);
                    }
                    if (selectedItems.length === 0) { alert(`Please select at least one ${currentType}`); return; }
                    let actionName;
                    if (currentType === "tags") actionName = currentAction === "add" ? "add_tags" : "remove_tags";
                    else if (currentType === "collections") actionName = currentAction === "add" ? "add_collection" :
                        "remove_collection";
                    const payload = {};
                    if (currentType === "tags") payload.tags = selectedItems; else payload.collection_id = selectedItems[0];

                    if (currentType === "collections") {
                        const targetCollectionId = selectedItems[0];
                        selectedProducts = selectedProducts.filter(pid => {
                            const row = document.querySelector(`.row-check[data-product-id="${pid}"]`)?.closest("tr");
                            if (!row) return false;
                            const rowCollections = Array.from(row.querySelectorAll("[data-collection-id]")).map(el => el.dataset.collectionId);
                            if (currentAction === "add") return !rowCollections.includes(targetCollectionId);
                            if (currentAction === "remove") return rowCollections.includes(targetCollectionId);
                            return true;
                        });
                        if (selectedProducts.length === 0) { alert("No valid products for this action (all filtered out)."); return; }
                    }

                    const res = await fetch("/products/bulk-action", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "Authorization": `Bearer ${token}`,
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ product_ids: selectedProducts, action: actionName, payload: payload, shop: SHOP })
                    });

                    if (!res.ok) {
                        const text = await res.text();
                        console.error("Request failed:", text);
                        showToast("❌ Failed to apply action", true);
                        return;
                    }

                    const data = await res.json();
                    if (data.batch_id) {
                        let finished = false;
                        showToast("⏳ Processing... Please wait");
                        while (!finished) {
                            const statusRes = await fetch(`/products/bulk-action/status/${data.batch_id}`);
                            const statusData = await statusRes.json();
                            if (statusData.finished) {
                                finished = true;
                                showToast("✅ Action completed successfully!");
                            } else if (statusData.failed) {
                                showToast("❌ Some jobs failed!");
                                break;
                            } else {
                                await new Promise(resolve => setTimeout(resolve, 1000));
                            }
                        }
                    }
                    closeModal();
                    document.querySelectorAll("input[name='collection']").forEach(input => input.checked = false);
                    applyFilters();
                } catch (err) {
                    console.error(err);
                    showToast("⚠️ Error: " + err.message, true);
                }
            };

            function capitalize(text) {
                return text.charAt(0).toUpperCase() + text.slice(1);
            };
        });
    </script>
</body>

</html>