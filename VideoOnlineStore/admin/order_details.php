<?php
include '../includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];
    $orderDetails = getOrderDetails($conn, $orderId);
    $orderItems = getOrderItems($conn, $orderId);

    if (!$orderDetails) {
        echo "<script>alert('Order not found'); window.location = 'orders.php'</script>";
    }
} else {
    echo "Invalid order ID.";
    exit;
}
?>
<link rel="stylesheet" href="assets\css\index.css">
<main class="admin-order-details container">
    <h2>Order Details</h2>

    <h3>Order Information</h3>
    <p><strong>Order ID:</strong> <?php echo $orderDetails['order_id']; ?></p>
    <p><strong>Customer:</strong> <?php echo $orderDetails['username']; ?></p>
    <p><strong>Order Date:</strong> <?php echo $orderDetails['order_date']; ?></p>
    <p><strong>Total Amount:</strong> $<?php echo number_format($orderDetails['total_amount'], 2); ?></p>

    <h3>Purchased Videos</h3>
    <table>
        <thead>
            <tr>
                <th>Video Title</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td><?php echo $item['title']; ?></td>
                    <td>$<?php echo $item['price']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include 'includes/footer.php'; ?>
