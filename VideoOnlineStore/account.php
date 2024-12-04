<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($conn, $userId);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    $newUsername = $_POST['username'] ?? null; 
    $newFullname = $_POST['fullname'] ?? null; 
    $newEmail = $_POST['email'] ?? null; 
    $curPassword = $_POST['curpassword'] ?? null; 
    $newPassword = $_POST['newpassword'] ?? null; 
    $confPassword = $_POST['confpassword'] ?? null; 

    if ($action === 'info') {
        $updateResult = UpdateCustomerInfo($conn, $userId, $newUsername, $newFullname, $newEmail, $newPassword);

        echo "<script>alert('Information updated successfully!');</script>";
    } elseif ($action === 'pass') {
        if ($newPassword != $confPassword) { 
            echo "<script>alert('Password do not match.');</script>"; 
        }
            if (password_verify($curPassword, $user['password'])) {
                $updateResult = UpdateCustomerInfo($conn, $userId, $newUsername, $newFullname, $newEmail, $newPassword);

                echo "<script>alert('Information updated successfully!');</script>";
            } else {
                echo "<script>alert('Current password incorrect!');</script>";
            }
    }
}

$purchaseHistory = getUserPurchaseHistory($conn, $userId);
?>

<link rel="stylesheet" href="assets/css/account.css">

<main class="account-page">
    <section class="account-page container">
        <div class="sidebar">
            <ul>
                <li><a href="?section=edit-profile">Edit Profile</a></li>
                <li><a href="?section=edit-password">Edit Password</a></li>
                <li><a href="?section=purchase-history">Purchase History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="content">
            <?php
            $section = $_GET['section'] ?? 'edit-profile';

            if ($section == 'edit-profile') {
                ?>
                <h3>Edit Profile</h3>
                <form action="account.php?section=edit-profile" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="info">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">

                    <label for="fullname">Fullname:</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>">

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

                    <button type="submit">Update Information</button>
                </form>

                <?php
            } elseif ($section == 'edit-password') {
                ?>
                <h3>Change Password</h3>
                <form action="account.php?section=edit-password" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="pass">
                    <label for="password">Current Password:</label>
                    <input type="password" id="curpassword" name="curpassword" 
                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" 
                        title="Password must be at least 8 characters long, contain at least one number, one uppercase letter, one lowercase letter, and one special character." 
                        required>

                    <label for="password">New Password:</label>
                    <input type="password" id="newpassword" name="newpassword" 
                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" 
                        title="Password must be at least 8 characters long, contain at least one number, one uppercase letter, one lowercase letter, and one special character." 
                        required>

                    <label for="password">Confirm New Password:</label>
                    <input type="password" id="confpassword" name="confpassword" 
                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" 
                        title="Password must be at least 8 characters long, contain at least one number, one uppercase letter, one lowercase letter, and one special character." 
                        required>
                    <button type="submit">Update Password</button>
                </form>
                <?php
            } elseif ($section == 'purchase-history') {
                ?>
                <h3>Purchase History</h3>
                <?php if (empty($purchaseHistory)): ?>
                    <p>You have not purchased any videos yet.</p>
                <?php else: ?>
                    <div class="order-list">
                        <?php
                        $currentOrderId = null;
                        foreach ($purchaseHistory as $item):
                            if ($item['order_id'] !== $currentOrderId):
                                if ($currentOrderId !== null): ?>
                                    </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif;
                        $currentOrderId = $item['order_id']; ?>
                        <div class="order-item">
                            <div class="order-header">
                                <h3>Order ID: <?php echo $item['order_id']; ?></h3>
                                <p>Order Date: <?php echo date('H:i - d/m/Y', strtotime($item['order_date'])); ?></p>
                                <p>Total Amount: $<?php echo number_format($item['total_amount'], 2); ?></p>
                                <p>Payment Date: <?php echo date('H:i - d/m/Y', strtotime($item['payment_date'])); ?></p>
                                <p>Payment Method: <?php echo $item['payment_method']; ?></p>
                                <p>Payment Status: <?php echo $item['payment_status']; ?></p>
                            </div>
                            <button class="toggle-details">View Details</button>
                            <div class="order-details">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Video Title</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            <?php endif; ?>
                                        <tr>
                                            <td>
                                                <a href="video_details.php?video_id=<?php echo $item['video_id']; ?>">
                                                    <?php echo $item['video_title']; ?>
                                                </a>
                                            </td>
                                            <td>$<?php echo $item['price']; ?></td>
                                        </tr>
                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
                <?php
            }
            ?>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-details');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.nextElementSibling.classList.toggle('show');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
