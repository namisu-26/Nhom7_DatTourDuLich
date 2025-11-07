<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

function handleLogin() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
        $conn = getDbConnection();

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // ✅ Lấy thông tin user theo username
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // ✅ Kiểm tra mật khẩu (tạm thời chưa mã hóa)
            if ($password === $user['password']) {
                // Gán session đăng nhập
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['success'] = "Đăng nhập thành công!";

                // ✅ Điều hướng theo vai trò
                if ($user['role'] === 'admin') {
                    header("Location: ../views/tour/list_tour.php");
                } else {
                    header("Location: ../views/customer/index.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Sai mật khẩu. Vui lòng thử lại.";
                header("Location: ../views/customer/login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Không tìm thấy tài khoản này.";
            header("Location: ../views/customer/login.php");
            exit();
        }
    }
}

handleLogin();
?>
