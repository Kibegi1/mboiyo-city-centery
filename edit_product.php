<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$error = '';
$success = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid product ID.');
}

/* Get product */
$stmt = $conn->prepare("
    SELECT id, name, description, price, size, image, category, status
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param('i', $id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {
    die('Product not found.');
}

/* Get existing pictures */
$existingImages = [];

$imageFolder = __DIR__ . '/../images/products/';

if (is_dir($imageFolder)) {

    $files = glob(
        $imageFolder . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}',
        GLOB_BRACE
    );

    if ($files) {

        foreach ($files as $file) {

            if (is_file($file)) {
                $existingImages[] = basename($file);
            }

        }

        sort($existingImages);
    }
}


/* Update product */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'available';

    $imagePath = $product['image'];

    if ($name === '') {

        $error = 'Product name is required.';

    } elseif ($price === '' || !is_numeric($price)) {

        $error = 'Please enter a valid price.';

    } else {

        /*
         * SELECT EXISTING IMAGE
         */

        $selectedExisting = basename(
            trim($_POST['existing_image'] ?? '')
        );

        if ($selectedExisting !== '') {

            $validImage = false;

            foreach ($existingImages as $img) {

                if ($img === $selectedExisting) {
                    $validImage = true;
                    break;
                }

            }

            if ($validImage) {
                $imagePath = $selectedExisting;
            } else {
                $error = 'Selected picture is not valid.';
            }
        }


        /*
         * UPLOAD NEW IMAGE
         */

        if (
            $error === '' &&
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                $error = 'There was a problem uploading the image.';

            } else {

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                $tmpFile = $_FILES['image']['tmp_name'];

                $mime = mime_content_type($tmpFile);

                if (!isset($allowedTypes[$mime])) {

                    $error = 'Only JPG, PNG and WEBP images are allowed.';

                } else {

                    $uploadDir = __DIR__ . '/../uploads/products/';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0775, true);
                    }

                    $extension = $allowedTypes[$mime];

                    $safeName = preg_replace(
                        '/[^a-zA-Z0-9_-]/',
                        '_',
                        pathinfo(
                            $_FILES['image']['name'],
                            PATHINFO_FILENAME
                        )
                    );

                    $fileName =
                        $safeName .
                        '_' .
                        time() .
                        '.' .
                        $extension;

                    if (
                        move_uploaded_file(
                            $tmpFile,
                            $uploadDir . $fileName
                        )
                    ) {

                        $imagePath =
                            'uploads/products/' .
                            $fileName;

                    } else {

                        $error =
                            'Could not save the uploaded image.';
                    }
                }
            }
        }


        /*
         * SAVE CHANGES
         */

        if ($error === '') {

            $sql = "
                UPDATE products
                SET
                    name = ?,
                    description = ?,
                    price = ?,
                    size = ?,
                    image = ?,
                    category = ?,
                    status = ?
                WHERE id = ?
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                $error =
                    'Database error: ' .
                    $conn->error;

            } else {

                $priceValue = (float)$price;

                $stmt->bind_param(
                    'ssdssssi',
                    $name,
                    $description,
                    $priceValue,
                    $size,
                    $imagePath,
                    $category,
                    $status,
                    $id
                );

                if ($stmt->execute()) {

                    $success =
                        'Product updated successfully.';

                    /* Refresh product */
                    $stmt->close();

                    $stmt = $conn->prepare("
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
                        WHERE id = ?
                        LIMIT 1
                    ");

                    $stmt->bind_param('i', $id);
                    $stmt->execute();

                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();

                    $stmt->close();

                } else {

                    $error =
                        'Could not update product: ' .
                        $stmt->error;

                    $stmt->close();
                }
            }
        }
    }
}


/*
 * Current image
 */
$currentImage = $product['image'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Edit Product - MBOIYO CITY CENTER
</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f6f8;
    color: #222;
}

.container {
    width: 95%;
    max-width: 1100px;
    margin: 30px auto;
}

