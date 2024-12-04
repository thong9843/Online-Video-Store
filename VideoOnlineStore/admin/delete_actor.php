<?php
session_start();
include '../includes/db.php';
include 'includes/functions.php';

if (isset($_GET['actor_id'])) {
    $actorId = $_GET['actor_id'];

    try {
        DeleteActors($conn, $actorId);
        echo "<script>alert('Actor deleted successfully!'); window.location = 'actors.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error deleting actor: " . $e->getMessage() . "');</script>";
    }
} else {
    echo "Invalid actor ID.";
}
