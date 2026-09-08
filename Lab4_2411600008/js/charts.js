const ChartManager = (() => {

    const charts = {};

    const GREEN = "#198754";
    const YELLOW = "#ffc107";
    const RED = "#dc3545";
    const BLUE = "#0d6efd";

    const base = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 500
        },
        plugins: {
            legend: {
                position: "bottom"
            }
        }
    };

    const destroy = name => {
        if (charts[name]) {
            charts[name].destroy();
        }
    };

    function render(products) {

        // Inventory Value by Category
        const summary = DataManager.getCategorySummary();
        const categories = Object.keys(summary);

        destroy("v");

        charts.v = new Chart(
            categoryValueChart,
            {
                type: "bar",

                data: {
                    labels: categories,

                    datasets: [
                        {
                            label: "Inventory Value (₱)",
                            data: categories.map(
                                category => summary[category].value
                            ),
                            backgroundColor: GREEN
                        }
                    ]
                },

                options: {
                    ...base,

                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            }
        );


        // Stock Status Distribution
        const stock = DataManager.getStockStatistics();

        destroy("st");

        charts.st = new Chart(
            stockStatusChart,
            {
                type: "doughnut",

                data: {
                    labels: [
                        "In Stock",
                        "Low Stock",
                        "Out of Stock"
                    ],

                    datasets: [
                        {
                            data: [
                                stock["in-stock"],
                                stock["low-stock"],
                                stock["out-of-stock"]
                            ],

                            backgroundColor: [
                                GREEN,
                                YELLOW,
                                RED
                            ]
                        }
                    ]
                },

                options: base
            }
        );


        // Top Products by Value
        const top = [...products]
            .sort(
                (a, b) =>
                    b.quantity * b.unitPrice -
                    a.quantity * a.unitPrice
            )
            .slice(0, 8);

        destroy("t");

        charts.t = new Chart(
            topProductsChart,
            {
                type: "bar",

                data: {
                    labels: top.map(
                        product => product.name
                    ),

                    datasets: [
                        {
                            label: "Value (₱)",

                            data: top.map(
                                product =>
                                    product.quantity *
                                    product.unitPrice
                            ),

                            backgroundColor: BLUE
                        }
                    ]
                },

                options: {
                    ...base,
                    indexAxis: "y"
                }
            }
        );


        // Category Quantity Distribution
        destroy("q");

        charts.q = new Chart(
            categoryQuantityChart,
            {
                type: "bar",

                data: {
                    labels: categories,

                    datasets: [
                        {
                            label: "Quantity",

                            data: categories.map(
                                category =>
                                    summary[category].quantity
                            ),

                            backgroundColor: YELLOW
                        }
                    ]
                },

                options: {
                    ...base,

                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            }
        );
    }

    return {
        render
    };

})();