<?php
session_start();

// Kiểm tra quyền (admin)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . '/../../functions/db_connection.php';
$conn = getDbConnection();

// --- Thống kê ---
$totalTours = 0;
$r = $conn->query("SELECT COUNT(*) AS cnt FROM tours");
if ($r) { $row = $r->fetch_assoc(); $totalTours = (int)$row['cnt']; }

$totalBookings = 0;
$r = $conn->query("SELECT COUNT(*) AS cnt FROM bookings");
if ($r) { $row = $r->fetch_assoc(); $totalBookings = (int)$row['cnt']; }

$totalRevenue = 0.0;
$r = $conn->query("SELECT IFNULL(SUM(total_price),0) AS s FROM bookings WHERE status = 'confirmed'");
if ($r) { $row = $r->fetch_assoc(); $totalRevenue = (float)$row['s']; }

$todayBookings = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE DATE(booking_date) = CURDATE()");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) { $row = $res->fetch_assoc(); $todayBookings = (int)$row['cnt']; }
    $stmt->close();
}

// --- Lấy danh sách tours ---
$search = "";
$tours = [];
$result = null;

if (isset($_GET['q']) && trim($_GET['q']) !== "") {
    $search = trim($_GET['q']);
    $like = "%$search%";
    $stmt2 = $conn->prepare("SELECT * FROM tours WHERE name LIKE ? OR description LIKE ? ORDER BY id DESC");
    if ($stmt2) {
        $stmt2->bind_param("ss", $like, $like);
        $stmt2->execute();
        $result = $stmt2->get_result();
    }
} else {
    $result = $conn->query("SELECT * FROM tours ORDER BY id DESC");
}

