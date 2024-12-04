<?php
include '../includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete_order'])) {
    $orderId = $_GET['delete_order'];
    
    try {
        $sql = "EXEC DeleteOrder @orderId = :orderId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
        $stmt->execute();

        echo "<script>alert('Order deleted successfully!'); window.location = 'orders.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error deleting order: " . $e->getMessage() . "');</script>";
    }
}

$allOrders = getAllOrders($conn);
?>
<link rel="stylesheet" href="assets\css\index.css">
<main class="admin-orders container">
    <h2>Order Management</h2>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Username</th>
                <th>Order Date</th>
                <th>Total Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allOrders as $order): ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['username']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td>$<?php echo $order['total_amount']; ?></td>
                    <td>
                        <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-secondary" target="_blank">View Details</a>
                        <a href="orders.php?delete_order=<?php echo $order['order_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include 'includes/footer.php'; ?>
