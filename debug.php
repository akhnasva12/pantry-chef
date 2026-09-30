<?php
// debug.php — visit this to check BASE_URL value
// DELETE after checking!
require_once 'includes/config.php';
echo "<h2>PantryChef Debug</h2>";
echo "<p><strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>BASE_PATH:</strong> " . BASE_PATH . "</p>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
echo "<p><strong>ASSETS:</strong> " . ASSETS . "</p>";
echo "<p><strong>UPLOADS:</strong> " . UPLOADS . "</p>";
echo "<p style='color:green'>If BASE_URL is <strong>/pantry-chef/</strong> then everything is correct!</p>";
echo "<p style='color:red'>Delete this file after checking.</p>";