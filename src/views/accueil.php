<?php
// ============================================================
// src/views/accueil.php
// Page principale après connexion
// ============================================================

// Sécurité : si pas connecté → retour au login
if (empty($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Initiale de l'utilisateur pour l'avatar
$initiale = strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1));
$username = htmlspecialchars($_SESSION['username'] ?? 'Utilisateur');
$email    = htmlspecialchars($_SESSION['email']    ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebPêche — Carte</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- CSS page -->
    <link rel="stylesheet" href="/assets/css/accueil.css">
</head>
<body class="theme-light" id="app">

<div class="app-container">

    <!-- ══════════════════════════════════════
         SIDEBAR GAUCHE
    ══════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">

        <!-- En-tête : avatar + nom + bouton fermer -->
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

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <p class="nav-label">ACCUEIL</p>

            <a href="/accueil" class="nav-link active">
                <i class="fa-solid fa-house"></i>
                <span>Home Map</span>
            </a>
            <a href="/communaute" class="nav-link">
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

        <!-- Bas de la sidebar : déconnexion + toggle thème -->
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

    <!-- ══════════════════════════════════════
         ZONE CARTE
    ══════════════════════════════════════ -->
    <main class="map-wrapper">

        <!-- Barre flottante en haut de la carte -->
        <div class="map-topbar">

            <!-- Groupe gauche : ouvrir sidebar + recherche -->
            <div class="topbar-group topbar-left">
                <button class="btn-icon" id="btn-open" title="Ouvrir le menu">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="search-input" placeholder="Rechercher un lac ...">
                    <div class="search-suggestions" id="search-suggestions"></div>
                </div>
            </div>

            <!-- Groupe droite : modifier + filtrer par poisson -->
            <div class="topbar-group topbar-right">
                <button class="btn-icon" id="btn-add-spot" title="Ajouter un spot de pêche">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn-filter-pill" id="btn-filter" title="Filtrer par poisson">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Filtrer</span>
                    <span class="filter-badge" id="filter-badge"></span>
                </button>

                <!-- Panneau déroulant de filtre par espèce de poisson -->
                <div class="filter-panel" id="filter-panel">
                    <div class="filter-panel-header">
                        <span><i class="fa-solid fa-filter"></i> Filtrer par poisson</span>
                        <button type="button" id="filter-reset">Réinitialiser</button>
                    </div>
                    <div class="filter-fixed">
                        <button type="button" class="filter-item filter-support" id="filter-support">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Spots Support</span>
                            <i class="fa-solid fa-check filter-check"></i>
                        </button>
                    </div>
                    <div class="filter-divider">Espèces</div>
                    <div class="filter-list" id="filter-list"></div>
                </div>
            </div>

        </div>

        <!-- Carte Leaflet -->
        <div id="map"></div>

    </main>
</div>

<!-- ══════════════════════════════════════
     BANNIÈRE — Mode "placement" (ajout d'un spot)
══════════════════════════════════════ -->
<div class="placement-banner" id="placement-banner">
    <i class="fa-solid fa-location-crosshairs"></i>
    <span>Cliquez sur la carte pour choisir l'emplacement de votre spot</span>
    <button id="placement-cancel" class="placement-cancel" title="Annuler">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>

<!-- ══════════════════════════════════════
     MODALE — Ajouter un spot
══════════════════════════════════════ -->
<div class="modal-overlay" id="add-spot-overlay"></div>
<div class="modal-box" id="add-spot-modal">
    <div class="modal-header">
        <h2>Ajouter un spot de pêche</h2>
        <button type="button" class="btn-icon" id="add-spot-cancel">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <form id="add-spot-form" class="modal-form">
        <input type="hidden" name="latitude"  id="spot-lat">
        <input type="hidden" name="longitude" id="spot-lng">
        <input type="hidden" name="region"    id="spot-region">

        <label>Nom du spot *</label>
        <input type="text" name="name" placeholder="Ex : Lac de la Forêt" required maxlength="100">

        <label>Lieu <span class="optional-tag">(détecté automatiquement)</span></label>
        <div class="modal-detected-location" id="spot-region-preview">
            <i class="fa-solid fa-location-dot"></i>
            <span id="spot-region-text">Détection du lieu...</span>
        </div>

        <label>Espèces de poissons <span class="optional-tag">(facultatif)</span></label>
        <input type="text" name="species" id="spot-species" placeholder="Laissez vide si vous ne savez pas" maxlength="255">
        <div class="fish-chips" id="fish-chips"></div>

        <label>Description <span class="optional-tag">(facultatif)</span></label>
        <textarea name="description" placeholder="Accès, conseils, ambiance du lieu..."></textarea>

        <button type="submit" class="btn-auth">Ajouter ce spot</button>
    </form>
</div>

<div class="modal-overlay" id="cooldown-overlay"></div>
<div class="modal-box cooldown-box" id="cooldown-modal">
    <div class="cooldown-icon"><i class="fa-solid fa-hourglass-half"></i></div>
    <h2>Doucement, pêcheur !</h2>
    <p>Pour garder la carte fiable et éviter le spam, vous pouvez ajouter <strong>1 spot par heure</strong>.</p>
    <div class="cooldown-timer" id="cooldown-timer">--:--</div>
    <p class="cooldown-hint">avant de pouvoir ajouter un nouveau spot</p>
    <button type="button" class="btn-auth cooldown-close" id="cooldown-close">J'ai compris</button>
</div>

<!-- ══════════════════════════════════════
     PANNEAU LATÉRAL — Détails d'un spot
══════════════════════════════════════ -->
<div class="panel-overlay" id="spot-panel-overlay"></div>
<aside class="spot-panel" id="spot-panel">
    <div class="panel-header">
        <span class="panel-header-title">Détails du spot</span>
        <button type="button" class="btn-icon" id="spot-panel-close">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <div class="panel-body" id="spot-panel-body">
        <!-- Rempli dynamiquement en JS -->
    </div>
</aside>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ================================================================
   THÈME — on relit la préférence sauvegardée AVANT de créer la carte
   ================================================================ */
const app      = document.getElementById('app');
const btnLight = document.getElementById('btn-light');
const btnDarkT = document.getElementById('btn-dark');

const savedTheme = localStorage.getItem('webpeche_theme') || 'light';

if (savedTheme === 'dark') {
    app.classList.remove('theme-light');
    app.classList.add('theme-dark');
    btnDarkT.classList.add('active');
    btnLight.classList.remove('active');
}

/* ================================================================
   CARTE LEAFLET
   ================================================================ */
const tileLight = 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png?lang=fr';
const tileDark  = tileLight; // même fond, assombri par CSS en mode dark

const map = L.map('map', {
    zoomControl: false,
    attributionControl: false,
    minZoom: 3,                              // empêche de trop dézoomer (carte qui se répète)
    maxBounds: [[-90, -180], [90, 180]],      // empêche de glisser au-delà d'une seule carte du monde
    maxBoundsViscosity: 1.0
}).setView([46.6, 2.3], 6); // France entière

L.control.zoom({ position: 'bottomright' }).addTo(map);

let currentLayer = L.tileLayer(
    savedTheme === 'dark' ? tileDark : tileLight,
    { maxZoom: 19, detectRetina: true, noWrap: true }
).addTo(map);

// Groupe qui contiendra tous les marqueurs de spots (facile à vider/recharger)
const spotsLayer  = L.layerGroup().addTo(map);
let allSpots       = [];  // copie des données des spots (pour la recherche)
const markersById  = {};  // accès rapide à un marqueur depuis l'id du spot

/* ================================================================
   SIDEBAR — ouvrir / fermer
   ================================================================ */
const sidebar  = document.getElementById('sidebar');
const btnOpen  = document.getElementById('btn-open');
const btnClose = document.getElementById('btn-close');

btnOpen.style.display = 'none';

btnClose.addEventListener('click', () => {
    sidebar.classList.add('collapsed');
    btnOpen.style.display  = 'flex';
    btnClose.style.display = 'none';
    setTimeout(() => map.invalidateSize(), 280);
});

btnOpen.addEventListener('click', () => {
    sidebar.classList.remove('collapsed');
    btnOpen.style.display  = 'none';
    btnClose.style.display = 'flex';
    setTimeout(() => map.invalidateSize(), 280);
});

/* ================================================================
   THÈME LIGHT / DARK — clic sur les boutons
   ================================================================ */
btnLight.addEventListener('click', () => {
    app.classList.remove('theme-dark');
    app.classList.add('theme-light');
    btnLight.classList.add('active');
    btnDarkT.classList.remove('active');

    localStorage.setItem('webpeche_theme', 'light');
});

btnDarkT.addEventListener('click', () => {
    app.classList.remove('theme-light');
    app.classList.add('theme-dark');
    btnDarkT.classList.add('active');
    btnLight.classList.remove('active');

    map.removeLayer(currentLayer);
    currentLayer = L.tileLayer(tileDark, { maxZoom: 19, detectRetina: true, noWrap: true }).addTo(map);

    localStorage.setItem('webpeche_theme', 'dark');
});

/* ================================================================
   OUTILS — échappement HTML + formatage de date
   ================================================================ */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function formatDate(dateStr) {
    const d = new Date(dateStr.replace(' ', 'T'));
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Enlève les accents et met en minuscule, pour comparer du texte
// sans se soucier des accents (ex: "lac de la foret" trouve "Lac de la Forêt")
function normalizeText(str) {
    return (str || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

/* ================================================================
   NOTE EN POURCENTAGE — convertit la note sur 5 en pourcentage
   et choisit une couleur selon le résultat (vert / orange / rouge)
   ================================================================ */
function ratingToPercent(rating) {
    const value = parseFloat(rating);
    if (!value) return null; // pas encore de note (0 ou vide)
    return Math.round((value / 5) * 100);
}

function ratingClass(pct) {
    if (pct === null) return 'no-rating';
    if (pct < 50)     return 'rating-bad';
    if (pct < 75)     return 'rating-mid';
    return 'rating-good';
}

// Construit le HTML du badge (utilisé à l'affichage initial)
function ratingBadgeHtml(rating) {
    const pct  = ratingToPercent(rating);
    const cls  = ratingClass(pct);
    const text = pct === null ? 'Pas encore noté' : `${pct}%`;
    return `<span class="spot-rating-value rating-badge ${cls}">${text}</span>`;
}

// Met à jour un badge déjà affiché (utilisé après un vote, sans recharger)
function applyRatingBadge(el, rating) {
    const pct = ratingToPercent(rating);
    el.className   = `spot-rating-value rating-badge ${ratingClass(pct)}`;
    el.textContent = pct === null ? 'Pas encore noté' : `${pct}%`;
}

/* ================================================================
   RECHERCHE DE LIEU — autocomplétion + zoom sur la carte
   API gratuite OpenStreetMap Nominatim, sans clé nécessaire
   ================================================================ */
const searchInput       = document.getElementById('search-input');
const searchSuggestions = document.getElementById('search-suggestions');
let searchTimeout = null;

searchInput.addEventListener('input', () => {
    const query = searchInput.value.trim();

    if (query.length < 2) {
        hideSuggestions();
        return;
    }

    // Les spots déjà ajoutés par les utilisateurs s'affichent tout de suite
    const localMatches = filterLocalSpots(query);
    renderSuggestions(localMatches, []);

    // On attend que l'utilisateur arrête de taper avant d'interroger l'API
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchSuggestions(query, localMatches), 400);
});

// Recherche dans les spots déjà ajoutés (nom ou région), sans tenir compte des accents
function filterLocalSpots(query) {
    const q = normalizeText(query);
    return allSpots
        .filter(spot =>
            normalizeText(spot.name).includes(q) ||
            normalizeText(spot.region).includes(q)
        )
        .slice(0, 5);
}

const placesCache = new Map();
let searchController = null;

async function fetchSuggestions(query, localMatches) {
    const key = normalizeText(query);
    if (placesCache.has(key)) {
        renderSuggestions(localMatches, placesCache.get(key));
        return;
    }

    if (searchController) searchController.abort(); // annule la requête précédente
    searchController = new AbortController();

    try {
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=8&accept-language=fr&countrycodes=fr,be`;
        const res    = await fetch(url, { signal: searchController.signal });
        const places = await res.json();
        placesCache.set(key, places);
        renderSuggestions(localMatches, places);
    } catch (err) {
        if (err.name === 'AbortError') return;
        console.error('Erreur de recherche :', err);
        renderSuggestions(localMatches, []);
    }
}

function renderSuggestions(spots, places) {
    if (!spots.length && !places.length) {
        searchSuggestions.innerHTML = '<div class="search-no-result">Aucun résultat trouvé</div>';
        searchSuggestions.classList.add('visible');
        return;
    }

    // Spots déjà ajoutés sur WebPêche (mis en avant avec une icône poisson)
    const spotsHtml = spots.map(s => `
        <div class="search-suggestion-item" data-type="spot" data-id="${s.id}" data-lat="${s.latitude}" data-lon="${s.longitude}">
            <i class="fa-solid fa-fish"></i>
            <span>${escapeHtml(s.name)}${s.region ? ' — ' + escapeHtml(s.region) : ''}</span>
        </div>
    `).join('');

    // Lieux trouvés via la recherche géographique générale
    const placesHtml = places.map(p => `
        <div class="search-suggestion-item" data-type="place" data-lat="${p.lat}" data-lon="${p.lon}">
            <i class="fa-solid fa-location-dot"></i>
            <span>${escapeHtml(p.display_name)}</span>
        </div>
    `).join('');

    searchSuggestions.innerHTML = spotsHtml + placesHtml;
    searchSuggestions.classList.add('visible');

    searchSuggestions.querySelectorAll('.search-suggestion-item').forEach(item => {
        item.addEventListener('click', () => {
            const lat  = parseFloat(item.dataset.lat);
            const lon  = parseFloat(item.dataset.lon);

            if (item.dataset.type === 'spot') {
                goToSpot(parseInt(item.dataset.id, 10), lat, lon);
            } else {
                goToLocation(lat, lon, item.querySelector('span').textContent);
            }
        });
    });
}

// Va vers un spot déjà existant et ouvre sa popup
function goToSpot(id, lat, lon) {
    map.setView([lat, lon], 14);
    hideSuggestions();
    searchInput.value = '';

    const marker = markersById[id];
    if (marker) marker.openPopup();
}

// Va vers un lieu trouvé par la recherche géographique (sans créer de marqueur)
function goToLocation(lat, lon, label) {
    map.setView([lat, lon], 12);
    hideSuggestions();
    searchInput.value = label;
}

function hideSuggestions() {
    searchSuggestions.classList.remove('visible');
    searchSuggestions.innerHTML = '';
}

// Appui sur "Entrée" → va directement au premier résultat affiché
searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        const firstItem = searchSuggestions.querySelector('.search-suggestion-item');
        if (firstItem) firstItem.click();
    }
});

// Ferme les suggestions si on clique en dehors de la barre de recherche
document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-bar')) {
        hideSuggestions();
    }
});

/* ================================================================
   SPOTS — chargement + affichage des marqueurs sur la carte
   ================================================================ */
async function loadSpots() {
    try {
        const res   = await fetch('/api/spots');
        const spots = await res.json();
        allSpots = spots;
        spotsLayer.clearLayers();
        spots.forEach(addSpotMarker);
    } catch (err) {
        console.error('Erreur de chargement des spots :', err);
    }
}

// Raccourcit une adresse complète aux 2 premiers éléments, pour la popup compacte
// (l'adresse complète reste visible dans le panneau "Voir plus")
function shortAddress(address, parts = 2) {
    if (!address) return '';
    return address.split(',').slice(0, parts).map(s => s.trim()).join(', ');
}

// Badge auteur : "Support" (admin) ou le nom de l'utilisateur
function authorBadgeHtml(isAdmin, name) {
    if (isAdmin) {
        return `<div class="popup-author is-admin"><i class="fa-solid fa-circle-check"></i><span>Support</span></div>`;
    }
    const label = name ? escapeHtml(name) : 'Utilisateur';
    return `<div class="popup-author"><i class="fa-solid fa-circle-user"></i><span>${label}</span></div>`;
}

// Espèces sous forme de pastilles (max 3 dans la popup, "+N" pour le reste)
function speciesChipsHtml(species, max = 3) {
    const list = (species || '').split(',').map(s => s.trim()).filter(Boolean);
    if (!list.length) return '<span class="species-chip species-empty">Espèces non renseignées</span>';

    const shown = list.slice(0, max).map(f => `<span class="species-chip">${escapeHtml(f)}</span>`).join('');
    const more  = list.length > max ? `<span class="species-chip species-more">+${list.length - max}</span>` : '';
    return shown + more;
}

function popupHtml(spot) {
    const region  = spot.region ? shortAddress(spot.region) : '';
    const myScore = spot.my_score || 0;

    return `
        <div class="spot-popup ${spot.creator_is_admin ? 'is-support' : ''}" data-spot-id="${spot.id}">
            <div class="popup-top">
                ${authorBadgeHtml(spot.creator_is_admin, spot.creator_name)}
                ${ratingBadgeHtml(spot.rating)}
            </div>

            <h3 class="popup-title">${escapeHtml(spot.name)}</h3>

            ${region ? `<div class="popup-meta"><i class="fa-solid fa-location-dot"></i><span>${escapeHtml(region)}</span></div>` : ''}

            <div class="popup-species">${speciesChipsHtml(spot.species)}</div>

            <div class="popup-rate">
                <span class="popup-rate-label">Votre note</span>
                <div class="popup-stars" data-selected="${myScore}">${buildStarsHtml(spot.id, myScore)}</div>
            </div>

            <div class="popup-actions">
                ${spot.can_delete ? `
                    <button class="popup-delete" onclick="deleteSpot(${spot.id})" title="Supprimer ce spot">
                        <i class="fa-solid fa-trash"></i>
                    </button>` : ''}
                <button class="popup-more" onclick="openSpotPanel(${spot.id})">
                    Voir les détails <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>
    `;
}

function addSpotMarker(spot) {
    // Les spots du support ont une icône d'une autre couleur (voir .marker-support en CSS)
    const icon = new L.Icon.Default({ className: spot.creator_is_admin ? 'marker-support' : '' });
    const marker = L.marker([spot.latitude, spot.longitude], { icon }).addTo(spotsLayer);

    marker.bindPopup(() => popupHtml(spot), { minWidth: 260, maxWidth: 300 });

    // Dès que la popup s'ouvre, on précharge les détails en arrière-plan :
    // "Voir les détails" sera alors instantané
    marker.on('popupopen', () => fetchSpotDetails(spot.id).catch(() => {}));

    markersById[spot.id] = marker;
}

function removeSpotFromMap(id) {
    const marker = markersById[id];
    if (marker) {
        spotsLayer.removeLayer(marker);
        delete markersById[id];
    }
    allSpots = allSpots.filter(s => s.id !== id);
    spotDetailsCache.delete(id);
}

// Supprime un spot (popup ou panneau) après confirmation
async function deleteSpot(id) {
    if (!confirm('Voulez-vous vraiment supprimer ce spot ? Cette action est irréversible.')) {
        return;
    }

    try {
        const res = await fetch('/api/spot/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `spot_id=${id}`
        });
        const data = await res.json();

        if (data.error) { alert(data.error); return; }

        removeSpotFromMap(id);

        if (spotPanel.dataset.currentSpot === String(id)) {
            closeSpotPanel();
        }
    } catch (err) {
        console.error('Erreur lors de la suppression :', err);
        alert('Une erreur est survenue lors de la suppression.');
    }
}

// Premier chargement des spots existants (depuis la base de données)
loadSpots();

/* ================================================================
   CHIPS DE POISSONS — suggestions cliquables dans le formulaire
   ================================================================ */
const FISH_LIST = [
    'Truite fario', 'Carpe', 'Brochet', 'Sandre', 'Perche', 'Black-bass',
    'Silure', 'Gardon', 'Tanche', 'Brème', 'Anguille', 'Ombre commun',
    'Saumon de fontaine', 'Goujon', 'Chevesne', 'Barbeau', 'Ablette',
    'Rotengle', 'Mulet', 'Bar'
];
const FISH_INITIAL_COUNT = 8;

const fishChipsContainer = document.getElementById('fish-chips');
const speciesInput       = document.getElementById('spot-species');

function renderFishChips(expanded = false) {
    const list     = expanded ? FISH_LIST : FISH_LIST.slice(0, FISH_INITIAL_COUNT);
    const selected = speciesInput.value.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);

    let html = list.map(fish => `
        <button type="button" class="fish-chip ${selected.includes(fish.toLowerCase()) ? 'selected' : ''}" data-fish="${fish}">
            ${fish}
        </button>
    `).join('');

    if (!expanded && FISH_LIST.length > FISH_INITIAL_COUNT) {
        html += `
            <button type="button" class="fish-chip fish-chip-more" id="fish-chip-more">
                <i class="fa-solid fa-plus"></i> Plus
            </button>
        `;
    }

    fishChipsContainer.innerHTML = html;
}

renderFishChips();

fishChipsContainer.addEventListener('click', (e) => {
    const moreBtn = e.target.closest('#fish-chip-more');
    if (moreBtn) {
        renderFishChips(true);
        return;
    }

    const chip = e.target.closest('.fish-chip');
    if (!chip) return;

    const fish    = chip.dataset.fish;
    let   current = speciesInput.value.split(',').map(s => s.trim()).filter(Boolean);
    const idx     = current.findIndex(s => s.toLowerCase() === fish.toLowerCase());

    if (idx >= 0) {
        current.splice(idx, 1);
        chip.classList.remove('selected');
    } else {
        current.push(fish);
        chip.classList.add('selected');
    }

    speciesInput.value = current.join(', ');
});

/* ================================================================
   FILTRE PAR ESPÈCE — sélection multiple
   Affiche/cache les spots de la carte selon les poissons choisis
   ================================================================ */
const activeFishFilters = new Set();

const btnFilter   = document.getElementById('btn-filter');
const filterPanel = document.getElementById('filter-panel');
const filterList  = document.getElementById('filter-list');
const filterBadge = document.getElementById('filter-badge');

btnFilter.addEventListener('click', (e) => {
    e.stopPropagation();
    filterPanel.classList.contains('visible') ? closeFilterPanel() : openFilterPanel();
});

function openFilterPanel() {
    renderFilterList();
    filterPanel.classList.add('visible');
}

function closeFilterPanel() {
    filterPanel.classList.remove('visible');
}

// Liste des espèces réellement présentes parmi les spots déjà ajoutés
function getAvailableSpecies() {
    const set = new Set();
    allSpots.forEach(spot => {
        if (!spot.species) return;
        spot.species.split(',').forEach(s => {
            const trimmed = s.trim();
            if (trimmed) set.add(trimmed);
        });
    });
    return Array.from(set).sort((a, b) => a.localeCompare(b, 'fr'));
}

function renderFilterList() {
    const species = getAvailableSpecies();

    if (!species.length) {
        filterList.innerHTML = '<p class="filter-empty">Aucune espèce renseignée pour le moment.</p>';
        return;
    }

    filterList.innerHTML = species.map(fish => `
        <button type="button" class="filter-item ${activeFishFilters.has(fish) ? 'active' : ''}" data-fish="${escapeHtml(fish)}">
            <i class="fa-solid fa-fish"></i>
            <span>${escapeHtml(fish)}</span>
            <i class="fa-solid fa-check filter-check"></i>
        </button>
    `).join('');
}

filterList.addEventListener('click', (e) => {
    const item = e.target.closest('.filter-item');
    if (!item) return;

    const fish = item.dataset.fish;

    if (activeFishFilters.has(fish)) {
        activeFishFilters.delete(fish);
    } else {
        activeFishFilters.add(fish);
    }

    applyFishFilter();
    renderFilterList();
    updateFilterBadge();
});

document.getElementById('filter-reset').addEventListener('click', () => {
    activeFishFilters.clear();
    applyFishFilter();
    renderFilterList();
    updateFilterBadge();
});

let supportOnly = false;
const btnSupport = document.getElementById('filter-support');

btnSupport.addEventListener('click', () => {
    supportOnly = !supportOnly;
    btnSupport.classList.toggle('active', supportOnly);
    applyFishFilter();
    updateFilterBadge();
});

document.getElementById('filter-reset').addEventListener('click', () => {
    activeFishFilters.clear();
    supportOnly = false;
    btnSupport.classList.remove('active');
    applyFishFilter();
    renderFilterList();
    updateFilterBadge();
});

// Affiche les spots qui respectent TOUS les filtres actifs :
// (support uniquement, si coché) ET (au moins un des poissons choisis)
function applyFishFilter() {
    const queries = Array.from(activeFishFilters).map(normalizeText);

    allSpots.forEach(spot => {
        const marker = markersById[spot.id];
        if (!marker) return;

        const spotSpecies  = normalizeText(spot.species || '');
        const matchSupport = !supportOnly || spot.creator_is_admin;
        const matchFish    = queries.length === 0 || queries.some(q => spotSpecies.includes(q));
        const matches      = matchSupport && matchFish;

        if (matches && !spotsLayer.hasLayer(marker)) {
            spotsLayer.addLayer(marker);
        } else if (!matches && spotsLayer.hasLayer(marker)) {
            spotsLayer.removeLayer(marker);
        }
    });
}

function updateFilterBadge() {
    const count = activeFishFilters.size + (supportOnly ? 1 : 0);
    if (count > 0) {
        filterBadge.textContent = count;
        filterBadge.classList.add('visible');
    } else {
        filterBadge.classList.remove('visible');
    }
}

// Ferme le panneau de filtre si on clique en dehors
document.addEventListener('click', (e) => {
    if (!e.target.closest('.topbar-right')) {
        closeFilterPanel();
    }
});

/* ================================================================
   ÉTOILES — note rapide (1 à 5) depuis la popup ou le panneau
   ================================================================ */

// Construit le HTML des 5 étoiles cliquables pour un spot donné
// "selected" pré-remplit les étoiles avec la note déjà donnée par l'utilisateur (s'il y en a une)
function buildStarsHtml(spotId, selected = 0) {
    return [1, 2, 3, 4, 5].map(n => `
        <i class="fa-solid fa-star star-icon ${n <= selected ? 'filled' : ''}"
           data-value="${n}"
           onmouseenter="previewStars(this, ${n})"
           onmouseleave="resetStarsPreview(this)"
           onclick="rateSpotStars(${spotId}, ${n}, this)"></i>
    `).join('');
}

// Aperçu au survol : remplit les étoiles jusqu'à celle survolée
function previewStars(starEl, value) {
    const container = starEl.closest('.popup-stars, .panel-stars');
    if (!container) return;
    container.querySelectorAll('.star-icon').forEach((s, i) => {
        s.classList.toggle('filled', i < value);
    });
}

// Quand la souris quitte les étoiles, on revient à la note réellement choisie
function resetStarsPreview(starEl) {
    const container = starEl.closest('.popup-stars, .panel-stars');
    if (!container) return;
    const selected = parseInt(container.dataset.selected || '0', 10);
    container.querySelectorAll('.star-icon').forEach((s, i) => {
        s.classList.toggle('filled', i < selected);
    });
}

// Envoie la note choisie au serveur et met à jour l'affichage partout où ce spot apparaît
async function rateSpotStars(id, score, starEl) {
    const container = starEl.closest('.popup-stars, .panel-stars');
    if (container) {
        container.dataset.selected = score;
        container.querySelectorAll('.star-icon').forEach((s, i) => {
            s.classList.toggle('filled', i < score);
        });
    }

    try {
        const res = await fetch('/api/spot/rate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `spot_id=${id}&score=${score}`
        });
        const data = await res.json();

        if (data.error) { alert(data.error); return; }

        // Met à jour la donnée en mémoire : essentiel pour que la popup reste
        // juste si on la ferme puis la rouvre, sans recharger toute la page
        const spotData = allSpots.find(s => s.id === id);
        if (spotData) {
            spotData.rating   = data.rating;
            spotData.my_score = score;
        }

        const cached = spotDetailsCache.get(id);
        if (cached) { cached.rating = data.rating; cached.my_score = score; }

        // Met à jour le badge de pourcentage dans la popup, si elle est ouverte
        const popupBadge = document.querySelector(`.spot-popup[data-spot-id="${id}"] .spot-rating-value`);
        if (popupBadge) applyRatingBadge(popupBadge, data.rating);

        // Met à jour le badge dans le panneau "Voir plus", s'il affiche ce même spot
        if (spotPanel.dataset.currentSpot === String(id)) {
            const panelBadge = document.querySelector('.panel-rating-row .spot-rating-value');
            if (panelBadge) applyRatingBadge(panelBadge, data.rating);
        }
    } catch (err) {
        console.error('Erreur lors du vote :', err);
    }
}

/* ================================================================
   MODE PLACEMENT — ajouter un nouveau spot sur la carte
   ================================================================ */
let placementMode = false;
let tempMarker     = null;

const btnAddSpot      = document.getElementById('btn-add-spot');
const placementBanner = document.getElementById('placement-banner');
const addSpotOverlay  = document.getElementById('add-spot-overlay');
const addSpotModal    = document.getElementById('add-spot-modal');
const addSpotForm     = document.getElementById('add-spot-form');

btnAddSpot.addEventListener('click', async () => {
    if (placementMode) { cancelPlacement(); return; }

    // On vérifie le cooldown AVANT de faire placer le pin
    try {
        const res  = await fetch('/api/spots/cooldown');
        const data = await res.json();
        if (data.remaining > 0) { showCooldownModal(data.remaining); return; }
    } catch (err) { /* le serveur re-vérifiera de toute façon */ }

    startPlacement();
});

function startPlacement() {
    placementMode = true;
    btnAddSpot.classList.add('active');
    placementBanner.classList.add('visible');
    map.getContainer().style.cursor = 'crosshair';
}

function cancelPlacement() {
    placementMode = false;
    btnAddSpot.classList.remove('active');
    placementBanner.classList.remove('visible');
    map.getContainer().style.cursor = '';
    if (tempMarker) {
        map.removeLayer(tempMarker);
        tempMarker = null;
    }
}

document.getElementById('placement-cancel').addEventListener('click', cancelPlacement);

map.on('click', (e) => {
    if (!placementMode) return;

    if (tempMarker) map.removeLayer(tempMarker);
    tempMarker = L.marker(e.latlng, { opacity: 0.85 }).addTo(map);

    document.getElementById('spot-lat').value = e.latlng.lat;
    document.getElementById('spot-lng').value = e.latlng.lng;

    detectLocation(e.latlng.lat, e.latlng.lng);

    openAddSpotModal();
});

// Devine automatiquement le lieu (adresse) à partir des coordonnées cliquées,
// pour ne pas obliger l'utilisateur à le taper lui-même
async function detectLocation(lat, lon) {
    const regionInput   = document.getElementById('spot-region');
    const regionPreview = document.getElementById('spot-region-text');

    regionPreview.textContent = 'Détection du lieu...';
    regionInput.value = '';

    try {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&accept-language=fr&zoom=16`;
        const res  = await fetch(url);
        const data = await res.json();

        const label = data.display_name || 'Lieu non déterminé';
        regionInput.value          = label;
        regionPreview.textContent  = label;
    } catch (err) {
        console.error('Erreur de géocodage inverse :', err);
        regionPreview.textContent = 'Lieu non déterminé';
    }
}

function openAddSpotModal() {
    addSpotOverlay.classList.add('visible');
    addSpotModal.classList.add('visible');
}

function closeAddSpotModal() {
    addSpotOverlay.classList.remove('visible');
    addSpotModal.classList.remove('visible');
}

function closeAddSpotFlow() {
    closeAddSpotModal();
    cancelPlacement();
    addSpotForm.reset();
    document.getElementById('spot-region-text').textContent = 'Détection du lieu...';
    renderFishChips();
}

document.getElementById('add-spot-cancel').addEventListener('click', closeAddSpotFlow);
addSpotOverlay.addEventListener('click', closeAddSpotFlow);

addSpotForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(addSpotForm);

    try {
        const res  = await fetch('/api/spots', { method: 'POST', body: formData });
        const spot = await res.json();

        if (spot.cooldown) {                       // sécurité si le cooldown a été dépassé entre-temps
            closeAddSpotFlow();
            showCooldownModal(spot.retry_after);
            return;
        }
        if (spot.error) { alert(spot.error); return; }

        allSpots.push(spot);
        addSpotMarker(spot);
        closeAddSpotModal();
        cancelPlacement();
        addSpotForm.reset();
        document.getElementById('spot-region-text').textContent = 'Détection du lieu...';
        renderFishChips();
    } catch (err) {
        console.error('Erreur lors de l\'ajout du spot :', err);
        alert("Une erreur est survenue, le spot n'a pas pu être ajouté.");
    }
});

/* ================================================================
   POPUP COOLDOWN — 1 spot par heure
   ================================================================ */
const cooldownOverlay = document.getElementById('cooldown-overlay');
const cooldownModal   = document.getElementById('cooldown-modal');
const cooldownTimer   = document.getElementById('cooldown-timer');
let cooldownInterval  = null;

function showCooldownModal(seconds) {
    let left = Math.max(1, Math.ceil(seconds));

    const render = () => {
        const m = String(Math.floor(left / 60)).padStart(2, '0');
        const s = String(left % 60).padStart(2, '0');
        cooldownTimer.textContent = `${m}:${s}`;
    };

    render();
    clearInterval(cooldownInterval);
    cooldownInterval = setInterval(() => {
        left--;
        if (left <= 0) {
            clearInterval(cooldownInterval);
            cooldownTimer.textContent = 'C\'est bon !';
            return;
        }
        render();
    }, 1000);

    cooldownOverlay.classList.add('visible');
    cooldownModal.classList.add('visible');
}

function closeCooldownModal() {
    clearInterval(cooldownInterval);
    cooldownOverlay.classList.remove('visible');
    cooldownModal.classList.remove('visible');
}

document.getElementById('cooldown-close').addEventListener('click', closeCooldownModal);
cooldownOverlay.addEventListener('click', closeCooldownModal);

/* ================================================================
   PANNEAU LATÉRAL — "Voir plus" : détails + avis d'un spot
   ================================================================ */
const spotPanel        = document.getElementById('spot-panel');
const spotPanelBody    = document.getElementById('spot-panel-body');
const spotPanelOverlay = document.getElementById('spot-panel-overlay');

let currentSpotComments = []; // avis du spot actuellement affiché (pour le filtre par étoiles)

// Cache des détails (avis inclus) : évite de re-télécharger à chaque ouverture
const spotDetailsCache   = new Map(); // id -> spot détaillé
const spotDetailsPending = new Map(); // id -> requête en cours (évite les doublons)
const DETAILS_TTL        = 60 * 1000; // les avis sont rafraîchis après 60 s

function fetchSpotDetails(id) {
    const cached = spotDetailsCache.get(id);
    if (cached && Date.now() - cached._cachedAt < DETAILS_TTL) return Promise.resolve(cached);
    if (spotDetailsPending.has(id)) return spotDetailsPending.get(id);

    const p = fetch(`/api/spot?id=${id}`)
        .then(r => r.json())
        .then(spot => {
            if (!spot.error) {
                spot._cachedAt = Date.now();
                spotDetailsCache.set(id, spot);
            }
            return spot;
        })
        .finally(() => spotDetailsPending.delete(id));

    spotDetailsPending.set(id, p);
    return p;
}

async function openSpotPanel(id) {
    spotPanel.classList.add('open');
    spotPanelOverlay.classList.add('visible');
    spotPanel.dataset.currentSpot = id;

    // 1) Affichage IMMÉDIAT avec ce qu'on a déjà (cache ou données de la carte)
    const base    = allSpots.find(s => s.id === id);
    const instant = spotDetailsCache.get(id) || (base ? { ...base, comments: null } : null);

    if (instant) renderSpotPanel(instant);
    else spotPanelBody.innerHTML = '<p class="panel-loading">Chargement...</p>';

    // 2) Les avis arrivent ensuite, sans reconstruire tout le panneau
    try {
        const spot = await fetchSpotDetails(id);
        if (spotPanel.dataset.currentSpot !== String(id)) return; // panneau fermé ou changé entre-temps

        if (spot.error) {
            if (!instant) spotPanelBody.innerHTML = `<p class="panel-loading">${escapeHtml(spot.error)}</p>`;
            return;
        }

        if (!instant)              renderSpotPanel(spot);
        else if (spot !== instant) setPanelComments(spot.comments);
    } catch (err) {
        if (!instant) spotPanelBody.innerHTML = '<p class="panel-loading">Erreur de chargement.</p>';
    }
}

// Met à jour uniquement la liste d'avis (garde le texte que l'utilisateur est en train d'écrire)
function setPanelComments(comments) {
    currentSpotComments = comments;
    const countEl = document.getElementById('reviews-count');
    if (countEl) countEl.textContent = comments.length;

    const active = document.querySelector('#review-filter .review-filter-btn.active');
    renderReviewsList(active ? active.dataset.filter : 'all');
}

function renderSpotPanel(spot) {
    const region      = spot.region      || 'Non renseignée';
    const description = spot.description || 'Aucune description pour le moment.';
    const loaded      = Array.isArray(spot.comments);

    currentSpotComments = loaded ? spot.comments : [];

    spotPanelBody.innerHTML = `
        ${authorBadgeHtml(spot.creator_is_admin, spot.creator_name)}

        <div class="panel-title-row">
            <h2>${escapeHtml(spot.name)}</h2>
            ${spot.can_delete ? `
                <button type="button" class="panel-delete-btn" onclick="deleteSpot(${spot.id})" title="Supprimer ce spot">
                    <i class="fa-solid fa-trash"></i> Supprimer
                </button>
            ` : ''}
        </div>

        <div class="panel-rating-row">
            ${ratingBadgeHtml(spot.rating)}
            <div class="panel-stars" data-selected="${spot.my_score || 0}">${buildStarsHtml(spot.id, spot.my_score || 0)}</div>
        </div>
        <p class="panel-rating-hint">Cliquez sur une étoile pour donner votre note</p>

        <div class="panel-info-block">
            <div class="panel-meta-line"><i class="fa-solid fa-location-dot"></i><span>${escapeHtml(region)}</span></div>
            <div class="popup-species panel-species">${speciesChipsHtml(spot.species, 99)}</div>
        </div>

        <p class="panel-description">${escapeHtml(description)}</p>

        <hr>

        <div class="reviews-header">
            <h3>Avis (<span id="reviews-count">${loaded ? spot.comments.length : '…'}</span>)</h3>
            <div class="review-filter" id="review-filter">
                <button type="button" class="review-filter-btn active" data-filter="all">Tout</button>
                <button type="button" class="review-filter-btn" data-filter="5">5★</button>
                <button type="button" class="review-filter-btn" data-filter="4">4★</button>
                <button type="button" class="review-filter-btn" data-filter="3">3★</button>
                <button type="button" class="review-filter-btn" data-filter="2">2★</button>
                <button type="button" class="review-filter-btn" data-filter="1">1★</button>
            </div>
        </div>

        <div class="reviews-list" id="reviews-list">
            ${loaded ? '' : '<p class="no-reviews">Chargement des avis...</p>'}
        </div>

        <form class="add-review-form" onsubmit="submitReview(event, ${spot.id})">
            <textarea name="comment" placeholder="Partagez votre expérience sur ce spot..." required></textarea>
            <button type="submit" class="btn-auth">Publier mon avis</button>
        </form>
    `;

    if (loaded) renderReviewsList('all');

    document.querySelectorAll('#review-filter .review-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#review-filter .review-filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderReviewsList(btn.dataset.filter);
        });
    });
}

