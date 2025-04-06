<?php
session_start();
require_once 'config/database.php';

try {
    // Get statistics
    $stats = [
        'total_projects' => $conn->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
        'active_projects' => $conn->query("SELECT COUNT(*) FROM projects WHERE status = 'active'")->fetchColumn(),
        'total_donations' => $conn->query("SELECT COALESCE(SUM(amount), 0) FROM donations")->fetchColumn(),
        'total_donors' => $conn->query("SELECT COUNT(DISTINCT user_id) FROM donations")->fetchColumn()
    ];

    // Add sample donations if needed
    $stmt = $conn->query("SELECT COUNT(*) FROM donations");
    if ($stmt->fetchColumn() == 0) {
        // Get all active projects
        $stmt = $conn->query("SELECT id FROM projects WHERE status = 'active'");
        $projects = $stmt->fetchAll();
        
        // Get admin user ID
        $stmt = $conn->query("SELECT id FROM users WHERE username = 'admin'");
        $admin = $stmt->fetch();
        
        if ($admin && count($projects) > 0) {
            // Sample donor names
            $donor_names = [
                'Sarah Johnson', 'Michael Chen', 'Emma Wilson', 'David Brown', 'Lisa Anderson',
                'James Smith', 'Maria Garcia', 'Robert Taylor', 'Jennifer Lee', 'Thomas Wright',
                'Patricia Martinez', 'Daniel Kim', 'Elizabeth Davis', 'Christopher Lee', 'Michelle Wong'
            ];
            
            // Add sample donations to each project
            foreach ($projects as $project) {
                // Add 3-5 donations per project
                $num_donations = rand(3, 5);
                for ($i = 0; $i < $num_donations; $i++) {
                    $amount = rand(100, 1000);
                    $donor_name = $donor_names[array_rand($donor_names)];
                    
                    // Generate random date within last 30 days
                    $random_days = rand(0, 30);
                    $donation_date = date('Y-m-d H:i:s', strtotime("-{$random_days} days"));
                    
                    // Insert donation with custom donor name and date
                    $stmt = $conn->prepare("INSERT INTO donations (project_id, user_id, amount, created_at) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$project['id'], $admin['id'], $amount, $donation_date]);
                    
                    // Insert donor name into users table if not exists
                    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, full_name, email, password) VALUES (?, ?, ?, ?)");
                    $username = strtolower(str_replace(' ', '', $donor_name));
                    $email = $username . '@example.com';
                    $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
                    $stmt->execute([$username, $donor_name, $email, $hashed_password]);
                    
                    // Get the donor's user ID
                    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    $donor = $stmt->fetch();
                    
                    // Update donation with donor's user ID
                    if ($donor) {
                        $stmt = $conn->prepare("UPDATE donations SET user_id = ? WHERE project_id = ? AND amount = ? AND created_at = ?");
                        $stmt->execute([$donor['id'], $project['id'], $amount, $donation_date]);
                    }
                }
            }
            
            // Update project current amounts
            $conn->exec("UPDATE projects p 
                        SET current_amount = (
                            SELECT COALESCE(SUM(amount), 0) 
                            FROM donations 
                            WHERE project_id = p.id
                        )");
        }
    }

    // Get featured projects (latest 3 active projects from different categories)
    $stmt = $conn->query("SELECT p.*, u.username, u.full_name as creator_name,
                          (SELECT COUNT(*) FROM donations WHERE project_id = p.id) as donor_count,
                          (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE project_id = p.id) as total_donations
                          FROM projects p 
                          JOIN users u ON p.user_id = u.id 
                          WHERE p.status = 'active' 
                          ORDER BY p.created_at DESC");
    $all_projects = $stmt->fetchAll();

    // Group projects by category and select the latest one from each
    $featured_projects = [];
    $categories_seen = [];
    foreach ($all_projects as $project) {
        if (!in_array($project['category'], $categories_seen)) {
            $featured_projects[] = $project;
            $categories_seen[] = $project['category'];
        }
        if (count($featured_projects) >= 3) break;
    }

    // Get categories with project counts (limit to 3)
    $stmt = $conn->query("SELECT category, COUNT(*) as count 
                          FROM projects 
                          WHERE status = 'active' 
                          GROUP BY category 
                          ORDER BY count DESC 
                          LIMIT 3");
    $categories = $stmt->fetchAll();

    // Category images mapping
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

    // Project images mapping
    $project_images = [
        'Smart Home Automation System' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=800&q=80',
        'Community Garden Initiative' => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&w=800&q=80',
        'Digital Learning Platform' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80',
        'Healthcare Mobile App' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80',
        'Public Art Installation' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80',
        'Youth Mentorship Program' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?auto=format&fit=crop&w=800&q=80',
        'Eco-Friendly Packaging' => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?auto=format&fit=crop&w=800&q=80',
        'Sports Equipment for Schools' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=800&q=80',
        'Local Business Support' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=800&q=80',
        'Mental Health Awareness' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80',
        'Music Education Program' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=800&q=80',
        'Clean Water Initiative' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?auto=format&fit=crop&w=800&q=80'
    ];

    // Create uploads directory if it doesn't exist
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Add more sample projects if needed
    $stmt = $conn->query("SELECT COUNT(*) FROM projects WHERE status = 'active'");
    $active_count = $stmt->fetchColumn();
    
    if ($active_count < 12) {
        // Get admin user ID
        $stmt = $conn->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Sample projects data with multiple projects per category
            $sample_projects = [
                // Technology Projects
                [
                    'title' => 'Smart Home Automation System',
                    'description' => 'Create an affordable and user-friendly home automation system that can be controlled via smartphone.',
                    'funding_goal' => 50000,
                    'category' => 'Technology',
                    'image_url' => 'uploads/technology-project1.jpg'
                ],
                [
                    'title' => 'AI-Powered Learning Assistant',
                    'description' => 'Develop an AI-powered learning assistant to help students with their studies and homework.',
                    'funding_goal' => 75000,
                    'category' => 'Technology',
                    'image_url' => 'uploads/technology-project2.jpg'
                ],
                // Environment Projects
                [
                    'title' => 'Community Garden Initiative',
                    'description' => 'Establish a community garden to promote sustainable living and provide fresh produce to local residents.',
                    'funding_goal' => 25000,
                    'category' => 'Environment',
                    'image_url' => 'uploads/environment-project1.jpg'
                ],
                [
                    'title' => 'Solar Energy for Schools',
                    'description' => 'Install solar panels in local schools to reduce carbon footprint and save on energy costs.',
                    'funding_goal' => 45000,
                    'category' => 'Environment',
                    'image_url' => 'uploads/environment-project2.jpg'
                ],
                // Healthcare Projects
                [
                    'title' => 'Healthcare Mobile App',
                    'description' => 'Create a mobile app to connect patients with healthcare providers and manage medical records.',
                    'funding_goal' => 100000,
                    'category' => 'Healthcare',
                    'image_url' => 'uploads/healthcare-project1.jpg'
                ],
                [
                    'title' => 'Mental Health Support Platform',
                    'description' => 'Develop an online platform providing mental health resources and connecting users with counselors.',
                    'funding_goal' => 60000,
                    'category' => 'Healthcare',
                    'image_url' => 'uploads/healthcare-project2.jpg'
                ],
                // Arts Projects
                [
                    'title' => 'Public Art Installation',
                    'description' => 'Install a large-scale public art piece to beautify the city center and promote local artists.',
                    'funding_goal' => 30000,
                    'category' => 'Arts',
                    'image_url' => 'uploads/arts-project1.jpg'
                ],
                [
                    'title' => 'Community Theater Renovation',
                    'description' => 'Renovate the local community theater to provide a better space for performances and workshops.',
                    'funding_goal' => 55000,
                    'category' => 'Arts',
                    'image_url' => 'uploads/arts-project2.jpg'
                ],
                // Social Impact Projects
                [
                    'title' => 'Youth Mentorship Program',
                    'description' => 'Launch a mentorship program to help young people develop leadership and career skills.',
                    'funding_goal' => 40000,
                    'category' => 'Social Impact',
                    'image_url' => 'uploads/social-impact-project1.jpg'
                ],
                [
                    'title' => 'Food Bank Expansion',
                    'description' => 'Expand the local food bank to serve more families in need and reduce food waste.',
                    'funding_goal' => 65000,
                    'category' => 'Social Impact',
                    'image_url' => 'uploads/social-impact-project2.jpg'
                ]
            ];

            // Add new projects
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
                    error_log("Added new project: " . $project['title']);
                }
            }
        }
    }

    // Debug information
    error_log("Number of categories found: " . count($categories));
    foreach ($categories as $cat) {
        error_log("Category: {$cat['category']} - Count: {$cat['count']}");
    }

    // If user is logged in, get additional user-specific data
    $user = null;
    $recent_donations = [];
    if (isset($_SESSION['user_id'])) {
        // Get user information
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        // Get user's recent donations
        $stmt = $conn->prepare("SELECT d.*, p.title as project_title 
                               FROM donations d 
                               JOIN projects p ON d.project_id = p.id 
                               WHERE d.user_id = ? 
                               ORDER BY d.created_at DESC LIMIT 5");
        $stmt->execute([$_SESSION['user_id']]);
        $recent_donations = $stmt->fetchAll();
    }

} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while fetching data. Please try again later.");
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-white">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    Fund Your Dreams, Change the World
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Join our community of creators and supporters. Start your project today and make a difference in the world.
                </p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="create-project.php" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Start Your Project
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus mr-2"></i>Get Started
                    </a>
                <?php endif; ?>
            </div>
            <div>
                <?php if (file_exists('uploads/hero-image.jpg')): ?>
                    <img src="uploads/hero-image.jpg" alt="Crowdfunding Platform" 
                         class="rounded-xl shadow-lg w-full h-[400px] object-cover">
                <?php else: ?>
                    <div class="bg-gray-100 rounded-xl p-12 text-center">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Welcome to Crowdfunding</h3>
                        <p class="text-gray-600">Start your journey today!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="container mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h2 class="text-4xl font-bold text-primary-600 mb-2">
                <?php echo number_format($stats['total_projects']); ?>
            </h2>
            <p class="text-gray-600">Total Projects</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h2 class="text-4xl font-bold text-green-600 mb-2">
                $<?php echo number_format($stats['total_donations'], 0); ?>
            </h2>
            <p class="text-gray-600">Total Donations</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h2 class="text-4xl font-bold text-blue-600 mb-2">
                <?php echo number_format($stats['total_donors']); ?>
            </h2>
            <p class="text-gray-600">Total Donors</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <h2 class="text-4xl font-bold text-yellow-600 mb-2">
                <?php echo number_format($stats['active_projects']); ?>
            </h2>
            <p class="text-gray-600">Active Projects</p>
        </div>
    </div>
</div>

<!-- Featured Projects Section -->
<section class="bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">Featured Projects</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($featured_projects as $project): ?>
                <?php
                $image_url = !empty($project['image_url']) ? $project['image_url'] : '';
                if (!empty($image_url) && !file_exists($image_url) && isset($project_images[$project['title']])) {
                    $image_data = @file_get_contents($project_images[$project['title']]);
                    if ($image_data) {
                        file_put_contents($image_url, $image_data);
                    }
                }
                
                $display_image = (!empty($image_url) && file_exists($image_url)) ? $image_url : 'assets/images/default-project.jpg';
                ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <img src="<?php echo htmlspecialchars($display_image); ?>" 
                         class="w-full h-64 object-cover" 
                         alt="<?php echo htmlspecialchars($project['title']); ?>">
                    <div class="p-6">
                        <h5 class="text-xl font-semibold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($project['title']); ?>
                        </h5>
                        <p class="text-gray-600 mb-4">
                            <?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?>
                        </p>
                        
                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <?php 
                                $progress = ($project['total_donations'] / $project['funding_goal']) * 100;
                                $progress = min(100, max(0, $progress));
                                ?>
                                <div class="h-full bg-green-500 rounded-full transition-all duration-500"
                                     style="width: <?php echo $progress; ?>%">
                                    <span class="text-white text-xs px-2"><?php echo round($progress); ?>%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                            <span>
                                $<?php echo number_format($project['total_donations'], 2); ?> raised
                            </span>
                            <span><?php echo $project['donor_count']; ?> donors</span>
                        </div>
                        
                        <a href="project.php?id=<?php echo $project['id']; ?>" 
                           class="btn btn-primary w-full">
                            <i class="fas fa-eye mr-2"></i>View Project
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">Popular Categories</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($categories as $category): ?>
                <?php
                $image_url = 'uploads/' . strtolower(str_replace(' ', '-', $category['category'])) . '.jpg';
                
                if (!file_exists($image_url) && isset($category_images[$category['category']])) {
                    $image_data = @file_get_contents($category_images[$category['category']]);
                    if ($image_data) {
                        file_put_contents($image_url, $image_data);
                    }
                }
                
                $display_image = file_exists($image_url) ? $image_url : 'assets/images/default-category.jpg';
                ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <img src="<?php echo htmlspecialchars($display_image); ?>" 
                         class="w-full h-64 object-cover" 
                         alt="<?php echo htmlspecialchars($category['category']); ?>">
                    <div class="p-6 text-center">
                        <h5 class="text-xl font-semibold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($category['category']); ?>
                        </h5>
                        <p class="text-gray-600 mb-4"><?php echo $category['count']; ?> active projects</p>
                        <a href="projects.php?category=<?php echo urlencode($category['category']); ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Explore Projects
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-8">
            <a href="projects.php" class="btn btn-secondary">
                <i class="fas fa-th-large mr-2"></i>View All Categories
            </a>
        </div>
    </div>
</section>

<?php if (isset($_SESSION['user_id'])): ?>
<!-- Recent Donations Section -->
<section class="bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">Your Recent Donations</h2>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Project</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Amount</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($recent_donations)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-hand-holding-heart text-4xl mb-3"></i>
                                    <p>No donations yet. Start supporting projects today!</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_donations as $donation): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($donation['project_title']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-green-600 font-medium">
                                            $<?php echo number_format($donation['amount'], 2); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($donation['created_at'])); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action -->
<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Ready to Make a Difference?</h2>
        <p class="text-xl text-gray-600 mb-8">
            Join our community of creators and supporters. Start your project today!
        </p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="create-project.php" class="btn btn-primary btn-lg">
                <i class="fas fa-plus mr-2"></i>Start Your Project
            </a>
        <?php else: ?>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus mr-2"></i>Get Started
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 

