<?php
session_start();

use Google\Client;
use Google\Service\Oauth2;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Models/User.php';

try {
    $userModel = new User($conn);
    $client = buildGoogleClient();

    if (!isset($_GET['code'])) {
        $authUrl = $client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit;
    }

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        throw new RuntimeException($token['error_description'] ?? $token['error']);
    }

    $client->setAccessToken($token);

    $oauth2 = new Oauth2($client);
    $googleUser = $oauth2->userinfo->get();

    $email = $googleUser->email ?? '';
    if ($email === '') {
        throw new RuntimeException('Unable to retrieve email address from Google.');
    }

    $name = $googleUser->name ?: trim(($googleUser->givenName ?? '') . ' ' . ($googleUser->familyName ?? ''));
    $username = $googleUser->givenName ?: ($googleUser->email ? strstr($googleUser->email, '@', true) : 'user');

    $user = $userModel->findByEmail($email);

    if (!$user) {
        $uniqueUsername = generateUniqueUsername($userModel, $username, $email);
        $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $userId = $userModel->create([
            'username' => $uniqueUsername,
            'passwordHash' => $passwordHash,
            'name' => $name ?: ucfirst($uniqueUsername),
            'email' => $email,
            'phonenumber' => null,
            'user_role' => 'customer',
        ]);

        if (!$userId) {
            throw new RuntimeException('Failed to create user from Google account.');
        }

        $user = $userModel->findByUsernameOrEmail($email);
        if (!$user) {
            throw new RuntimeException('Failed to retrieve user after creation.');
        }
    }

    // Log the user in
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['user_role'];

    header('Location: /GuillermosWebSystem/');
    exit;
} catch (Throwable $e) {
    redirectWithError($e->getMessage());
}

function buildGoogleClient(): Client
{
    $client = new Client();
    $client->setAuthConfig(clientSecretPath());
    $client->setRedirectUri('http://localhost/GuillermosWebSystem/Controllers/GoogleAuthController.php');
    $client->setAccessType('offline');
    $client->setPrompt('consent');
    $client->setScopes([
        'email',
        'profile',
    ]);

    return $client;
}

function clientSecretPath(): string
{
    $matches = glob(__DIR__ . '/../client_secret_*.json');
    if (!$matches) {
        throw new RuntimeException('Client secret JSON not found.');
    }

    return $matches[0];
}

function generateUniqueUsername(User $userModel, string $base, string $email): string
{
    $candidate = preg_replace('/[^a-z0-9_]/i', '', strtolower($base));
    if ($candidate === '') {
        $candidate = 'user';
    }

    $original = $candidate;
    $suffix = 0;

    while ($userModel->existsByUsernameOrEmail($candidate, $email)) {
        $suffix++;
        $candidate = $original . $suffix;
    }

    return $candidate;
}

function redirectWithError(string $message): void
{
    $target = '/GuillermosWebSystem/Views/landing/index.php?error=' . urlencode($message);
    header('Location: ' . $target);
    exit;
}
