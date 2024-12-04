<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$totalUsers = countUsers($conn);
$totalVideos = countVideos($conn);
$totalActors = countActors($conn);
$totalOrders = countOrders($conn);
?>
<link rel="stylesheet" href="assets\css\index.css">
<main class="admin-dashboard container">
    <section class="welcome">
        <h2>Welcome to Admin Panel</h2>
        <p>Manage your video store from here.</p>
    </section>

    <section class="statistics">
        <div class="stat-box">
            <h3><?php echo $totalVideos; ?></h3>
            <p>Videos</p>
        </div>
        <div class="stat-box">
            <h3><?php echo $totalUsers; ?></h3>
            <p>Customers</p>
        </div>
        <div class="stat-box">
            <h3><?php echo $totalActors; ?></h3>
            <p>Actors</p>
        </div>
        <div class="stat-box">
            <h3><?php echo $totalOrders; ?></h3>
            <p>Orders</p>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
