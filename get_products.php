<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    http_response_code(500); // ส่งสถานะ 500 (Internal Server Error)
    echo json_encode(["message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // ตรวจสอบว่ามี ID ถูกส่งมาหรือไม่
    if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        $id = intval($_GET['id']);
        
        // ใช้ Prepared Statement สำหรับดึงข้อมูลสินค้าตาม ID
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            http_response_code(200); // ส่งสถานะ 200 (OK)
            echo json_encode($product);
        } else {
            http_response_code(404); // ส่งสถานะ 404 (Not Found)
            echo json_encode(["message" => "Product not found"]);
        }
        $stmt->close();
    } else {
        // ดึงข้อมูลสินค้าทั้งหมด
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $products = array();
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            http_response_code(200); // ส่งสถานะ 200 (OK)
            echo json_encode($products);
        } else {
            http_response_code(404); // ส่งสถานะ 404 (Not Found)
            echo json_encode(["message" => "No products found"]);
        }
    }
} elseif ($method == 'DELETE') {
    // ตรวจสอบว่ามี ID ถูกส่งมาหรือไม่
    if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        $id = intval($_GET['id']);
        
        // ใช้ Prepared Statement สำหรับลบสินค้าตาม ID
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            http_response_code(200); // ส่งสถานะ 200 (OK)
            echo json_encode(["message" => "Product deleted successfully"]);
        } else {
            http_response_code(500); // ส่งสถานะ 500 (Internal Server Error)
            echo json_encode(["message" => "Error deleting product: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        http_response_code(400); // ส่งสถานะ 400 (Bad Request)
        echo json_encode(["message" => "No valid product ID provided"]);
    }
} else {
    http_response_code(405); // ส่งสถานะ 405 (Method Not Allowed)
    echo json_encode(["message" => "Method not allowed"]);
}

$conn->close();
?>
