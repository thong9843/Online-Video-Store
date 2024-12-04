<?php
session_start();
include '../includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    $title = htmlspecialchars($_POST['title'] ?? null);
    $price = is_numeric($_POST['price']) ? (float)$_POST['price'] : null;
    $description = htmlspecialchars($_POST['description'] ?? null);
    $type = in_array($_POST['type'], ['movie', 'series', 'other']) ? $_POST['type'] : null;
    $episodes = is_numeric($_POST['episodes']) ? (int)$_POST['episodes'] : null;
    $duration = is_numeric($_POST['duration']) ? date("H:i:s", mktime(0, $_POST['duration'])) : null;
    $isVideo = isset($_POST['is_video']) ? 1 : 0;
    $downloadUrl = filter_var($_POST['download_url'] ?? null, FILTER_VALIDATE_URL);
    $actorNames = isset($_POST['actors']) ? implode(',', $_POST['actors']) : null;
    $categoryIds = isset($_POST['categories']) ? $_POST['categories'] : [];
    $categoryIdsString = implode(',', $categoryIds);
    $actorIds = isset($_POST['actors']) ? $_POST['actors'] : [];
    $actorIdsString = implode(',', $actorIds); 
    $isInactive = isset($_POST['is_inactive']) ? 1 : 0; 
    $releaseDay = isset($_POST['release_day']) ? $_POST['release_day'] : null;


    $imageUrl = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageUrl = uploadImage($_FILES['image']);
    } elseif ($action === 'edit') {
        $imageUrl = $_POST['existing_image_url'] ?? null;
    }

    try {
        if ($action === 'add') {
            $result = AddVideoWithActors($conn, $title, $price, $description, $imageUrl, $type, $episodes, $duration, $isVideo, $actorIds, $categoryIds, $downloadUrl, $releaseDay);
            $message = "Video added successfully!";
        } elseif ($action === 'edit' && isset($_POST['video_id'])) {
            $videoId = $_POST['video_id'];
            $result = UpdateVideoWithActors($conn, $videoId, $title, $price, $description, $imageUrl, $type, $episodes, $duration, $isVideo, $actorIds, $categoryIds, $downloadUrl, $releaseDay);
            $message = "Video updated successfully!";
        } else {
            throw new Exception("Invalid action or missing video ID.");
        }

        if ($result === true) {
            echo "<script>alert('$message'); ";
            echo "window.opener.location.reload(); window.close();</script>";
            exit;
        } else {
            $errorMessage = is_string($result) ? $result : "Unknown error occurred.";
            echo "<script>alert('Error saving video: $errorMessage');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error saving video: " . $e->getMessage() . "');</script>";
    }
exit;
}