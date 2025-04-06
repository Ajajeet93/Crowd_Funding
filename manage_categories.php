<?php
require_once 'config/database.php';

try {
    // Create uploads directory if it doesn't exist
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Category images mapping with direct image URLs
    $category_images = [
        'Technology' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
        'Environment' => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?auto=format&fit=crop&w=800&q=80',
        'Education' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80',
        'Healthcare' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80',
        'Arts' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80',
        'Social Impact' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?auto=format&fit=crop&w=800&q=80',
        'Business' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=800&q=80',
        'Sports' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=800&q=80'
    ];

    echo "<h2>Category Management Report</h2>";

    // Get all projects
    $stmt = $conn->query("SELECT id, title, category, status FROM projects");
    $projects = $stmt->fetchAll();

    echo "<h3>Project Statistics</h3>";
    echo "<p>Total projects found: " . count($projects) . "</p>";
    
    // Count active vs inactive projects
    $active_projects = array_filter($projects, function($p) { return $p['status'] === 'active'; });
    $inactive_projects = array_filter($projects, function($p) { return $p['status'] !== 'active'; });
    
    echo "<p>Active projects: " . count($active_projects) . "</p>";
    echo "<p>Inactive projects: " . count($inactive_projects) . "</p><br>";

    // Get unique categories
    $stmt = $conn->query("SELECT DISTINCT category FROM projects WHERE status = 'active'");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Categories Analysis</h3>";
    echo "<p>Total categories found: " . count($categories) . "</p>";
    
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Category</th><th>Active Projects</th><th>Image Status</th><th>Action</th></tr></thead>";
    echo "<tbody>";
    
    foreach ($categories as $category) {
        // Count projects in this category
        $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE category = ? AND status = 'active'");
        $stmt->execute([$category]);
        $count = $stmt->fetchColumn();

        // Check if category image exists
        $image_url = 'uploads/' . strtolower(str_replace(' ', '-', $category)) . '.jpg';
        $image_exists = file_exists($image_url);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($category) . "</td>";
        echo "<td>" . $count . "</td>";
        echo "<td>" . ($image_exists ? 
            "<span class='text-success'>✓ Image exists</span>" : 
            "<span class='text-danger'>⚠️ Missing image</span>") . "</td>";
        echo "<td>";
        
        // Add image if missing
        if (!$image_exists && isset($category_images[$category])) {
            $image_data = @file_get_contents($category_images[$category]);
            if ($image_data) {
                if (file_put_contents($image_url, $image_data)) {
                    echo "<span class='text-success'>✓ Image added</span>";
                } else {
                    echo "<span class='text-danger'>Failed to save image</span>";
                }
            } else {
                echo "<span class='text-danger'>Failed to download image</span>";
                error_log("Failed to download image for category: " . $category);
            }
        } else {
            echo "-";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";

    // Check for missing images
    $missing_images = array_filter($categories, function($category) {
        $image_url = 'uploads/' . strtolower(str_replace(' ', '-', $category)) . '.jpg';
        return !file_exists($image_url);
    });

    if (!empty($missing_images)) {
        echo "<h3>Missing Category Images</h3>";
        echo "<p>The following categories are missing images:</p>";
        echo "<ul>";
        foreach ($missing_images as $category) {
            echo "<li>" . htmlspecialchars($category) . "</li>";
        }
        echo "</ul>";
        echo "<p>Please check the error logs for more details.</p>";
    }

    echo "<br><p>You can now view the updated homepage at: <a href='index.php'>View Homepage</a></p>";

} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>Database Error</h4>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
    echo "</div>";
}
?> 