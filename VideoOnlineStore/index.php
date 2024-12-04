<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

$latestVideos = getLatestVideos($conn, 5);  
?>

<link rel="stylesheet" href="assets\css\index.css">
<main>
<section class="hero">
        <div class="hero-overlay">
            <h1>Welcome to Video Online Store</h1>
            <p>Browse our vast collection of movies and TV shows.</p>
        </div>
    </section>

    <section class="slideshow">
    <h2>New Releases</h2>
    <div class="slideshow-wrapper">
        <div class="slideshow-container">
            <?php foreach ($latestVideos as $video): ?>
            <div class="slide">
                <a href="video_details.php?video_id=<?php echo $video['video_id']; ?>">
                    <img src="<?php echo $video['image_url']; ?>" alt="<?php echo $video['title']; ?>">
                    <h3><?php echo $video['title']; ?></h3>
                </a>
            </div>
            <?php endforeach; ?>
            <?php foreach ($latestVideos as $video): ?>
            <div class="slide">
                <a href="video_details.php?video_id=<?php echo $video['video_id']; ?>">
                    <img src="<?php echo $video['image_url']; ?>" alt="<?php echo $video['title']; ?>">
                    <h3><?php echo $video['title']; ?></h3>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



</main>

<?php include 'includes/footer.php'; ?>