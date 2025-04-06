<?php
require_once 'config/database.php';

// Sample projects data
$sample_projects = [
    [
        'title' => 'Eco-Friendly Water Bottle',
        'description' => 'Revolutionary water bottle made from sustainable materials. Features include temperature control, built-in filter, and smart hydration tracking.',
        'funding_goal' => 50000.00,
        'category' => 'Technology',
        'image_url' => 'uploads/water-bottle.jpg'
    ],
    [
        'title' => 'Community Garden Initiative',
        'description' => 'Transform unused urban space into a thriving community garden. Includes educational programs for local schools and sustainable farming workshops.',
        'funding_goal' => 25000.00,
        'category' => 'Environment',
        'image_url' => 'uploads/garden.jpg'
    ],
    [
        'title' => 'Children\'s Educational App',
        'description' => 'Interactive learning app for children aged 5-12. Features gamified lessons in math, science, and reading comprehension.',
        'funding_goal' => 75000.00,
        'category' => 'Education',
        'image_url' => 'uploads/education-app.jpg'
    ],
    [
        'title' => 'Local Food Bank Expansion',
        'description' => 'Expand our food bank to serve more families in need. Includes new refrigeration units and delivery vehicles.',
        'funding_goal' => 100000.00,
        'category' => 'Community',
        'image_url' => 'uploads/food-bank.jpg'
    ],
    [
        'title' => 'Renewable Energy Research',
        'description' => 'Research project to develop more efficient solar panels using innovative materials and manufacturing techniques.',
        'funding_goal' => 150000.00,
        'category' => 'Science',
        'image_url' => 'uploads/solar-panel.jpg'
    ],
    [
        'title' => 'Art Therapy Program',
        'description' => 'Establish an art therapy program for veterans and first responders. Includes art supplies and professional therapy sessions.',
        'funding_goal' => 30000.00,
        'category' => 'Health',
        'image_url' => 'uploads/art-therapy.jpg'
    ]
];

try {
    // Get admin user ID
    $stmt = $conn->query("SELECT id FROM users WHERE username = 'admin'");
    $admin_id = $stmt->fetchColumn();

    if (!$admin_id) {
        die("Admin user not found. Please run setup_database.php first.");
    }

    // Insert sample projects
    $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, funding_goal, category, image_url, status) 
                           VALUES (:user_id, :title, :description, :funding_goal, :category, :image_url, 'active')");

    foreach ($sample_projects as $project) {
        $stmt->execute([
            ':user_id' => $admin_id,
            ':title' => $project['title'],
            ':description' => $project['description'],
            ':funding_goal' => $project['funding_goal'],
            ':category' => $project['category'],
            ':image_url' => $project['image_url']
        ]);
    }

    echo "Sample projects added successfully!<br>";
    echo "You can now view them at: <a href='projects.php'>View Projects</a>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 