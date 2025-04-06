<?php
require_once 'config/database.php';

try {
    // Sample projects data
    $sample_projects = [
        [
            'title' => 'Smart Home Automation System',
            'description' => 'Create an affordable and user-friendly home automation system that can be controlled via smartphone.',
            'funding_goal' => 50000,
            'category' => 'Technology',
            'image_url' => 'uploads/technology.jpg'
        ],
        [
            'title' => 'Community Garden Initiative',
            'description' => 'Establish a community garden to promote sustainable living and provide fresh produce to local residents.',
            'funding_goal' => 25000,
            'category' => 'Environment',
            'image_url' => 'uploads/environment.jpg'
        ],
        [
            'title' => 'Digital Learning Platform',
            'description' => 'Develop an interactive online learning platform for students in remote areas.',
            'funding_goal' => 75000,
            'category' => 'Education',
            'image_url' => 'uploads/education.jpg'
        ],
        [
            'title' => 'Healthcare Mobile App',
            'description' => 'Create a mobile app to connect patients with healthcare providers and manage medical records.',
            'funding_goal' => 100000,
            'category' => 'Healthcare',
            'image_url' => 'uploads/healthcare.jpg'
        ],
        [
            'title' => 'Public Art Installation',
            'description' => 'Install a large-scale public art piece to beautify the city center and promote local artists.',
            'funding_goal' => 30000,
            'category' => 'Arts',
            'image_url' => 'uploads/arts.jpg'
        ],
        [
            'title' => 'Youth Mentorship Program',
            'description' => 'Launch a mentorship program to help young people develop leadership and career skills.',
            'funding_goal' => 40000,
            'category' => 'Social Impact',
            'image_url' => 'uploads/social-impact.jpg'
        ]
    ];

    // Get admin user ID
    $stmt = $conn->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
    $admin = $stmt->fetch();

    if (!$admin) {
        echo "Error: Admin user not found. Please create an admin user first.";
        exit;
    }

    // Get current number of projects
    $stmt = $conn->query("SELECT COUNT(*) FROM projects");
    $current_count = $stmt->fetchColumn();

    echo "<h2>Project Verification Report</h2>";
    echo "<p>Current number of projects: " . $current_count . "</p>";

    // Add sample projects if needed
    if ($current_count < 6) {
        echo "<h3>Adding Sample Projects</h3>";
        foreach ($sample_projects as $project) {
            // Check if project already exists
            $stmt = $conn->prepare("SELECT id FROM projects WHERE title = ?");
            $stmt->execute([$project['title']]);
            if (!$stmt->fetch()) {
                $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, funding_goal, category, image_url, status, created_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
                $stmt->execute([
                    $admin['id'],
                    $project['title'],
                    $project['description'],
                    $project['funding_goal'],
                    $project['category'],
                    $project['image_url']
                ]);
                echo "<p class='text-success'>✓ Added project: " . htmlspecialchars($project['title']) . "</p>";
            } else {
                echo "<p class='text-info'>Project already exists: " . htmlspecialchars($project['title']) . "</p>";
            }
        }
    } else {
        echo "<p class='text-success'>✓ Sufficient number of projects already exist.</p>";
    }

    // Verify active projects
    $stmt = $conn->query("SELECT COUNT(*) FROM projects WHERE status = 'active'");
    $active_count = $stmt->fetchColumn();
    echo "<p>Number of active projects: " . $active_count . "</p>";

    echo "<br><p>You can now view the updated homepage at: <a href='index.php'>View Homepage</a></p>";

} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>Database Error</h4>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
    echo "</div>";
}
?> 