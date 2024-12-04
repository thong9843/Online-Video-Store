<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

$categories = getCategories($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    $videoId = $_POST['video_id'];
    $userId = $_SESSION['user_id'];

    if (checkCartItem($pdo, $userId, $videoId)) {
        echo "<script>alert('This video is already in your cart!');</script>";
    } else {
        addToCart($pdo, $userId, $videoId);
        echo "<script>alert('Added to your cart!');</script>";
    }
}

?>

<h1>Videos</h1>

<?php foreach ($categories as $category): ?>
    <h2><?php echo htmlspecialchars($category['category_name']); ?></h2>
    <div class="video-list">
        <?php
        $videos = getVideosByCategory($pdo, $category['category_id']);
        foreach ($videos as $video):
        ?>
        <div class="video-item">
            <img src="<?php echo htmlspecialchars($video['image_url']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>">
            <h3><?php echo htmlspecialchars($video['title']); ?></h3>
            <p>Price: $<?php echo htmlspecialchars($video['price']); ?></p>
            <form method="post">
                <input type="hidden" name="video_id" value="<?php echo htmlspecialchars($video['video_id']); ?>">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                <?php else: ?>
                    <p><a href="login.php">Login</a> to add to cart.</p>
                <?php endif; ?>
            </form>
            <a href="video_details.php?video_id=<?php echo htmlspecialchars($video['video_id']); ?>">View Details</a>
        </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>
