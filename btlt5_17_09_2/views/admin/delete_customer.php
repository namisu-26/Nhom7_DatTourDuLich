<?php
require_once __DIR__ . '/../../functions/db_connection.php';
require_once __DIR__ . '/../../functions/auth.php';
checkLogin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn = getDbConnection();

    // 🔹 Bước 1: Xóa các booking của user
    $conn->query("DELETE FROM bookings WHERE user_id = $id");

    // 🔹 Bước 2: Xóa user
    $conn->query("DELETE FROM users WHERE id = $id");

    $_SESSION['success'] = "Đã xóa khách hàng và các đơn đặt tour liên quan!";
    header("Location: customer_manage.php");
    exit();
} else {
    $_SESSION['error'] = "Không tìm thấy ID khách hàng cần xóa.";
    header("Location: customer_manage.php");
    exit();
}
?>
