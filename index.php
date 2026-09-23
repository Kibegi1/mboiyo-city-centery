<?php
require_once __DIR__ . '/db.php';

$products = [];

$result = $conn->query("
    SELECT
        id,
        name,
        description,
        price,
        size,
        image,
        category,
        status
    FROM products
    WHERE status = 'available'
    ORDER BY id DESC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$categories = [];

foreach ($products as $product) {
    if (!empty($product['category'])) {
        $categories[] = $product['category'];
    }
}

$categories = array_values(array_unique($categories));

$whatsappNumber = '255782775318';

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
MBOIYO CITY CENTER | Men's Fashion
</title>

<style>

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    font-family:
        Arial,
        Helvetica,
        sans-serif;
    background: #f7f7f7;
    color: #111827;
}


/* =========================
   HEADER
========================= */

header {
    background: #111827;
    color: white;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav {
    width: 94%;
    max-width: 1250px;
    margin: auto;
    min-height: 72px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;
}

.logo {
    color: white;
    text-decoration: none;
}

.logo strong {
    display: block;
    font-size: 21px;
    letter-spacing: .5px;
}

.logo span {
    font-size: 12px;
    color: #d1d5db;
}

.nav-links {
    display: flex;
    gap: 25px;
    align-items: center;
}

.nav-links a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
}

.nav-links a:hover {
    color: #fbbf24;
}

.admin-link {
    background: #fbbf24;
    color: #111827 !important;
    padding: 9px 14px;
    border-radius: 6px;
}


/* =========================
   HERO
========================= */

.hero {
    min-height: 570px;

    display: flex;
    align-items: center;

    background:
        linear-gradient(
            rgba(17,24,39,.82),
            rgba(17,24,39,.82)
        ),
        url('images/products/WhatsApp%20Image%202026-09-20%20at%2019.45.41.jpeg');

    background-size: cover;
    background-position: center;

    color: white;
}

.hero-inner {
    width: 94%;
    max-width: 1250px;
    margin: auto;
}

.hero-content {
    max-width: 650px;
}

.hero-label {
    color: #fbbf24;
    font-weight: bold;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 15px;
}

.hero h1 {
    font-size: clamp(42px, 7vw, 75px);
    line-height: .95;
    margin: 0 0 25px;
}

.hero p {
    font-size: 20px;
    line-height: 1.6;
    color: #e5e7eb;
    margin-bottom: 30px;
}

.hero-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.hero-btn {
    display: inline-block;
    padding: 15px 23px;
    border-radius: 7px;
    text-decoration: none;
    font-weight: bold;
}

.shop-btn {
    background: #fbbf24;
    color: #111827;
}

.whatsapp-btn {
    background: #16a34a;
    color: white;
}


/* =========================
   SECTIONS
========================= */

.section {
    width: 94%;
    max-width: 1250px;
    margin: 70px auto;
}

.section-heading {
    text-align: center;
    margin-bottom: 35px;
}

.section-heading h2 {
    font-size: 35px;
    margin: 0 0 10px;
}

.section-heading p {
    color: #6b7280;
    margin: 0;
}


/* =========================
   CATEGORY BUTTONS
========================= */

.categories {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 35px;
}

.category-btn {
    border: 1px solid #d1d5db;
    background: white;
    padding: 10px 17px;
    border-radius: 30px;
    cursor: pointer;
    font-weight: bold;
}

.category-btn:hover,
.category-btn.active {
    background: #111827;
    color: white;
}


/* =========================
   SEARCH
========================= */

.search-box {
    max-width: 600px;
    margin: 0 auto 30px;
}

.search-box input {
    width: 100%;
    padding: 15px 18px;
    border: 1px solid #d1d5db;
    border-radius: 30px;
    font-size: 16px;
    outline: none;
}

.search-box input:focus {
    border-color: #111827;
}


/* =========================
   PRODUCTS
========================= */

.products-grid {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 22px;
}

.product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;

    box-shadow:
        0 4px 18px rgba(0,0,0,.07);

    transition: .2s;
}

.product-card:hover {
    transform: translateY(-4px);

    box-shadow:
        0 8px 25px rgba(0,0,0,.12);
}

.product-image {
    width: 100%;
    height: 270px;
    object-fit: cover;
    display: block;
    background: #f3f4f6;
}

.no-image {
    width: 100%;
    height: 270px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #e5e7eb;
    color: #6b7280;
}

.product-info {
    padding: 17px;
}

.product-category {
    color: #6b7280;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: bold;
}

.product-name {
    font-size: 18px;
    font-weight: bold;
    margin: 7px 0;
}

.product-description {
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
    min-height: 38px;
}

