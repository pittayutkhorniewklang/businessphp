<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$conn = new mysqli("localhost", "root", "", "test");

if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

// ตรวจสอบว่ามีการรับข้อมูลผ่าน POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'], $_POST['name'], $_POST['category'], $_POST['brand'], $_POST['stock'], $_POST['price'], $_POST['description'])) {

        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $category = $conn->real_escape_string($_POST['category']);
        $brand = $conn->real_escape_string($_POST['brand']);
        $stock = intval($_POST['stock']);
        $price = floatval($_POST['price']);
        $description = $conn->real_escape_string($_POST['description']);

        // ตรวจสอบว่ามีการอัปโหลดไฟล์หรือไม่
        $fileDestination = null;
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileName = $_FILES['file']['name'];
            $fileTmpName = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExt, $allowed)) {
                if ($fileSize <= 5000000) {
                    if (!file_exists('uploads')) {
                        mkdir('uploads', 0777, true);
                    }

                    $newFileName = uniqid('', true) . "." . $fileExt;
                    $fileDestination = 'uploads/' . $newFileName;
                    move_uploaded_file($fileTmpName, $fileDestination);
                } else {
                    echo json_encode(["message" => "File size too large. Maximum is 5MB"]);
                    exit();
                }
            } else {
                echo json_encode(["message" => "Invalid file type"]);
                exit();
            }
        }

        // SQL สำหรับแก้ไขสินค้า
        if ($fileDestination) {
            $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, brand = ?, stock = ?, price = ?, description = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sssisssi", $name, $category, $brand, $stock, $price, $description, $fileDestination, $id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, brand = ?, stock = ?, price = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sssissi", $name, $category, $brand, $stock, $price, $description, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(["message" => "Product updated successfully"]);
        } else {
            echo json_encode(["message" => "Error updating product: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["message" => "Incomplete product data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>
