<?php
// ============================================================
// src/views/communaute.php
// Page communauté : posts typés, réactions, filtres, commentaires
// ============================================================

if (empty($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$initiale = strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1));
$username = htmlspecialchars($_SESSION['username'] ?? 'Utilisateur');
$email    = htmlspecialchars($_SESSION['email']    ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebPêche — Communauté</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/communaute.css">
</head>
<body class="theme-light" id="app">

<div class="app-container">

    <!-- ══════════ SIDEBAR ══════════ -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-header">
            <div class="user-card">
                <div class="user-avatar"><?= $initiale ?></div>
                <div class="user-info">
                    <span class="app-brand">WebPêche</span>
                    <span class="user-email"><?= $email ?: $username ?></span>
                </div>
            </div>
            <button class="btn-icon" id="btn-close" title="Fermer le menu">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <p class="nav-label">ACCUEIL</p>

            <a href="/accueil" class="nav-link">
                <i class="fa-solid fa-house"></i>
                <span>Home Map</span>
            </a>
            <a href="/communaute" class="nav-link active">
                <i class="fa-regular fa-circle-user"></i>
                <span>Communauté</span>
            </a>
            <a href="/parametres" class="nav-link">
                <i class="fa-solid fa-gear"></i>
                <span>Paramètre</span>
            </a>
            <a href="/documentation" class="nav-link">
                <i class="fa-solid fa-book"></i>
                <span>Documentation</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="/logout" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Se déconnecter
            </a>

            <div class="theme-toggle">
                <button class="theme-btn active" id="btn-light">
                    <i class="fa-solid fa-sun"></i> Light
                </button>
                <button class="theme-btn" id="btn-dark">
                    <i class="fa-solid fa-moon"></i> Dark
                </button>
            </div>
        </div>

    </aside>

    <!-- ══════════ ZONE PRINCIPALE ══════════ -->
    <main class="community-wrapper">

        <div class="community-topbar">
            <button class="btn-icon" id="btn-open" title="Ouvrir le menu">
                <i class="fa-solid fa-arrow-right"></i>
            </button>

            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="post-search" placeholder="Rechercher un post...">
            </div>

            <!-- Filtre par type de post -->
            <div class="filter-wrap">
                <button type="button" class="btn-filter-pill" id="btn-filter" title="Filtrer les posts">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Filtrer</span>
                    <span class="filter-badge" id="filter-badge"></span>
                </button>

                <div class="filter-panel" id="filter-panel">
                    <div class="filter-panel-header">
                        <span><i class="fa-solid fa-filter"></i> Filtrer les posts</span>
                        <button type="button" id="filter-reset">Réinitialiser</button>
                    </div>
                    <div class="filter-fixed">
                        <button type="button" class="filter-item filter-support" id="filter-support">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Posts Support</span>
                            <i class="fa-solid fa-check filter-check"></i>
                        </button>
                    </div>
                    <div class="filter-divider">Type de post</div>
                    <div class="filter-list" id="filter-list"></div>
                </div>
            </div>

            <button class="btn-new-post" id="btn-new-post">
                <i class="fa-solid fa-plus"></i> Nouveau post
            </button>
        </div>

        <div class="community-feed" id="community-feed"></div>

        <!-- Zone de chargement automatique des posts suivants -->
        <div class="feed-more" id="feed-more" hidden>
            <button type="button" class="feed-more-btn" id="feed-more-btn">Voir plus de posts</button>
        </div>

    </main>
</div>

<!-- ══════════ MODALE — Nouveau post ══════════ -->
<div class="modal-overlay" id="post-modal-overlay"></div>
<div class="modal-box post-modal" id="post-modal" role="dialog" aria-modal="true" aria-label="Nouveau post">
    <div class="modal-header">
        <h2>Nouveau post</h2>
        <button type="button" class="btn-icon" id="post-modal-cancel" title="Fermer">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <form id="post-form" class="modal-form" autocomplete="off">

        <div class="field-row"><label>Type de post *</label></div>
        <div class="cat-picker" id="cat-picker"></div>

        <div class="field-row">
            <label for="post-title">Titre *</label>
            <span class="char-count" id="title-count">0/100</span>
        </div>
        <input type="text" id="post-title" name="title" placeholder="Ex : Meilleure technique pour le brochet" maxlength="100" required>

        <div class="field-row">
            <label for="post-content">Message *</label>
            <span class="char-count" id="content-count">0/1500</span>
        </div>
        <textarea id="post-content" name="content" placeholder="Partagez vos avis, idées ou techniques de pêche..." maxlength="1500" required></textarea>

        <div class="switch-row">
            <div class="switch-text">
                <strong>Autoriser les commentaires</strong>
                <span>Désactivez pour que personne ne puisse répondre à ce post.</span>
            </div>
            <button type="button" class="switch on" id="switch-comments" role="switch" aria-checked="true" title="Autoriser les commentaires"></button>
        </div>

        <button type="submit" class="btn-auth" id="post-submit" disabled>Publier</button>
        <p class="submit-hint" id="submit-hint">Choisissez un type de post pour continuer.</p>
    </form>
</div>

<div class="toast-zone" id="toast-zone"></div>

<script>
/* ================================================================
   CONSTANTES (les mêmes limites sont revérifiées côté serveur)
   ================================================================ */
const LIMITS = { title: 100, content: 1500, comment: 300 };

const CATEGORIES = {
    sortie:   { label: 'Sortie',   icon: 'fa-sailboat' },
    avis:     { label: 'Avis',     icon: 'fa-star' },
    question: { label: 'Question', icon: 'fa-circle-question' },
    astuce:   { label: 'Astuce',   icon: 'fa-lightbulb' },
    prise:    { label: 'Prise',    icon: 'fa-fish' },
    autre:    { label: 'Autre',    icon: 'fa-ellipsis' }
};

/* ================================================================
   THÈME (même clé localStorage que les autres pages)
   ================================================================ */
const app      = document.getElementById('app');
const btnLight = document.getElementById('btn-light');
const btnDarkT = document.getElementById('btn-dark');

function applyTheme(t) {
    app.classList.toggle('theme-dark',  t === 'dark');
    app.classList.toggle('theme-light', t !== 'dark');
    btnDarkT.classList.toggle('active', t === 'dark');
    btnLight.classList.toggle('active', t !== 'dark');
    localStorage.setItem('webpeche_theme', t);
}
applyTheme(localStorage.getItem('webpeche_theme') || 'light');
btnLight.addEventListener('click', () => applyTheme('light'));
btnDarkT.addEventListener('click', () => applyTheme('dark'));

/* ================================================================
   SIDEBAR
   ================================================================ */
const sidebar  = document.getElementById('sidebar');
const btnOpen  = document.getElementById('btn-open');
const btnClose = document.getElementById('btn-close');

btnOpen.style.display = 'none';

btnClose.addEventListener('click', () => {
    sidebar.classList.add('collapsed');
    btnOpen.style.display  = 'flex';
    btnClose.style.display = 'none';
});
btnOpen.addEventListener('click', () => {
    sidebar.classList.remove('collapsed');
    btnOpen.style.display  = 'none';
    btnClose.style.display = 'flex';
});

/* ================================================================
   OUTILS
   ================================================================ */
const ESC = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ESC[c]);
}

