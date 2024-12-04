<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

$actors = getAllActors($conn);
$categories = getCategories($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Video</title>
    <link rel="stylesheet" href="assets\css\editor.css">
</head>
<body>
<h2>Add Video</h2>
<form method="post" action="save_video.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add">

    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required>

    <label for="price">Price:</label>
    <input type="number" id="price" name="price" step="0.01" min="0" required>

    <label for="description">Description:</label>
    <textarea id="description" name="description"></textarea>

    <label for="release_day">Release Day:</label>
    <input type="date" name="release_day"><br><br>

    <label for="image">Image:</label>
    <input type="file" id="image" name="image" accept="image/*">

    <label for="type">Type:</label>
    <select id="type" name="type">
        <option value="movie">Movie</option>
        <option value="series">Series</option>
        <option value="other">Other</option>
    </select>

    <div id="episodes-container" style="display:none;">
        <label for="episodes">Episodes:</label>
        <input type="number" id="episodes" name="episodes" min="1">
    </div>

    <div id="duration-container" style="display:block;">
        <label for="duration">Duration (minutes):</label>
        <input type="number" id="duration" name="duration" min="1">
    </div>

    <label for="is_video">Is Video:</label>
    <input type="checkbox" id="is_video" name="is_video" checked>

    <label for="download_url">Download URL:</label>
    <input type="url" id="download_url" name="download_url">

    <label for="actors">Actors:</label>
    <select id="actors" name="actors[]" multiple>
        <?php foreach ($actors as $actor): ?>
            <option value="<?php echo $actor['actor_id']; ?>"><?php echo $actor['actor_name']; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="categories">Categories:</label>
    <select id="categories" name="categories[]" multiple>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
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
        durationContainer.style.display = 'block';
    }
});
</script>
</body>
</html>
