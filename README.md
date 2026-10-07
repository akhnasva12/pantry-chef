**LIVE DEMO** -  https://pantry-chef.site.je/

# PantryChef 

**Pantry Chef** is a web-based application designed to help customers decide what they can cook using the ingredients they already have at home. Instead of buying additional ingredients or spending time searching through different recipes, customers can enter or select the ingredients available in their pantry, and the application helps them find suitable recipes based on those ingredients. This makes meal planning easier, reduces food wastage by encouraging customers to use ingredients before they expire, and saves both time and money. The project can also be useful for customers who are unsure about what to prepare for breakfast, lunch, dinner, or snacks, as it gives them practical recipe suggestions based on what is already available. Overall, Pantry Chef provides a convenient and user-friendly way for customers to manage their available ingredients and discover meals they can prepare at home.

# PantryChef — Setup Guide

## Requirements
- PHP 8.0+
- MySQL 8.0+
- Apache with mod_rewrite enabled

## Installation

### 1. Database Setup
```bash
mysql -u root -p < database.sql
```
This creates the `pantry_chef` database with all tables and seed data.

### 2. Configure Database
Edit `/includes/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'pantry_chef');
```

### 3. Web Server Setup
Point your Apache/Nginx document root to the `/project` folder.

For Apache, ensure `AllowOverride All` is set for the directory.

### 4. Upload Directories
Ensure these directories are **writable** by the web server:
```bash
chmod 755 uploads/recipes/
chmod 755 uploads/creators/
```

### 5. Default Admin Account
- **Email:** admin@pantrychef.com
- **Password:** 

## File Structure
```
/pantry-chef
├── /assets
│   ├── /css        → main.css (all styles)
│   ├── /js         → main.js (all scripts)
│   └── /images     → placeholder.jpg
├── /includes
│   ├── db.php      → PDO database connection
│   └── auth.php    → Session, CSRF, validation helpers
├── /components
│   ├── navbar.php  → Top navigation
│   ├── footer.php  → Site footer
│   └── recipe-card.php → Reusable recipe card
├── /ajax
│   ├── recipes.php     → Filter & search recipes
│   ├── search.php      → Live search (navbar)
│   ├── favorite.php    → Toggle favorite
│   ├── delete-recipe.php
│   └── delete-user.php
├── /creator
│   ├── dashboard.php   → Analytics + recipe management
│   ├── add-recipe.php  → Add new recipe form
│   └── edit-recipe.php → Edit existing recipe
├── /admin
│   └── dashboard.php   → Admin panel (users, creators, recipes)
├── /uploads
│   ├── /recipes        → Recipe images (writable)
│   └── /creators       → Chef profile images (writable)
├── index.php           → Homepage
├── login.php           → Login
├── signup.php          → User registration
├── creator-signup.php  → Creator (chef) registration
├── recipe.php          → Recipe detail + voice guide
├── profile.php         → User profile (favorites, history)
├── creator-profile.php → Public chef profile page
├── logout.php
├── 404.php
├── database.sql        → Full DB schema + seed data
└── .htaccess           → Security + rewrite rules
```

## Features
- ✅ Session-based auth with CSRF protection
- ✅ Separate User + Creator signup flows
- ✅ Live AJAX search & filters (category, cuisine, budget, time)
- ✅ Voice guide for recipe steps (Web Speech API)
- ✅ AJAX favorites with heart animation
- ✅ Custom animated cursor
- ✅ Skeleton loaders
- ✅ Creator analytics dashboard
- ✅ Admin panel (manage users, creators, recipes)
- ✅ Responsive (mobile-first)
- ✅ Lazy loading images
- ✅ Fade-in scroll animations

## Security
- All DB queries use PDO prepared statements
- Passwords hashed with bcrypt (cost 12)
- CSRF tokens on all POST forms
- Input sanitization throughout
- Role-based access control
- Direct folder access blocked via .htaccess
