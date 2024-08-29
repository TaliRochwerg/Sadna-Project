<?php
if (!isset($_SESSION)) {
    session_start();
}
$currentFile = basename($_SERVER['PHP_SELF']);

?>

<header>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">
                    <img src="/assets/img/logo.png" alt="Army Management" class="army-header-logo" />
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php if (!isset($_SESSION['user'])) { ?>
                        <!-- <li class="nav-item">
                            <a class="nav-link m-2 <?php echo strpos($currentFile, 'register') !== false ? 'active' : ''; ?>" href="./register.php">Register</a>
                        </li> -->
                        <li class="nav-item">
                            <a class="nav-link m-2 <?php echo strpos($currentFile, 'login') !== false ? 'active' : ''; ?>" href="./login.php">התחברות</a>
                        </li>
                        <?php } else { ?>
                        <li class="nav-item">
                            <a class="nav-link m-2 <?php echo strpos($currentFile, 'certification') !== false ? 'active' : ''; ?>" href="./certification.php">אימונים והסמכות</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link m-2 <?php echo strpos($currentFile, 'attendance') !== false ? 'active' : ''; ?>" href="./attendance.php">נוכחות</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link m-2 <?php echo strpos($currentFile, 'inventory') !== false ? 'active' : ''; ?>" href="./inventory.php">ניהול מלאי</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link m-2" href="logout.php">התנתקות</a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>
