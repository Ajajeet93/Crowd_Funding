<?php
session_start();
require_once 'config/database.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    if (empty($message)) {
        $errors[] = "Message is required";
    }

    if (empty($errors)) {
        // Here you would typically send an email or save to database
        // For now, we'll just set success to true
        $success = true;
    }
}

include 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-3xl font-bold text-gray-900 text-center">Contact Us</h2>
                    <p class="text-gray-600 text-center mt-2">Have questions? We'd love to hear from you.</p>
                </div>
                <div class="p-6">
                    <?php if ($success): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                                <p class="text-green-700">Thank you for your message! We'll get back to you soon.</p>
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
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                            <input type="text" class="form-input w-full" id="name" name="name" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                   placeholder="Enter your name" required>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" class="form-input w-full" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   placeholder="Enter your email" required>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                            <input type="text" class="form-input w-full" id="subject" name="subject" 
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" 
                                   placeholder="Enter the subject" required>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                            <textarea class="form-input w-full" id="message" name="message" rows="5" 
                                      placeholder="Enter your message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-map-marker-alt text-primary-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Our Location</h3>
                    <p class="text-gray-600">123 Main Street<br>City, State 12345</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-phone text-primary-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Phone Number</h3>
                    <p class="text-gray-600">+1 (123) 456-7890<br>Mon-Fri, 9am-5pm</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-envelope text-primary-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Email Address</h3>
                    <p class="text-gray-600">support@crowdfunding.com<br>info@crowdfunding.com</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 