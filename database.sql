-- ============================================
-- THE PANTRY CHEF — Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS pantry_chef CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pantry_chef;

-- ============================================
-- USERS
-- ============================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'creator', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- ============================================
-- CREATORS (Chef profiles)
-- ============================================
CREATE TABLE creators (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    chef_name VARCHAR(100) NOT NULL,
    bio TEXT,
    profile_image VARCHAR(255),
    location VARCHAR(100),
    speciality_cuisine VARCHAR(100),
    social_link VARCHAR(255),
    experience_years TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- ============================================
-- CATEGORIES
-- ============================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('meal', 'food_type', 'special') DEFAULT 'meal',
    icon VARCHAR(10),
    sort_order TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB;

-- Seed categories
INSERT INTO categories (name, type, icon, sort_order) VALUES
('Breakfast', 'meal', '🌅', 1),
('Lunch', 'meal', '☀️', 2),
('Dinner', 'meal', '🌙', 3),
('Snacks', 'meal', '🍿', 4),
('Fast Food', 'meal', '🍔', 5),
('Street Food', 'meal', '🌮', 6),
('Bakery', 'meal', '🥐', 7),
('Desserts', 'meal', '🍰', 8),
('Sweets', 'meal', '🍬', 9),
('Beverages', 'meal', '🥤', 10),
('Juices', 'meal', '🍊', 11),
('Smoothies', 'meal', '🥤', 12),
('Tea & Coffee', 'meal', '☕', 13),
('Soups', 'meal', '🍜', 14),
('Salads', 'meal', '🥗', 15),
('Veg', 'food_type', '🥦', 16),
('Non-Veg', 'food_type', '🍗', 17),
('Vegan', 'food_type', '🌱', 18),
('Egg-based', 'food_type', '🥚', 19),
('Healthy', 'special', '💪', 20),
('Quick Meals', 'special', '⚡', 21),
('High Protein', 'special', '🏋️', 22),
('Low Budget', 'special', '💰', 23),
('Kids Friendly', 'special', '👶', 24),
('Spicy', 'special', '🌶️', 25),
('Ramadan Special', 'special', '🌙', 26);

-- ============================================
-- RECIPES
-- ============================================
CREATE TABLE recipes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    cooking_time SMALLINT UNSIGNED DEFAULT 0 COMMENT 'minutes',
    budget DECIMAL(8,2) DEFAULT 0.00 COMMENT 'in local currency',
    category_id INT UNSIGNED,
    cuisine VARCHAR(100),
    created_by INT UNSIGNED NOT NULL,
    status ENUM('active', 'draft', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_creator (created_by),
    INDEX idx_budget (budget),
    INDEX idx_cooking_time (cooking_time),
    INDEX idx_cuisine (cuisine),
    INDEX idx_status (status),
    FULLTEXT INDEX idx_search (title, description)
) ENGINE=InnoDB;

-- ============================================
-- INGREDIENTS
-- ============================================
CREATE TABLE ingredients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    ingredient_name VARCHAR(200) NOT NULL,
    quantity VARCHAR(100),
    sort_order TINYINT UNSIGNED DEFAULT 0,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    INDEX idx_recipe (recipe_id)
) ENGINE=InnoDB;

-- ============================================
-- STEPS
-- ============================================
CREATE TABLE steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    step_number TINYINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    INDEX idx_recipe (recipe_id)
) ENGINE=InnoDB;

-- ============================================
-- FAVORITES
-- ============================================
CREATE TABLE favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    recipe_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fav (user_id, recipe_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================
-- HISTORY
-- ============================================
CREATE TABLE history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    recipe_id INT UNSIGNED NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    INDEX idx_user_viewed (user_id, viewed_at)
) ENGINE=InnoDB;

-- ============================================
-- VIEWS (analytics)
-- ============================================
CREATE TABLE views (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED,
    ip_address VARCHAR(45),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_recipe (recipe_id),
    INDEX idx_viewed_at (viewed_at)
) ENGINE=InnoDB;

-- ============================================
-- DEFAULT ADMIN
-- Password: Admin@1234 (change after setup)
-- ============================================
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@pantrychef.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
