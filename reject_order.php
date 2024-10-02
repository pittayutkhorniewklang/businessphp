<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

// รับ order_id จาก POST request
$data = json_decode(file_get_contents("php://input"), true);
$order_id = $data['order_id'];

// อัปเดตสถานะคำสั่งซื้อเป็น 'cancelled'
$query = $conn->prepare("UPDATE orders SET delivery_status = 'cancelled' WHERE id = ?");
$query->bind_param("i", $order_id);
if ($query->execute()) {
    echo json_encode(["message" => "Order rejected successfully"]);
} else {
    echo json_encode(["message" => "Error rejecting order: " . $conn->error]);
}

$query->close();
$conn->close();
?>
