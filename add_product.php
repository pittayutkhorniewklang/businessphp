<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["message" => "Only POST requests are allowed"]);
    exit();
}

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

// ตรวจสอบการรับข้อมูลจาก POST และ FILES
var_dump($_POST);
var_dump($_FILES);
exit();  // หยุดการทำงานชั่วคราวเพื่อตรวจสอบข้อมูลที่ส่งมาว่าถูกต้องหรือไม่

// รับข้อมูลจาก POST
$name = isset($_POST['name']) ? $_POST['name'] : null;
$category = isset($_POST['category']) ? $_POST['category'] : null;
$brand = isset($_POST['brand']) ? $_POST['brand'] : null;
$stock = isset($_POST['stock']) ? intval($_POST['stock']) : null;  // แปลงเป็น int
$price = isset($_POST['price']) ? floatval($_POST['price']) : null;  // แปลงเป็น float
$description = isset($_POST['description']) ? $_POST['description'] : null;

// ตรวจสอบข้อมูลที่รับมา
if (!$name || !$category || !$brand || !$stock || !$price || !$description) {
    echo json_encode(["message" => "All fields are required"]);
    exit();
}

// ตรวจสอบว่ามีโฟลเดอร์ 'uploads' หรือไม่
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// จัดการอัปโหลดไฟล์
$fileDestination = null;
if (isset($_FILES['file'])) {
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileError = $_FILES['file']['error'];
    $fileSize = $_FILES['file']['size'];

    // ตรวจสอบว่าการอัปโหลดไฟล์มีปัญหาหรือไม่
    if ($fileError === 0) {
        if ($fileSize <= 5000000) {
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $allowedExt = ['jpg', 'jpeg', 'png'];
            if (in_array(strtolower($fileExt), $allowedExt)) {
                $newFileName = uniqid('', true) . "." . $fileExt;
                $fileDestination = 'uploads/' . $newFileName;
                if (!move_uploaded_file($fileTmpName, $fileDestination)) {
                    echo json_encode(["message" => "Error moving uploaded file"]);
                    exit();
                }
            } else {
                echo json_encode(["message" => "File type not allowed"]);
                exit();
            }
        } else {
            echo json_encode(["message" => "File size is too large"]);
            exit();
        }
    } else {
        echo json_encode(["message" => "Error uploading file"]);
        exit();
    }
}

// SQL สำหรับเพิ่มสินค้า
$stmt = $conn->prepare("INSERT INTO products (name, category, brand, stock, price, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssisss", $name, $category, $brand, $stock, $price, $description, $fileDestination);

if ($stmt->execute()) {
    echo json_encode(["message" => "Product added successfully"]);
} else {
    error_log("Error executing SQL: " . $stmt->error); // บันทึก log ข้อผิดพลาด SQL
    echo json_encode(["message" => "Error adding product: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
