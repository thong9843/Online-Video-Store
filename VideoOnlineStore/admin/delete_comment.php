
<?php
session_start();
include '../includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['rating_id'])) {
    $ratingId = $_GET['rating_id'];

    $result = deleteCommentAndRating($conn, $ratingId);
    if ($result === true) {
        echo "<script>alert('Comment and rating deleted successfully!'); window.location = 'comments.php';</script>";
    } else {
        echo "<script>alert('$result'); window.location = 'comments.php';</script>"; 
    }

} else {
    echo "Invalid rating ID.";
}
?>
