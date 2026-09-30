<?php
// components/navbar.php
require_once __DIR__ . '/../includes/auth.php';
$user = currentUser();
?>
<nav class="navbar" id="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <a href="<?= BASE_URL ?>index.php" class="nav-logo">
            <span class="logo-icon">🍳</span>
            <span class="logo-text">Pantry<strong>Chef</strong></span>
        </a>

        <!-- Desktop search -->
        <div class="nav-search-wrap">
            <div class="nav-search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="navSearchInput" placeholder="Search recipes, cuisines..." autocomplete="off">
                <div id="navSearchResults" class="nav-search-dropdown"></div>
            </div>
        </div>

        <!-- Desktop nav links -->
        <div class="nav-links">
            <a href="<?= BASE_URL ?>index.php" class="nav-link">Explore</a>
            <?php if ($user['role'] === 'creator'): ?>
                <a href="<?= BASE_URL ?>creator/dashboard.php" class="nav-link">My Kitchen</a>
            <?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>admin/dashboard.php" class="nav-link nav-link--admin">Admin</a>
            <?php endif; ?>
            <?php if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>profile.php" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <?= sanitize($user['name']) ?>
                </a>
                <a href="<?= BASE_URL ?>logout.php" class="btn btn-ghost">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login.php" class="btn btn-ghost">Login</a>
                <a href="<?= BASE_URL ?>signup.php" class="btn btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>

        <!-- Hamburger button (mobile only) -->
        <button class="nav-hamburger" id="navHamburger" aria-label="Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>

    <!-- Mobile menu (slides down) -->
    <div class="nav-mobile-menu" id="navMobileMenu">

        <!-- Search inside mobile menu -->
        <div class="nav-mobile-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" id="mobileSearchInput" placeholder="Search recipes..." autocomplete="off">
        </div>

        <a href="<?= BASE_URL ?>index.php">🍽️ Explore Recipes</a>

        <?php if (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>profile.php">👤 My Profile</a>
            <?php if ($user['role'] === 'creator'): ?>
                <a href="<?= BASE_URL ?>creator/dashboard.php">🍳 My Kitchen</a>
            <?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>admin/dashboard.php" style="color:var(--danger)">🛠 Admin Panel</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>logout.php" style="color:var(--danger)">🚪 Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>login.php">🔑 Login</a>
            <a href="<?= BASE_URL ?>signup.php">✨ Sign Up Free</a>
            <a href="<?= BASE_URL ?>creator-signup.php">👨‍🍳 Join as Creator Chef</a>
        <?php endif; ?>
    </div>
</nav>