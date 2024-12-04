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
    $actorName = $_POST['actor_name'];

    try {
        if ($action === 'add') {
            AddActor($conn, $actorName);
            echo "<script>alert('Actor added successfully!'); window.close(); opener.location.reload();</script>";
        } elseif ($action === 'edit' && isset($_POST['actor_id'])) { 
            $actorId = $_POST['actor_id'];
            $actorName = $_POST['actor_name'];
            UpdateActor($conn, $actorId, $actorName); 

            echo "<script>alert('Actor updated successfully!'); window.close(); opener.location.reload();</script>";
            exit;
        } else {
            throw new Exception("Invalid action or missing actor ID.");
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error saving actor: " . $e->getMessage() . "');</script>";
    }
}
?>
