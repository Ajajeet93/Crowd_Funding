<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $goal_amount = floatval($_POST['goal_amount']);
    $category = trim($_POST['category']);
    $deadline = $_POST['deadline'];
    $image_url = '';

    // Validation
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    if ($goal_amount <= 0) {
        $errors[] = "Goal amount must be greater than 0";
    }
    if (empty($category)) {
        $errors[] = "Category is required";
    }
    if (empty($deadline)) {
        $errors[] = "Deadline is required";
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $newname = uniqid() . '.' . $filetype;
            $upload_path = 'uploads/' . $newname;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = $upload_path;
            } else {
                $errors[] = "Failed to upload image";
            }
        } else {
            $errors[] = "Invalid file type. Allowed types: " . implode(', ', $allowed);
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, funding_goal, category, deadline, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $title, $description, $goal_amount, $category, $deadline, $image_url])) {
            $success = true;
        } else {
            $errors[] = "Failed to create project";
        }
    }
}

include 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-3xl font-bold text-gray-900 text-center">Create New Project</h2>
                </div>
                <div class="p-6">
                    <?php if ($success): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                                <p class="text-green-700">Project created successfully! <a href="index.php" class="underline">View all projects</a></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                                <ul class="text-red-700">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Project Title</label>
                            <input type="text" class="form-input w-full" id="title" name="title" 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                   placeholder="Enter your project title" required>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Project Description</label>
                            <textarea class="form-input w-full" id="description" name="description" rows="5" 
                                      placeholder="Describe your project in detail" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div>
                            <label for="goal_amount" class="block text-sm font-medium text-gray-700 mb-2">Goal Amount ($)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                                <input type="number" class="form-input w-full pl-8" id="goal_amount" name="goal_amount" 
                                       value="<?php echo isset($_POST['goal_amount']) ? htmlspecialchars($_POST['goal_amount']) : ''; ?>" 
                                       min="1" step="0.01" placeholder="Enter your funding goal" required>
                            </div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select class="form-input w-full" id="category" name="category" required>
                                <option value="">Select a category</option>
                                <option value="Education" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Education') ? 'selected' : ''; ?>>Education</option>
                                <option value="Environment" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Environment') ? 'selected' : ''; ?>>Environment</option>
                                <option value="Health" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Health') ? 'selected' : ''; ?>>Health</option>
                                <option value="Social" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Social') ? 'selected' : ''; ?>>Social</option>
                                <option value="Technology" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Technology') ? 'selected' : ''; ?>>Technology</option>
                                <option value="Other" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="deadline" class="block text-sm font-medium text-gray-700 mb-2">Project Deadline</label>
                            <input type="date" class="form-input w-full" id="deadline" name="deadline" 
                                   value="<?php echo isset($_POST['deadline']) ? htmlspecialchars($_POST['deadline']) : ''; ?>" required>
                        </div>

                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Project Image</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-3"></i>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                            <span>Upload a file</span>
                                            <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PNG, JPG, GIF up to 10MB
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Create Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 