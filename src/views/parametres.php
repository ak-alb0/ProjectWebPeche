<?php
// Vue chargée par ParametresController::show() — $user injecté par le contrôleur
$initiale    = strtoupper(substr($user['username'] ?? 'U', 0, 1));
$mois = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
$ts   = !empty($user['created_at']) ? strtotime($user['created_at']) : null;
$memberSince = $ts ? $mois[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebPêche — Paramètres</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/accueil.css">
    <link rel="stylesheet" href="/assets/css/parametres.css">
</head>
<body class="theme-light" id="app">
<div class="app-container">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-card">
                <div class="user-avatar" id="sb-avatar"><?= $initiale ?></div>
                <div class="user-info">
                    <span class="app-brand">WebPêche</span>
                    <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                </div>
            </div>
            <button class="btn-icon" id="btn-close"><i class="fa-solid fa-arrow-left"></i></button>
        </div>
        <nav class="sidebar-nav">
            <p class="nav-label">NAVIGATION</p>
            <a href="/accueil"       class="nav-link"><i class="fa-solid fa-house"></i><span>Home Map</span></a>
            <a href="/communaute"        class="nav-link"><i class="fa-regular fa-circle-user"></i><span>Communauté</span></a>
            <a href="/parametres"     class="nav-link active"><i class="fa-solid fa-gear"></i><span>Paramètre</span></a>
            <a href="/documentation" class="nav-link"><i class="fa-solid fa-book"></i><span>Documentation</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="theme-toggle">
                <button class="theme-btn active" id="btn-light"><i class="fa-solid fa-sun"></i> Light</button>
                <button class="theme-btn"         id="btn-dark"><i class="fa-solid fa-moon"></i> Dark</button>
            </div>
        </div>
    </aside>

    <!-- ══ ZONE PARAMÈTRES ══ -->
    <main class="param-wrapper">
        <div class="param-topbar">
            <button class="btn-icon" id="btn-open" style="display:none"><i class="fa-solid fa-arrow-right"></i></button>
        </div>

        <div class="param-scroll">
            <div class="param-card">

                <!-- Initiale + date membre -->
                <div class="param-hero">
                    <div class="param-initiale"><?= $initiale ?></div>
                    <p class="param-member-since">Membre depuis <?= $memberSince ?></p>
                </div>

                <!-- ── Informations ── -->
                <div class="param-section">
                    <h3 class="param-section-title"><i class="fa-solid fa-user"></i> Informations</h3>

                    <div class="input-float has-value">
                        <input type="text" id="inp-username"
                               value="<?= htmlspecialchars($user['username']) ?>"
                               autocomplete="username" maxlength="50">
                        <label>Nom d'utilisateur</label>
                        <div class="input-icon" id="username-icon"></div>
                        <p class="input-hint">Autorisé : a-z A-Z 0-9 . - _</p>
                    </div>

                    <div class="input-float has-value">
                        <input type="email" id="inp-email"
                               value="<?= htmlspecialchars($user['email']) ?>"
                               autocomplete="email">
                        <label>Adresse email</label>
                    </div>

                    <button class="btn-param btn-param--primary" id="btn-save-profile">
                        <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
                    </button>
                    <p class="field-msg" id="msg-profile"></p>
                </div>

                <!-- ── Mot de passe ── -->
                <div class="param-section">
                    <h3 class="param-section-title"><i class="fa-solid fa-lock"></i> Mot de passe</h3>

                    <div class="input-float input-float--password">
                        <input type="password" id="inp-current" autocomplete="current-password" placeholder=" ">
                        <label>Mot de passe actuel</label>
                        <button type="button" class="eye-toggle" data-target="inp-current">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>

                    <div class="input-float input-float--password">
                        <input type="password" id="inp-new" autocomplete="new-password" placeholder=" ">
                        <label>Nouveau mot de passe</label>
                        <button type="button" class="eye-toggle" data-target="inp-new">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                        <div class="password-strength" id="pwd-strength"></div>
                    </div>

                    <div class="input-float input-float--password">
                        <input type="password" id="inp-confirm" autocomplete="new-password" placeholder=" ">
                        <label>Confirmer le nouveau mot de passe</label>
                        <button type="button" class="eye-toggle" data-target="inp-confirm">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>

                    <button class="btn-param btn-param--primary" id="btn-save-password">
                        <i class="fa-solid fa-key"></i> Changer le mot de passe
                    </button>
                    <p class="field-msg" id="msg-password"></p>
                </div>

                <!-- ── Compte ── -->
                <div class="param-section param-section--danger-zone">
                    <h3 class="param-section-title"><i class="fa-solid fa-circle-exclamation"></i> Compte</h3>
                    <button class="btn-param btn-param--logout" id="btn-logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion
                    </button>
                    <button class="btn-param btn-param--danger" id="btn-delete">
                        <i class="fa-solid fa-trash"></i> Supprimer le compte
                    </button>
                </div>

            </div>
        </div>
    </main>

</div>

<!-- ══ MODAL SUPPRESSION ══ -->
<div class="modal-overlay" id="modal-delete">
    <div class="modal-box modal-box--danger">
        <div class="modal-danger-icon"><i class="fa-solid fa-trash"></i></div>
        <h3>Supprimer votre compte ?</h3>
        <p>Cette action est <strong>irréversible</strong>. Toutes vos données seront définitivement supprimées.</p>
        <div class="input-float input-float--password" style="margin-top:18px">
            <input type="password" id="inp-delete-confirm" placeholder=" ">
            <label>Confirmez avec votre mot de passe</label>
            <button type="button" class="eye-toggle" data-target="inp-delete-confirm">
                <i class="fa-regular fa-eye"></i>
            </button>
        </div>
        <p class="field-msg" id="msg-delete"></p>
        <div class="modal-actions">
            <button class="btn-param btn-param--ghost" id="btn-delete-cancel">Annuler</button>
            <button class="btn-param btn-param--danger" id="btn-delete-confirm">
                <i class="fa-solid fa-trash"></i> Supprimer définitivement
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    // ── Thème ────────────────────────────────────────────────
    const app  = document.getElementById('app');
    const btnL = document.getElementById('btn-light');
    const btnD = document.getElementById('btn-dark');
    const applyTheme = t => {
        app.classList.toggle('theme-dark',  t === 'dark');
        app.classList.toggle('theme-light', t !== 'dark');
        btnL.classList.toggle('active', t !== 'dark');
        btnD.classList.toggle('active', t === 'dark');
        localStorage.setItem('webpeche_theme', t);
    };
    applyTheme(localStorage.getItem('webpeche_theme') || 'light');
    btnL.addEventListener('click', () => applyTheme('light'));
    btnD.addEventListener('click', () => applyTheme('dark'));

    // ── Sidebar ──────────────────────────────────────────────
    const sidebar  = document.getElementById('sidebar');
    const btnOpen  = document.getElementById('btn-open');
    const btnClose = document.getElementById('btn-close');
    btnClose.addEventListener('click', () => {
        sidebar.classList.add('collapsed');
        btnOpen.style.display = 'flex';
        btnClose.style.display = 'none';
    });
    btnOpen.addEventListener('click', () => {
        sidebar.classList.remove('collapsed');
        btnOpen.style.display = 'none';
        btnClose.style.display = 'flex';
    });

    // ── Floating labels ──────────────────────────────────────
    document.querySelectorAll('.input-float input').forEach(inp => {
        const sync = () => inp.closest('.input-float').classList.toggle('has-value', inp.value !== '');
        sync();
        inp.addEventListener('input', sync);
    });

    // ── Show / hide password ─────────────────────────────────
    document.querySelectorAll('.eye-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const inp = document.getElementById(btn.dataset.target);
            const isText = inp.type === 'text';
            inp.type = isText ? 'password' : 'text';
            btn.querySelector('i').className = isText ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
        });
    });

    // ── Barre résistance mot de passe ────────────────────────
    document.getElementById('inp-new').addEventListener('input', function () {
        const v   = this.value;
        const bar = document.getElementById('pwd-strength');
        if (!v) { bar.innerHTML = ''; return; }
        let score = 0;
        if (v.length >= 8)          score++;
        if (/[A-Z]/.test(v))        score++;
        if (/[0-9]/.test(v))        score++;
        if (/[^a-zA-Z0-9]/.test(v)) score++;
        const labels = ['Faible','Faible','Moyen','Bon','Fort'];
        const colors = ['#ef4444','#ef4444','#f59e0b','#22c55e','#2563eb'];
        bar.innerHTML = `
            <span style="color:${colors[score]};font-size:.75rem;font-weight:600">${labels[score]}</span>
            <div class="pwd-bar-track">
                <div class="pwd-bar-fill" style="width:${score*25}%;background:${colors[score]}"></div>
            </div>`;
    });

    // ── Unicité username ─────────────────────────────────────
    let usernameTimer;
    const usernameIcon = document.getElementById('username-icon');
    document.getElementById('inp-username').addEventListener('input', function () {
        clearTimeout(usernameTimer);
        usernameIcon.innerHTML = '';
        const val = this.value.trim();
        if (val.length < 3) return;
        usernameTimer = setTimeout(() => {
            fetch(`/api/check-username?username=${encodeURIComponent(val)}`)
                .then(r => r.json())
                .then(d => {
                    usernameIcon.innerHTML = d.available
                        ? '<i class="fa-solid fa-circle-check" style="color:#22c55e"></i>'
                        : '<i class="fa-solid fa-circle-xmark"  style="color:#ef4444"></i>';
                });
        }, 500);
    });

    // ── Sauvegarder profil ───────────────────────────────────
    document.getElementById('btn-save-profile').addEventListener('click', async () => {
        const msg = document.getElementById('msg-profile');
        try {
            const res  = await fetch('/api/parametres/update', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    username: document.getElementById('inp-username').value.trim(),
                    email:    document.getElementById('inp-email').value.trim()
                })
            });
            const data = await res.json();
            showMsg(msg, data.success ? 'Modifications enregistrées !' : data.message, data.success);
            if (data.success && data.username) {
                document.getElementById('sb-avatar').textContent = data.username[0].toUpperCase();
            }
        } catch (e) {
            showMsg(msg, 'Erreur réseau.', false);
        }
    });

    // ── Changer le mot de passe ──────────────────────────────
    document.getElementById('btn-save-password').addEventListener('click', async () => {
        const msg = document.getElementById('msg-password');
        try {
            const res  = await fetch('/api/parametres/password', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({
                    current_password: document.getElementById('inp-current').value,
                    new_password:     document.getElementById('inp-new').value,
                    confirm_password: document.getElementById('inp-confirm').value
                })
            });
            const data = await res.json();
            showMsg(msg, data.success ? 'Mot de passe mis à jour !' : data.message, data.success);
            if (data.success) {
                ['inp-current','inp-new','inp-confirm'].forEach(id => {
                    const el = document.getElementById(id);
                    el.value = '';
                    el.closest('.input-float').classList.remove('has-value');
                });
                document.getElementById('pwd-strength').innerHTML = '';
            }
        } catch (e) {
            showMsg(msg, 'Erreur réseau.', false);
        }
    });

    // ── Déconnexion ──────────────────────────────────────────
    document.getElementById('btn-logout').addEventListener('click', () => {
        window.location.href = '/logout';
    });

    // ── Supprimer le compte ──────────────────────────────────
    const modalDelete = document.getElementById('modal-delete');

    document.getElementById('btn-delete').addEventListener('click', () => {
        document.getElementById('inp-delete-confirm').value = '';
        document.getElementById('msg-delete').textContent   = '';
        document.getElementById('inp-delete-confirm').closest('.input-float').classList.remove('has-value');
        modalDelete.classList.add('open');
    });

    document.getElementById('btn-delete-cancel').addEventListener('click', () => {
        modalDelete.classList.remove('open');
    });

    // Clic sur le fond → ferme
    modalDelete.addEventListener('click', e => {
        if (e.target === modalDelete) modalDelete.classList.remove('open');
    });

    document.getElementById('btn-delete-confirm').addEventListener('click', async () => {
        const password = document.getElementById('inp-delete-confirm').value;
        const msg      = document.getElementById('msg-delete');

        if (!password) { showMsg(msg, 'Entrez votre mot de passe.', false); return; }

        const btn = document.getElementById('btn-delete-confirm');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Suppression…';

        try {
            const res  = await fetch('/api/parametres/delete', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ password })
            });
            const data = await res.json();

            if (data.success) {
                window.location.href = '/login';
            } else {
                showMsg(msg, data.message || 'Mot de passe incorrect.', false);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-trash"></i> Supprimer définitivement';
            }
        } catch (e) {
            showMsg(msg, 'Erreur réseau, réessayez.', false);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-trash"></i> Supprimer définitivement';
        }
    });

    // ── Helper ───────────────────────────────────────────────
    function showMsg(el, text, ok) {
        el.textContent = text;
        el.className   = 'field-msg ' + (ok ? 'field-msg--ok' : 'field-msg--err');
        if (ok) setTimeout(() => { el.textContent = ''; el.className = 'field-msg'; }, 4000);
    }
})();
</script>
</body>
</html>