.product-price {
    font-size: 20px;
    font-weight: bold;
    margin: 12px 0;
}

.product-size {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 12px;
}

.product-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 7px;
}

.view-btn,
.order-btn {
    padding: 10px;
    border-radius: 6px;
    text-decoration: none;
    text-align: center;
    font-size: 13px;
    font-weight: bold;
}

.view-btn {
    background: #111827;
    color: white;
}

.order-btn {
    background: #16a34a;
    color: white;
}


/* =========================
   ABOUT
========================= */

.about {
    background: #111827;
    color: white;
    padding: 70px 0;
}

.about-inner {
    width: 94%;
    max-width: 1100px;
    margin: auto;
    text-align: center;
}

.about h2 {
    font-size: 35px;
}

.about p {
    max-width: 750px;
    margin: auto;
    color: #d1d5db;
    line-height: 1.8;
    font-size: 17px;
}


/* =========================
   CONTACT
========================= */

.contact-box {
    display: grid;
    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;
}

.contact-card {
    background: white;
    padding: 30px;
    text-align: center;
    border-radius: 12px;

    box-shadow:
        0 4px 18px rgba(0,0,0,.06);
}

.contact-card .icon {
    font-size: 35px;
    margin-bottom: 12px;
}

.contact-card h3 {
    margin: 5px 0 10px;
}

.contact-card p {
    color: #6b7280;
}

.contact-card a {
    color: #111827;
    font-weight: bold;
}


/* =========================
   FOOTER
========================= */

footer {
    background: #030712;
    color: white;
    padding: 40px 20px;
    text-align: center;
}

footer h2 {
    margin-top: 0;
}

footer p {
    color: #9ca3af;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 1000px) {

    .products-grid {
        grid-template-columns:
            repeat(3, 1fr);
    }

}

