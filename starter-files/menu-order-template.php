<?php
// menu-order.php
// Byte Bistro order confirmation page
// This page receives order JSON from menu-order.html and calculates the receipt in PHP.

// -----------------------------
// Helper function: safely display text
// -----------------------------
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// -----------------------------
// Helper function: format money
// -----------------------------
function money($amount) {
    return '$' . number_format((float)$amount, 2);
}

// -----------------------------
// Get submitted values
// -----------------------------
$orderJson = $_POST['orderJson'] ?? '[]';
$submittedTaxRate = $_POST['taxRate'] ?? '0';

// Convert JSON string into a PHP associative array
$order = json_decode($orderJson, true);

// DEBUGGING (optional)
// Uncomment to inspect submitted data
// echo "<pre>";
// print_r($order);
// echo "</pre>";

// If the JSON is missing or invalid, stop and show a helpful message
if (!is_array($order)) {
    $order = [];
}

$taxRate = is_numeric($submittedTaxRate) ? (float)$submittedTaxRate : 0.0;

// -----------------------------
// Basic order values
// -----------------------------
$customer = $order['customer'] ?? 'Guest';
$phone = $order['phone'] ?? '';
$date = $order['date'] ?? date('Y-m-d');
$city = $order['city'] ?? 'Unknown';
$diningMethod = $order['diningMethod'] ?? 'pick-up';
$isDelivery = !empty($order['delivery']);
$tipPercent = isset($order['tipPercent']) && is_numeric($order['tipPercent'])
    ? (float)$order['tipPercent']
    : 0.0;

$deliveryFee = $isDelivery ? 5.00 : 0.00;

// -----------------------------
// Calculation helpers
// -----------------------------
function calculateItemTotal($items) {
    $total = 0.0;

    if (!is_array($items)) {
        return $total;
    }

    foreach ($items as $item) {
        $price = isset($item['price']) && is_numeric($item['price'])
            ? (float)$item['price']
            : 0.0;

        $quantity = isset($item['quantity']) && is_numeric($item['quantity'])
            ? (int)$item['quantity']
            : 0;

        if ($quantity > 0) {
            $total += $price * $quantity;
        }
    }

    return $total;
}

$entrees = $order['entrees'] ?? [];
$drinks = $order['drinks'] ?? [];
$desserts = $order['desserts'] ?? [];
$addOns = $order['addOns'] ?? [];

$subtotal =
    calculateItemTotal($entrees) +
    calculateItemTotal($drinks) +
    calculateItemTotal($desserts) +
    calculateItemTotal($addOns);

$tax = $subtotal * $taxRate;
$tip = $subtotal * ($tipPercent / 100);
$total = $subtotal + $tax + $tip + $deliveryFee;

// -----------------------------
// Display helper for item sections
// -----------------------------
function displayItems($heading, $items) {
    echo '<section class="receipt-section">';
    echo '<h3>' . h($heading) . '</h3>';

    if (!is_array($items) || count($items) === 0) {
        echo '<p>None</p>';
    } else {
        echo '<ul>';
        foreach ($items as $item) {
            $name = isset($item['name'])? $item['name'] : 'Unknown item';
            $price = isset($item['price']) && is_numeric($item['price'])
                ? (float)$item['price']
                : 0.0;
            $quantity = isset($item['quantity']) && is_numeric($item['quantity'])
                ? (int)$item['quantity']
                : 0;
            $lineTotal = $price * $quantity;

            echo '<li>';
            echo h($name) . ' x ' . h($quantity) . ' — ' . money($lineTotal);
            echo '</li>';
        }
        echo '</ul>';
    }

    echo '</section>';
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation - Byte Bistro</title>

    <!--
      STUDENTS:
      Add your stylesheet links here so this page matches the rest of your Byte Bistro site.
      Example:
      <link rel="stylesheet" href="styles/style.css">
      <link rel="stylesheet" href="styles/style-custom-onid.css">
      <link rel="stylesheet" href="styles/style-menu-onid.css">
    -->
    <style>
	  .totals div {
		margin: 4px 0;
	  }

	  .grand-total {
		font-weight: bold;
	  }
	</style>
</head>

<body>
    <!--
      STUDENTS:
      Replace this starter header/nav with your own Byte Bistro header and navigation.
    -->
    <header>
        <h1>Byte Bistro</h1>
        <nav>
            <a href="index.html">Home</a> |
            <a href="menu-order.html">Menu &amp; Order</a> |
            <a href="reviews.html">Reviews</a>
        </nav>
    </header>

    <main>
        <section class="confirmation-card">
            <h2>Order Confirmation</h2>

            <p><strong>Customer:</strong> <?php echo h($customer); ?></p>
            <p><strong>Phone:</strong> <?php echo h($phone); ?></p>
            <p><strong>Date:</strong> <?php echo h($date); ?></p>
            <p><strong>City:</strong> <?php echo h($city); ?></p>
            <p><strong>Dining Method:</strong> <?php echo h($diningMethod); ?></p>

            <?php
                displayItems('Entrées', $entrees);
                displayItems('Drinks', $drinks);
                displayItems('Desserts', $desserts);
                displayItems('Add-ons', $addOns);
            ?>

            <section class="totals">
				<h3>Receipt Totals</h3>

				<div>Subtotal: <?php echo money($subtotal); ?></div>

				<div>Tax Rate: <?php echo h($taxRate * 100); ?>%</div>

				<div>Tax: <?php echo money($tax); ?></div>

				<div>Tip (<?php echo h($tipPercent); ?>%): <?php echo money($tip); ?></div>

				<div>Delivery Fee: <?php echo money($deliveryFee); ?></div>

				<div class="grand-total">
					<strong>Total: <?php echo money($total); ?></strong>
				</div>
			</section>

            <p class="note">
                This page recalculates the receipt using the JSON order data submitted from JavaScript.
                Compare this total with the receipt preview on your original menu page.
            </p>
        </section>
    </main>

    <!--
      STUDENTS:
      Replace this starter footer with your own Byte Bistro footer.
    -->
    <footer>
        <p>Created by Your Name | info@bytebistro.test | (123) 456-7890</p>
        <p>&copy; 2026 Byte Bistro. All rights reserved.</p>
    </footer>
</body>
</html>