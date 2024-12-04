<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';

$searchVideos = searchVideos($conn, $searchQuery);

$isLoggedIn = isset($_SESSION['user_id']);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart']) && $isLoggedIn) {
    $videoId = $_POST['video_id'];
    $userId = $_SESSION['user_id'];

    if (!videoExistsInCart($conn, $userId, $videoId)) {
        addToCart($conn, $userId, $videoId);
        echo "<script>alert('Added to your cart!');</script>";
    } else {
        echo "<script>alert('This video is already in your cart!');</script>";
    }
}
?>

<link rel="stylesheet" href="assets/css/browse.css">
<main>
    <section class="search-results">
        <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>

        <?php if (empty($searchVideos)): ?>
            <p>No videos found.</p>
        <?php else: ?>
            <div class="video-grid">
                <?php foreach ($searchVideos as $video):
                    $videoDetails = getVideoDetails($conn, $video['video_id']);
                ?>
                    <div class="video-item <?php if ($isPurchased) echo 'user-purchased'; ?>">
                        <img src="<?php echo $video['image_url']; ?>" alt="<?php echo $video['title']; ?>">
                        <h3><?php echo $video['title']; ?></h3>
                        <p class="price">$<?php echo $video['price']; ?></p>

                        <?php if ($isLoggedIn) : ?>
                            <div class="button-group">
                            <button type="button" class="btn view-detail-btn" data-video-id="<?php echo $video['video_id']; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 100 125">
                                    <g><path d="M76.2,49.1c-1.8,0-3.3-1.5-3.3-3.3c0-9.9,0-19.9,0-29.8c0-3.2-0.2-3.4-3.3-3.4c-18,0-36,0-53.9,0c-1.8,0-3.2,1.4-3.2,3.2c0,22.8,0,45.7,0,68.5c0,2.7,0.4,3.1,3.1,3.1c17.9,0,35.8,0,53.7,0c1.8,0,3.3,1.4,3.3,3.2l0,0c0,1.8-1.5,3.3-3.3,3.3c-17.5,0-36.8,0-54.1,0c-4.9,0-8.2-2.3-9.1-6.4c-0.2-1-0.2-2-0.2-3C5.9,61.8,6,39,5.8,16.3c0-3.5,0.8-6.3,3-8.2c1.5-1.4,3.6-2,5.6-2l56.7,0c2.1,0,4.1,0.7,5.7,2.1c2.2,2,2.9,4.9,2.9,8.4c-0.2,10.5-0.1,19-0.1,29.1C79.6,47.6,78.1,49.1,76.2,49.1L76.2,49.1z"/><path d="M93,92.8L93,92.8c-1.5,1.5-3.9,1.5-5.4,0c-4.4-4.4-8.8-8.8-12.8-12.9c-3.3,0.5-6,1.4-8.6,1.3c-7.5-0.2-13.5-6.4-13.8-14.1c-0.3-7.5,5.5-13.9,13.4-14.8c6.9-0.8,13.8,4.4,15.3,11.6c0.6,3,0.3,6-1,8.7c-0.5,1-0.3,1.5,0.4,2.3c4.2,4.1,8.3,8.3,12.5,12.4C94.5,88.9,94.5,91.3,93,92.8z M66.8,75.3c4.9,0,8.6-3.6,8.7-8.4c0-4.8-3.7-8.6-8.4-8.6c-4.8,0-8.6,3.7-8.7,8.4C58.3,71.5,61.9,75.3,66.8,75.3z"/><path d="M59.4,44.4c0,2.2,0,4.3,0,6.5c-13.6,0-27.2,0-40.9,0c0-2.1,0-4.2,0-6.5C32,44.4,45.6,44.4,59.4,44.4z"/><path d="M18.4,32.1c0-2.1,0-4.2,0-6.5c13.6,0,27.2,0,40.9,0c0,2.1,0,4.2,0,6.5C45.8,32.1,32.2,32.1,18.4,32.1z"/></g>
                                </svg>
                            </button>

                            <?php
                            $isPurchased = userHasPurchasedVideo($conn, $userId, $video['video_id']);
                            if ($isLoggedIn && !$isPurchased) : ?>
                                <form method="post" action="browse.php">
                                    <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn add-to-cart-btn"
                                            <?php if (videoExistsInCart($conn, $userId, $video['video_id'])) echo 'disabled'; ?>>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" version="1.1" viewBox="-5.0 -10.0 110.0 135.0">
                                            <path d="m3.3242 38.281c-1.2656 0-2.2969-1.0273-2.2969-2.2969s1.0273-2.2969 2.2969-2.2969h16.285c1.0391 0 1.918 0.69141 2.1992 1.6367l13.02 37.77h56.66c2.0508 0 3.918 0.83984 5.2773 2.1953l0.015625 0.015626c1.3516 1.3594 2.1914 3.2266 2.1914 5.2695 0 2.0547-0.84375 3.9219-2.1953 5.2773l-0.007813 0.007813c-1.3555 1.3555-3.2266 2.1953-5.2773 2.1953h-6.7188c-2.0508 0-3.918-0.83984-5.2773-2.1953l-0.015625-0.015625c-1.3516-1.3594-2.1914-3.2266-2.1914-5.2695 0-1.0234 0.21094-2 0.58984-2.8945h-14.223c0.37891 0.89062 0.58984 1.8711 0.58984 2.8945 0 2.0508-0.83984 3.918-2.1953 5.2773l-0.015625 0.015626c-1.3594 1.3516-3.2266 2.1914-5.2695 2.1914h-6.7188c-2.0508 0-3.918-0.83984-5.2773-2.1953l-0.015625-0.015625c-1.3516-1.3594-2.1914-3.2266-2.1914-5.2695 0-1.0234 0.21094-2 0.58984-2.8945h-9.9453c-0.95312 0-1.8438-0.60156-2.168-1.5508l-13.051-37.855h-14.648zm87.32 39.41h-5.875c-0.79297 0-1.5195 0.32812-2.0391 0.85156l-0.007813-0.007813-0.007812 0.007813c-0.51953 0.51953-0.84375 1.2422-0.84375 2.043 0 0.79687 0.32031 1.5195 0.84375 2.0391l0.007812 0.007812c0.51953 0.51953 1.2422 0.84375 2.0391 0.84375h6.7188c0.79688 0 1.5195-0.32422 2.0469-0.84766 0.52344-0.52344 0.84766-1.2461 0.84766-2.0469s-0.32031-1.5195-0.84375-2.043l-0.007812-0.007812c-0.51953-0.52344-1.2422-0.84375-2.0391-0.84375h-0.84375zm-33.887 0h-6.7188c-0.80078 0-1.5195 0.32031-2.043 0.84375l-0.007813 0.007813c-0.51953 0.51953-0.84375 1.2422-0.84375 2.043 0 0.79687 0.32031 1.5195 0.84375 2.0391l0.007813 0.007812c0.51953 0.51953 1.2422 0.84375 2.043 0.84375h6.7188c0.79688 0 1.5195-0.32031 2.0391-0.84375l0.007813-0.007812c0.51953-0.51953 0.84375-1.2422 0.84375-2.0391 0-0.75781-0.28906-1.4414-0.75781-1.9531l-0.09375-0.089843c-0.52344-0.52344-1.2461-0.85156-2.0391-0.85156zm2.8906-41.156v-22.305c0-1.2656 1.0273-2.2969 2.2969-2.2969 1.2656 0 2.2969 1.0273 2.2969 2.2969v22.305l7.8906-7.8906c0.89844-0.89844 2.3477-0.89844 3.2461 0 0.89453 0.89844 0.89453 2.3477 0 3.2461l-11.812 11.809c-0.89844 0.89844-2.3477 0.89844-3.2461 0l-11.809-11.809c-0.89844-0.89844-0.89844-2.3477 0-3.2461 0.89453-0.89844 2.3477-0.89844 3.2461 0zm-19.938-7.5703c1.2695 0 2.2969 1.0273 2.2969 2.2969s-1.0273 2.2969-2.2969 2.2969h-5.7227l9.8203 28.496h38.906l7.4414-28.496h-5.9805c-1.2656 0-2.2969-1.0273-2.2969-2.2969 0-1.2656 1.0273-2.2969 2.2969-2.2969h8.9414v0.007812c0.1875 0 0.38281 0.023438 0.57422 0.070313 1.2227 0.31641 1.957 1.5664 1.6406 2.7891l-8.5898 32.906c-0.1875 1.0781-1.1289 1.9023-2.2617 1.9023h-42.309c-0.95312 0-1.8438-0.60156-2.168-1.5508l-11.375-32.992c-0.10156-0.26172-0.16016-0.54297-0.16016-0.83984 0-1.2656 1.0273-2.2969 2.2969-2.2969h8.9414z"/>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php else : ?>
                            <p><a href="login.php">Login</a> to buy.</p>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>




            </div>
        <?php endif; ?>
    </section>
</main>

<script>
const viewDetailBtns = document.querySelectorAll('.view-detail-btn');
viewDetailBtns.forEach(button => {
    button.addEventListener('click', () => {
        const videoId = button.dataset.videoId;
        const videoUrl = `video_details.php?video_id=${videoId}`;
        window.open(videoUrl, '_blank', 'width=850px,height=850px');
    });
});
</script>

<?php include 'includes/footer.php'; ?>
