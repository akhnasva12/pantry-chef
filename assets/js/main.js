// assets/js/main.js

/* ---- Base URL Detection ---- */
// Set by PHP: <script>window.BASE_URL="/pantry-chef/";</script>
// Fallback: auto-detect from current page URL
(function() {
  if (!window.BASE_URL) {
    // Detect by finding where index.php / the site root is
    var path = window.location.pathname;
    // Remove known page filenames to get the base path
    var pages = ['index.php','login.php','signup.php','creator-signup.php',
                 'recipe.php','profile.php','logout.php','404.php',
                 'creator-profile.php','setup.php','debug.php'];
    for (var i = 0; i < pages.length; i++) {
      if (path.indexOf('/' + pages[i]) !== -1) {
        path = path.substring(0, path.indexOf('/' + pages[i]) + 1);
        break;
      }
    }
    // Remove known subdirs
    path = path.replace(/\/(creator|admin|ajax|components|includes)\/$/, '/');
    window.BASE_URL = path;
  }
  // Ensure trailing slash
  if (window.BASE_URL && window.BASE_URL.slice(-1) !== '/') {
    window.BASE_URL += '/';
  }
})();

/* ---- Navbar scroll behaviour ---- */
(function() {
  const navbar = document.getElementById('navbar');
  if (!navbar) return;
  let lastY = 0;
  window.addEventListener('scroll', () => {
    const y = window.scrollY;
    navbar.style.transform = (y > lastY && y > 80) ? 'translateY(-100%)' : 'translateY(0)';
    lastY = y;
  }, { passive: true });
  navbar.style.transition = 'transform .3s ease';
})();

/* ---- Mobile hamburger ---- */
// ---- Mobile Hamburger ----
(function() {
  const btn  = document.getElementById('navHamburger');
  const menu = document.getElementById('navMobileMenu');
  if (!btn || !menu) return;

  btn.addEventListener('click', function() {
    const isOpen = menu.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', isOpen);
    // Animate hamburger → X
    const spans = btn.querySelectorAll('span');
    if (isOpen) {
      spans[0].style.cssText = 'transform:rotate(45deg) translate(5px,5px)';
      spans[1].style.cssText = 'opacity:0; transform:translateX(-8px)';
      spans[2].style.cssText = 'transform:rotate(-45deg) translate(5px,-5px)';
    } else {
      spans.forEach(s => s.style.cssText = '');
    }
  });

  // Close menu when clicking outside
  document.addEventListener('click', function(e) {
    if (!btn.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.remove('is-open');
      btn.setAttribute('aria-expanded', 'false');
      btn.querySelectorAll('span').forEach(s => s.style.cssText = '');
    }
  });

  // Close menu when a link is clicked
  menu.querySelectorAll('a').forEach(function(link) {
    link.addEventListener('click', function() {
      menu.classList.remove('is-open');
      btn.setAttribute('aria-expanded', 'false');
      btn.querySelectorAll('span').forEach(s => s.style.cssText = '');
    });
  });
})();

// ---- Mobile Search (mirrors desktop search) ----
document.getElementById('mobileSearchInput')?.addEventListener('input', function() {
  const navInput = document.getElementById('navSearchInput');
  if (navInput) {
    navInput.value = this.value;
    navInput.dispatchEvent(new Event('input'));
  }
});

// document.getElementById('searchButton').addEventListener('click', function(){
//   setTimeout(() => {
//     const searchResult = document.getElementById("resultsCount");
//     if (searchResult){
//       searchResult.scrollIntoView({
//         behavior : "smooth"
//       });
//     }
//   }, 200);
// })

/* ---- Fade-in on scroll ---- */
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('is-visible'); observer.unobserve(e.target); }
  });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

/* ---- Toast Notifications ---- */
function showToast(message, type = '') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = `toast${type ? ' toast--' + type : ''}`;
  toast.textContent = message;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

/* ---- AJAX Favorite Toggle ---- */
function toggleFavorite(e, recipeId) {
  e.preventDefault(); e.stopPropagation();
  const btn = e.currentTarget;
  btn.classList.add('heart-pop');
  setTimeout(() => btn.classList.remove('heart-pop'), 350);
  fetch((window.BASE_URL||'/')+'ajax/favorite.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ recipe_id: recipeId })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const svg = btn.querySelector('svg');
      if (data.action === 'added') {
        btn.classList.add('is-active');
        svg.setAttribute('fill', 'currentColor');
        showToast('Added to favorites ❤️', 'success');
      } else {
        btn.classList.remove('is-active');
        svg.setAttribute('fill', 'none');
        showToast('Removed from favorites');
      }
    } else if (data.redirect) {
      window.location.href = data.redirect;
    } else {
      showToast(data.message || 'Something went wrong', 'error');
    }
  })
  .catch(() => showToast('Network error', 'error'));
}

