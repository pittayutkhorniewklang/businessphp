<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

// ดึงข้อมูลจากตาราง orders และ order_items
$query = "
    SELECT o.id, o.customer_name, o.order_date, o.delivery_date, o.delivery_price, o.delivery_status, o.payment_status, 
           GROUP_CONCAT(CONCAT(oi.product_name, ' (', oi.quantity, ' x ', oi.price, ')') SEPARATOR ', ') AS order_items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
";
$result = $conn->query($query);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// ส่งข้อมูลกลับในรูปแบบ JSON
echo json_encode($orders);

$conn->close();
?>
