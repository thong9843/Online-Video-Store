<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errorMessage = "Please type password and email.";
    } else {
        try {
            $sql = "SELECT * FROM Users WHERE username = :username AND user_type = 'admin'";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                header("Location: index.php");
                exit;
            } else {
                $errorMessage = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Error during login: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Video Online Store</title>
    <link rel="stylesheet" href="assets\css\editor.css">
</head>
<body>
    <main>
        <section class="login">
            <h2>Admin Login</h2>

            <?php if (!empty($errorMessage)): ?>
                <p class="error-message"><?php echo $errorMessage; ?></p>
            <?php endif; ?>

            <form action="login.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
            </form>
        </section>
    </main>
</body>
</html>
