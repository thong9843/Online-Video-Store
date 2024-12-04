<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$purchasedVideos = getUserPurchasedVideos($conn, $userId);
?>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.content {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h3 {
    margin-bottom: 20px;
    color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background-color: #f4f4f4;
}

img {
    border-radius: 3px;
}

a {
    color: #007bff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

span {
    color: #999;
}

</style>
<main class="account-page container">
    <div class="content">
        <h3>Purchased Videos</h3>
        <?php
        $purchasedVideos = getUserPurchasedVideos($conn, $userId);
        if (count($purchasedVideos) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Video Title</th>
                        <th>Image</th>
                        <th>Purchase Date</th>
                        <th>Download</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchasedVideos as $video): 
                        $videoDetails = getVideoById($conn, $video['video_id']);
                        $downloadUrl = $videoDetails['download_url'] ?? '';
                    ?>
                        <tr>
                            <td><?php echo $video['title']; ?></td>
                            <td><img src="<?php echo $video['image_url']; ?>" alt="<?php echo $video['title']; ?>" width="50"></td>
                            <td><?php echo date('H:i - d/m/Y', strtotime($video['order_date'])); ?></td>
                            <td>
                                <?php if (!empty($downloadUrl)): ?>
                                    <a href="<?php echo $downloadUrl; ?>" download>Download</a>
                                <?php else: ?>
                                    <span>Not Available Now</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have not purchased any videos yet.</p>
        <?php endif; ?>
    </div>
</main>
<?php include 'includes/footer.php'; ?>