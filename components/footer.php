<?php
// components/footer.php
?>
<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <a href="<?= BASE_URL ?>index.php" class="nav-logo">
                <span class="logo-icon">🍳</span>
                <span class="logo-text">Pantry<strong>Chef</strong></span>
            </a>
            <p>Find delicious recipes that fit your budget and your pantry.</p>
        </div>
        <div class="footer-links">
            <h4>Explore</h4>
            <a href="<?= BASE_URL ?>index.php">All Recipes</a>
            <a href="<?= BASE_URL ?>index.php?category=Breakfast">Breakfast</a>
            <a href="<?= BASE_URL ?>index.php?category=Dinner">Dinner</a>
            <a href="<?= BASE_URL ?>index.php?tag=Low+Budget">Budget Meals</a>
        </div>
        <div class="footer-links">
            <h4>For Chefs</h4>
            <a href="<?= BASE_URL ?>creator-signup.php">Become a Creator</a>
            <a href="<?= BASE_URL ?>creator/dashboard.php">Creator Dashboard</a>
        </div>
        <div class="footer-links">
            <h4>Account</h4>
            <a href="<?= BASE_URL ?>login.php">Login</a>
            <a href="<?= BASE_URL ?>signup.php">Sign Up</a>
            <a href="<?= BASE_URL ?>profile.php">My Profile</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> PantryChef, for food lovers.</p>
    </div>
</footer>