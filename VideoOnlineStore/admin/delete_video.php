<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['video_id'])) {
    $video_id = $_GET['video_id'];

    try {
        $sql = "EXEC DeleteVideo @video_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$video_id]);
        
        header("Location: videos.php");
        exit;
    } catch (PDOException $e) {
        echo "Error deleting video: " . $e->getMessage();
    }
} else {
    echo "No video ID specified.";
}
?>
