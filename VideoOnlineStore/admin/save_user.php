<?php
session_start();
include '../includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $userId = $_POST['user_id']; 
    $newUsername = $_POST['username'] ?? null;
    $newFullname = $_POST['fullname'] ?? null;
    $newEmail = $_POST['email'] ?? null;
    $newPassword = $_POST['password'] ?? null;
    $newType = $_POST['user_type'] ?? null;

    try {
        if (empty($newUsername) || empty($newFullname) || empty($newEmail)) {
            throw new Exception("Please fill in all required fields.");
        }
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                throw new Exception("Password must be at least 8 characters long.");
            }
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            UpdateCustomerInfoAdmin($conn, $userId, $newUsername, $newFullname, $newEmail, $hashedPassword, $newType);
        } else {
            UpdateCustomerInfoAdmin($conn, $userId, $newUsername, $newFullname, $newEmail, null, $newType);
        }

        echo "<script>alert('User updated successfully!'); window.close(); opener.location.reload();</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error saving user: " . $e->getMessage() . "'); window.location = 'users.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('{$e->getMessage()}');</script>";
    }
}