function parseDate(dateStr) {
    return new Date(String(dateStr).replace(' ', 'T'));
}

function fullDate(dateStr) {
    const d = parseDate(dateStr);
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
        + ' à ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

// "il y a 5 min", "il y a 3 h"... puis la date complète après une semaine
function timeAgo(dateStr) {
    const diff = (Date.now() - parseDate(dateStr).getTime()) / 1000;
    if (diff < 60)         return "à l'instant";
    if (diff < 3600)       return `il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400)      return `il y a ${Math.floor(diff / 3600)} h`;
    if (diff < 7 * 86400)  return `il y a ${Math.floor(diff / 86400)} j`;
    return fullDate(dateStr);
}

function toast(message, type = 'info') {
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.textContent = message;
    document.getElementById('toast-zone').appendChild(el);
    requestAnimationFrame(() => el.classList.add('show'));
    setTimeout(() => {
        el.classList.remove('show');
        setTimeout(() => el.remove(), 250);
    }, 3200);
}

// Requête JSON : lève une erreur lisible si le serveur répond une erreur
async function api(url, options = {}) {
    const res  = await fetch(url, options);
    const data = await res.json();
    if (data && data.error) throw new Error(data.error);
    return data;
}

function formBody(obj) {
    return new URLSearchParams(obj).toString();
}
const FORM_HEADERS = { 'Content-Type': 'application/x-www-form-urlencoded' };

/* ================================================================
   RENDU D'UN POST
   ================================================================ */
function authorBadgeHtml(post) {
    return post.is_admin
        ? `<span class="support-badge"><i class="fa-solid fa-circle-check"></i> Support</span>`
        : `<span class="post-author"><i class="fa-solid fa-circle-user"></i> ${escapeHtml(post.username)}</span>`;
}

function categoryBadgeHtml(key) {
    const k = CATEGORIES[key] ? key : 'autre';
    return `<span class="cat-badge cat-${k}"><i class="fa-solid ${CATEGORIES[k].icon}"></i>${CATEGORIES[k].label}</span>`;
}

function reactionsHtml(post) {
    return `
        <div class="reactions">
            <button type="button" class="react-btn like ${post.my_reaction === 1 ? 'active' : ''}" data-action="like" title="J'aime">
                <i class="fa-solid fa-thumbs-up"></i><span class="react-count">${post.likes}</span>
            </button>
            <button type="button" class="react-btn dislike ${post.my_reaction === -1 ? 'active' : ''}" data-action="dislike" title="Je n'aime pas">
                <i class="fa-solid fa-thumbs-down"></i><span class="react-count">${post.dislikes}</span>
            </button>
        </div>`;
}

function commentsToggleHtml(post) {
    const n = post.comment_count;
    if (!post.comments_enabled && n === 0) {
        return `<span class="comments-off"><i class="fa-solid fa-comment-slash"></i> Commentaires désactivés</span>`;
    }
    return `
        <button type="button" class="post-comments-toggle" data-action="comments">
            <i class="fa-regular fa-comment"></i>
            <span class="comment-count">${n}</span>
            <span class="comment-label">commentaire${n > 1 ? 's' : ''}</span>
        </button>`;
}

function commentItemHtml(c) {
    return `
        <div class="comment-item ${c.is_admin ? 'is-support' : ''}">
            <div class="comment-header">
                <span class="comment-author">${escapeHtml(c.username)}${c.is_admin ? ' <span class="support-tag">Support</span>' : ''}</span>
                <span class="comment-date">${timeAgo(c.created_at)}</span>
            </div>
            <p class="comment-text">${escapeHtml(c.content)}</p>
        </div>`;
}

function postCardHtml(post) {
    const showComments = post.comments_enabled || post.comment_count > 0;

    return `
        <article class="post-card ${post.is_admin ? 'is-support' : ''}" data-post-id="${post.id}">
            <div class="post-header">
                <div class="post-author-wrap">
                    ${authorBadgeHtml(post)}
                    ${categoryBadgeHtml(post.category)}
                </div>
                <div class="post-header-right">
                    <span class="post-date" title="${escapeHtml(fullDate(post.created_at))}">${timeAgo(post.created_at)}</span>
                    ${post.can_delete ? `
                        <button type="button" class="post-delete-btn" data-action="delete" title="Supprimer ce post">
                            <i class="fa-solid fa-trash"></i>
                        </button>` : ''}
                </div>
            </div>

            <h3 class="post-title">${escapeHtml(post.title)}</h3>
            <p class="post-content">${escapeHtml(post.content)}</p>

            <div class="post-footer">
                ${reactionsHtml(post)}
                ${commentsToggleHtml(post)}
            </div>

            ${showComments ? `
            <div class="post-comments">
                <div class="comments-list"></div>
                ${post.comments_enabled ? `
                    <form class="comment-form">
                        <input type="text" name="content" placeholder="Ajouter un commentaire..." required maxlength="${LIMITS.comment}">
                        <button type="submit" title="Envoyer"><i class="fa-solid fa-paper-plane"></i></button>
                    </form>` : `<p class="comments-closed">Les commentaires sont désactivés sur ce post.</p>`}
            </div>` : ''}
        </article>`;
}

function skeletonHtml(n = 3) {
    return Array.from({ length: n }, () => `
        <div class="post-card skeleton">
            <div class="sk-line sk-short"></div>
            <div class="sk-line sk-title"></div>
            <div class="sk-line"></div>
            <div class="sk-line sk-mid"></div>
        </div>`).join('');
}

/* ================================================================
   ÉTAT + CHARGEMENT (pagination, annulation des requêtes périmées)
   ================================================================ */
const feed     = document.getElementById('community-feed');
const moreWrap = document.getElementById('feed-more');
const moreBtn  = document.getElementById('feed-more-btn');

const state = {
    posts: [],
    hasMore: false,
    loading: false,
    search: '',
    categories: new Set(),
    support: false
};

let loadController = null;

function findPost(id) {
    return state.posts.find(p => p.id === id);
}

function hasActiveFilters() {
    return state.categories.size > 0 || state.support || state.search !== '';
}

function emptyHtml() {
    return hasActiveFilters()
        ? '<p class="feed-empty">Aucun post ne correspond à votre recherche ou à vos filtres.</p>'
        : '<p class="feed-empty">Aucun post pour le moment. Soyez le premier à en publier un !</p>';
}

function buildQuery(before) {
    const p = new URLSearchParams();
    if (state.search)            p.set('search', state.search);
    if (state.categories.size)   p.set('categories', [...state.categories].join(','));
    if (state.support)           p.set('support', '1');
    if (before)                  p.set('before', before);
    return p.toString();
}

async function loadPosts(append = false) {
    if (append && (state.loading || !state.hasMore)) return;

    if (loadController) loadController.abort();   // une nouvelle recherche annule l'ancienne
    loadController = new AbortController();
    const signal = loadController.signal;

    state.loading = true;

    if (!append) {
        state.posts   = [];
        state.hasMore = false;
        feed.innerHTML = skeletonHtml();
        moreWrap.hidden = true;
    } else {
        moreBtn.disabled = true;
        moreBtn.textContent = 'Chargement...';
    }

    try {
        const before = append && state.posts.length ? state.posts[state.posts.length - 1].id : 0;
        const res    = await fetch('/api/posts?' + buildQuery(before), { signal });
        const data   = await res.json();
        if (data.error) throw new Error(data.error);

        state.posts.push(...data.posts);
        state.hasMore = data.has_more;

        const html = data.posts.map(postCardHtml).join('');
        if (append) {
            feed.insertAdjacentHTML('beforeend', html);
        } else {
            feed.innerHTML = state.posts.length ? html : emptyHtml();
        }
    } catch (err) {
        if (err.name === 'AbortError') return;
        console.error('Erreur de chargement des posts :', err);
        if (!append) feed.innerHTML = '<p class="feed-empty">Erreur de chargement. Réessayez dans un instant.</p>';
        else toast('Impossible de charger la suite.', 'error');
    } finally {
        if (!signal.aborted) {
            state.loading = false;
            moreBtn.disabled = false;
            moreBtn.textContent = 'Voir plus de posts';
            moreWrap.hidden = !state.hasMore;
            if (state.hasMore) { io.unobserve(moreWrap); io.observe(moreWrap); } // re-teste la visibilité
        }
    }
}

// Chargement automatique quand on arrive en bas de la liste
const io = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) loadPosts(true);
}, { rootMargin: '300px' });
io.observe(moreWrap);
moreBtn.addEventListener('click', () => loadPosts(true));

/* ================================================================
   ACTIONS SUR LES POSTS — un seul écouteur pour toute la liste
   ================================================================ */
feed.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const card = btn.closest('.post-card');
    if (!card) return;
    const id = parseInt(card.dataset.postId, 10);

    switch (btn.dataset.action) {
        case 'like':     react(id, 'like', card);     break;
        case 'dislike':  react(id, 'dislike', card);  break;
        case 'comments': toggleComments(id, card);    break;
        case 'delete':   deletePost(id, card);        break;
    }
});

feed.addEventListener('submit', (e) => {
    if (!e.target.matches('.comment-form')) return;
    e.preventDefault();
    const card = e.target.closest('.post-card');
    submitComment(e.target, parseInt(card.dataset.postId, 10), card);
});

/* ── Réactions : l'affichage change tout de suite, le serveur confirme ensuite ── */
function paintReactions(card, post) {
    const like    = card.querySelector('.react-btn.like');
    const dislike = card.querySelector('.react-btn.dislike');
    like.classList.toggle('active', post.my_reaction === 1);
    dislike.classList.toggle('active', post.my_reaction === -1);
    like.querySelector('.react-count').textContent    = post.likes;
    dislike.querySelector('.react-count').textContent = post.dislikes;
}

async function react(id, type, card) {
    const post = findPost(id);
    if (!post || post._reacting) return;

    const value = type === 'like' ? 1 : -1;
    const prev  = { likes: post.likes, dislikes: post.dislikes, my: post.my_reaction };

    // Calcul local immédiat
    if (post.my_reaction === 1)  post.likes--;
    if (post.my_reaction === -1) post.dislikes--;
    if (post.my_reaction === value) {
        post.my_reaction = 0;
    } else {
        post.my_reaction = value;
        if (value === 1) post.likes++; else post.dislikes++;
    }
    paintReactions(card, post);

    const btn = card.querySelector(`.react-btn.${type}`);
    btn.classList.remove('pop'); void btn.offsetWidth; btn.classList.add('pop');

    post._reacting = true;
    try {
        const data = await api('/api/post/react', {
            method: 'POST', headers: FORM_HEADERS,
            body: formBody({ post_id: id, reaction: type })
        });
        post.likes = data.likes; post.dislikes = data.dislikes; post.my_reaction = data.my_reaction;
        paintReactions(card, post);
    } catch (err) {
        post.likes = prev.likes; post.dislikes = prev.dislikes; post.my_reaction = prev.my;
        paintReactions(card, post);
        toast(err.message || 'Impossible d\'enregistrer votre réaction.', 'error');
    } finally {
        post._reacting = false;
    }
}

/* ── Commentaires : chargés uniquement à la première ouverture ── */
function updateCommentCount(card, n) {
    const count = card.querySelector('.comment-count');
    const label = card.querySelector('.comment-label');
    if (count) count.textContent = n;
    if (label) label.textContent = 'commentaire' + (n > 1 ? 's' : '');
}

async function toggleComments(id, card) {
    const box = card.querySelector('.post-comments');
    if (!box) return;

    const open = box.classList.toggle('visible');
    if (!open) return;

    const post = findPost(id);
    if (!post || post._comments) return;

    const list = box.querySelector('.comments-list');

    if (post.comment_count === 0) {          // rien à télécharger
        post._comments = [];
        return;
    }

    list.innerHTML = '<p class="comments-loading">Chargement...</p>';
    try {
        const comments = await api(`/api/post/comments?post_id=${id}`);
        post._comments = comments;
        list.innerHTML = comments.map(commentItemHtml).join('');
    } catch (err) {
        list.innerHTML = '<p class="comments-loading">Impossible de charger les commentaires.</p>';
    }
}

async function submitComment(form, id, card) {
    const input   = form.content;
    const content = input.value.trim();
    if (!content) return;

    const submit = form.querySelector('button');
    submit.disabled = true;

    try {
        const comment = await api('/api/post/comment', {
            method: 'POST', headers: FORM_HEADERS,
            body: formBody({ post_id: id, content })
        });

        const post = findPost(id);
        if (post) {
            post.comment_count++;
            if (post._comments) post._comments.push(comment);
            updateCommentCount(card, post.comment_count);
        }
        card.querySelector('.comments-list').insertAdjacentHTML('beforeend', commentItemHtml(comment));
        form.reset();
    } catch (err) {
        toast(err.message || 'Erreur lors de l\'envoi du commentaire.', 'error');
    } finally {
        submit.disabled = false;
    }
}

/* ── Suppression ── */
async function deletePost(id, card) {
    if (!confirm('Voulez-vous vraiment supprimer ce post ? Cette action est irréversible.')) return;

    try {
        await api('/api/post/delete', {
            method: 'POST', headers: FORM_HEADERS,
            body: formBody({ post_id: id })
        });
        state.posts = state.posts.filter(p => p.id !== id);
        card.remove();
        if (!state.posts.length && !state.hasMore) feed.innerHTML = emptyHtml();
        toast('Post supprimé.', 'success');
    } catch (err) {
        toast(err.message || 'Erreur lors de la suppression.', 'error');
    }
}

/* ================================================================
   RECHERCHE + FILTRES
   ================================================================ */
let searchTimeout = null;
const searchInput = document.getElementById('post-search');

searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const value = e.target.value.trim();
        if (value === state.search) return;
        state.search = value;
        loadPosts();
    }, 300);
});

const btnFilter     = document.getElementById('btn-filter');
const filterPanel   = document.getElementById('filter-panel');
const filterList    = document.getElementById('filter-list');
const filterBadge   = document.getElementById('filter-badge');
const filterSupport = document.getElementById('filter-support');

filterList.innerHTML = Object.entries(CATEGORIES).map(([key, c]) => `
    <button type="button" class="filter-item cat-${key}" data-cat="${key}">
        <i class="fa-solid ${c.icon}"></i>
        <span>${c.label}</span>
        <i class="fa-solid fa-check filter-check"></i>
    </button>`).join('');

btnFilter.addEventListener('click', (e) => {
    e.stopPropagation();
    filterPanel.classList.toggle('visible');
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.filter-wrap')) filterPanel.classList.remove('visible');
});

function updateFilterBadge() {
    const count = state.categories.size + (state.support ? 1 : 0);
    filterBadge.textContent = count;
    filterBadge.classList.toggle('visible', count > 0);
}

filterList.addEventListener('click', (e) => {
    const item = e.target.closest('.filter-item');
    if (!item) return;
    const cat = item.dataset.cat;

    if (state.categories.has(cat)) state.categories.delete(cat);
    else state.categories.add(cat);

    item.classList.toggle('active', state.categories.has(cat));
    updateFilterBadge();
    loadPosts();
});

filterSupport.addEventListener('click', () => {
    state.support = !state.support;
    filterSupport.classList.toggle('active', state.support);
    updateFilterBadge();
    loadPosts();
});

function resetFilters(reload = true) {
    state.categories.clear();
    state.support = false;
    state.search  = '';
    searchInput.value = '';
    filterSupport.classList.remove('active');
    filterList.querySelectorAll('.filter-item').forEach(i => i.classList.remove('active'));
    updateFilterBadge();
    if (reload) loadPosts();
}

document.getElementById('filter-reset').addEventListener('click', () => resetFilters());

/* ================================================================
   NOUVEAU POST
   ================================================================ */
const postModal        = document.getElementById('post-modal');
const postModalOverlay = document.getElementById('post-modal-overlay');
const postForm         = document.getElementById('post-form');
const postSubmit       = document.getElementById('post-submit');
const submitHint       = document.getElementById('submit-hint');
const titleInput       = document.getElementById('post-title');
const contentInput     = document.getElementById('post-content');
const titleCount       = document.getElementById('title-count');
const contentCount     = document.getElementById('content-count');
const switchComments   = document.getElementById('switch-comments');
const catPicker        = document.getElementById('cat-picker');

let selectedCategory = null;
let commentsEnabled  = true;

catPicker.innerHTML = Object.entries(CATEGORIES).map(([key, c]) => `
    <button type="button" class="cat-option cat-${key}" data-cat="${key}">
        <i class="fa-solid ${c.icon}"></i><span>${c.label}</span>
    </button>`).join('');

catPicker.addEventListener('click', (e) => {
    const opt = e.target.closest('.cat-option');
    if (!opt) return;
    selectedCategory = opt.dataset.cat;
    catPicker.querySelectorAll('.cat-option').forEach(o => o.classList.toggle('selected', o === opt));
    updateSubmitState();
});

switchComments.addEventListener('click', () => {
    commentsEnabled = !commentsEnabled;
    switchComments.classList.toggle('on', commentsEnabled);
    switchComments.setAttribute('aria-checked', String(commentsEnabled));
});

function updateCounter(input, counterEl, max) {
    const len = input.value.length;
    counterEl.textContent = `${len}/${max}`;
    counterEl.classList.toggle('near', len >= max * 0.9);
}

function updateSubmitState() {
    const ready = selectedCategory && titleInput.value.trim() && contentInput.value.trim();
    postSubmit.disabled = !ready;
    submitHint.style.display = selectedCategory ? 'none' : 'block';
}

titleInput.addEventListener('input',   () => { updateCounter(titleInput, titleCount, LIMITS.title);       updateSubmitState(); });
contentInput.addEventListener('input', () => { updateCounter(contentInput, contentCount, LIMITS.content); updateSubmitState(); });

function openPostModal() {
    postModal.classList.add('visible');
    postModalOverlay.classList.add('visible');
}

function closePostModal() {
    postModal.classList.remove('visible');
    postModalOverlay.classList.remove('visible');
    postForm.reset();
    selectedCategory = null;
    commentsEnabled  = true;
    switchComments.classList.add('on');
    switchComments.setAttribute('aria-checked', 'true');
    catPicker.querySelectorAll('.cat-option').forEach(o => o.classList.remove('selected'));
    updateCounter(titleInput, titleCount, LIMITS.title);
    updateCounter(contentInput, contentCount, LIMITS.content);
    updateSubmitState();
}

document.getElementById('btn-new-post').addEventListener('click', openPostModal);
document.getElementById('post-modal-cancel').addEventListener('click', closePostModal);
postModalOverlay.addEventListener('click', closePostModal);

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    closePostModal();
    filterPanel.classList.remove('visible');
});

postForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!selectedCategory || postSubmit.disabled) return;

    const fd = new FormData(postForm);
    fd.set('category', selectedCategory);
    fd.set('comments_enabled', commentsEnabled ? '1' : '0');

    postSubmit.disabled = true;
    postSubmit.textContent = 'Publication...';

    try {
        const post = await api('/api/posts', { method: 'POST', body: fd });

        closePostModal();

        if (hasActiveFilters()) {
            // Le nouveau post pourrait être caché par les filtres : on les retire pour le montrer
            resetFilters();
        } else {
            state.posts.unshift(post);
            feed.querySelector('.feed-empty')?.remove();
            feed.insertAdjacentHTML('afterbegin', postCardHtml(post));
        }
        toast('Post publié !', 'success');
    } catch (err) {
        toast(err.message || 'Le post n\'a pas pu être publié.', 'error');
    } finally {
        postSubmit.textContent = 'Publier';
        updateSubmitState();
    }
});

/* ================================================================
   DÉMARRAGE
   ================================================================ */
updateCounter(titleInput, titleCount, LIMITS.title);
updateCounter(contentInput, contentCount, LIMITS.content);
updateSubmitState();
loadPosts();
</script>

</body>
</html>