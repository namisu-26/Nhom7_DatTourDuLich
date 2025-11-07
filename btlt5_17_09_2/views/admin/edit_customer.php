<?php
require_once '../../functions/db_connection.php';
require_once '../../functions/auth.php';

checkLogin('../../views/customer/login.php');
$user = getCurrentUser();
if ($user['role'] != 'admin') die("Không có quyền truy cập");

$conn = getDbConnection();
$id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $fullname, $email, $id);
    $stmt->execute();

    header("Location: customer_manage.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #dbeafe, #eff6ff);
            font-family: 'Montserrat', sans-serif;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .edit-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }

        .edit-container h2 {
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            color: #0b7af7;
        }

        .form-label {
            font-weight: 600;
            margin-top: 10px;
        }

        .form-control {
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            padding: 10px 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #0b7af7;
            box-shadow: 0 0 0 0.2rem rgba(11,122,247,0.25);
        }

        .btn-submit {
            background: linear-gradient(90deg, #0b7af7, #3b82f6);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 12px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37,99,235,0.4);
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            font-weight: 600;
            color: #0b7af7;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="edit-container">
    <h2><i class="fa-solid fa-user-pen me-2"></i>Sửa thông tin khách hàng</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Họ tên</label>
            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($data['fullname']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" required>
        </div>
        <button type="submit" class="btn-submit mt-3"><i class="fa-solid fa-check me-2"></i>Cập nhật</button>
    </form>
    <a href="customers_manage.php" class="back-link"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách</a>
</div>

</body>
</html>
