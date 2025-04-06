<?php
// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Sample images URLs
$images = [
    'water-bottle.jpg' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=800&auto=format&fit=crop&q=60',
    'garden.jpg' => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=800&auto=format&fit=crop&q=60',
    'education-app.jpg' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&auto=format&fit=crop&q=60',
    'food-bank.jpg' => 'https://images.unsplash.com/photo-1507048331197-7d4ac70811cf?w=800&auto=format&fit=crop&q=60',
    'solar-panel.jpg' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800&auto=format&fit=crop&q=60',
    'art-therapy.jpg' => 'https://images.unsplash.com/photo-1499781350541-7783f6c6a0c8?w=800&auto=format&fit=crop&q=60'
];

// Download and save images
foreach ($images as $filename => $url) {
    $filepath = 'uploads/' . $filename;
    if (!file_exists($filepath)) {
        $image_data = file_get_contents($url);
        if ($image_data !== false) {
            file_put_contents($filepath, $image_data);
            echo "Downloaded: $filename<br>";
        } else {
            echo "Failed to download: $filename<br>";
        }
    } else {
        echo "File already exists: $filename<br>";
    }
}

echo "<br>Image setup completed!<br>";
echo "You can now view the dashboard at: <a href='dashboard.php'>View Dashboard</a>";
?> 