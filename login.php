<?php
session_start();
require_once 'config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username)) {
        $errors[] = "Username is required";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Invalid username or password";
        }
    }
}

include 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-3xl font-bold text-gray-900 text-center">Login</h2>
                </div>
                <div class="p-6">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                                <p class="text-green-700">
                                    <?php 
                                    echo $_SESSION['success'];
                                    unset($_SESSION['success']);
                                    ?>
                                </p>
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

                    <form method="POST" action="" class="space-y-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input type="text" class="form-input w-full" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   placeholder="Enter your username" required>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" class="form-input w-full" id="password" name="password" 
                                   placeholder="Enter your password" required>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary w-full">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login
                            </button>
                        </div>
                    </form>
                    <div class="text-center mt-6">
                        <p class="text-gray-600">Don't have an account? <a href="register.php" class="text-primary-600 hover:text-primary-500">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 