<?php

require_once "settings.php";

$result = $conn->query("
    SELECT id, name, description, price, size, image, category, status
    FROM products
    ORDER BY id DESC
");

$shopName = getSetting("shop_name", "MBOIYO CITY CENTER");
$location = getSetting("location", "Dodoma, Tanzania");

function getProductImage($image)
{
    if (empty($image)) {
        return "";
    }

    $filename = basename($image);

    if (file_exists(__DIR__ . "/uploads/" . $filename)) {
        return "uploads/" . rawurlencode($filename);
    }

    if (file_exists(__DIR__ . "/uploads/products/" . $filename)) {
        return "uploads/products/" . rawurlencode($filename);
    }

    if (file_exists(__DIR__ . "/images/products/" . $filename)) {
        return "images/products/" . rawurlencode($filename);
    }

    return "";
}

function makeWhatsAppMessage($product, $shopName, $location)
{
    $message =
        "Hello " . $shopName . " 👋\n\n" .
        "I am interested in this product:\n\n" .
        "Product: " . $product["name"] . "\n" .
        "Category: " . ($product["category"] ?? "Men's Fashion") . "\n" .
        "Price: TSh " . number_format((float)$product["price"]) . "\n" .
        "Available Size: " . ($product["size"] ?: "Please confirm") . "\n\n";

    $image = getProductImage($product["image"]);

    if ($image !== "") {

        $imageUrl =
            "http://" .
            $_SERVER["HTTP_HOST"] .
            "/mboiyo/" .
            $image;

        $message .=
            "Product Image:\n" .
            $imageUrl . "\n\n";
    }

    $message .=
        "Location: " . $location . "\n\n" .
        "Please let me know if this product is available.";

    return $message;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Shop | <?= htmlspecialchars($shopName) ?>
</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f5f5;
    color: #222;
}

header {
    background: #111;
    color: white;
    padding: 18px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 22px;
    font-weight: bold;
}

nav a {
    color: white;
    text-decoration: none;
    margin-left: 22px;
    font-weight: bold;
}

.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

h1 {
    text-align: center;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    color: #777;
    margin-bottom: 35px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
}

.card {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,.07);
    transition: .2s;
}

.card:hover {
    transform: translateY(-4px);
}

.image {
    width: 100%;
    height: 280px;
    background: #eee;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    color: #999;
}

.content {
    padding: 18px;
}

.category {
    color: #777;
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 7px;
}

.name {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 10px;
}

.description {
    color: #666;
    font-size: 14px;
    min-height: 40px;
}

.price {
    font-size: 21px;
    font-weight: bold;
    margin: 15px 0;
}

.size {
    font-size: 14px;
    color: #555;
}

.buttons {
    margin-top: 15px;
    display: flex;
    gap: 8px;
}

.view,
.whatsapp {
    flex: 1;
    text-align: center;
    padding: 11px 5px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    font-size: 13px;
}

.view {
    background: #111;
    color: white;
}

.whatsapp {
    background: #25D366;
    color: white;
}

.sold {
    display: inline-block;
    margin-top: 15px;
    padding: 7px 10px;
    background: #ffe7e7;
    color: #b00020;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

footer {
    margin-top: 60px;
    background: #111;
    color: white;
    text-align: center;
    padding: 25px;
}

@media(max-width: 1000px) {
    .grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media(max-width: 700px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width: 500px) {
    .grid {
        grid-template-columns: 1fr;
    }

    header {
        flex-direction: column;
        gap: 12px;
    }
}

</style>

</head>

<body>

<header>

<div class="logo">
<?= htmlspecialchars($shopName) ?>
</div>

<nav>

<a href="index.php">
Home
</a>

<a href="index.php#shop">
Shop
</a>

<a href="admin/index.php">
Admin
</a>

</nav>

</header>


<div class="container">

<h1>
Our Products
</h1>

<div class="subtitle">
Men's Fashion | <?= htmlspecialchars($location) ?>
</div>


<div class="grid">

<?php if ($result && $result->num_rows > 0): ?>

<?php while ($product = $result->fetch_assoc()): ?>

<?php

$imageUrl = getProductImage($product["image"]);

$message = makeWhatsAppMessage(
    $product,
    $shopName,
    $location
);

$whatsappUrl = whatsappUrl($message);

?>

<div class="card">


<div class="image">

<?php if ($imageUrl): ?>

<img
src="<?= htmlspecialchars($imageUrl) ?>"
alt="<?= htmlspecialchars($product["name"]) ?>"
>

<?php else: ?>

<div class="no-image">
No image
</div>

<?php endif; ?>

</div>


<div class="content">


<div class="category">

<?= htmlspecialchars(
    $product["category"] ?? "Men's Fashion"
) ?>

</div>


<div class="name">

<?= htmlspecialchars($product["name"]) ?>

</div>


<?php if (!empty($product["description"])): ?>

<div class="description">

<?= htmlspecialchars(
    $product["description"]
) ?>

</div>

<?php endif; ?>


<div class="price">

TSh <?= number_format(
    (float)$product["price"]
) ?>

</div>


<div class="size">

<strong>Size:</strong>

<?= htmlspecialchars(
    $product["size"] ?: "Contact us"
) ?>

</div>


<?php if ($product["status"] === "available"): ?>

<div class="buttons">

<a
href="product.php?id=<?= (int)$product["id"] ?>"
class="view"
>
View
</a>

<a
href="<?= htmlspecialchars($whatsappUrl) ?>"
class="whatsapp"
target="_blank"
>
WhatsApp
</a>

</div>

<?php else: ?>

<span class="sold">
SOLD OUT
</span>

<?php endif; ?>


</div>

</div>

<?php endwhile; ?>

<?php else: ?>

<p>
No products available.
</p>

<?php endif; ?>

</div>

</div>


<footer>

© <?= date("Y") ?>
<?= htmlspecialchars($shopName) ?>

<br>

Men's Fashion |
<?= htmlspecialchars($location) ?>

</footer>

</body>

</html>
