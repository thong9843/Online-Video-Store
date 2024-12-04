<?php
ob_start();
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$selectedVideos = isset($_POST['selected_videos']) ? $_POST['selected_videos'] : [];
$discountApplied = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_purchase'])) {
    $videoIdsToCheckout = $_POST['selected_videos'];
    $paymentMethod = $_POST['payment_method'];

    $videoIdsString = implode(',', $videoIdsToCheckout);
    Checkout($conn, $userId, $videoIdsString, $paymentMethod);

    echo "<script>alert('Purchase successful!');</script>";
    header("Location: account.php?section=purchase-history");
    exit;
}

ob_end_flush();
?>

<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
}

.checkout {
    max-width: 800px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #333;
    margin-bottom: 20px;
}

.checkout-items {
    margin-bottom: 20px;
}

.checkout-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.checkout-item img {
    margin-right: 10px;
    border-radius: 3px;
}

.checkout-item span {
    flex-grow: 1;
}

.checkout-total {
    margin-top: 20px;
    font-size: 18px;
    text-align: right;
}

.discount-notice {
    color: green;
    font-size: 16px;
    margin-top: 10px;
    text-align: right;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    margin-top: 20px;
    font-size: 16px;
    text-align: center;
    cursor: pointer;
    border-radius: 5px;
    text-decoration: none;
}

.btn-primary {
    background-color: #007bff;
    color: #fff;
}

</style>

<main>
    <section class="checkout">
        <h2>Checkout</h2>
        <form method="post" action="checkout.php">
            <div class="checkout-items">
                <?php
                $totalPrice = 0;
                foreach ($selectedVideos as $videoId) {
                    $videoDetails = getVideoDetails($conn, $videoId);
                    $totalPrice += $videoDetails['price'];
                ?>
                    <div class="checkout-item">
                        <input type="hidden" name="selected_videos[]" value="<?php echo $videoId; ?>">
                        <img src="<?php echo $videoDetails['image_url']; ?>" alt="<?php echo $videoDetails['title']; ?>" width="50">
                        <span><?php echo $videoDetails['title']; ?></span> - $<?php echo number_format($videoDetails['price'], 2); ?>
                    </div>
                <?php } 
                
                if (count($selectedVideos) >= 3) {
                    $totalPrice *= 0.8;
                    $discountApplied = true;
                }
                ?>
            </div>
            <div class="checkout-total">
                <strong>Total: $<?php echo number_format($totalPrice, 2); ?></strong>
                <?php if ($discountApplied) { ?>
                    <div class="discount-notice">A 20% discount has been applied when you buy more than 3 videos!</div>
                <?php } ?>
            </div>
            <div>
                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="Visa">Visa</option>
                    <option value="Bank">Bank</option>
                </select>
            </div>
            <button type="submit" name="confirm_purchase" class="btn btn-primary">Confirm Purchase</button>
        </form>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
