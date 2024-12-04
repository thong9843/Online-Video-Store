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
            <a href="browse.php">Browse</a>
            <div class="dropdown">
                <a href="#">Categories</a>
                <div class="dropdown-content">
                    <?php
                    $categoryQuery = "SELECT * FROM Categories";
                    $stmt = $conn->prepare($categoryQuery);
                    $stmt->execute();
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($categories as $category) : ?>
                        <a href="browse.php?category_id=<?php echo $category['category_id']; ?>">
                            <?php echo $category['category_name']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </nav>
            <form method="get" action="search.php" class="search-bar">
                <input type="text" name="q" placeholder="Search for movies...">
                <button type="submit"><i class="fas fa-search">Search</i></button>
        </form>

        <div class="user-actions">
            <?php if (!isset($_SESSION['user_id'])) { ?>
                <a href="login.php">Login</a>
                <a href="signup.php">Sign Up</a>
            <?php } else { ?>
                <a href="videopurchased.php">My Videos</a>
                <a href="cart.php">Cart</a>
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
