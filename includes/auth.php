<?php
// includes/auth.php
if (!defined('BASE_URL')) require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isCreator(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'creator';
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(string $redirect = ''): void {
    if (!isLoggedIn()) {
        $dest = $redirect ?: BASE_URL . 'login.php';
        header("Location: $dest");
        exit;
    }
}

function requireCreator(): void {
    requireLogin();

    // Admin should always have access
    if (isAdmin()) return;

    global $pdo;

    $stmt = $pdo->prepare("SELECT status FROM creators WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $creator = $stmt->fetch();

    // If no creator record OR not approved → block
    if (!$creator) {
        header("Location: " . BASE_URL . "index.php?error=not_creator");
        exit;
    }

    if ($creator['status'] === 'pending') {
        header("Location: " . BASE_URL . "creator-request-pending.php");
        exit;
    }

    if ($creator['status'] === 'rejected') {
        header("Location: " . BASE_URL . "creator-request-pending.php?status=rejected");
        exit;
    }

    if ($creator['status'] !== 'approved') {
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header("Location: " . BASE_URL . "index.php?error=access_denied");
        exit;
    }
}

function currentUser(): array {
    return [
        'id'   => $_SESSION['user_id']   ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['role']      ?? 'guest',
    ];
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword(string $password): bool {
    // Min 8 chars, at least 1 letter and 1 number
    return strlen($password) >= 8
        && preg_match('/[A-Za-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}