<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowd Funding Platform</title>
    
    <!-- Tailwind CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="text-2xl font-bold text-primary-600">
                    Crowd Funding
                </a>
                
                <div class="flex items-center space-x-4">
                    <a href="projects.php" class="text-gray-600 hover:text-primary-600">
                        <i class="fas fa-project-diagram mr-2"></i>Projects
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="create-project.php" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>Create Project
                        </a>
                        <a href="profile.php" class="text-gray-600 hover:text-primary-600">
                            <i class="fas fa-user-circle mr-2"></i>Profile
                        </a>
                        <a href="logout.php" class="text-gray-600 hover:text-primary-600">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="register.php" class="btn btn-secondary">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4"> 