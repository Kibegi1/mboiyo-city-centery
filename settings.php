<?php

require_once __DIR__ . "/db.php";

function getSetting($key, $default = "")
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1"
    );

    if (!$stmt) {
        return $default;
    }

    $stmt->bind_param("s", $key);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["setting_value"] ?? $default;
    }

    return $default;
}

function saveSetting($key, $value)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ss", $key, $value);

    return $stmt->execute();
}

/*
|--------------------------------------------------------------------------
| Convert Tanzania number to WhatsApp international format
|--------------------------------------------------------------------------
*/

function whatsappNumber()
{
    $number = getSetting("whatsapp", "0782775318");

    $number = preg_replace("/[^0-9]/", "", $number);

    if (substr($number, 0, 1) === "0") {
        $number = "255" . substr($number, 1);
    }

    if (substr($number, 0, 3) !== "255") {
        $number = "255" . $number;
    }

    return $number;
}

function whatsappUrl($message = "")
{
    return "https://wa.me/" . whatsappNumber() .
        "?text=" . urlencode($message);
}
?>
