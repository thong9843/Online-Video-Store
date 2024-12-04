<?php
session_start();

include 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    try {
        $query = "SELECT fullname FROM Users WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $fullName = $row['fullname'];
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Video Online Store</title>
    <link rel="stylesheet" href="assets\css\header.css">
</head>
<body>
    <header>
        <div class="logo">Video Online Store</div>
        <nav>
            <a href="index.php">Home</a>

            <div class="dropdown">
                <a href="#">Content Management</a>
                <div class="dropdown-content">
                    <a href="videos.php">Manage Videos</a>
                    <a href="actors.php">Manage Actors</a>
                </div>
            </div>

            <div class="dropdown">
                <a href="#">User Management</a>
                <div class="dropdown-content">
                    <a href="users.php">Manage Accounts</a>
                    <a href="comments.php">Manage Comments</a>
                    <a href="orders.php">Manage Orders</a>
                </div>
            </div>
        </nav>
        </form>

        <div class="user-actions">
            <?php if (!isset($_SESSION['user_id'])) { ?>
                <a href="login.php">Login</a>
            <?php } else { ?>
                <div class="dropdown">
                    <a href="#"><?php echo "Welcome, $fullName"; ?></a> 
                    <div class="dropdown-content">
                        <a href="account.php">My Account</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </header>

    </body>
</html>
