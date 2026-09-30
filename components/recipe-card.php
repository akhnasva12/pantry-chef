<?php
// components/recipe-card.php
// Usage: include with $recipe array in scope
$isFavorited = !empty($recipe['is_favorited']);
$isGuest     = !isLoggedIn();
$image       = !empty($recipe['image']) ? UPLOADS . '/recipes/' . htmlspecialchars($recipe['image']) : ASSETS . '/images/placeholder.jpg';
$recipeUrl   = BASE_URL . 'recipe.php?id=' . (int)$recipe['id'];
$loginUrl    = BASE_URL . 'login.php?redirect=' . urlencode($recipeUrl) . '&reason=recipe';
$cardLink    = $isGuest ? $loginUrl : $recipeUrl;
?>
<article class="recipe-card fade-in" data-recipe-id="<?= (int)$recipe['id'] ?>">
    <a href="<?= $cardLink ?>" class="recipe-card__image-wrap">
        <img src="<?= $image ?>" alt="<?= htmlspecialchars($recipe['title']) ?>" loading="lazy" class="recipe-card__image">

        <?php if (!empty($recipe['category_name'])): ?>
            <span class="recipe-card__badge"><?= htmlspecialchars($recipe['category_name']) ?></span>
        <?php endif; ?>

        <?php if ($isGuest): ?>
        <!-- Lock overlay for guests -->
        <div class="recipe-card__lock">
            <div class="lock-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <span>Login to view</span>
        </div>
        <?php else: ?>
        <button class="recipe-card__fav <?= $isFavorited ? 'is-active' : '' ?>"
                data-id="<?= (int)$recipe['id'] ?>"
                onclick="toggleFavorite(event, <?= (int)$recipe['id'] ?>)"
                aria-label="<?= $isFavorited ? 'Remove from' : 'Add to' ?> favorites">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= $isFavorited ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
        </button>
        <?php endif; ?>
    </a>

    <div class="recipe-card__body">
        <h3 class="recipe-card__title">
            <a href="<?= $cardLink ?>"><?= htmlspecialchars($recipe['title']) ?></a>
        </h3>
        <?php if (!empty($recipe['description'])): ?>
            <p class="recipe-card__desc"><?= htmlspecialchars(substr($recipe['description'], 0, 90)) ?>...</p>
        <?php endif; ?>
        <div class="recipe-card__meta">
            <span class="meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?= (int)$recipe['cooking_time'] ?> min
            </span>
            <span class="meta-item">
                <!-- <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> -->
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 3h12"/>
                    <path d="M6 7h12"/>
                    <path d="M6 11h7a4 4 0 0 0 0-8H6"/>
                    <path d="M6 11l8 10"/>
                </svg>
                <?= number_format((float)$recipe['budget'], 0) ?>
            </span>
            <?php if (!empty($recipe['chef_name'])): ?>
                <span class="meta-item meta-chef">by <?= htmlspecialchars($recipe['chef_name']) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($isGuest): ?>
        <div style="margin-top:10px; padding:8px 12px; background:var(--primary-light); border-radius:8px; display:flex; align-items:center; gap:8px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span style="font-size:12px; color:var(--primary); font-weight:600;">
                <a href="<?= $loginUrl ?>" style="color:var(--primary);">Login</a> or
                <a href="<?= BASE_URL ?>signup.php" style="color:var(--primary);">Sign up</a> to see full recipe
            </span>
        </div>
        <?php endif; ?>
    </div>
</article>