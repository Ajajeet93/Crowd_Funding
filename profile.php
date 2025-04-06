<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch user's projects
$stmt = $conn->prepare("SELECT p.*, 
    (SELECT COUNT(*) FROM donations WHERE project_id = p.id) as donor_count,
    (SELECT SUM(amount) FROM donations WHERE project_id = p.id) as total_donations
    FROM projects p 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();

// Fetch user's donations
$stmt = $conn->prepare("SELECT d.*, p.title as project_title 
    FROM donations d 
    JOIN projects p ON d.project_id = p.id 
    WHERE d.user_id = ? 
    ORDER BY d.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$donations = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Profile Information -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h5 class="text-xl font-bold text-gray-900 mb-4">Profile Information</h5>
                <div class="space-y-3">
                    <p class="text-gray-600">
                        <span class="font-semibold text-gray-900">Username:</span> 
                        <?php echo htmlspecialchars($user['username']); ?>
                    </p>
                    <p class="text-gray-600">
                        <span class="font-semibold text-gray-900">Full Name:</span> 
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    </p>
                    <p class="text-gray-600">
                        <span class="font-semibold text-gray-900">Email:</span> 
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p class="text-gray-600">
                        <span class="font-semibold text-gray-900">Member Since:</span> 
                        <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Projects and Donations -->
        <div class="md:col-span-2 space-y-8">
            <!-- My Projects -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h5 class="text-xl font-bold text-gray-900">My Projects</h5>
                </div>
                <div class="p-6">
                    <?php if (empty($projects)): ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500 mb-4">You haven't created any projects yet.</p>
                            <a href="create-project.php" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Create a Project
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($projects as $project): ?>
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h5 class="text-lg font-semibold text-gray-900 mb-2">
                                        <?php echo htmlspecialchars($project['title']); ?>
                                    </h5>
                                    <p class="text-gray-600 mb-4">
                                        <?php echo substr(htmlspecialchars($project['description']), 0, 150) . '...'; ?>
                                    </p>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mb-4">
                                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <?php 
                                            $percentage = ($project['total_donations'] / $project['goal_amount']) * 100;
                                            $percentage = min($percentage, 100);
                                            ?>
                                            <div class="h-full bg-primary-600 rounded-full transition-all duration-500"
                                                 style="width: <?php echo $percentage; ?>%">
                                                <span class="text-white text-xs px-2"><?php echo round($percentage); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                                        <span>
                                            $<?php echo number_format($project['total_donations'], 2); ?> raised of 
                                            $<?php echo number_format($project['goal_amount'], 2); ?> goal
                                        </span>
                                        <span><?php echo $project['donor_count']; ?> donors</span>
                                    </div>
                                    
                                    <a href="project.php?id=<?php echo $project['id']; ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-eye mr-2"></i>View Project
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- My Donations -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h5 class="text-xl font-bold text-gray-900">My Donations</h5>
                </div>
                <div class="p-6">
                    <?php if (empty($donations)): ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500 mb-4">You haven't made any donations yet.</p>
                            <a href="projects.php" class="btn btn-primary">
                                <i class="fas fa-search mr-2"></i>Browse Projects
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($donations as $donation): ?>
                                <div class="flex justify-between items-start p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <h6 class="font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($donation['project_title']); ?>
                                        </h6>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-semibold text-primary-600">
                                            $<?php echo number_format($donation['amount'], 2); ?>
                                        </span>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?php echo date('M j, Y', strtotime($donation['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 