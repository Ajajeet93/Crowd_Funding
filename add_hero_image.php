<?php
// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Hero image URL
$hero_image_url = 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=1600&auto=format&fit=crop&q=60';

// Download and save hero image
$filepath = 'uploads/hero-image.jpg';
if (!file_exists($filepath)) {
    $image_data = file_get_contents($hero_image_url);
    if ($image_data !== false) {
        file_put_contents($filepath, $image_data);
        echo "Hero image downloaded successfully!<br>";
    } else {
        echo "Failed to download hero image<br>";
    }
} else {
    echo "Hero image already exists<br>";
}

echo "<br>You can now view the updated homepage at: <a href='index.php'>View Homepage</a>";

include 'includes/footer.php';
?> 