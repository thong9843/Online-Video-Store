<?php
session_start();
include '../includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || !isset($_GET['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_GET['user_id'];

$user = getUserById($conn, $userId);

if (!$user) {
    echo "User not found.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/editor.css">
</head>
<body>
<link rel="stylesheet" href="assets\css\editor.css">
<h2>Edit User</h2>
<form method="post" action="save_user.php">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
    
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

    <label for="fullname">Full Name:</label>
    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

    <label for="password">New Password:</label>
    <input type="password" id="password" name="password">

    <label for="user_type">User Type:</label>
    <select id="user_type" name="user_type">
        <option value="customer" <?php if ($user['user_type'] == 'customer') echo 'selected'; ?>>Customer</option>
        <option value="admin" <?php if ($user['user_type'] == 'admin') echo 'selected'; ?>>Admin</option>
    </select>

    <button type="submit">Update</button>
</form>

</body>
</html>
