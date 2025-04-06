<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Specific category images to fix
$category_images = [
    'Healthcare' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80',
    'Arts' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80',
    'Social Impact' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?auto=format&fit=crop&w=800&q=80'
];

echo "<h2>Category Image Fix Report</h2>";
echo "<p>Checking and fixing missing category images...</p>";

foreach ($category_images as $category => $image_url) {
    $filename = 'uploads/' . strtolower(str_replace(' ', '-', $category)) . '.jpg';
    
    echo "<h3>Processing: " . htmlspecialchars($category) . "</h3>";
    
    if (file_exists($filename)) {
        echo "<p>✓ Image already exists for " . htmlspecialchars($category) . "</p>";
        continue;
    }
    
    echo "<p>Downloading image for " . htmlspecialchars($category) . "...</p>";
    
    // Download image
    $image_data = @file_get_contents($image_url);
    
    if ($image_data === false) {
        echo "<p class='text-danger'>Failed to download image for " . htmlspecialchars($category) . "</p>";
        continue;
    }
    
    // Save image
    if (file_put_contents($filename, $image_data)) {
        echo "<p class='text-success'>✓ Successfully saved image for " . htmlspecialchars($category) . "</p>";
    } else {
        echo "<p class='text-danger'>Failed to save image for " . htmlspecialchars($category) . "</p>";
    }
}

echo "<br><p>You can now view the updated homepage at: <a href='index.php'>View Homepage</a></p>";
?> 