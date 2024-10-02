<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

// รับข้อมูลจาก PUT request
$data = json_decode(file_get_contents("php://input"), true);
$order_id = $data['order_id'];
$delivery_status = $data['delivery_status'];
$payment_status = $data['payment_status'];

// อัปเดตสถานะคำสั่งซื้อ
$query = $conn->prepare("UPDATE orders SET delivery_status = ?, payment_status = ? WHERE id = ?");
$query->bind_param("ssi", $delivery_status, $payment_status, $order_id);
if ($query->execute()) {
    echo json_encode(["message" => "Order updated successfully"]);
} else {
    echo json_encode(["message" => "Error updating order: " . $conn->error]);
}

$query->close();
$conn->close();
?>