.header {
    background: #111827;
    color: white;
    padding: 22px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.header h1 {
    margin: 0 0 8px;
    font-size: 26px;
}

.header p {
    margin: 0;
    color: #d1d5db;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

input,
textarea,
select {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 7px;
    font-size: 15px;
    margin-bottom: 18px;
}

textarea {
    min-height: 100px;
    resize: vertical;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

button {
    border: 0;
    padding: 13px 22px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 15px;
    font-weight: bold;
}

.save-btn {
    background: #111827;
    color: white;
}

.save-btn:hover {
    background: #000;
}

.back-btn {
    display: inline-block;
    background: #e5e7eb;
    color: #111827;
    padding: 11px 18px;
    border-radius: 7px;
    text-decoration: none;
    margin-right: 10px;
}

.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
    padding: 14px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
    padding: 14px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.section-title {
    margin-top: 0;
    margin-bottom: 5px;
}

.section-description {
    color: #6b7280;
    margin-top: 0;
    margin-bottom: 20px;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.image-option {
    position: relative;
}

.image-option input {
    display: none;
}

.image-option label {
    display: block;
    cursor: pointer;
    border: 3px solid transparent;
    border-radius: 10px;
    overflow: hidden;
    background: #f9fafb;
    margin: 0;
    transition: 0.2s;
}

.image-option label:hover {
    border-color: #9ca3af;
}

.image-option input:checked + label {
    border-color: #111827;
    box-shadow: 0 0 0 3px #d1d5db;
}

.image-option img {
    width: 100%;
    height: 170px;
    object-fit: cover;
    display: block;
}

.image-name {
    padding: 8px;
    font-size: 11px;
    word-break: break-word;
    color: #374151;
    background: white;
}

.current-image {
    margin-bottom: 25px;
}

.current-image img {
    width: 180px;
    height: 180px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #d1d5db;
}

.current-image p {
    color: #6b7280;
}

.upload-box {
    background: #f9fafb;
    border: 2px dashed #d1d5db;
    padding: 20px;
    border-radius: 10px;
}

@media (max-width: 700px) {

    .row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .image-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>✏️ Edit Product</h1>

        <p>
            MBOIYO CITY CENTER — Men's Clothes & Fashion
        </p>

    </div>


    <?php if ($success): ?>

        <div class="success">
            <?= htmlspecialchars($success) ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form
        method="POST"
        enctype="multipart/form-data"
    >


        <div class="card">

            <h2 class="section-title">
                Product Information
            </h2>

            <p class="section-description">
                Update the product details below.
            </p>


            <label for="name">
                Product Name *
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                required
            >


            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
            ><?= htmlspecialchars($product['description'] ?? '') ?></textarea>


            <div class="row">

                <div>

                    <label for="price">
                        Price (TZS) *
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        step="0.01"
                        value="<?= htmlspecialchars($product['price'] ?? '') ?>"
                        required
                    >

                </div>


                <div>

                    <label for="size">
                        Sizes
                    </label>

                    <input
                        type="text"
                        id="size"
                        name="size"
                        placeholder="S M L XL XXL"
                        value="<?= htmlspecialchars($product['size'] ?? '') ?>"
                    >

                </div>

            </div>


            <div class="row">

                <div>

                    <label for="category">
                        Category
                    </label>

                    <select
                        id="category"
                        name="category"
                    >

                        <option value="">
                            Select Category
                        </option>

                        <?php

                        $categories = [
                            'T-Shirts',
                            'Shirts',
                            'Trousers',
                            'Jeans',
                            'Shoes',
                            'Suits',
                            'Jackets',
                            'Shorts',
                            'Sweaters',
                            'Other'
                        ];

                        foreach ($categories as $cat):

                        ?>

                            <option
                                value="<?= htmlspecialchars($cat) ?>"
                                <?= ($product['category'] ?? '') === $cat
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                <?= htmlspecialchars($cat) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div>

                    <label for="status">
                        Availability
                    </label>

                    <select
                        id="status"
                        name="status"
                    >

                        <option
                            value="available"
                            <?= ($product['status'] ?? '') === 'available'
                                ? 'selected'
                                : ''
                            ?>
                        >
                            Available
                        </option>

                        <option
                            value="sold_out"
                            <?= ($product['status'] ?? '') === 'sold_out'
                                ? 'selected'
                                : ''
                            ?>
                        >
                            Sold Out
                        </option>

                    </select>

                </div>

            </div>

        </div>


        <div class="card">

            <h2 class="section-title">
                📸 Product Picture
            </h2>

            <p class="section-description">
                Choose another picture from your existing product pictures.
            </p>


            <?php if ($currentImage !== ''): ?>

                <div class="current-image">

                    <strong>
                        Current Picture
                    </strong>

                    <br><br>

                    <?php if (strpos($currentImage, 'uploads/') === 0): ?>

                        <img
                            src="../<?= htmlspecialchars($currentImage) ?>"
                            alt="Current product picture"
                        >

                    <?php else: ?>

                        <img
                            src="../images/products/<?= rawurlencode(basename($currentImage)) ?>"
                            alt="Current product picture"
                        >

                    <?php endif; ?>

                    <p>
                        Current file:
                        <?= htmlspecialchars($currentImage) ?>
                    </p>

                </div>

            <?php endif; ?>


            <div class="image-grid">

                <?php foreach ($existingImages as $index => $img): ?>

                    <div class="image-option">

                        <input
                            type="radio"
                            name="existing_image"
                            id="image_<?= $index ?>"
                            value="<?= htmlspecialchars($img) ?>"
                            <?= basename($currentImage) === $img
                                ? 'checked'
                                : ''
                            ?>
                        >

                        <label for="image_<?= $index ?>">

                            <img
                                src="../images/products/<?= rawurlencode($img) ?>"
                                alt="<?= htmlspecialchars($img) ?>"
                            >

                            <div class="image-name">
                                <?= htmlspecialchars($img) ?>
                            </div>

                        </label>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>


        <div class="card">

            <h2 class="section-title">
                ⬆️ Or Upload a New Picture
            </h2>

            <p class="section-description">
                Upload a new picture if you don't want to use the existing
                pictures.
            </p>

            <div class="upload-box">

                <label for="image">
                    New Product Picture
                </label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >

            </div>

        </div>


        <div class="card">

            <a
                href="index.php"
                class="back-btn"
            >
                ← Back to Admin
            </a>

            <button
                type="submit"
                class="save-btn"
            >
                💾 Save Changes
            </button>

        </div>

    </form>

</div>


<script>

const radios =
    document.querySelectorAll(
        'input[name="existing_image"]'
    );

const upload =
    document.getElementById('image');


radios.forEach(function(radio) {

    radio.addEventListener('change', function() {

        if (this.checked && upload) {
            upload.value = '';
        }

    });

});


if (upload) {

    upload.addEventListener('change', function() {

        if (this.files.length > 0) {

            radios.forEach(function(radio) {
                radio.checked = false;
            });

        }

    });

}

</script>

</body>

</html>
