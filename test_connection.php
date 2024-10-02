<?php
// เชื่อมต่อกับฐานข้อมูล MySQL
$conn = new mysqli("localhost", "root", "", "test"); // 'localhost' คือที่อยู่เซิร์ฟเวอร์, 'root' คือ username, '' คือ password (กรณีไม่มีรหัสผ่าน), 'test' คือชื่อฐานข้อมูล

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully";
?>
