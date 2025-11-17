<?php
session_start(); // Start session at the very top

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handles registration and login actions
require_once __DIR__ . '/../vendor/autoload.php'; // Composer's autoloader for PHPMailer
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/EmailApiController.php'; // Use the new Email API controller

$userModel = new User($conn);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

function isAjaxRequest(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function jsonResponse(array $payload, int $statusCode = 200): void
{
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phonenumber = trim($_POST['phonenumber'] ?? '');

    $errors = [];
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($password)) $errors[] = 'Password is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required';
    }

    if (!empty($errors)) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => implode(' ', $errors),
            ], 422);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode(implode(', ', $errors)));
        exit;
    }

    if ($userModel->existsByUsernameOrEmail($username, $email)) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Username or email already exists',
            ], 409);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Username or email already exists'));
        exit;
    }

    // Generate verification code and store user data in session
    $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
    $_SESSION['registration_data'] = [
        'username' => $username,
        'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
        'name' => $name,
        'email' => $email,
        'phonenumber' => $phonenumber === '' ? null : $phonenumber,
        'user_role' => 'customer',
        'verification_code' => $verification_code,
        'timestamp' => time() // To check for expiration
    ];

    // --- Send Email using the EmailApiController ---
    $emailResult = EmailApiController::sendVerificationEmail($email, $name, $verification_code);

    if ($emailResult === true) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'success',
                'email' => $email,
                'message' => 'Verification code sent to ' . $email . '.',
            ]);
        }

        // Redirect to verification page on success
        header('Location: /GuillermosWebSystem/Views/landing/verify.php?email=' . urlencode($email));
        exit;
    } else {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => is_string($emailResult) ? $emailResult : 'Failed to send verification email.',
            ], 500);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Unable to send verification email. Please try again later.'));
        exit;
    }
}

if ($action === 'verify-email' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = trim($_POST['verification_code'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($submitted_code) || empty($email)) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Verification code is required.',
            ], 422);
        }

        header('Location: /GuillermosWebSystem/Views/landing/verify.php?error=' . urlencode('Verification code is required.'));
        exit;
    }

    // Check if session data exists and is not expired (e.g., 10 minutes)
    if (!isset($_SESSION['registration_data']) || $_SESSION['registration_data']['email'] !== $email) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Verification session not found. Please register again.',
            ], 409);
        }

        header('Location: /GuillermosWebSystem/Views/landing/verify.php?error=' . urlencode('Verification session not found. Please register again.'));
        exit;
    }

    if (time() - $_SESSION['registration_data']['timestamp'] > 600) { // 10 minutes
        unset($_SESSION['registration_data']);
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Verification code has expired. Please register again.',
            ], 410);
        }

        header('Location: /GuillermosWebSystem/Views/landing/verify.php?error=' . urlencode('Verification code has expired. Please register again.'));
        exit;
    }

    if ($_SESSION['registration_data']['verification_code'] !== $submitted_code) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Invalid verification code.',
            ], 422);
        }

        header('Location: /GuillermosWebSystem/Views/landing/verify.php?email=' . urlencode($email) . '&error=' . urlencode('Invalid verification code.'));
        exit;
    }

    // --- Verification successful, create user ---
    $data = $_SESSION['registration_data'];
    $created = $userModel->create($data);

    unset($_SESSION['registration_data']); // Clean up session

    if ($created) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'success',
                'message' => 'Account verified successfully.',
            ]);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?registered=1');
        exit;
    } else {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Failed to create account after verification.',
            ], 500);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Failed to create account after verification.'));
        exit;
    }
}

if ($action === 'resend-code' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['registration_data'])) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'No pending registration found. Please start again.',
            ], 410);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('No pending registration found. Please register again.'));
        exit;
    }

    $email = $_SESSION['registration_data']['email'];
    $name = $_SESSION['registration_data']['name'] ?? 'Customer';

    // Optional email check if provided to avoid tampering
    if (!empty($_POST['email']) && trim($_POST['email']) !== $email) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Email mismatch. Please register again.',
            ], 409);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Email mismatch. Please register again.'));
        exit;
    }

    $verification_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
    $_SESSION['registration_data']['verification_code'] = $verification_code;
    $_SESSION['registration_data']['timestamp'] = time();

    $emailResult = EmailApiController::sendVerificationEmail($email, $name, $verification_code);

    if ($emailResult === true) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'success',
                'message' => 'A new verification code has been sent.',
            ]);
        }

        header('Location: /GuillermosWebSystem/Views/landing/verify.php?email=' . urlencode($email) . '&resent=1');
        exit;
    }

    if (isAjaxRequest()) {
        jsonResponse([
            'status' => 'error',
            'message' => is_string($emailResult) ? $emailResult : 'Unable to resend verification email.',
        ], 500);
    }

    header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Unable to resend verification email. Please try again later.'));
    exit;
}

