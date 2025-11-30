<?php
require_once 'config/database.php';

$conn = getDBConnection();
$file = 'pics/image_links.txt';

if (!file_exists($file)) {
    die("Error: $file not found.");
}

$lines = file($file);
$updates = 0;
$errors = 0;

echo "Starting database update...\n";

foreach ($lines as $line) {
    // Updated regex to handle optional space after colon
    if (preg_match('/^ID (\d+) - .*?:\s*(.*)$/', trim($line), $matches)) {
        $id = $matches[1];
        $path = trim($matches[2]);
        
        // Clean up path: remove leading slash if present
        if (strpos($path, '/') === 0) {
            $path = substr($path, 1);
        }
        
        // Check if file exists
        if (!file_exists($path) && strpos($path, 'http') !== 0) {
            echo "WARNING: File not found for ID $id: $path\n";
        }

        $stmt = $conn->prepare("UPDATE products SET image_url = ? WHERE id = ?");
        $stmt->bind_param("si", $path, $id);
        
        if ($stmt->execute()) {
            echo "Updated Product ID $id: $path\n";
            $updates++;
        } else {
            echo "Error updating ID $id: " . $conn->error . "\n";
            $errors++;
        }
        $stmt->close();
    }
}

echo "\nUpdate complete. Updated $updates products.\n";
$conn->close();
?>
