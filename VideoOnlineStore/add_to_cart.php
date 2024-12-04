<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (isset($_SESSION['user_id']) && isset($_POST['video_id'])) {
    $userId = $_SESSION['user_id'];
    $videoId = $_POST['video_id'];
    addToCart($conn, $userId, $videoId);

    echo 'success';
} else {
    echo 'error';
}