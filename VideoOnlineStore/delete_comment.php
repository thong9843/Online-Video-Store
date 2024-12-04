<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_GET['rating_id'])) {
    $ratingId = $_GET['rating_id'];

    $rating = getRatingById($conn, $ratingId);

    DeleteCommentAndRating($conn, $ratingId);
    header("Location: video_details.php?video_id=" . $rating['video_id']);
    exit;
} else {
    echo "Invalid rating ID.";
    exit;
}
