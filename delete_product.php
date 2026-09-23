<?php

require_once "auth.php";
require_once "../db.php";

/* Check product ID */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET["id"];

/* Get product image */
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$product = $result->fetch_assoc();
$image = trim($product["image"] ?? "");

/*
|--------------------------------------------------------------------------
| Delete product from database FIRST
|--------------------------------------------------------------------------
*/
$delete = $conn->prepare("DELETE FROM products WHERE id = ?");
$delete->bind_param("i", $id);

if (!$delete->execute()) {
    die("Could not delete product: " . $conn->error);
}

/*
|--------------------------------------------------------------------------
| Delete uploaded image only
|--------------------------------------------------------------------------
|
| We DO NOT delete images inside images/products because those are
| your original shop pictures and may be used by other products.
|
*/
if ($image !== "") {

    /* Only delete files from uploads/products */
    if (strpos($image, "uploads/products/") === 0) {

        $imagePath = "../" . $image;

        if (file_exists($imagePath) && is_file($imagePath)) {
            @unlink($imagePath);
        }

    } elseif (strpos($image, "uploads/") === 0) {

        $imagePath = "../" . $image;

        if (file_exists($imagePath) && is_file($imagePath)) {
            @unlink($imagePath);
        }
    }
}

/* Return to dashboard */
header("Location: index.php?deleted=1");
exit;

?>