/* ---- Nav Live Search ---- */
const navSearchInput = document.getElementById('navSearchInput');
const navSearchResults = document.getElementById('navSearchResults');
let searchTimer;
if (navSearchInput) {
  navSearchInput.addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) { navSearchResults.innerHTML = ''; navSearchResults.classList.remove('is-open'); return; }
    searchTimer = setTimeout(() => {
      fetch(`${window.BASE_URL||'/'}ajax/search.php?q=${encodeURIComponent(q)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        if (!data.results || !data.results.length) {
          navSearchResults.innerHTML = '<div class="search-result-item"><div class="info"><h4>No results found</h4></div></div>';
        } else {
          navSearchResults.innerHTML = data.results.map(r => `
            <a href="${window.BASE_URL||'/'}recipe.php?id=${r.id}" class="search-result-item">
              <img src="${r.image ? (window.BASE_URL||'/')+'uploads/recipes/' + r.image : (window.BASE_URL||'/')+'assets/images/placeholder.jpg'}" alt="${r.title}">
              <div class="info">
                <h4>${r.title}</h4>
                <p>${r.cooking_time} min · ₹${r.budget}</p>
              </div>
            </a>`).join('');
        }
        navSearchResults.classList.add('is-open');
      });
    }, 280);
  });
  document.addEventListener('click', e => {
    if (!navSearchInput.contains(e.target) && !navSearchResults.contains(e.target))
      navSearchResults.classList.remove('is-open');
  });
}

/* ---- Image Upload Preview ---- */
function initUploadPreview(inputId, previewId, clearId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  const clearBtn = document.getElementById(clearId);
  if (!input || !preview) return;
  input.addEventListener('change', function() {
    const file = this.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = e => {
      preview.querySelector('img').src = e.target.result;
      preview.classList.add('has-image');
    };
    reader.readAsDataURL(file);
  });
  clearBtn?.addEventListener('click', () => {
    input.value = '';
    preview.querySelector('img').src = '';
    preview.classList.remove('has-image');
  });
  // Drag & drop
  const area = input.closest('.upload-area');
  area?.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('drag-over'); });
  area?.addEventListener('dragleave', () => area.classList.remove('drag-over'));
  area?.addEventListener('drop', e => {
    e.preventDefault(); area.classList.remove('drag-over');
    input.files = e.dataTransfer.files;
    input.dispatchEvent(new Event('change'));
  });
}

/* ---- Dynamic Ingredient / Step Fields ---- */
function addDynamicField(containerId, placeholder, isStep = false) {
  const container = document.getElementById(containerId);
  if (!container) return;
  const items = container.querySelectorAll('.dynamic-item');
  const idx = items.length + 1;
  const item = document.createElement('div');
  item.className = 'dynamic-item';
  if (isStep) {
    item.innerHTML = `
      <span class="step-num">${idx}</span>
      <textarea class="form-input form-textarea" name="steps[]" rows="2" placeholder="Step ${idx} description..." required></textarea>
      <button type="button" class="remove-btn" onclick="removeDynamicItem(this)">×</button>`;
  } else {
    item.innerHTML = `
      <input type="text" class="form-input" name="ingredients[]" placeholder="${placeholder}" required>
      <button type="button" class="remove-btn" onclick="removeDynamicItem(this)">×</button>`;
  }
  container.appendChild(item);
}

function removeDynamicItem(btn) {
  const item = btn.closest('.dynamic-item');
  const container = item.parentElement;
  if (container.children.length <= 1) return; // keep at least one
  item.remove();
  // Renumber steps
  container.querySelectorAll('.step-num').forEach((num, i) => num.textContent = i + 1);
}

/* ---- Form Validation Helpers ---- */
function validateForm(form) {
  let valid = true;
  form.querySelectorAll('[required]').forEach(field => {
    field.classList.remove('is-error');
    const err = field.parentElement.querySelector('.form-error');
    if (err) err.remove();
    if (!field.value.trim()) {
      field.classList.add('is-error');
      const msg = document.createElement('p');
      msg.className = 'form-error';
      msg.textContent = 'This field is required';
      field.parentElement.appendChild(msg);
      valid = false;
    }
  });
  return valid;
}

/* ---- Filter AJAX (homepage) ---- */
let filterPage = 1;
let selectedCategory = '';

function applyFilters(reset = true) {
  if (reset) filterPage = 1;
  const params = new URLSearchParams();

  // Category from chip selection
  if (selectedCategory) params.set('category', selectedCategory);

  // Active tag chip
  const activeTag = document.querySelector('.chip.is-active[data-tag]');
  if (activeTag) params.set('tag', activeTag.dataset.tag);

  // Dropdowns
  ['filterCuisine','filterTime','filterSort'].forEach(id => {
    const el = document.getElementById(id);
    if (el && el.value) params.set(id.replace('filter','').toLowerCase(), el.value);
  });

  // Budget slider
  const budget = document.getElementById('filterBudget');
  if (budget && budget.value && parseInt(budget.value) < 2000) params.set('budget', budget.value);

  // Search
  const q = document.getElementById('heroSearch');
  if (q && q.value.trim()) params.set('q', q.value.trim());

  params.set('page', filterPage);

  const grid = document.getElementById('recipesGrid');
  if (!grid) return;
  if (reset) grid.innerHTML = skeletonCards(8);

  const base = window.BASE_URL || '/';
  fetch(base + 'ajax/recipes.php?' + params.toString(), {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (reset) grid.innerHTML = '';
    if (!data.recipes || !data.recipes.length) {
      if (reset) grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><div class="icon">🍽️</div><h3>No recipes found</h3><p>Try adjusting your filters</p></div>';
      const lb = document.getElementById('loadMoreBtn');
      if (lb) lb.style.display = 'none';
      return;
    }
    if (data.total !== undefined) {
      const rc = document.getElementById('resultsCount');
      if (rc) rc.textContent = data.total + ' recipes found';
    }
    data.recipes.forEach(function(r) {
      const card = document.createElement('div');
      card.innerHTML = buildRecipeCard(r);
      if (card.firstElementChild) grid.appendChild(card.firstElementChild);
    });
    grid.querySelectorAll('.fade-in:not(.is-visible)').forEach(function(el) {
      observer.observe(el);
    });
    const loadMore = document.getElementById('loadMoreBtn');
    if (loadMore) loadMore.style.display = data.hasMore ? 'block' : 'none';
  })
  .catch(function(err) {
    console.error('Filter error:', err);
    if (reset) grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><div class="icon">⚠️</div><h3>Failed to load recipes</h3><p>Please refresh the page</p></div>';
  });
}

function loadMore() {
  filterPage++;
  applyFilters(false);
}

function filterByCategory(cat) {
  selectedCategory = cat;
  document.querySelectorAll('.chip[data-cat]').forEach(function(c) {
    c.classList.toggle('is-active', c.dataset.cat === cat);
  });
  applyFilters();
}

function filterByTag(tag) {
  const chips = document.querySelectorAll('.chip[data-tag]');
  const isActive = document.querySelector('.chip.is-active[data-tag="' + tag + '"]');
  chips.forEach(function(c) { c.classList.remove('is-active'); });
  if (!isActive) {
    const chip = document.querySelector('.chip[data-tag="' + tag + '"]');
    if (chip) chip.classList.add('is-active');
  }
  applyFilters();
}

function clearFilters() {
  selectedCategory = '';
  document.querySelectorAll('.chip[data-cat]').forEach(function(c) {
    c.classList.toggle('is-active', c.dataset.cat === '');
  });
  document.querySelectorAll('.chip[data-tag]').forEach(function(c) {
    c.classList.remove('is-active');
  });
  ['filterCuisine','filterTime','filterSort'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  const fb = document.getElementById('filterBudget');
  if (fb) fb.value = 2000;
  const bd = document.getElementById('budgetDisplay');
  if (bd) bd.textContent = '₹2000';
  const hs = document.getElementById('heroSearch');
  if (hs) hs.value = '';
  applyFilters();
}

let heroSearchTimer;
function debounceHeroSearch(val) {
  clearTimeout(heroSearchTimer);
  heroSearchTimer = setTimeout(function() { applyFilters(); }, 350);
}

function buildRecipeCard(r) {
  const base    = window.BASE_URL || '/';
  const img     = r.image ? base+'uploads/recipes/'+r.image : base+'assets/images/placeholder.jpg';
  const isGuest = !window.IS_LOGGED_IN;
  const recipeUrl = base+'recipe.php?id='+r.id;
  const loginUrl  = base+'login.php?redirect='+encodeURIComponent(recipeUrl)+'&reason=recipe';
  const cardLink  = isGuest ? loginUrl : recipeUrl;
  const isFav   = r.is_favorited ? 'is-active' : '';
  const fill    = r.is_favorited ? 'currentColor' : 'none';

  const lockHtml = isGuest ? `
    <div class="recipe-card__lock">
      <div class="lock-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
      <span>Login to view</span>
    </div>` : '';

  const favHtml = isGuest ? '' : `
    <button class="recipe-card__fav ${isFav}" data-id="${r.id}"
      onclick="toggleFavorite(event, ${r.id})" aria-label="Favorite">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="${fill}" stroke="currentColor" stroke-width="2">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
      </svg>
    </button>`;

  const guestBanner = isGuest ? `
    <div style="margin-top:10px;padding:8px 12px;background:var(--primary-light);border-radius:8px;display:flex;align-items:center;gap:8px;">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <span style="font-size:12px;color:var(--primary);font-weight:600;">
        <a href="${loginUrl}" style="color:var(--primary)">Login</a> or
        <a href="${base}signup.php" style="color:var(--primary)">Sign up</a> to see full recipe
      </span>
    </div>` : '';

  return `
  <article class="recipe-card fade-in" data-recipe-id="${r.id}">
    <a href="${cardLink}" class="recipe-card__image-wrap">
      <img src="${img}" alt="${escHtml(r.title)}" loading="lazy" class="recipe-card__image">
      ${r.category_name ? `<span class="recipe-card__badge">${escHtml(r.category_name)}</span>` : ''}
      ${lockHtml}${favHtml}
    </a>
    <div class="recipe-card__body">
      <h3 class="recipe-card__title"><a href="${cardLink}">${escHtml(r.title)}</a></h3>
      ${r.description ? `<p class="recipe-card__desc">${escHtml(r.description.substring(0,90))}...</p>` : ''}
      <div class="recipe-card__meta">
        <span class="meta-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>${r.cooking_time} min</span>
        <span class="meta-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 3h12"/>
                    <path d="M6 7h12"/>
                    <path d="M6 11h7a4 4 0 0 0 0-8H6"/>
                    <path d="M6 11l8 10"/>
                </svg>${Number(r.budget).toFixed(0)}</span>
        ${r.chef_name ? `<span class="meta-item meta-chef">by ${escHtml(r.chef_name)}</span>` : ''}
      </div>
      ${guestBanner}
    </div>
  </article>`;
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function skeletonCards(n) {
  return Array(n).fill(`
    <div class="skeleton-card">
      <div class="skeleton skeleton-image"></div>
      <div class="skeleton-body">
        <div class="skeleton skeleton-line"></div>
        <div class="skeleton skeleton-line skeleton-line--short"></div>
        <div class="skeleton skeleton-line skeleton-line--shorter"></div>
      </div>
    </div>`).join('');
}

/* ---- Voice Guide ---- */
let voiceUtterance = null;
let voiceSteps = [];
let voiceIdx = 0;

function initVoiceGuide(steps) {
  voiceSteps = steps;
}

function playVoice() {
  if (!window.speechSynthesis || !voiceSteps.length) return;
  window.speechSynthesis.cancel();
  voiceIdx = 0;
  speakStep();
}

function speakStep() {
  if (voiceIdx >= voiceSteps.length) return;
  const stepCards = document.querySelectorAll('.step-card');
  stepCards.forEach(s => s.classList.remove('is-reading'));
  if (stepCards[voiceIdx]) stepCards[voiceIdx].classList.add('is-reading');
  stepCards[voiceIdx]?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  voiceUtterance = new SpeechSynthesisUtterance(`Step ${voiceIdx + 1}. ${voiceSteps[voiceIdx]}`);
  voiceUtterance.rate = 0.9;
  voiceUtterance.onend = () => { voiceIdx++; setTimeout(speakStep, 600); };
  window.speechSynthesis.speak(voiceUtterance);
}

function stopVoice() {
  window.speechSynthesis.cancel();
  document.querySelectorAll('.step-card').forEach(s => s.classList.remove('is-reading'));
}

/* ---- Budget Slider Display ---- */
document.getElementById('filterBudget')?.addEventListener('input', function() {
  const display = document.getElementById('budgetDisplay');
  if (display) display.textContent = `₹${this.value}`;
});

/* ---- Mobile Filter Toggle ---- */
function toggleMobileFilters() {
  const bar     = document.getElementById('filterBar');
  const chevron = document.getElementById('filterChevron');
  const btn     = document.getElementById('mobileFilterToggle');
  if (!bar) return;
  const isOpen = bar.classList.toggle('is-open');
  if (chevron) chevron.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
  if (btn) btn.style.borderColor = isOpen ? 'var(--primary)' : '';
}

function updateFilterCount() {
  const badge = document.getElementById('activeFilterCount');
  if (!badge) return;
  let count = 0;
  ['filterCuisine','filterTime'].forEach(id => {
    const el = document.getElementById(id);
    if (el && el.value) count++;
  });
  const budget = document.getElementById('filterBudget');
  if (budget && parseInt(budget.value) < 2000) count++;
  badge.textContent = count;
  badge.style.display = count > 0 ? 'inline' : 'none';
}