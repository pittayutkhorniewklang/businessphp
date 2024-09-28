<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

// รับค่า ID จาก query parameters (GET)
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = intval($_GET['id']);

    // SQL สำหรับลบสินค้าตาม ID (ใช้ Prepared Statement เพื่อความปลอดภัย)
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Product deleted successfully"]);
    } else {
        echo json_encode(["message" => "Error deleting product: " . $stmt->error]);
    }

    $stmt->close();
} else {
    // กรณีไม่มี ID ที่ถูกต้องส่งมา
    echo json_encode(["message" => "No valid product ID provided or invalid ID"]);
}

$conn->close();
?>
