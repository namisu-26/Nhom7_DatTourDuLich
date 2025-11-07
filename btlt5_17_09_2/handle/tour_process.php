<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>✅ Đang chạy tour_process.php...</pre>";

require_once __DIR__ . '/../functions/db_connection.php';

$conn = getDbConnection();

// Kiểm tra xem có nhấn nút thêm tour không
if (isset($_POST['add_tour'])) {
    echo "<pre>📦 Dữ liệu POST:</pre>";
    print_r($_POST);
    print_r($_FILES);

    $tour_name   = $_POST['tour_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price       = $_POST['price'] ?? 0;
    $start_date  = $_POST['start_date'] ?? '';
    $end_date    = $_POST['end_date'] ?? '';
    $image_name  = null;

    // --- Xử lý upload ảnh ---
    if (!empty($_FILES['image']['name'])) {
        $target_dir = __DIR__ . '/../uploads/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            echo "<p>✅ Ảnh đã upload thành công: $image_name</p>";
        } else {
            echo "<p>❌ Upload ảnh thất bại!</p>";
        }
    }

    // --- Thực hiện thêm tour ---
    $sql = "INSERT INTO tours (name, description, price, start_date, end_date, image)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("❌ Lỗi prepare SQL: " . $conn->error);
    }

    $stmt->bind_param("ssdsss", $tour_name, $description, $price, $start_date, $end_date, $image_name);
    if ($stmt->execute()) {
        echo "<p>✅ Thêm tour thành công!</p>";
        echo "<a href='../views/tour/list_tour.php'>← Quay lại danh sách</a>";
    } else {
        echo "<p>❌ Lỗi khi thêm tour: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Nếu không có hành động gì
echo "<p>⚠️ Không có hành động nào được thực hiện!</p>";
