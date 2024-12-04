<?php
session_start();
include '../includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    
    if ($userId == $_SESSION['user_id']) {
        echo "<script>alert('You cannot delete your own account.'); window.location = 'users.php';</script>";
        exit;
    }
    try {
        DeleteUser($conn, $userId);
        echo "<script>alert('User deleted successfully!'); window.location = 'users.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error deleting user: " . $e->getMessage() . "'); window.location = 'users.php';</script>";
    }
} else {
    echo "Invalid user ID.";
}
