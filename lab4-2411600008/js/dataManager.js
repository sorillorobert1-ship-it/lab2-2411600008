const DataManager = (() => {

    let products = [];
    let changes = [];

    const API_URL = "api/products.php";


    // Fallback Data
    const fallbackData = [
        [1, "HSK-001", "Bath Towels", "Housekeeping", "CleanPro", 45, 20, 350],
        [2, "HSK-002", "Bed Sheets", "Housekeeping", "LinenHub", 28, 15, 650],
        [3, "HTL-001", "Shampoo", "Toiletries", "FreshCare", 18, 25, 95],
        [4, "HTL-002", "Soap", "Toiletries", "FreshCare", 60, 30, 45],
        [5, "FNB-001", "Bottled Water", "Food & Beverage", "AquaPure", 90, 40, 25],
        [6, "FNB-002", "Coffee Beans", "Food & Beverage", "BeanWorks", 14, 20, 480],
        [7, "FNB-003", "Tea Bags", "Food & Beverage", "TeaHouse", 55, 20, 160],
        [8, "MNT-001", "LED Bulbs", "Maintenance", "BrightTech", 12, 15, 120],
        [9, "MNT-002", "Cleaning Gloves", "Maintenance", "SafeHands", 35, 20, 75],
        [10, "MNT-003", "Aircon Filter", "Maintenance", "CoolAir", 7, 10, 450],
        [11, "LND-001", "Laundry Detergent", "Laundry", "WashPro", 22, 15, 280],
        [12, "LND-002", "Fabric Softener", "Laundry", "WashPro", 9, 12, 310],
        [13, "HSK-003", "Pillow Cases", "Housekeeping", "LinenHub", 8, 12, 180],
        [14, "HTL-003", "Toothpaste", "Toiletries", "FreshCare", 0, 10, 60],
        [15, "FNB-004", "Canned Juice", "Food & Beverage", "DrinkCo", 6, 10, 55]
    ].map(product => ({
        id: product[0],
        sku: product[1],
        name: product[2],
        category: product[3],
        supplier: product[4],
        quantity: product[5],
        reorderLevel: product[6],
        unitPrice: product[7]
    }));


    // Determine Stock Status
    const statusOf = product =>
        product.quantity === 0
            ? "out-of-stock"
            : product.quantity <= product.reorderLevel
                ? "low-stock"
                : "in-stock";


    // Initialize Data
    async function initializeData() {

        try {

            const response = await fetch(API_URL);

            if (!response.ok) {
                throw Error("API " + response.status);
            }

            const json = await response.json();

            if (!json.success) {
                throw Error(json.message);
            }

            products = json.data;

            return {
                source: "api"
            };

        } catch (error) {

            products = fallbackData.map(
                product => ({ ...product })
            );

            return {
                source: "fallback",
                error: error.message
            };
        }
    }


    // Get Products
    const getProducts = () => [...products];


    // Get Product by ID
    const getProductById = id =>
        products.find(
            product => +product.id === +id
        );


    // Get Products by Category
    const getProductsByCategory = category =>
        products.filter(
            product =>
                !category ||
                product.category === category
        );


    // Get Low Stock Products
    const getLowStockProducts = () =>
        products.filter(
            product =>
                statusOf(product) !== "in-stock"
        );


    // Stock Statistics
    const getStockStatistics = () => {

        const statistics = {
            "in-stock": 0,
            "low-stock": 0,
            "out-of-stock": 0
        };

        products.forEach(product => {
            statistics[statusOf(product)]++;
        });

        return statistics;
    };


    // Category Summary
    const getCategorySummary = () => {

        const summary = {};

        products.forEach(product => {

            summary[product.category] ??= {
                quantity: 0,
                value: 0
            };

            summary[product.category].quantity +=
                +product.quantity;

            summary[product.category].value +=
                product.quantity * product.unitPrice;
        });

        return summary;
    };


    // Filter by Category
    const filterByCategory = category =>
        getProductsByCategory(category);


    // Filter by Stock Status
    const filterByStockStatus = status =>
        products.filter(
            product =>
                !status ||
                statusOf(product) === status
        );


    // Filter by Price Range
    const filterByPriceRange = (min, max) =>
        products.filter(
            product =>
                (min === "" || product.unitPrice >= +min) &&
                (max === "" || product.unitPrice <= +max)
        );


    // Search Products
    const searchProducts = query => {

        query = String(query || "")
            .toLowerCase()
            .trim();

        if (!query) {
            return getProducts();
        }

        return products.filter(
            product =>
                product.name
                    .toLowerCase()
                    .includes(query) ||

                product.sku
                    .toLowerCase()
                    .includes(query)
        );
    };


    // Apply All Filters
    const applyFilters = ({
        category = "",
        status = "",
        min = "",
        max = "",
        query = ""
    } = {}) => {

        return products.filter(product =>

            (!category ||
                product.category === category)

            &&

            (!status ||
                statusOf(product) === status)

            &&

            (min === "" ||
                product.unitPrice >= +min)

            &&

            (max === "" ||
                product.unitPrice <= +max)

            &&

            (
                !query ||
                product.name
                    .toLowerCase()
                    .includes(query.toLowerCase())

                ||

                product.sku
                    .toLowerCase()
                    .includes(query.toLowerCase())
            )
        );
    };


    // Update Stock
    async function updateStock(id, quantity) {

        const response = await fetch(
            `${API_URL}?id=${encodeURIComponent(id)}`,
            {
                method: "PUT",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    quantity: +quantity
                })
            }
        );

        const json = await response.json();

        if (!response.ok || !json.success) {
            throw Error(
                json.message ||
                "Stock update failed"
            );
        }

        const index = products.findIndex(
            product => +product.id === +id
        );

        if (index >= 0) {
            products[index] = json.data;
        }

        changes.unshift({
            time: new Date().toLocaleString(),
            text:
                `${json.data.name}: stock updated to ${json.data.quantity}`
        });

        changes = changes.slice(0, 8);

        return json.data;
    }


    // Add Product
    async function addProduct(product) {

        try {

            const response = await fetch(
                API_URL,
                {
                    method: "POST",

                    headers: {
                        "Content-Type": "application/json"
                    },

                    body: JSON.stringify(product)
                }
            );

            const json = await response.json();

            if (
                !response.ok ||
                !json.success ||
                !json.data
            ) {
                throw Error(
                    json.message ||
                    "Add failed"
                );
            }

            const listResponse =
                await fetch(API_URL)
                    .then(response => response.json())
                    .catch(() => null);

            if (
                listResponse &&
                listResponse.success &&
                Array.isArray(listResponse.data)
            ) {

                products = listResponse.data;

            } else {

                products.push(json.data);
            }

            changes.unshift({
                time: new Date().toLocaleString(),
                text:
                    `Added ${json.data.name}`
            });

            return Object.assign(
                json.data,
                {
                    persisted: true
                }
            );

        } catch (error) {

            const localProduct = {

                id:
                    Math.max(
                        0,
                        ...products.map(
                            product => +product.id
                        )
                    ) + 1,

                sku: product.sku,
                name: product.name,
                category: product.category,
                supplier: product.supplier,
                quantity: +product.quantity,
                reorderLevel: +product.reorderLevel,
                unitPrice: +product.unitPrice
            };

            products.push(localProduct);

            changes.unshift({
                time: new Date().toLocaleString(),
                text:
                    `Added ${localProduct.name}`
            });

            return Object.assign(
                localProduct,
                {
                    persisted: false,
                    error: error.message
                }
            );
        }
    }


    // Get Changes
    const getChanges = () =>
        [...changes];


    // Export CSV
    const exportToCSV = data => {

        const headers = [
            "ID",
            "SKU",
            "Product",
            "Category",
            "Supplier",
            "Quantity",
            "Reorder Level",
            "Unit Price",
            "Stock Status"
        ];

        const escapeValue = value =>
            `"${String(value ?? "")
                .replaceAll('"', '""')}"`;

        return [
            headers,
            ...data.map(product => [
                product.id,
                product.sku,
                product.name,
                product.category,
                product.supplier,
                product.quantity,
                product.reorderLevel,
                product.unitPrice,
                statusOf(product)
            ])
        ]
            .map(row =>
                row.map(escapeValue).join(",")
            )
            .join("\n");
    };


    // Download CSV
    const downloadCSV = (content, filename) => {

        const blob = new Blob(
            [content],
            {
                type: "text/csv;charset=utf-8"
            }
        );

        const link =
            document.createElement("a");

        link.href =
            URL.createObjectURL(blob);

        link.download = filename;

        link.click();

        URL.revokeObjectURL(
            link.href
        );
    };


    // Public Functions
    return {

        initializeData,

        getProducts,

        getProductById,

        getProductsByCategory,

        getLowStockProducts,

        getStockStatistics,

        getCategorySummary,

        filterByCategory,

        filterByStockStatus,

        filterByPriceRange,

        applyFilters,

        searchProducts,

        updateStock,

        addProduct,

        getChanges,

        exportToCSV,

        downloadCSV,

        statusOf
    };

})();