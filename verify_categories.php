<?php
require_once 'config/database.php';

try {
    // Get all projects
    $stmt = $conn->query("SELECT id, title, category, status FROM projects");
    $projects = $stmt->fetchAll();

    echo "<div class='max-w-4xl mx-auto p-6'>";
    echo "<h2 class='text-2xl font-bold text-gray-900 mb-4'>Category Verification Report</h2>";
    echo "<p class='text-gray-600 mb-2'>Total projects found: " . count($projects) . "</p>";
    
    // Count active vs inactive projects
    $active_projects = array_filter($projects, function($p) { return $p['status'] === 'active'; });
    $inactive_projects = array_filter($projects, function($p) { return $p['status'] !== 'active'; });
    
    echo "<p class='text-gray-600 mb-2'>Active projects: " . count($active_projects) . "</p>";
    echo "<p class='text-gray-600 mb-6'>Inactive projects: " . count($inactive_projects) . "</p>";

    // Get unique categories
    $stmt = $conn->query("SELECT DISTINCT category FROM projects WHERE status = 'active'");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3 class='text-xl font-semibold text-gray-900 mb-4'>Categories Analysis</h3>";
    echo "<p class='text-gray-600 mb-4'>Total categories found: " . count($categories) . "</p>";
    
    echo "<div class='overflow-x-auto'>";
    echo "<table class='min-w-full bg-white border border-gray-200 rounded-lg'>";
    echo "<thead class='bg-gray-50'>";
    echo "<tr>";
    echo "<th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Category</th>";
    echo "<th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Active Projects</th>";
    echo "<th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Image Status</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody class='divide-y divide-gray-200'>";
    
    foreach ($categories as $category) {
        // Count projects in this category
        $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE category = ? AND status = 'active'");
        $stmt->execute([$category]);
        $count = $stmt->fetchColumn();

        // Check if category image exists
        $image_url = 'uploads/' . strtolower(str_replace(' ', '-', $category)) . '.jpg';
        $image_exists = file_exists($image_url);
        
        echo "<tr class='hover:bg-gray-50'>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . htmlspecialchars($category) . "</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $count . "</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm'>" . 
            ($image_exists ? 
                "<span class='text-green-600'>✓ Image exists</span>" : 
                "<span class='text-red-600'>⚠️ Missing image</span>") . 
            "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Check for missing images
    $missing_images = array_filter($categories, function($category) {
        $image_url = 'uploads/' . strtolower(str_replace(' ', '-', $category)) . '.jpg';
        return !file_exists($image_url);
    });

    if (!empty($missing_images)) {
        echo "<div class='mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6'>";
        echo "<h3 class='text-lg font-semibold text-yellow-800 mb-4'>Missing Category Images</h3>";
        echo "<p class='text-yellow-700 mb-4'>The following categories are missing images:</p>";
        echo "<ul class='list-disc list-inside text-yellow-700 space-y-2'>";
        foreach ($missing_images as $category) {
            echo "<li>" . htmlspecialchars($category) . "</li>";
        }
        echo "</ul>";
        echo "<p class='mt-4 text-yellow-700'>Please run the category images script to add missing images.</p>";
        echo "</div>";
    }

    echo "<div class='mt-6'>";
    echo "<a href='index.php' class='inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500'>";
    echo "View Homepage";
    echo "</a>";
    echo "</div>";
    echo "</div>";

} catch(PDOException $e) {
    echo "<div class='max-w-4xl mx-auto p-6'>";
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-6'>";
    echo "<h4 class='text-lg font-semibold text-red-800 mb-2'>Database Error</h4>";
    echo "<p class='text-red-700 mb-2'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='text-red-700'>Please check your database connection and try again.</p>";
    echo "</div>";
    echo "</div>";
}
?> 