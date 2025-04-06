<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

// Get project ID from URL
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get project details
$stmt = $conn->prepare("SELECT p.*, u.username, u.full_name as creator_name,
                        (SELECT COUNT(*) FROM donations WHERE project_id = p.id) as donor_count,
                        (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE project_id = p.id) as total_donations
                        FROM projects p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: index.php");
    exit();
}

// Handle donation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $amount = (float)$_POST['amount'];
    if ($amount > 0 && $amount <= 100) {
        try {
            $stmt = $conn->prepare("INSERT INTO donations (project_id, user_id, amount) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $_SESSION['user_id'], $amount]);
            
            // Update project current amount
            $stmt = $conn->prepare("UPDATE projects SET current_amount = current_amount + ? WHERE id = ?");
            $stmt->execute([$amount, $project_id]);
            
            header("Location: project.php?id=" . $project_id . "&success=1");
            exit();
        } catch (PDOException $e) {
            $error = "Error processing donation. Please try again.";
        }
    } else {
        $error = "Donation amount must be between $1 and $100.";
    }
}

// Get recent donations
$stmt = $conn->prepare("SELECT d.*, u.username, u.full_name 
                        FROM donations d 
                        JOIN users u ON d.user_id = u.id 
                        WHERE d.project_id = ? 
                        ORDER BY d.created_at DESC 
                        LIMIT 5");
$stmt->execute([$project_id]);
$recent_donations = $stmt->fetchAll();
?>

<div class="min-h-screen bg-gray-50">
    <!-- Hero Section with Project Image -->
    <div class="bg-white border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2">
            <div class="relative h-[600px] md:h-full overflow-hidden">
                <img src="<?php echo !empty($project['image_url']) && file_exists($project['image_url']) ? $project['image_url'] : 'assets/images/default-project.jpg'; ?>" 
                     class="w-full h-full object-cover transition-transform duration-300 hover:scale-105" 
                     alt="<?php echo htmlspecialchars($project['title']); ?>">
                <div class="absolute top-5 right-5">
                    <span class="px-3 py-1 bg-blue-600 text-white rounded-full text-sm"><?php echo htmlspecialchars($project['category']); ?></span>
                </div>
            </div>
            <div class="p-8 md:p-12 flex flex-col justify-center">
                <div class="flex justify-between items-start mb-4">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-0"><?php echo htmlspecialchars($project['title']); ?></h1>
                    <span class="px-4 py-1 bg-green-500 text-white rounded-full text-sm">Active</span>
                </div>
                <p class="text-gray-600 mb-6">
                    <i class="fas fa-user-circle mr-2"></i>Created by <?php echo htmlspecialchars($project['creator_name']); ?>
                </p>
                
                <!-- Progress Bar -->
                <div class="bg-gray-50 p-6 rounded-xl mb-6">
                    <div class="flex justify-between mb-3">
                        <span class="text-green-600 font-bold">$<?php echo number_format($project['total_donations'], 2); ?></span>
                        <span class="text-gray-500">$<?php echo number_format($project['funding_goal'], 2); ?></span>
                    </div>
                    <div class="h-6 bg-gray-200 rounded-full overflow-hidden">
                        <?php 
                        $progress = 0;
                        if ($project['funding_goal'] > 0) {
                            $progress = ($project['total_donations'] / $project['funding_goal']) * 100;
                            $progress = min(100, max(0, $progress));
                        }
                        ?>
                        <div class="h-full bg-green-500 rounded-full transition-all duration-600" 
                             style="width: <?php echo $progress; ?>%">
                            <span class="text-white text-sm px-2"><?php echo round($progress); ?>%</span>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 p-4 rounded-xl flex items-center gap-4 hover:-translate-y-1 transition-transform">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-blue-600 mb-0"><?php echo $project['donor_count']; ?></h3>
                            <p class="text-gray-500 text-sm mb-0">Supporters</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl flex items-center gap-4 hover:-translate-y-1 transition-transform">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-blue-600 mb-0"><?php echo date('d M', strtotime($project['created_at'])); ?></h3>
                            <p class="text-gray-500 text-sm mb-0">Launched</p>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="" class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <div class="mb-4">
                            <label for="amount" class="block text-gray-700 font-bold mb-2">Support this project</label>
                            <div class="flex flex-col md:flex-row gap-2">
                                <div class="relative flex-1">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="fas fa-dollar-sign"></i>
                                    </span>
                                    <input type="number" 
                                           class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                           id="amount" name="amount" 
                                           min="1" max="100" required placeholder="Enter amount">
                                </div>
                                <button type="submit" name="donate" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors">
                                    <i class="fas fa-heart mr-2"></i>Donate Now
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>Maximum donation amount is $100
                            </p>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <a href="login.php" class="w-full bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors inline-block">
                            <i class="fas fa-user-lock mr-2"></i>Login to Donate
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Project Description and Impact -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h5 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>About this project
                    </h5>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                    
                    <!-- Project Impact Section -->
                    <div class="mt-8">
                        <h5 class="text-xl font-bold text-gray-900 mb-4">
                            <i class="fas fa-chart-line text-green-600 mr-2"></i>Project Impact
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-6 rounded-xl hover:-translate-y-1 transition-transform">
                                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-users text-green-600 text-2xl"></i>
                                </div>
                                <h6 class="text-lg font-semibold text-green-600 mb-3">Community Impact</h6>
                                <p class="text-gray-600">This project aims to create positive change in the community by <?php echo htmlspecialchars($project['category']); ?> initiatives. Your support will help make a real difference.</p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-xl hover:-translate-y-1 transition-transform">
                                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-seedling text-green-600 text-2xl"></i>
                                </div>
                                <h6 class="text-lg font-semibold text-green-600 mb-3">Long-term Benefits</h6>
                                <p class="text-gray-600">Beyond immediate impact, this project will create lasting benefits for future generations through sustainable development and innovation.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Project Details Section -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h5 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>Project Details
                    </h5>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 rounded-xl flex items-center gap-4 hover:translate-x-1 transition-transform">
                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar text-blue-600"></i>
                            </div>
                            <div>
                                <h6 class="font-semibold text-blue-600 mb-1">Timeline</h6>
                                <p class="text-gray-600 text-sm mb-0">Started on <?php echo date('F j, Y', strtotime($project['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl flex items-center gap-4 hover:translate-x-1 transition-transform">
                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-tag text-blue-600"></i>
                            </div>
                            <div>
                                <h6 class="font-semibold text-blue-600 mb-1">Category</h6>
                                <p class="text-gray-600 text-sm mb-0"><?php echo htmlspecialchars($project['category']); ?></p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl flex items-center gap-4 hover:translate-x-1 transition-transform">
                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <h6 class="font-semibold text-blue-600 mb-1">Creator</h6>
                                <p class="text-gray-600 text-sm mb-0"><?php echo htmlspecialchars($project['creator_name']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Project Updates Section -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h5 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-bullhorn text-yellow-600 mr-2"></i>Project Updates
            </h5>
            <div class="relative pl-6">
                <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                <div class="relative mb-6">
                    <div class="absolute -left-8 top-0 w-4 h-4 bg-blue-600 rounded-full"></div>
                    <div class="text-sm text-gray-500 mb-2"><?php echo date('M d, Y', strtotime($project['created_at'])); ?></div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h6 class="font-semibold text-blue-600 mb-2">Project Launched</h6>
                        <p class="text-gray-600">Project was successfully launched and is now accepting donations.</p>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -left-8 top-0 w-4 h-4 bg-blue-600 rounded-full"></div>
                    <div class="text-sm text-gray-500 mb-2">Current</div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h6 class="font-semibold text-blue-600 mb-2">Active Fundraising</h6>
                        <p class="text-gray-600">Currently raising funds to achieve the project goals. <?php echo round($progress); ?>% of the funding goal has been reached.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Donations -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h5 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-heart text-red-600 mr-2"></i>Recent Supporters
            </h5>
            <?php if (count($recent_donations) > 0): ?>
                <div class="space-y-3">
                    <?php foreach ($recent_donations as $donation): ?>
                        <div class="bg-gray-50 p-4 rounded-lg hover:translate-x-1 transition-transform">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h6 class="text-blue-600 font-semibold mb-1"><?php echo htmlspecialchars($donation['full_name']); ?></h6>
                                    <small class="text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?php echo date('M d, Y', strtotime($donation['created_at'])); ?>
                                    </small>
                                </div>
                                <span class="px-3 py-1 bg-green-500 text-white rounded-full text-sm">
                                    <i class="fas fa-dollar-sign mr-1"></i>
                                    <?php echo number_format($donation['amount'], 2); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-hand-holding-heart text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-500">No donations yet. Be the first to support this project!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 