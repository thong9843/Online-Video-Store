<?php
session_start();
include '../includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || !isset($_GET['video_id'])) {
    header("Location: login.php");
    exit;
}

$videoId = $_GET['video_id'];

$video = getVideoById($conn, $videoId);

if (!$video) {
    echo "Video not found.";
    exit;
}

$actors = getAllActors($conn);
$categories = getCategories($conn);

$selectedActorIds = array_column(getVideoActors($conn, $videoId), 'actor_id');
$selectedCategoryIds = array_column(getVideoCategories($conn, $videoId), 'category_id');

$durationInMinutes = $video['duration'] ? getDurationInMinutes($video['duration']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit video <?php echo htmlspecialchars($video['title']); ?></title>
    <link rel="stylesheet" href="assets\css\editor.css">
</head>
<body>
<h2>Edit Video</h2>
<form method="post" action="save_video.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="video_id" value="<?php echo $video['video_id']; ?>">

    <label for="title">Title:</label>
    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" required>

    <label for="price">Price:</label>
    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $video['price']; ?>" required>

    <label for="description">Description:</label>
    <textarea id="description" name="description"><?php echo htmlspecialchars($video['description']); ?></textarea>

    <label for="release_day">Release Day:</label>
    <input type="date" name="release_day" value="<?php echo htmlspecialchars($video['release_day']); ?>">


    <label for="image">Image:</label>
    <input type="file" id="image" name="image" accept="image/*">

    <input type="hidden" name="existing_image_url" value="<?php echo $video['image_url']; ?>">

    <label for="type">Type:</label>
    <select id="type" name="type">
        <option value="movie" <?php echo $video['type'] === 'movie' ? 'selected' : ''; ?>>Movie</option>
        <option value="series" <?php echo $video['type'] === 'series' ? 'selected' : ''; ?>>Series</option>
        <option value="other" <?php echo $video['type'] === 'other' ? 'selected' : ''; ?>>Other</option>
    </select>

    <div id="episodes-container" style="display: <?php echo $video['type'] === 'series' ? 'block' : 'none'; ?>;">
        <label for="episodes">Episodes:</label>
        <input type="number" id="episodes" name="episodes" min="1" value="<?php echo $video['episodes'] ?? ''; ?>"> 
    </div>

    <div id="duration-container" style="display: <?php echo $video['type'] === 'movie' ? 'block' : 'none'; ?>;">
        <label for="duration">Duration (minutes):</label>
        <input type="number" id="duration" name="duration" min="1" value="<?php echo $durationInMinutes; ?>"> 
    </div>

    <label for="is_video">Is Video:</label>
    <input type="checkbox" id="is_video" name="is_video" <?php echo $video['is_video'] ? 'checked' : ''; ?>>

    <label for="download_url">Download URL:</label>
    <input type="url" id="download_url" name="download_url" value="<?php echo $video['download_url'] ?? ''; ?>">

    <label for="actors">Actors:</label>
    <select id="actors" name="actors[]" multiple>
        <?php foreach ($actors as $actor): ?>
            <option value="<?php echo $actor['actor_id']; ?>"
                <?php echo in_array($actor['actor_id'], $selectedActorIds) ? 'selected' : ''; ?>>
                <?php echo $actor['actor_name']; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="categories">Categories:</label>
    <select id="categories" name="categories[]" multiple>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['category_id']; ?>"
                <?php echo in_array($category['category_id'], $selectedCategoryIds) ? 'selected' : ''; ?>>
                <?php echo $category['category_name']; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn btn-success">Save</button>
    <button type="reset" class="btn btn-secondary">Clear</button>
</form>

<script>
const typeSelect = document.getElementById('type');
const episodesContainer = document.getElementById('episodes-container');
const durationContainer = document.getElementById('duration-container');

typeSelect.addEventListener('change', () => {
    if (typeSelect.value === 'series') {
        episodesContainer.style.display = 'block';
        durationContainer.style.display = 'none';
    } else if (typeSelect.value === 'movie') {
        episodesContainer.style.display = 'none';
        durationContainer.style.display = 'block';
    } else {
        episodesContainer.style.display = 'none';
        durationContainer.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', () => {
    if (typeSelect.value === 'series') {
        episodesContainer.style.display = 'block';
    } else if (typeSelect.value === 'movie') {
        durationContainer.style.display = 'block';
    }
});
</script>

</body>
</html>