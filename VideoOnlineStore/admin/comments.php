<?php
include '../includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$commentsAndRatings = getAllCommentsAndRatingsFromAllVideos($conn); 

?>
<link rel="stylesheet" href="assets\css\index.css">
<main class="admin-comments container">
    <h2>Comment Management</h2>

    <table>
        <thead>
            <tr>
                <th>Rating ID</th>
                <th>Video Title</th> 
                <th>User</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commentsAndRatings as $comment):
                $video = getVideoById($conn, $comment['video_id']);
                ?>
                <tr>
                    <td><?php echo $comment['rating_id']; ?></td>
                    <td><?php echo $video['title']; ?></td>
                    <td><?php echo $comment['username']; ?></td>
                    <td><?php echo $comment['rating']; ?></td>
                    <td><?php echo $comment['comment']; ?></td>
                    <td>
                        <a href="delete_comment.php?rating_id=<?php echo $comment['rating_id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include 'includes/footer.php'; ?>

