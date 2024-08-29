<?php
session_start();
require_once('./config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location:./login.php');
    exit();
}

// Get total inventory
$totalInventoryResult = $conn->query("SELECT COUNT(*) AS total FROM inventory");
$totalInventory = $totalInventoryResult->fetch_assoc()['total'];

// Get total soldiers
$totalSoldiersResult = $conn->query("SELECT COUNT(*) AS total FROM soldiers");
$totalSoldiers = $totalSoldiersResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>מערכת ניהול צבאית | ניהול מלאי</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
    <!-- Material Design Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
</head>

<body>
    <?php include './header.php'; ?>
    <div class="main-content container">
        <div class="row">
            <div class="col-md-12">
                <h3>ניהול מלאי</h3>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <div class="card mini-stat bg-info">
                    <div class="card-body mini-stat-img">
                        <div class="text-white">
                            <h6 class="text-uppercase mb-3 font-size-16 text-white">סה"כ ציוד במלאי</h6>
                            <h2 class="mb-4 text-white"><?php echo $totalInventory; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mini-stat bg-warning">
                    <div class="card-body mini-stat-img">
                        <div class="text-white">
                            <h6 class="text-uppercase mb-3 font-size-16 text-white">סה"כ חיילים</h6>
                            <h2 class="mb-4 text-white"><?php echo $totalSoldiers; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="./inventory_list.php" class="btn btn-outline-danger btn-sm">מעבר לרשימת מלאי מלאה</a>
            </div>
        </div>
    </div>
</body>

</html>