@media (max-width: 750px) {

    .nav {
        flex-direction: column;
        padding: 15px 0;
    }

    .nav-links {
        flex-wrap: wrap;
        justify-content: center;
    }

    .hero {
        min-height: 500px;
    }

    .products-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .contact-box {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 500px) {

    .products-grid {
        grid-template-columns: 1fr;
    }

    .product-image,
    .no-image {
        height: 330px;
    }

}

</style>

</head>

<body>


<!-- HEADER -->

<header>

<div class="nav">

<a href="index.php" class="logo">

<strong>
MBOIYO CITY CENTER
</strong>

<span>
Men's Clothes & Fashion
</span>

</a>


<div class="nav-links">

<a href="#home">
Home
</a>

<a href="#products">
Products
</a>

<a href="#about">
About
</a>

<a href="#contact">
Contact
</a>

<a
    href="admin/"
    class="admin-link"
>
Admin
</a>

</div>

</div>

</header>


<!-- HERO -->

<section
    class="hero"
    id="home"
>

<div class="hero-inner">

<div class="hero-content">

<div class="hero-label">
MBOIYO CITY CENTER
</div>

<h1>
Men's Fashion.
<br>
Your Style.
</h1>

<p>
Discover quality men's clothing, shoes and fashion
at MBOIYO CITY CENTER in Dodoma.
</p>


<div class="hero-buttons">

<a
    href="#products"
    class="hero-btn shop-btn"
>
    🛍️ Shop Products
</a>

<a
    href="https://wa.me/<?= $whatsappNumber ?>"
    target="_blank"
    class="hero-btn whatsapp-btn"
>
    💬 WhatsApp Us
</a>

</div>

</div>

</div>

</section>


<!-- PRODUCTS -->

<section
    class="section"
    id="products"
>

<div class="section-heading">

<h2>
Our Products
</h2>

<p>
Browse our latest men's fashion collection.
</p>

</div>


<!-- SEARCH -->

<div class="search-box">

<input
    type="text"
    id="searchInput"
    placeholder="🔎 Search products..."
>

</div>


<!-- CATEGORIES -->

<div class="categories">

<button
    class="category-btn active"
    data-category="all"
>
    All
</button>

<?php foreach ($categories as $category): ?>

<button
    class="category-btn"
    data-category="<?= htmlspecialchars($category) ?>"
>
    <?= htmlspecialchars($category) ?>
</button>

<?php endforeach; ?>

</div>


<!-- PRODUCT GRID -->

<div
    class="products-grid"
    id="productsGrid"
>

<?php foreach ($products as $product): ?>

<?php

$image = $product['image'] ?? '';

$imageUrl = '';

if ($image !== '') {

    if (
        filter_var(
            $image,
            FILTER_VALIDATE_URL
        )
    ) {

        $imageUrl = $image;

    } elseif (
        strpos(
            $image,
            'uploads/'
        ) === 0
    ) {

        $imageUrl = $image;

    } else {

        $imageUrl =
            'images/products/' .
            rawurlencode(
                basename($image)
            );
    }
}

$message =
    "Hello MBOIYO CITY CENTER, I am interested in: " .
    $product['name'] .
    " - TZS " .
    number_format(
        (float)$product['price']
    );

$whatsappUrl =
    'https://wa.me/' .
    $whatsappNumber .
    '?text=' .
    urlencode($message);

?>

<article
    class="product-card"
    data-category="<?= htmlspecialchars($product['category'] ?? '') ?>"
    data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
>

<?php if ($imageUrl): ?>

<img
    src="<?= htmlspecialchars($imageUrl) ?>"
    class="product-image"
    alt="<?= htmlspecialchars($product['name']) ?>"
    loading="lazy"
>

<?php else: ?>

<div class="no-image">
    No Image
</div>

<?php endif; ?>


<div class="product-info">

<div class="product-category">

<?= htmlspecialchars(
    $product['category'] ?: 'Fashion'
) ?>

</div>


<div class="product-name">

<?= htmlspecialchars(
    $product['name']
) ?>

</div>


<div class="product-description">

<?= htmlspecialchars(
    mb_substr(
        $product['description'] ?? '',
        0,
        80
    )
) ?>

</div>


<div class="product-price">

TZS
<?= number_format(
    (float)$product['price']
) ?>

</div>


<?php if (!empty($product['size'])): ?>

<div class="product-size">

Size:
<?= htmlspecialchars(
    $product['size']
) ?>

</div>

<?php endif; ?>


<div class="product-buttons">

<a
    href="product.php?id=<?= (int)$product['id'] ?>"
    class="view-btn"
>
    View
</a>

<a
    href="<?= htmlspecialchars($whatsappUrl) ?>"
    target="_blank"
    class="order-btn"
>
    WhatsApp
</a>

</div>

</div>

</article>

<?php endforeach; ?>

</div>

</section>


<!-- ABOUT -->

<section
    class="about"
    id="about"
>

<div class="about-inner">

<h2>
About MBOIYO CITY CENTER
</h2>

<p>

MBOIYO CITY CENTER is a men's clothing and fashion
shop based in Dodoma, Tanzania. Browse our products
online, choose what you like, and contact us through
WhatsApp for more information.

</p>

</div>

</section>


<!-- CONTACT -->

<section
    class="section"
    id="contact"
>

<div class="section-heading">

<h2>
Visit & Contact Us
</h2>

<p>
We are ready to help you find your style.
</p>

</div>


<div class="contact-box">


<div class="contact-card">

<div class="icon">
📍
</div>

<h3>
Location
</h3>

<p>
Dodoma, Tanzania
</p>

</div>


<div class="contact-card">

<div class="icon">
💬
</div>

<h3>
WhatsApp
</h3>

<p>

<a
    href="https://wa.me/<?= $whatsappNumber ?>"
    target="_blank"
>
0782 775 318
</a>

</p>

</div>


<div class="contact-card">

<div class="icon">
👔
</div>

<h3>
Men's Fashion
</h3>

<p>
Clothes, shoes and more.
</p>

</div>


</div>

</section>


<!-- FOOTER -->

<footer>

<h2>
MBOIYO CITY CENTER
</h2>

<p>
Men's Clothes & Fashion
</p>

<p>
Dodoma, Tanzania
</p>

<p>
WhatsApp: 0782 775 318
</p>

<p>
© <?= date('Y') ?> MBOIYO CITY CENTER
</p>

</footer>


<script>

/* SEARCH */

const searchInput =
    document.getElementById('searchInput');

const cards =
    document.querySelectorAll('.product-card');

const categoryButtons =
    document.querySelectorAll('.category-btn');

let selectedCategory = 'all';


function filterProducts() {

    const search =
        searchInput.value
        .toLowerCase()
        .trim();


    cards.forEach(function(card) {

        const name =
            card.dataset.name || '';

        const category =
            card.dataset.category || '';


        const matchesSearch =
            name.includes(search);


        const matchesCategory =
            selectedCategory === 'all' ||
            category === selectedCategory;


        if (
            matchesSearch &&
            matchesCategory
        ) {

            card.style.display = '';

        } else {

            card.style.display = 'none';

        }

    });

}


searchInput.addEventListener(
    'input',
    filterProducts
);


categoryButtons.forEach(function(button) {

    button.addEventListener(
        'click',
        function() {

            categoryButtons.forEach(
                function(btn) {
                    btn.classList.remove('active');
                }
            );

            this.classList.add('active');

            selectedCategory =
                this.dataset.category;

            filterProducts();

        }
    );

});

</script>

</body>

</html>
