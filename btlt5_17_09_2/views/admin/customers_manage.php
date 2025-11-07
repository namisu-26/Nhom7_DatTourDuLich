<?php
require_once __DIR__ . '/../../functions/db_connection.php';
require_once __DIR__ . '/../../functions/auth.php';
checkLogin();

$conn = getDbConnection();
$sql = "
    SELECT u.id, u.fullname, u.username, u.email, u.role,
           COUNT(b.id) AS total_bookings,
           SUM(CASE WHEN b.payment_status = 'paid' THEN 1 ELSE 0 END) AS paid_count
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    WHERE u.role = 'customer'
    GROUP BY u.id, u.fullname, u.username, u.email, u.role
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khách hàng</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; }
        h2 { text-align: center; color: #333; margin-top: 20px; }
        table {
            width: 90%; margin: 20px auto; border-collapse: collapse;
            background: #fff; border-radius: 8px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px; border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th { background: #007bff; color: white; }
        tr:hover { background: #f1f1f1; }
        a.btn {
            padding: 6px 12px; border-radius: 4px;
            text-decoration: none; color: white;
        }
        .btn-view { background: #28a745; }
        .btn-delete { background: #dc3545; }
        .btn-edit { background: #ffc107; color: black; }
        .back { margin-left: 50px; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

    <h2>Danh sách khách hàng</h2>
    <a class="back" href="../tour/list_tour.php">← Quay lại trang quản trị</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Tài khoản</th>
                <th>Email</th>
                <th>Đơn đã đặt</th>
                <th>Đơn đã thanh toán</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['total_bookings'] ?? 0 ?></td>
                <td><?= $row['paid_count'] ?? 0 ?></td>
                <td>
                    <a class="btn btn-view" href="view_customer.php?id=<?= $row['id'] ?>">Xem</a>
                    <a class="btn btn-edit" href="edit_customer.php?id=<?= $row['id'] ?>">Sửa</a>
                    <a class="btn btn-delete" href="delete_customer.php?id=<?= $row['id'] ?>"
                       onclick="return confirm('Bạn có chắc muốn xóa khách hàng này không?')">Xóa</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">Chưa có khách hàng nào.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
