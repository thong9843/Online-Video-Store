<?php
include '../includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$users = getAllUsers($conn);
?>
<link rel="stylesheet" href="assets\css\index.css">
<main class="admin-users container">
    <h2>User Management</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Fullname</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['fullname']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['user_type']; ?></td>
                    <td>
                        <button class="btn btn-primary edit-user-btn" data-user-id="<?php echo $user['user_id']; ?>">Edit</button>
                        <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                            <a href="delete_user.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
<script>
const editUserBtns = document.querySelectorAll('.edit-user-btn');
editUserBtns.forEach(button => {
    button.addEventListener('click', () => {
        const userId = button.dataset.userId;
        window.open(`edit_user.php?user_id=${userId}`, '_blank', 'width=600,height=600'); 
    });
});
</script>

<?php include 'includes/footer.php'; ?>
