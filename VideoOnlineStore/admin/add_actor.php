<?php
session_start();
include '../includes/db.php';
include 'includes/functions.php';
?>
<link rel="stylesheet" href="assets\css\editor.css">
<h2>Add Actor</h2>
<form method="post" action="save_actor.php">
    <input type="hidden" name="action" value="add">

    <label for="actor_name">Actor Name:</label>
    <input type="text" id="actor_name" name="actor_name" required>
    
    <button type="submit" class="btn btn-success">Save</button>
</form>
