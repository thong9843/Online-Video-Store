<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

$categories = getCategories($conn);

$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';
$selectedCategory = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$videos = getVideos($conn, $searchQuery, $selectedCategory);

$isLoggedIn = isset($_SESSION['user_id']); 
$userId = $_SESSION['user_id'] ?? null; 

if (isset($_GET['video_id'])) {
    $videoId = $_GET['video_id'];
    $videoDetails = getVideoDetails($conn, $videoId);
    $commentsAndRatings = GetAllCommentsAndRatings($conn, $videoId);

    if (!$videoDetails) {
        echo "<script>alert('Video not found'); window.location = 'index.php'</script>";
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart']) && $isLoggedIn) {
        $videoId = $_POST['video_id'];

        if (!videoExistsInCart($conn, $userId, $videoId)) {
            addToCart($conn, $userId, $videoId);
            echo "<script>alert('Added to your cart!'); window.close(); window.opener.location.reload();</script>";
            exit;
        } else {
            echo "<script>alert('This video is already in your cart!'); window.close(); window.opener.location.reload();</script>";
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
        if ($isLoggedIn) {
            $rating = $_POST['rating'];
            $comment = $_POST['comment'];

            $result = AddCommentAndRating($conn, $videoId, $userId, $rating, $comment);
            if ($result === true) {
                header("Location: video_details.php?video_id=$videoId"); 
                exit;
            } else {
                echo "<p class='error'>$result</p>"; 
            }
        } else {
            header("Location: login.php");
            exit;
        }
    }
} else {
    echo "Invalid video ID.";
    exit;
}

$isPurchased = $userId !== null && userHasPurchasedVideo($conn, $userId, $videoId); 
?>

<link rel="stylesheet" href="assets/css/video">

<main class="video-detail">
    <section class="video-details container">
        <h1><?php echo $videoDetails['title']; ?></h1>
        <img src="<?php echo $videoDetails['image_url']; ?>" alt="<?php echo $videoDetails['title']; ?>">

        <p><strong>Price:</strong> $<?php echo $videoDetails['price']; ?></p>
        <p><strong>Description:</strong> <?php echo $videoDetails['description']; ?></p>

        <?php if ($videoDetails['is_video']): ?>
            <p><strong>Duration:</strong> <?php echo $videoDetails['duration']; ?></p>
        <?php else: ?>
            <p><strong>Type:</strong> <?php echo $videoDetails['type']; ?></p>
            <?php if ($videoDetails['episodes']): ?>
                <p><strong>Episodes:</strong> <?php echo $videoDetails['episodes']; ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <p><strong>Actors:</strong> <?php echo implode(", ", $videoDetails['actor_names']); ?></p>
        <p><strong>Categories:</strong> <?php echo implode(", ", $videoDetails['category_names']); ?></p>

        <?php if ($isLoggedIn && !$isPurchased): ?>
            <form method="post" action="">
                <input type="hidden" name="video_id" value="<?php echo $videoDetails['video_id']; ?>">
                <button type="submit" name="add_to_cart" class="btn add-to-cart-btn"
                        <?php if (videoExistsInCart($conn, $userId, $videoDetails['video_id'])) echo 'disabled'; ?>>
                    Add to Cart
                </button>
            </form>
        <?php endif; ?>

        <h3>Comments and Ratings</h3>
        <div id="comments-section">
            <?php foreach ($commentsAndRatings as $comment): ?>
                <div class="comment">
                    <p><strong><?php echo $comment['username']; ?></strong> (Rating: <?php echo $comment['rating']; ?>/5)</p>
                    <p><?php echo $comment['comment']; ?></p>

                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                        <div class="comment-actions">
                            <button class="edit-btn" data-rating-id="<?php echo $comment['rating_id']; ?>">Edit</button>
                            <button class="delete-btn" data-rating-id="<?php echo $comment['rating_id']; ?>">Delete</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <h4>Add Your Comment and Rating</h4>
                <form method="post" action="">
                    <label for="rating">Rating:</label>
                    <select id="rating" name="rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>

                    <label for="comment">Comment:</label>
                    <textarea id="comment" name="comment"></textarea>

                    <button type="submit" name="submit_comment">Submit</button>
                </form>
            <?php else: ?>
                <p>Please <a href="login.php">login</a> to add a comment and rating.</p>
            <?php endif; ?>
        </div>

        <div id="editCommentModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <div id="edit-comment-form-container"></div>
            </div>
        </div>
    </section>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.edit-btn').click(function() {
        var ratingId = $(this).data('rating-id');
        var url = 'edit_comment.php?rating_id=' + ratingId;
        window.open(url, '_blank', 'width=600,height=400');
    });

    $('.delete-btn').click(function() {
        if (confirm("Are you sure you want to delete this comment?")) {
            var ratingId = $(this).data('rating-id');
            window.location.href = "delete_comment.php?rating_id=" + ratingId;
        }
    });

    $('.close-btn').click(function() {
        $('#editCommentModal').hide();
    });
});

function closeModal() {
    $('#editCommentModal').hide();
}

function closeWindowAndReload() {
    window.opener.location.reload();
    window.close();
}
</script>
