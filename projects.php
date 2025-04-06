<?php
session_start();
require_once 'config/database.php';

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$query = "SELECT p.*, u.username, u.full_name as creator_name,
          (SELECT COUNT(*) FROM donations WHERE project_id = p.id) as donor_count,
          (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE project_id = p.id) as total_donations
          FROM projects p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.status = 'active'";

$params = [];

if (!empty($category)) {
    $query .= " AND p.category = :category";
    $params[':category'] = $category;
}

if (!empty($search)) {
    $query .= " AND p.title LIKE :search";
    $params[':search'] = "%$search%";
}

// Add sorting
switch ($sort) {
    case 'newest':
        $query .= " ORDER BY p.created_at DESC";
        break;
    case 'oldest':
        $query .= " ORDER BY p.created_at ASC";
        break;
    case 'highest':
        $query .= " ORDER BY total_donations DESC";
        break;
    case 'lowest':
        $query .= " ORDER BY total_donations ASC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
}

// Get total count for pagination
$count_query = str_replace("SELECT p.*, u.username", "SELECT COUNT(*)", $query);
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_projects = $stmt->fetchColumn();

// Add pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 9;
$total_pages = ceil($total_projects / $per_page);
$offset = ($page - 1) * $per_page;

$query .= " LIMIT :limit OFFSET :offset";
$params[':limit'] = $per_page;
$params[':offset'] = $offset;

// Fetch projects
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$projects = $stmt->fetchAll();

// Fetch categories for filter
$stmt = $conn->query("SELECT DISTINCT category FROM projects WHERE status = 'active' ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';
?>

<!-- Projects List -->
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">All Projects</h2>
        
        <form method="GET" class="flex flex-col md:flex-row gap-4 items-center">
            <div class="flex-1 w-full">
                <input type="text" class="form-input w-full h-12 text-lg" name="search" 
                       placeholder="Search project by name..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="w-full md:w-48">
                <select class="form-input w-full h-12 text-lg" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                                <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full md:w-24">
                <button type="submit" class="btn btn-primary w-full h-12 text-lg">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </div>
        </form>
    </div>

    <?php if (empty($projects)): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-700">No projects found matching your criteria.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($projects as $project): ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <?php if ($project['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($project['image_url']); ?>" 
                             class="w-full h-64 object-cover" 
                             alt="<?php echo htmlspecialchars($project['title']); ?>">
                    <?php endif; ?>
                    <div class="p-6">
                        <h5 class="text-xl font-semibold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($project['title']); ?>
                        </h5>
                        <p class="text-gray-600 mb-4">
                            <?php echo substr(htmlspecialchars($project['description']), 0, 100) . '...'; ?>
                        </p>
                        
                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <?php 
                                $percentage = ($project['total_donations'] / $project['funding_goal']) * 100;
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
                                $<?php echo number_format($project['funding_goal'], 2); ?> goal
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
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 