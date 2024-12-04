<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$videos = getAllVideos($conn);

function toggleVideoStatus($conn, $video_id, $action) {
    $procedure = $action === 'disable' ? 'InactiveVideo' : 'ActiveVideo';
    $sql = "EXEC $procedure @video_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$video_id]);
}

if (isset($_GET['action']) && isset($_GET['video_id'])) {
    toggleVideoStatus($conn, $_GET['video_id'], $_GET['action']);
    header("Location: videos.php");
    exit;
}
?>
<link rel="stylesheet" href="assets\css\index.css">
<main class="admin-videos container">
    <h2>Video Management</h2>
    <button class="btn btn-success add-video-btn">Add Video</button>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Price</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($videos as $video): ?>
                <tr>
                    <td><?php echo $video['video_id']; ?></td>
                    <td><?php echo $video['title']; ?></td>
                    <td>$<?php echo $video['price']; ?></td>
                    <td><?php echo $video['type']; ?></td>
                    <td><?php echo $video['is_inactive'] ? 'Inactive' : 'Active'; ?></td>
                    <td>
                        <form method="post" action="../video_details.php?video_id=<?php echo $video['video_id']; ?>" style="display:inline;">
                            <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">
                            <button type="submit" class="btn btn-secondary">View</button>
                        </form>

                        <button class="btn btn-primary edit-video-btn" data-video-id="<?php echo $video['video_id']; ?>">Edit</button>

                        <form method="post" action="../admin/delete_video.php?video_id=<?php echo $video['video_id']; ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this video?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>

                        <?php if ($video['is_inactive']): ?>
                            <form method="post" action="?action=enable&video_id=<?php echo $video['video_id']; ?>" style="display:inline;">
                                <input type="hidden" name="action" value="enable">
                                <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">
                                <button type="submit" class="btn btn-success">Enable</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="?action=disable&video_id=<?php echo $video['video_id']; ?>" style="display:inline;">
                                <input type="hidden" name="action" value="disable">
                                <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">
                                <button type="submit" class="btn btn-warning">Disable</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.querySelector('.add-video-btn').addEventListener('click', () => {
    const addVideoUrl = 'add_video.php';
    const addWindow = window.open(addVideoUrl, '_blank', 'width=850,height=850');

    const timer = setInterval(() => {
        if (addWindow.closed) {
            clearInterval(timer);
            location.reload();
        }
    }, 1000);
});

document.querySelectorAll('.edit-video-btn').forEach(button => {
    button.addEventListener('click', () => {
        const videoId = button.dataset.videoId;
        const editVideoUrl = `edit_video.php?video_id=${videoId}`;
        const editWindow = window.open(editVideoUrl, '_blank', 'width=850,height=850');

        const timer = setInterval(() => {
            if (editWindow.closed) {
                clearInterval(timer);
                location.reload();
            }
        }, 1000);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