if ($action === 'forgot-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Please provide a valid email address.',
            ], 422);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Please provide a valid email address.'));
        exit;
    }

    $user = $userModel->findByEmail($email);
    if (!$user) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'No account found with that email address.',
            ], 404);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('No account found with that email address.'));
        exit;
    }

    $resetCode = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
    $_SESSION['password_reset'] = [
        'user_id' => $user['user_id'],
        'email' => $email,
        'code' => $resetCode,
        'timestamp' => time(),
    ];

    $emailResult = EmailApiController::sendPasswordResetEmail($email, $user['name'] ?? $user['username'] ?? 'Customer', $resetCode);

    if ($emailResult !== true) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => is_string($emailResult) ? $emailResult : 'Unable to send reset code. Please try again later.',
            ], 500);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Unable to send reset code. Please try again later.'));
        exit;
    }

    if (isAjaxRequest()) {
        jsonResponse([
            'status' => 'success',
            'message' => 'A reset code has been sent to your email address.',
            'email' => $email,
        ]);
    }

    header('Location: /GuillermosWebSystem/Views/landing/index.php?notice=' . urlencode('A reset code has been sent to your email address.'));
    exit;
}

if ($action === 'reset-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['reset_code'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $code === '' || $newPassword === '') {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Incomplete reset information.',
            ], 422);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Incomplete reset information.'));
        exit;
    }

    if (!isset($_SESSION['password_reset']) || $_SESSION['password_reset']['email'] !== $email) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Reset session not found. Please start over.',
            ], 409);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Reset session not found. Please start over.'));
        exit;
    }

    $resetData = $_SESSION['password_reset'];
    if (time() - $resetData['timestamp'] > 600) {
        unset($_SESSION['password_reset']);
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Reset code has expired. Please request a new one.',
            ], 410);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Reset code has expired. Please request a new one.'));
        exit;
    }

    if ($resetData['code'] !== $code) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Invalid reset code. Please try again.',
            ], 422);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Invalid reset code. Please try again.'));
        exit;
    }

    if (strlen($newPassword) < 6) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Password must be at least 6 characters long.',
            ], 422);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Password must be at least 6 characters long.'));
        exit;
    }

    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updated = $userModel->updatePassword((int)$resetData['user_id'], $passwordHash);

    unset($_SESSION['password_reset']);

    if (!$updated) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Unable to update password. Please try again.',
            ], 500);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Unable to update password. Please try again.'));
        exit;
    }

    if (isAjaxRequest()) {
        jsonResponse([
            'status' => 'success',
            'message' => 'Password updated successfully. You can now log in.',
        ]);
    }

    header('Location: /GuillermosWebSystem/Views/landing/index.php?reset=1');
    exit;
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identity === '' || $password === '') {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Please provide both email/username and password.',
            ], 422);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Missing credentials'));
        exit;
    }

    $user = $userModel->findByUsernameOrEmail($identity);
    if (!$user || !password_verify($password, $user['password'])) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Invalid credentials. Please try again.',
            ], 401);
        }

        header('Location: /GuillermosWebSystem/Views/landing/index.php?error=' . urlencode('Invalid credentials'));
        exit;
    }

    // Simple session login
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['user_role'];

    $role = strtolower($user['user_role'] ?? '');
    switch ($role) {
        case 'customer':
            $destination = '/GuillermosWebSystem/Views/customer_dashboard/Customer.php';
            break;
        case 'staff':
            $destination = '/GuillermosWebSystem/Views/staff_dashboard/staff.php';
            break;
        case 'owner':
        case 'admin':
            $destination = '/GuillermosWebSystem/Views/owner_dashboard/Owner.php';
            break;
        default:
            $destination = '/GuillermosWebSystem/';
            break;
    }

    if (isAjaxRequest()) {
        jsonResponse([
            'status' => 'success',
            'redirect' => $destination,
        ]);
    }

    header('Location: ' . $destination);
    exit;
}

// Unknown action -> redirect
header('Location: /GuillermosWebSystem/');
exit;
