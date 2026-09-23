<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$error = '';
$success = '';

$imageFolder = __DIR__ . '/../images/products/';
$imageWebPath = 'images/products/';

// Create image folder if it does not exist
if (!is_dir($imageFolder)) {
    mkdir($imageFolder, 0755, true);
}

// Get existing images
$existingImages = [];

if (is_dir($imageFolder)) {
    $files = glob($imageFolder . '*');

    if ($files) {
        foreach ($files as $file) {
            if (
                is_file($file) &&
                preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)
            ) {
                $existingImages[] = basename($file);
            }
        }

        sort($existingImages);
    }
}

// Save product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'available';

    $selectedImage = trim($_POST['selected_image'] ?? '');
    $uploadedImage = $_FILES['image'] ?? null;

    // Validate required fields
    if ($name === '') {
        $error = 'Please enter the product name.';
    } elseif ($price === '' || !is_numeric($price) || $price < 0) {
        $error = 'Please enter a valid price.';
    } elseif (!in_array($status, ['available', 'sold_out'], true)) {
        $error = 'Invalid availability status.';
    }

    // Image path to save in database
    $imagePath = '';

    // Use selected existing image
    if ($error === '' && $selectedImage !== '') {

        $safeImage = basename($selectedImage);
        $fullImagePath = $imageFolder . $safeImage;

        if (
            is_file($fullImagePath) &&
            preg_match('/\.(jpg|jpeg|png|webp)$/i', $safeImage)
        ) {
            $imagePath = $imageWebPath . $safeImage;
        } else {
            $error = 'Selected image does not exist.';
        }
    }

    // Upload a new image
    if (
        $error === '' &&
        $uploadedImage &&
        $uploadedImage['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($uploadedImage['error'] !== UPLOAD_ERR_OK) {
            $error = 'Image upload failed. Error code: '
                   . $uploadedImage['error'];
        } elseif ($uploadedImage['size'] > 5 * 1024 * 1024) {
            $error = 'Image must be smaller than 5MB.';
        } else {

            $tmpName = $uploadedImage['tmp_name'];

            $mime = mime_content_type($tmpName);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($allowedTypes[$mime])) {
                $error = 'Only JPG, PNG and WEBP images are allowed.';
            } else {

                $extension = $allowedTypes[$mime];

                $newName = 'product_' .
                           date('YmdHis') . '_' .
                           bin2hex(random_bytes(4)) .
                           '.' . $extension;

                $destination = $imageFolder . $newName;

                if (!is_writable($imageFolder)) {
                    $error = 'Image folder is not writable.';
                } elseif (!move_uploaded_file($tmpName, $destination)) {
                    $error = 'Could not save the uploaded image.';
                } else {
                    $imagePath = $imageWebPath . $newName;
                }
            }
        }
    }

    // Save product in database
    if ($error === '') {

        $sql = "
            INSERT INTO products
            (name, description, price, size, image, category, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Database error: ' . $conn->error;
        } else {

            $priceValue = (float) $price;

            $stmt->bind_param(
                'ssdssss',
                $name,
                $description,
                $priceValue,
                $size,
                $imagePath,
                $category,
                $status
            );

            if ($stmt->execute()) {
                $success = 'Product added successfully!';

                // Clear form values
                $name = '';
                $description = '';
                $price = '';
                $size = '';
                $category = '';
                $selectedImage = '';

            } else {
                $error = 'Could not save product: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Product - MBOIYO CITY CENTER</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    color: #222;
}

.container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

h1 {
    margin-top: 0;
    color: #111827;
}

.subtitle {
    color: #666;
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: bold;
    margin-top: 15px;
    margin-bottom: 6px;
}

input,
textarea,
select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 7px;
    font-size: 15px;
}

textarea {
    min-height: 100px;
    resize: vertical;
}

button {
    background: #111827;
    color: white;
    border: none;
    padding: 13px 20px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 15px;
    margin-top: 20px;
}

button:hover {
    background: #374151;
}

.alert {
    padding: 15px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

.success {
    background: #dcfce7;
    color: #166534;
}

.images {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.image-card {
    border: 2px solid #ddd;
    padding: 8px;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    background: #fff;
}

.image-card:hover {
    border-color: #2563eb;
}

.image-card.selected {
    border-color: #16a34a;
    background: #f0fdf4;
}

.image-card img {
    width: 100%;
    height: 130px;
    object-fit: cover;
    border-radius: 5px;
}

.image-card small {
    display: block;
    margin-top: 7px;
    font-size: 11px;
    overflow-wrap: anywhere;
}

.section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.back {
    display: inline-block;
    margin-top: 20px;
    text-decoration: none;
    color: #2563eb;
}

@media (max-width: 600px) {
    .container {
        margin: 10px auto;
        padding: 10px;
    }

    .card {
        padding: 18px;
    }
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<h1>➕ Add New Product</h1>

<p class="subtitle">
MBOIYO CITY CENTER — Men's Clothes & Fashion
</p>

<?php if ($error !== ''): ?>

<div class="alert error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<?php if ($success !== ''): ?>

<div class="alert success">
<?= htmlspecialchars($success) ?>
</div>

<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<label>Product Name *</label>

<input
    type="text"
    name="name"
    placeholder="Example: Men's T-Shirt"
    value="<?= htmlspecialchars($name ?? '') ?>"
    required
>

<label>Description</label>

<textarea
    name="description"
    placeholder="Describe the product..."
><?= htmlspecialchars($description ?? '') ?></textarea>

<label>Price (TZS) *</label>

<input
    type="number"
    name="price"
    min="0"
    step="0.01"
    placeholder="Example: 18000"
    value="<?= htmlspecialchars($price ?? '') ?>"
    required
>

<label>Sizes</label>

<input
    type="text"
    name="size"
    placeholder="Example: M, L, XL"
    value="<?= htmlspecialchars($size ?? '') ?>"
>

<label>Category</label>

<select name="category">

<option value="">Select Category</option>

<option value="T-Shirts">T-Shirts</option>
<option value="Shirts">Shirts</option>
<option value="Trousers">Trousers</option>
<option value="Shoes">Shoes</option>
<option value="Sendos">Sendos</option>
<option value="Other">Other</option>

</select>

<label>Availability</label>

<select name="status">

<option value="available">Available</option>
<option value="sold_out">Sold Out</option>

</select>

<div class="section">

<h2>📸 Choose Existing Product Picture</h2>

<p>
Click a picture to select it for this product.
</p>

<input
    type="hidden"
    name="selected_image"
    id="selected_image"
    value="<?= htmlspecialchars($selectedImage ?? '') ?>"
>

<div class="images">

<?php foreach ($existingImages as $image): ?>

<div
    class="image-card"
    onclick="selectImage(
        '<?= htmlspecialchars($image, ENT_QUOTES) ?>',
        this
    )"
>

<img
    src="../images/products/<?= rawurlencode($image) ?>"
    alt="<?= htmlspecialchars($image) ?>"
>

<small>
<?= htmlspecialchars($image) ?>
</small>

</div>

<?php endforeach; ?>

</div>

</div>

<div class="section">

<h2>⬆️ Or Upload a New Picture</h2>

<p>
Use this when you want to add a picture not already in your folder.
</p>

<label>Upload New Picture</label>

<input
    type="file"
    name="image"
    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
>

</div>

<button type="submit">
➕ Save Product
</button>

</form>

<a class="back" href="index.php">
← Back to Admin
</a>

</div>

</div>

<script>

function selectImage(image, element) {

    document.getElementById('selected_image').value = image;

    document.querySelectorAll('.image-card').forEach(card => {
        card.classList.remove('selected');
    });

    element.classList.add('selected');

}

</script>

</body>
</html>
