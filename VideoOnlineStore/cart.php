<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';  

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_selected'])) {
    $videoIdsToRemove = isset($_POST['selected_videos']) ? $_POST['selected_videos'] : [];
    if (!empty($videoIdsToRemove)) {
        $videoIdsString = implode(',', $videoIdsToRemove);
        RemoveItemsFromCart($conn, $userId, $videoIdsString);
        header("Location: cart.php"); 
        exit;
    }
}

if (isset($_GET['remove'])) {
    $videoId = $_GET['remove'];
    RemoveItemsFromCart($conn, $userId, $videoId); 
    header("Location: cart.php"); 
    exit;
}

if (isset($_GET['remove_all'])) {
    $sql = "EXEC EmptyCart @userId = :userId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: cart.php");
    exit;
}

ob_start();
$cartDetails = getCartDetails($conn, $userId);
ob_end_flush();
?>

<link rel="stylesheet" href="assets/css/cart.css">
<main>
    <section class="cart">
        <h2>Shopping Cart</h2>

        <?php if (empty($cartDetails)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <form method="post" action="cart.php">
                <table>
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Video</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalPrice = 0; ?>
                        <?php foreach ($cartDetails as $item): ?>
                            <?php $totalPrice += $item['price']; ?>
                            <tr>
                                <td><input type="checkbox" name="selected_videos[]" value="<?php echo $item['video_id']; ?>"></td>
                                <td>
                                    <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['title']; ?>" width="50">
                                    <a href="video_details.php?video_id=<?php echo $item['video_id']; ?>"><?php echo $item['title']; ?></a>
                                </td>
                                <td>$<?php echo $item['price']; ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $item['video_id']; ?>">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2"></td>
                            <td>Total: $<?php echo number_format($totalPrice, 2); ?></td>
                            <td>
                                <button type="submit" formaction="checkout.php" class="btn btn-primary">Checkout</button>
                                <button type="submit" name="remove_selected" class="btn btn-danger">Remove Selected</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <a href="cart.php?remove_all=1" class="btn btn-danger">Remove All</a>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