if ($result) {
    while ($tour = $result->fetch_assoc()) {
        $tours[] = $tour;
    }
    $result->free();
}
// Đóng kết nối DB
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tour Hàng không (Admin)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Toàn bộ CSS bạn gửi giữ nguyên */
        :root {
            --primary-color: #0b7af7;
            --secondary-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #facc15;
            --text-dark: #1a202c;
            --text-muted: #718096;
            --bg-main-start: #e0f2fe;
            --bg-main-end: #d0e7ff;
            --card-shadow: 0 15px 45px rgba(0, 0, 0, 0.1);
            --card-bg: rgba(255, 255, 255, 0.9);
            --sky-blue: #0ea5e9;
            --navy-blue: #1e3a8a;
            --light-grey: #f8fafc;
        }

        body { 
            background: linear-gradient(135deg, var(--bg-main-start) 0%, var(--bg-main-end) 100%);
            font-family: 'Montserrat', sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        
        .container { flex: 1; }

        .navbar { 
            background: linear-gradient(90deg, #1a202c 0%, #2d3748 100%);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            padding: 20px 0; 
        }
        .navbar-brand { 
            font-weight:800; 
            font-size:1.7rem; 
            color: #ffffff !important; 
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2); 
        }
        .navbar-brand i { color: var(--warning-color); margin-right: 10px; }

        .search-form .form-control { 
            border-radius: 12px; 
            border: 1px solid rgba(255,255,255,0.2); 
            background-color: rgba(255,255,255,0.1); 
            color: white;
        }
        .search-form .form-control::placeholder { color: rgba(255,255,255,0.6); }
        .search-form .btn-primary { 
            background-color: var(--warning-color); 
            border-color: var(--warning-color); 
            color: var(--text-dark);
            border-radius: 0 12px 12px 0;
        }

        .btn-logout { 
            border-radius: 12px; 
            font-weight: 600; 
            color: var(--warning-color); 
            border: 2px solid var(--warning-color);
            padding: 8px 20px;
            background: transparent;
        }
        .btn-logout:hover { 
            background-color: var(--warning-color); 
            color: var(--text-dark);
        }

        .stat-card { 
            border-radius: 20px; 
            padding: 30px; 
            background: var(--card-bg); 
            backdrop-filter: blur(8px); 
            border: 1px solid rgba(255, 255, 255, 0.3); 
            text-align: center;
        }

        .stat-number { font-size: 2rem; font-weight: 800; color: var(--navy-blue); }
        .stat-label { color: var(--text-muted); font-weight: 600; }

        .card { 
            border: none;
            border-radius: 20px; 
            overflow: hidden; 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            background: var(--card-bg); 
        }
        .price-badge { 
            top: 20px; right: 20px;
            background: linear-gradient(45deg, #ef4444, #dc2626);
            padding: 10px 20px;
            border-radius: 50px; 
            font-weight: 700;
            color: white;
            position: absolute;
        }
        .card img { height: 250px; width: 100%; object-fit: cover; }
        .tour-name { font-size: 1.4rem; font-weight: 700; }
        .tour-desc { color: var(--text-muted); font-size: 0.95rem; }
        .btn-action { padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; }
        .btn-detail { background: var(--navy-blue); color: #fff; }
        .btn-edit { background: var(--warning-color); color: var(--text-dark); }
        .btn-delete { background: var(--danger-color); color: #fff; }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fa-solid fa-plane-departure"></i> Travel Admin</a>
        
        <div class="d-flex align-items-center ms-auto">
            <form class="d-flex search-form me-3" method="get" action="">
                <div class="input-group">
                    <input class="form-control" type="search" name="q" placeholder="Tìm tour theo tên/mô tả..." value="<?= htmlspecialchars($search) ?>" style="width: 280px;">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </form>

            <!-- ✅ Nút quản lý khách hàng -->
            <a href="../admin/customers_manage.php" class="btn btn-warning me-3" style="font-weight:600;">
                <i class="fa-solid fa-users-gear me-2"></i> Quản lý khách hàng
            </a>

            <a href="../../handle/logout.php" class="btn btn-logout"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-md-3">
            <div class="stat-card"><i class="fa-solid fa-plane-up fa-2x"></i><div class="stat-number"><?= $totalTours ?></div><div class="stat-label">Tổng tour</div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="stat-card"><i class="fa-solid fa-ticket fa-2x"></i><div class="stat-number"><?= $totalBookings ?></div><div class="stat-label">Đặt vé</div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="stat-card"><i class="fa-solid fa-money-bill-wave fa-2x"></i><div class="stat-number"><?= number_format($totalRevenue,0,',','.') ?>đ</div><div class="stat-label">Doanh thu</div></div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="stat-card"><i class="fa-solid fa-calendar-check fa-2x"></i><div class="stat-number"><?= $todayBookings ?></div><div class="stat-label">Hôm nay</div></div>
        </div>
    </div>

    <div class="section-header d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-map-marked-alt me-2"></i> Quản lý Tour</h2>
        <a href="add_tour.php" class="btn btn-success"><i class="fa-solid fa-plus me-2"></i> Thêm Tour</a>
    </div>

    <div class="row g-4">
        <?php if (!empty($tours)): ?>
            <?php foreach ($tours as $tour): ?>
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="card position-relative">
                        <div class="price-badge"><?= number_format($tour['price'], 0, ',', '.') ?>đ</div>
                        <img src="../../uploads/<?= htmlspecialchars($tour['image'] ?? 'no-image.jpg') ?>" alt="tour image">
                        <div class="card-body">
                            <h5 class="tour-name"><?= htmlspecialchars($tour['name']) ?></h5>
                            <p class="tour-desc"><?= htmlspecialchars(mb_substr($tour['description'], 0, 100)) ?>...</p>
                            <p><i class="fa-solid fa-calendar-days"></i> <?= htmlspecialchars($tour['start_date']) ?> → <?= htmlspecialchars($tour['end_date']) ?></p>
                            <div class="d-flex justify-content-between">
                                <div class="d-flex gap-2">
                                    <a href="edit_tour.php?id=<?= $tour['id'] ?>" class="btn-action btn-edit"><i class="fa-solid fa-pen"></i></a>
                                    <a href="delete_tour.php?id=<?= $tour['id'] ?>" onclick="return confirm('Xác nhận xóa tour này?')" class="btn-action btn-delete"><i class="fa-solid fa-trash"></i></a>
                                </div>
                                <a href="view_tour.php?id=<?= $tour['id'] ?>" class="btn-action btn-detail"><i class="fa-solid fa-eye"></i> Xem</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center mt-4">
                <div class="alert alert-info">Không có tour nào được tìm thấy.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
