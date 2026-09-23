<?php

require_once "db.php";

$result = $conn->query("SHOW TABLES");

if ($result) {
    echo "<h1>MBOIYO Database Connected Successfully!</h1>";
    echo "<p>Database: mboiyo</p>";
    echo "<p>Products table is available.</p>";
} else {
    echo "Database error: " . $conn->error;
}

$conn->close();

?>