// Affiche la liste des avis, filtrée par note en étoiles ("all" = tout afficher)
function renderReviewsList(filter) {
    const list = filter === 'all'
        ? currentSpotComments
        : currentSpotComments.filter(c => String(c.score) === String(filter));

    const reviewsListEl = document.getElementById('reviews-list');

    reviewsListEl.innerHTML = list.length
        ? list.map(c => `
            <div class="review-item">
                <div class="review-header">
                    <div class="review-header-left">
                        <span class="review-author">${escapeHtml(c.username)}</span>
                        <span class="review-stars-mini">${'★'.repeat(c.score)}${'☆'.repeat(5 - c.score)}</span>
                    </div>
                    <span class="review-date">${formatDate(c.created_at)}</span>
                </div>
                <p class="review-text">${escapeHtml(c.comment)}</p>
            </div>
        `).join('')
        : '<p class="no-reviews">Aucun avis ne correspond à ce filtre.</p>';
}

async function submitReview(e, spotId) {
    e.preventDefault();
    const form    = e.target;
    const comment = form.comment.value.trim();
    if (!comment) return;

    const starsContainer = document.querySelector('.panel-stars');
    const score = parseInt(starsContainer?.dataset.selected || '0', 10);

    if (!score) {
        alert('Merci de donner une note en étoiles avant de publier votre avis.');
        return;
    }

    try {
        const res = await fetch('/api/spot/comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `spot_id=${spotId}&score=${score}&comment=${encodeURIComponent(comment)}`
        });
        const data = await res.json();

        if (data.error) { alert(data.error); return; }

        currentSpotComments.unshift(data);
        document.getElementById('reviews-count').textContent = currentSpotComments.length;

        const activeBtn = document.querySelector('#review-filter .review-filter-btn.active');
        renderReviewsList(activeBtn ? activeBtn.dataset.filter : 'all');

        form.reset();
    } catch (err) {
        console.error('Erreur lors de l\'envoi de l\'avis :', err);
    }
}

function closeSpotPanel() {
    spotPanel.classList.remove('open');
    spotPanelOverlay.classList.remove('visible');
    spotPanel.dataset.currentSpot = '';
}

document.getElementById('spot-panel-close').addEventListener('click', closeSpotPanel);
spotPanelOverlay.addEventListener('click', closeSpotPanel);
</script>


</body>
</html>