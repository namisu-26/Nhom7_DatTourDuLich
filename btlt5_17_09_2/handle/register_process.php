<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

if (isset($_POST['register'])) {
    $conn = getDbConnection();

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $_SESSION['error'] = "Mật khẩu không khớp!";
        header("Location: ../register.php");
        exit();
    }

    // Kiểm tra trùng username/email
    $check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Tên đăng nhập hoặc email đã tồn tại!";
        header("Location: ../register.php");
        exit();
    }

    // Lưu tài khoản mới
    $hashed_password = $password;
    $role = 'user';

    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Đăng ký thành công! Mời bạn đăng nhập.";
        header("Location: ../index.php");
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra. Vui lòng thử lại.";
        header("Location: ../register.php");
    }
}
?>
