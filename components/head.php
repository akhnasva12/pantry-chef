<?php
// components/head.php  — include inside <head> on every page
// Requires config.php already loaded (via db.php or auth.php)
$pageTitle = $pageTitle ?? 'PantryChef — Recipes For Every Budget';
$pageDesc  = $pageDesc  ?? 'Find delicious recipes that fit your budget.';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style"
      href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
      onload="this.onload=null;this.rel='stylesheet'">
<noscript>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap">
</noscript>
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
