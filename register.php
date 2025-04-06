<?php
session_start();
require_once 'config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);

    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($errors)) {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username or email already exists";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password, $full_name])) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 text-center">Register</h3>
            </div>
            <div class="p-6">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <ul class="text-red-700 space-y-1">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-input" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               required>
                    </div>
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-input" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                    <div>
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-input" id="full_name" name="full_name" 
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                               required>
                    </div>
                    <div>
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-input" id="password" name="password" required>
                    </div>
                    <div>
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-input" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </button>
                </form>
                
                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Already have an account? 
                        <a href="login.php" class="text-primary-600 hover:text-primary-700 font-semibold">
                            Login here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 