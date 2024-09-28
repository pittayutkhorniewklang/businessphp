<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// รับข้อมูลจาก POST
$product_id = intval($_POST['product_id']);
$quantity_sold = intval($_POST['quantity_sold']);

// ตรวจสอบว่าสินค้ามีอยู่ในฐานข้อมูลหรือไม่และลดจำนวนสต็อก
$sql = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $quantity_sold, $product_id, $quantity_sold);  // ตรวจสอบว่าจำนวน stock มากพอหรือไม่

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Stock reduced successfully"]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Product not found or not enough stock"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["message" => "Error updating stock: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
