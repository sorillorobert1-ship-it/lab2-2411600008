<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}


// JSON file location
$jsonFile = __DIR__ . "/products.json";


// Read JSON file
function readProducts($file)
{
    if (!file_exists($file)) {
        return [];
    }

    $content = file_get_contents($file);

    if ($content === false || trim($content) === "") {
        return [];
    }

    $data = json_decode($content, true);

    return is_array($data) ? $data : [];
}


// Save products to JSON file
function saveProducts($file, $products)
{
    $json = json_encode(
        $products,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    if ($json === false) {
        throw new Exception("Failed to encode JSON data.");
    }

    if (file_put_contents($file, $json, LOCK_EX) === false) {
        throw new Exception(
            "Failed to save products.json. Check folder permissions."
        );
    }
}


// Read request body
function getRequestBody()
{
    $input = file_get_contents("php://input");

    if (!$input) {
        return [];
    }

    $data = json_decode($input, true);

    return is_array($data) ? $data : [];
}


try {

    $method = $_SERVER["REQUEST_METHOD"];

    $products = readProducts($jsonFile);

    $id = isset($_GET["id"])
        ? (int) $_GET["id"]
        : 0;


    // =========================
    // GET - Get all products
    // =========================

    if ($method === "GET") {

        echo json_encode([
            "success" => true,
            "data" => $products
        ]);

        exit;
    }


    // =========================
    // POST - Add product
    // =========================

    if ($method === "POST") {

        $data = getRequestBody();

        $requiredFields = [
            "sku",
            "name",
            "category",
            "supplier",
            "quantity",
            "reorderLevel",
            "unitPrice"
        ];

        foreach ($requiredFields as $field) {

            if (!isset($data[$field])) {

                throw new Exception(
                    "Missing field: " . $field
                );
            }
        }


        // Generate new ID
        $newId = 1;

        if (!empty($products)) {

            $ids = array_map(
                fn($product) => (int) $product["id"],
                $products
            );

            $newId = max($ids) + 1;
        }


        $newProduct = [
            "id" => $newId,
            "sku" => (string) $data["sku"],
            "name" => (string) $data["name"],
            "category" => (string) $data["category"],
            "supplier" => (string) $data["supplier"],
            "quantity" => (int) $data["quantity"],
            "reorderLevel" => (int) $data["reorderLevel"],
            "unitPrice" => (float) $data["unitPrice"]
        ];


        $products[] = $newProduct;

        saveProducts(
            $jsonFile,
            $products
        );


        echo json_encode([
            "success" => true,
            "message" => "Product added successfully.",
            "data" => $newProduct
        ]);

        exit;
    }


    // =========================
    // PUT - Update stock
    // =========================

    if ($method === "PUT") {

        if (!$id) {

            throw new Exception(
                "Product ID is required."
            );
        }


        $data = getRequestBody();


        if (!array_key_exists("quantity", $data)) {

            throw new Exception(
                "Quantity is required."
            );
        }


        $found = false;
        $updatedProduct = null;


        foreach ($products as &$product) {

            if ((int) $product["id"] === $id) {

                $product["quantity"] =
                    max(0, (int) $data["quantity"]);

                $updatedProduct = $product;

                $found = true;

                break;
            }
        }

        unset($product);


        if (!$found) {

            throw new Exception(
                "Product not found."
            );
        }


        saveProducts(
            $jsonFile,
            $products
        );


        echo json_encode([
            "success" => true,
            "message" => "Stock updated successfully.",
            "data" => $updatedProduct
        ]);

        exit;
    }


    // =========================
    // DELETE - Delete product
    // =========================

    if ($method === "DELETE") {

        if (!$id) {

            throw new Exception(
                "Product ID is required."
            );
        }


        $found = false;


        foreach ($products as $index => $product) {

            if ((int) $product["id"] === $id) {

                unset($products[$index]);

                $found = true;

                break;
            }
        }


        if (!$found) {

            throw new Exception(
                "Product not found."
            );
        }


        $products = array_values($products);

        saveProducts(
            $jsonFile,
            $products
        );


        echo json_encode([
            "success" => true,
            "message" => "Product deleted successfully."
        ]);

        exit;
    }


    // Unsupported request
    throw new Exception(
        "Unsupported HTTP method."
    );


} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

?>   