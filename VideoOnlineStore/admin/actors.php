<?php
include '../includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$actors = getAllActors($conn);
?>

<link rel="stylesheet" href="assets\css\index.css">
<main class="admin-actors container">
    <h2>Actor Management</h2>
    <button class="btn btn-success add-actor-btn">Add Actor</button>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($actors as $actor): ?>
                <tr>
                    <td><?php echo $actor['actor_id']; ?></td>
                    <td><?php echo $actor['actor_name']; ?></td>
                    <td>
                        <button class="btn btn-primary edit-actor-btn" data-actor-id="<?php echo $actor['actor_id']; ?>">Edit</button>
                        <a href="delete_actor.php?actor_id=<?php echo $actor['actor_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this actor?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    

</main>

<script>
const addActorBtn = document.querySelector('.add-actor-btn');
addActorBtn.addEventListener('click', () => {
    window.open('add_actor.php', '_blank', 'width=600,height=400');
});

const editActorBtns = document.querySelectorAll('.edit-actor-btn');
editActorBtns.forEach(button => {
    button.addEventListener('click', () => {
        const actorId = button.dataset.actorId;
        window.open(`edit_actor.php?actor_id=${actorId}`, '_blank', 'width=600,height=400');
    });
});
</script>

<?php include 'includes/footer.php'; ?>
