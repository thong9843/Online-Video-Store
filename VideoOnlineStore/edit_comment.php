<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_GET['rating_id'])) {
    $ratingId = $_GET['rating_id'];
    $rating = getRatingById($conn, $ratingId);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $newRating = $_POST['rating'];
        $newComment = $_POST['comment'];

        $result = UpdateCommentAndRating($conn, $ratingId, $newRating, $newComment);
        if ($result === true) {
            exit;
        } else {
            echo "<p class='error'>Error updating comment: $result</p>";
        }
    }
} else {
    echo "Invalid rating ID.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Comment</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    width: 100%;
    box-sizing: border-box;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

label {
    display: block;
    margin-top: 10px;
    color: #555;
}

select, textarea {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ddd;
    box-sizing: border-box;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 20px;
    width: 100%;
}

button:hover {
    background-color: #45a049;
}

.error {
    color: red;
    font-weight: bold;
    text-align: center;
}

    </style>
</head>
<body>

<h2>Edit Comment and Rating</h2>
<form method="post" action="">
    <input type="hidden" name="rating_id" value="<?php echo htmlspecialchars($rating['rating_id']); ?>">

    <label for="rating">Rating:</label>
    <select id="rating" name="rating">
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <option value="<?php echo $i; ?>" <?php if ($rating['rating'] == $i) echo 'selected'; ?>><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>

    <label for="comment">Comment:</label>
    <textarea id="comment" name="comment"><?php echo htmlspecialchars($rating['comment']); ?></textarea>

    <button type="submit">Update</button>

</form>

</body>

<script>
function closeWindowAndReload() {
    window.opener.location.reload(); 
    window.close(); 
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        event.preventDefault(); 
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (xhr.status === 200) {
                alert('Comment updated successfully!');
                closeWindowAndReload();
            } else {
                alert('Error updating comment.');
            }
        };

        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        xhr.send(params);
    });
});
</script>

</html>
