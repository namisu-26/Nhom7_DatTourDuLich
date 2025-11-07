<?php
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';

checkLogin('../../views/customer/login.php');
$user = getCurrentUser();
if ($user['role'] != 'admin') {
    die("Không có quyền truy cập");
}

$conn = getDbConnection();
$customer_id = intval($_GET['id'] ?? 0);

// Cập nhật trạng thái khi admin gửi form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];

    $updateSql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    echo "<script>alert('✅ Cập nhật trạng thái thành công!'); window.location.href = window.location.href;</script>";
    exit;
}

// Lấy thông tin khách hàng
$userSql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

if (!$userData) die("Không tìm thấy khách hàng");

// Lấy danh sách tour đã đặt
$bookSql = "SELECT b.id, t.name AS tour_name, b.booking_date, b.status, b.quantity, b.total_price
            FROM bookings b 
            JOIN tours t ON b.tour_id = t.id 
            WHERE b.user_id = ?";
$stmt2 = $conn->prepare($bookSql);
$stmt2->bind_param("i", $customer_id);
$stmt2->execute();
$bookings = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chi tiết khách hàng</title>
<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        padding: 20px;
    }
    h2, h3 {
        color: #0069d9;
        border-bottom: 2px solid #0069d9;
        padding-bottom: 5px;
    }
    p {
        font-size: 16px;
        line-height: 1.5;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }
    th {
        background-color: #007bff;
        color: #fff;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    select {
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    button {
        padding: 5px 10px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.2s;
    }
    button:hover {
        background-color: #218838;
    }
    .status-paid {
        color: green;
        font-weight: bold;
    }
    .status-pending {
        color: orange;
        font-weight: bold;
    }
    .status-cancelled {
        color: red;
        font-weight: bold;
    }
    a.back-btn {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border-radius: 6px;
        transition: 0.3s;
    }
    a.back-btn:hover {
        background-color: #0056b3;
    }
</style>
</head>
<body>

<h2>Thông tin khách hàng</h2>
<p><strong>Họ tên:</strong> <?= htmlspecialchars($userData['fullname']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($userData['email']) ?></p>
<p><strong>Tên đăng nhập:</strong> <?= htmlspecialchars($userData['username']) ?></p>

<h3>Các tour đã đặt</h3>
<table>
    <tr>
        <th>Tên tour</th>
        <th>Ngày đặt</th>
        <th>Số lượng</th>
        <th>Tổng tiền</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
    </tr>
    <?php while ($b = $bookings->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($b['tour_name']) ?></td>
        <td><?= $b['booking_date'] ?></td>
        <td><?= $b['quantity'] ?></td>
        <td><?= number_format($b['total_price'], 0, ',', '.') ?> đ</td>
        <td>
            <form method="POST" style="display:flex; align-items:center; gap:8px;">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <select name="status">
                    <option value="confirmed" <?= $b['status'] == 'confirmed' ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="pending" <?= $b['status'] == 'pending' ? 'selected' : '' ?>>Chưa thanh toán</option>
                    <option value="cancelled" <?= $b['status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
                <button type="submit">Cập nhật</button>
            </form>
        </td>
        <td>
            <?php if ($b['status'] == 'confirmed'): ?>
                <span class="status-paid">✅ Đã thanh toán</span>
            <?php elseif ($b['status'] == 'pending'): ?>
                <span class="status-pending">⏳ Chưa thanh toán</span>
            <?php else: ?>
                <span class="status-cancelled">❌ Đã hủy</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="customers_manage.php" class="back-btn">← Quay lại trang quản lý khách hàng</a>

</body>
</html>
