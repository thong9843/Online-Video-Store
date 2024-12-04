<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($conn, $userId);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newUsername = $_POST['username'] ?? null; 
    $newFullname = $_POST['fullname'] ?? null; 
    $newEmail = $_POST['email'] ?? null; 
    $newPassword = $_POST['password'] ?? null; 

    if ($newPassword) {
        $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    $updateResult = UpdateCustomerInfo($conn, $userId, $newUsername, $newFullname, $newEmail, $newPassword);

    if ($updateResult) {
        if ($newUsername) {
            $_SESSION['username'] = $newUsername;
        }
        echo "<script>alert('Information updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating information. Please try again.');</script>";
    }
}

$purchaseHistory = getUserPurchaseHistory($conn, $userId);
?>

<link rel="stylesheet" href="assets/css/account.css">

<main class="account-page">
    <section class="account-page container">
        <div class="sidebar">
            <ul>
                <li><a href="?section=edit-profile">Edit Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="content">
            <?php
            $section = $_GET['section'] ?? 'edit-profile';

            if ($section == 'edit-profile') {
                ?>
                <h3>Edit Profile</h3>
                <form action="account.php?section=edit-profile" method="post">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">

                    <label for="fullname">Fullname:</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>">

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password">
                    <button type="submit">Update Information</button>
                </form>
                <?php
            }
            ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
