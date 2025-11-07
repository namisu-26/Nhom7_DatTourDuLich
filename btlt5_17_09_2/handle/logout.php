<?php
// handle/logout.php
session_start();

// Xóa tất cả session data
$_SESSION = [];

// Hủy session cookie (nếu có)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Chuyển về trang login (thay đường dẫn nếu cần)
header("Location: ../index.php");
exit;
?>
