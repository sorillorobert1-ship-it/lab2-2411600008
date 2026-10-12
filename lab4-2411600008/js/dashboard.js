const Dashboard = (() => {

    const state = {
        category: "",
        status: "",
        min: "",
        max: "",
        query: ""
    };

    const $ = id => document.getElementById(id);

    const esc = s =>
        String(s).replace(
            /[&<>"']/g,
            c => ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;"
            }[c])
        );

    function highlight(text, query) {
        const s = esc(text);
        const x = esc(query || "").trim();

        if (!x) return s;

        return s.replace(
            new RegExp(
                "(" +
                x.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") +
                ")",
                "ig"
            ),
            "<mark>$1</mark>"
        );
    }

    function populateCategories() {
        const select = $("categoryFilter");
        const current = state.category;

        select.innerHTML = '<option value="">All Categories</option>';

        [
            ...new Set(
                DataManager
                    .getProducts()
                    .map(p => p.category)
            )
        ]
            .sort()
            .forEach(category => {
                const option = document.createElement("option");

                option.value = category;
                option.textContent = category;

                select.appendChild(option);
            });

        select.value = current;
    }

    function updateSearchResults(query) {
        state.query = query;
        refresh();
    }

    function renderTable(data) {

        $("inventoryTableBody").innerHTML = data.length

            ? data.map(product => {

                const status = DataManager.statusOf(product);

                const rowClass =
                    status === "out-of-stock"
                        ? "table-danger"
                        : status === "low-stock"
                            ? "table-warning"
                            : "";

                const badgeClass =
                    status === "in-stock"
                        ? "success"
                        : status === "low-stock"
                            ? "warning"
                            : "danger";

                return `
                    <tr class="${rowClass}">
                        <td>${highlight(product.sku, state.query)}</td>
                        <td>${highlight(product.name, state.query)}</td>
                        <td>${esc(product.category)}</td>
                        <td>${product.quantity}</td>
                        <td>${product.reorderLevel}</td>
                        <td>₱${(+product.unitPrice).toLocaleString()}</td>
                        <td>
                            <span class="badge text-bg-${badgeClass}">
                                ${status.replaceAll("-", " ")}
                            </span>
                        </td>
                    </tr>
                `;

            }).join("")

            : `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No matching products.
                    </td>
                </tr>
            `;
    }

    function updateStats() {

        const products = DataManager.getProducts();
        const lowStock = DataManager.getLowStockProducts();

        $("totalProducts").textContent =
            products.length;

        $("totalQuantity").textContent =
            products
                .reduce((sum, product) => sum + product.quantity, 0)
                .toLocaleString();

        $("inventoryValue").textContent =
            "₱" +
            products
                .reduce(
                    (sum, product) =>
                        sum + product.quantity * product.unitPrice,
                    0
                )
                .toLocaleString();

        $("lowStockStat").textContent =
            lowStock.length;

        $("lowStockCount").textContent =
            lowStock.length;

        $("lowStockBanner").classList.toggle(
            "d-none",
            !lowStock.length
        );
    }

    function renderAlerts() {

        const lowStock =
            DataManager.getLowStockProducts();

        $("alertsList").innerHTML = lowStock.length

            ? lowStock.map(product => `
                <div class="alert ${
                    product.quantity === 0
                        ? "alert-danger"
                        : "alert-warning"
                } py-2 mb-2">

                    <strong>${esc(product.name)}</strong>
                    (${esc(product.sku)}) —

                    ${
                        product.quantity === 0
                            ? "OUT OF STOCK"
                            : `only ${product.quantity} left;
                               reorder at ${product.reorderLevel}`
                    }

                </div>
            `).join("")

            : `
                <div class="text-success">
                    ✓ No low-stock products.
                </div>
            `;
    }

    function renderChanges() {

        const changes =
            DataManager.getChanges();

        $("changesList").innerHTML = changes.length

            ? changes.map(change => `
                <div class="change-item">
                    <strong>${esc(change.time)}</strong>
                    <br>
                    ${esc(change.text)}
                </div>
            `).join("")

            : `
                <div class="text-muted">
                    No changes yet.
                </div>
            `;
    }

    function refresh() {

        const data =
            DataManager.applyFilters(state);

        renderTable(data);
        updateStats();
        renderAlerts();
        renderChanges();

        ChartManager.render(data);
    }

    function toast(message) {

        $("toastMessage").textContent = message;

        bootstrap.Toast
            .getOrCreateInstance($("appToast"))
            .show();
    }

    async function simulateUpdate() {

        const products =
            DataManager.getProducts();

        if (!products.length) return;

        const product =
            products[
                Math.floor(
                    Math.random() * products.length
                )
            ];

        const quantity =
            Math.max(
                0,
                +product.quantity +
                (Math.random() > 0.5 ? 1 : -1)
            );

        try {

            await DataManager.updateStock(
                product.id,
                quantity
            );

            toast(
                `Backend updated: ${product.name} → ${quantity}`
            );

            refresh();

        } catch (error) {

            toast(
                "Update failed: " +
                error.message
            );
        }
    }

    async function handleAddDemoProduct() {

        try {

            const product =
                await DataManager.addProduct({

                    sku: "DEMO-" + Date.now(),

                    name: "Demo Guest Amenities",

                    category: "Toiletries",

                    supplier: "Robert Supplier",

                    quantity: 25,

                    reorderLevel: 10,

                    unitPrice: 80
                });

            toast(
                "Added to backend: " +
                product.name +
                " (Products: " +
                DataManager.getProducts().length +
                ")"
            );

            populateCategories();
            refresh();

        } catch (error) {

            toast(
                "Add failed: " +
                error.message
            );
        }
    }

    function bind() {

        const on = (id, event, functionHandler) => {
            $(id).addEventListener(
                event,
                functionHandler
            );
        };

        on(
            "categoryFilter",
            "change",
            event => {
                state.category =
                    event.target.value;

                refresh();
            }
        );

        on(
            "statusFilter",
            "change",
            event => {
                state.status =
                    event.target.value;

                refresh();
            }
        );

        on(
            "minPrice",
            "input",
            event => {
                state.min =
                    event.target.value;

                refresh();
            }
        );

        on(
            "maxPrice",
            "input",
            event => {
                state.max =
                    event.target.value;

                refresh();
            }
        );

        on(
            "searchInput",
            "input",
            event => {
                updateSearchResults(
                    event.target.value
                );
            }
        );

        on(
            "clearFilters",
            "click",
            () => {

                Object.assign(
                    state,
                    {
                        category: "",
                        status: "",
                        min: "",
                        max: "",
                        query: ""
                    }
                );

                $("categoryFilter").value = "";
                $("statusFilter").value = "";

                $("minPrice").value = "";
                $("maxPrice").value = "";
                $("searchInput").value = "";

                refresh();
            }
        );

        on(
            "exportCsv",
            "click",
            () => {

                const data =
                    DataManager.applyFilters(state);

                const csv =
                    DataManager.exportToCSV(data);

                DataManager.downloadCSV(
                    csv,
                    "Robert_Hotel_inventory.csv"
                );
            }
        );

        on(
            "simulateUpdate",
            "click",
            simulateUpdate
        );

        on(
            "addDemoProduct",
            "click",
            handleAddDemoProduct
        );
    }

    async function start() {

        bind();

        const result =
            await DataManager.initializeData();

        $("apiStatus").className =
            result.source === "api"
                ? "alert alert-success"
                : "alert alert-warning";

        $("apiStatus").textContent =
            result.source === "api"
                ? "✓ Connected to PHP/JSON backend."
                : "⚠ API unavailable; fallback data is active. Run through XAMPP for backend writes.";

        populateCategories();

        refresh();

        setInterval(
            simulateUpdate,
            30000
        );
    }

    return {
        start
    };

})();

document.addEventListener(
    "DOMContentLoaded",
    Dashboard.start
);
