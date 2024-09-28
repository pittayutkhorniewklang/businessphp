<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

// รับข้อมูลจาก POST และทำการตรวจสอบค่าที่ได้รับ
$name = isset($_POST['name']) ? $_POST['name'] : null;
$category = isset($_POST['category']) ? $_POST['category'] : null;
$brand = isset($_POST['brand']) ? $_POST['brand'] : null;
$stock = isset($_POST['stock']) ? $_POST['stock'] : null;
$price = isset($_POST['price']) ? $_POST['price'] : null;
$description = isset($_POST['description']) ? $_POST['description'] : null;

if (!$name || !$category || !$brand || !$stock || !$price || !$description) {
    echo json_encode(["message" => "All fields are required"]);
    exit();
}

// ตรวจสอบว่ามีโฟลเดอร์ 'uploads' หรือไม่ ถ้าไม่มีก็สร้างขึ้น
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// จัดการอัปโหลดไฟล์ (หากมีการอัปโหลดไฟล์)
$fileDestination = null;
if (isset($_FILES['file'])) {
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileError = $_FILES['file']['error'];
    $fileSize = $_FILES['file']['size'];

    if ($fileError === 0) {
        if ($fileSize <= 5000000) {
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $allowedExt = ['jpg', 'jpeg', 'png'];
            if (in_array(strtolower($fileExt), $allowedExt)) {
                $newFileName = uniqid('', true) . "." . $fileExt;
                $fileDestination = 'uploads/' . $newFileName;
                move_uploaded_file($fileTmpName, $fileDestination);
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
    echo json_encode(["message" => "Error adding product: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
