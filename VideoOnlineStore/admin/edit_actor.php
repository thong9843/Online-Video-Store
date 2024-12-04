<?php
session_start();
include '../includes/db.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || !isset($_GET['actor_id'])) {
    header("Location: login.php");
    exit;
}

$actorId = $_GET['actor_id'];

$actor = getActorById($conn, $actorId);

if (!$actor) {
    echo "Actor not found.";
    exit;
}
?>
<link rel="stylesheet" href="assets\css\editor.css">
<h2>Edit Actor</h2>
<form method="post" action="save_actor.php">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="actor_id" value="<?php echo $actor['actor_id']; ?>">
    <label for="actor_name">Actor Name:</label>
    <input type="text" id="actor_name" name="actor_name" value="<?php echo htmlspecialchars($actor['actor_name']); ?>" required>
    <button type="submit" class="btn btn-success">Save</button>
</form>
