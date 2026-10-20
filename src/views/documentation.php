<?php
if (empty($_SESSION['user_id'])) { header('Location: /login'); exit; }
$initiale = strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1));
$username = htmlspecialchars($_SESSION['username'] ?? 'Utilisateur');
$email    = htmlspecialchars($_SESSION['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebPêche — Documentation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/accueil.css">
    <link rel="stylesheet" href="/assets/css/documentation.css">
</head>
<body class="theme-light" id="app">
<div class="app-container">

    <!-- ══════════════════════════════════════
         SIDEBAR GAUCHE
    ══════════════════════════════════════ -->
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
            <p class="nav-label">NAVIGATION</p>
            <a href="/accueil" class="nav-link">
                <i class="fa-solid fa-house"></i><span>Home Map</span>
            </a>
            <a href="/communaute" class="nav-link">
                <i class="fa-regular fa-circle-user"></i><span>Communauté</span>
            </a>
            <a href="/parametres" class="nav-link">
                <i class="fa-solid fa-gear"></i><span>Paramètre</span>
            </a>
            <a href="/documentation" class="nav-link active">
                <i class="fa-solid fa-book"></i><span>Documentation</span>
            </a>
        </nav>
        <div class="sidebar-footer">
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
         ZONE DOCUMENTATION
    ══════════════════════════════════════ -->
    <div class="doc-wrapper">

        <!-- Bouton flottant ouvrir sidebar (visible quand sidebar fermée) -->
        <button class="btn-icon btn-open-float" id="btn-open" title="Ouvrir le menu" style="display:none">
            <i class="fa-solid fa-arrow-right"></i>
        </button>
        <!-- Bouton sommaire mobile flottant -->
        <button class="toc-float-btn" id="toc-toggle" title="Sommaire">
            <i class="fa-solid fa-list"></i> Sommaire
        </button>

        <div class="doc-layout">

            <!-- ── SOMMAIRE LATÉRAL ── -->
            <nav class="doc-toc" id="doc-toc">
                <p class="toc-label">Sur cette page</p>
                <ul>
                    <li><a href="#intro"          class="toc-link active"><i class="fa-solid fa-fish"></i> Introduction</a></li>
                    <li><a href="#materiel"        class="toc-link"><i class="fa-solid fa-toolbox"></i> Le matériel</a></li>
                    <li><a href="#ou-aller"        class="toc-link"><i class="fa-solid fa-map-location-dot"></i> Où aller pêcher</a></li>
                    <li><a href="#techniques"      class="toc-link"><i class="fa-solid fa-hand-holding-water"></i> Techniques</a></li>
                    <li><a href="#reglementation"  class="toc-link"><i class="fa-solid fa-scale-balanced"></i> Réglementation</a></li>
                    <li><a href="#protegees"       class="toc-link"><i class="fa-solid fa-shield-halved"></i> Espèces protégées</a></li>
                    <li><a href="#invasives"       class="toc-link"><i class="fa-solid fa-triangle-exclamation"></i> Espèces invasives</a></li>
                    <li><a href="#videos"          class="toc-link"><i class="fa-solid fa-video"></i> Vidéos</a></li>
                    <li><a href="#astuces-pro"     class="toc-link"><i class="fa-solid fa-trophy"></i> Astuces de pro</a></li>
                    <li><a href="#pour-tous"       class="toc-link"><i class="fa-solid fa-users"></i> Techniques pour tous</a></li>
                    <li><a href="#astuces-pro"     class="toc-link"><i class="fa-solid fa-trophy"></i> Astuces de pro</a></li>
                    <li><a href="#pour-tous"       class="toc-link"><i class="fa-solid fa-users"></i> Pour tout le monde</a></li>
                </ul>
                <div class="toc-card-info">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>En cas de doute sur une réglementation, consultez toujours la <a href="https://www.federationpeche.fr" target="_blank" rel="noopener">Fédération Nationale</a>.</p>
                </div>
            </nav>

            <!-- ── CONTENU PRINCIPAL ── -->
            <main class="doc-content">

                <!-- HERO -->
                <div class="doc-hero">
                    <div class="doc-hero-tag">Guide complet</div>
                    <h1 class="doc-hero-title">La pêche pour les débutants</h1>
                    <p class="doc-hero-subtitle">Tout ce qu'il faut savoir pour commencer à pêcher en France : matériel, lieux, techniques, réglementation et espèces à connaître.</p>
                    <div class="doc-hero-meta">
                        <span><i class="fa-regular fa-clock"></i> ~15 min de lecture</span>
                        <span><i class="fa-solid fa-layer-group"></i> Niveau débutant</span>
                    </div>
                </div>

                <!-- 01 — Introduction -->
                <section class="doc-section" id="intro">
                    <div class="section-label">01 — Introduction</div>
                    <h2>Pourquoi se mettre à la pêche ?</h2>
                    <p>La pêche est l'une des activités de plein air les plus pratiquées en France, avec plus de <strong>1,5 million de pêcheurs</strong> réguliers. C'est un loisir accessible à tous les âges, peu coûteux à démarrer, et qui permet de se reconnecter avec la nature tout en développant la patience et l'observation.</p>
                    <p>Contrairement à ce qu'on croit souvent, la pêche n'est pas réservée aux experts. Avec un équipement simple et quelques notions de base, vous pouvez attraper vos premiers poissons dès votre première sortie.</p>
                    <div class="doc-callout doc-callout--blue">
                        <i class="fa-solid fa-lightbulb"></i>
                        <div>
                            <strong>Bon à savoir</strong>
                            <p>En France, la pêche en eau douce nécessite l'adhésion à une Association Agréée de Pêche et de Protection du Milieu Aquatique (AAPPMA). Cette carte de pêche est votre premier achat obligatoire — environ <strong>90 €/an</strong> pour un adulte, bien moins pour les jeunes de moins de 18 ans.</p>
                        </div>
                    </div>
                </section>

                <!-- 02 — Matériel -->
                <section class="doc-section" id="materiel">
                    <div class="section-label">02 — Le matériel</div>
                    <h2>Le matériel nécessaire</h2>
                    <p>Inutile de dépenser des centaines d'euros pour débuter. Un kit de base bien choisi suffit amplement pour vos premières sorties.</p>
                    <h3>L'essentiel absolu</h3>
                    <div class="materiel-grid">
                        <div class="materiel-card">
                            <div class="materiel-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                            <div class="materiel-body">
                                <h4>Canne à pêche</h4>
                                <p>Pour débuter, une canne entre 3 et 4 m est idéale. Simple à utiliser, pas de moulinet à maîtriser.</p>
                                <div class="materiel-links">
                                    <a href="https://www.decathlon.fr/tous-les-sports/peche/cannes-a-peche" target="_blank" rel="noopener" class="btn-link btn-link--decathlon"><i class="fa-solid fa-bag-shopping"></i> Decathlon (~15 €)</a>
                                </div>
                            </div>
                        </div>
                        <div class="materiel-card">
                            <div class="materiel-icon"><i class="fa-solid fa-rotate"></i></div>
                            <div class="materiel-body">
                                <h4>Fil de pêche</h4>
                                <p>Un nylon de 0,18 à 0,22 mm de diamètre convient à la plupart des situations en eau douce.</p>
                                <div class="materiel-links">
                                    <a href="https://www.decathlon.fr/tous-les-sports/peche/fils-tresses-fluorocarbones-peche" target="_blank" rel="noopener" class="btn-link btn-link--decathlon"><i class="fa-solid fa-bag-shopping"></i> Decathlon (~5 €)</a>
                                </div>
                            </div>
                        </div>
                        <div class="materiel-card">
                            <div class="materiel-icon"><i class="fa-regular fa-circle-dot"></i></div>
                            <div class="materiel-body">
                                <h4>Flotteur &amp; plombs</h4>
                                <p>Le flotteur vous indique quand un poisson mord. Choisissez un kit flotteur + plombs adapté à votre canne.</p>
                                <div class="materiel-links">
                                    <a href="https://www.decathlon.fr/tous-les-sports/peche/flotteurs-bouchons-de-peche" target="_blank" rel="noopener" class="btn-link btn-link--decathlon"><i class="fa-solid fa-bag-shopping"></i> Decathlon (~8 €)</a>
                                </div>
                            </div>
                        </div>
                        <div class="materiel-card">
                            <div class="materiel-icon"><i class="fa-solid fa-location-pin"></i></div>
                            <div class="materiel-body">
                                <h4>Hameçons</h4>
                                <p>Pour débuter, des hameçons de taille 10 à 14 couvrent la plupart des espèces courantes. Achetez-en en sachet.</p>
                                <div class="materiel-links">
                                    <a href="https://www.decathlon.fr/tous-les-sports/peche/hamecons-de-peche" target="_blank" rel="noopener" class="btn-link btn-link--decathlon"><i class="fa-solid fa-bag-shopping"></i> Decathlon (~3 €)</a>
                                </div>
                            </div>
                        </div>
                        <div class="materiel-card">
                            <div class="materiel-icon"><i class="fa-solid fa-seedling"></i></div>
                            <div class="materiel-body">
                                <h4>Appâts</h4>
                                <p>Pour commencer : des vers de terre (jardin ou animalerie) ou du pain de mie. Les vers fonctionnent avec quasiment toutes les espèces.</p>
                                <div class="materiel-links">
                                    <a href="https://www.decathlon.fr/tous-les-sports/peche/amorces-appats" target="_blank" rel="noopener" class="btn-link btn-link--decathlon"><i class="fa-solid fa-bag-shopping"></i> Decathlon (~4 €)</a>
                                </div>
                            </div>
                        </div>
                        <div class="materiel-card">
                            <div class="materiel-icon"><i class="fa-solid fa-box-open"></i></div>
                            <div class="materiel-body">
                                <h4>Boîte de pêche &amp; épuisette</h4>
                                <p>Une petite boîte pour ranger vos accessoires + une épuisette pour sortir les poissons proprement sans les blesser.</p>
                                <div class="materiel-links">
                                    <a href="https://www.decathlon.fr/search?Ntt=boite+de+peche" target="_blank" rel="noopener" class="btn-link btn-link--decathlon"><i class="fa-solid fa-bag-shopping"></i> Decathlon (~12 €)</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="doc-callout doc-callout--green">
                        <i class="fa-solid fa-wallet"></i>
                        <div>
                            <strong>Budget débutant complet</strong>
                            <p>Comptez entre <strong>40 et 60 €</strong> pour un kit de départ complet chez Decathlon ou dans un magasin de pêche spécialisé. Évitez les équipements bas de gamme (moins de 15 € le kit complet) qui cassent rapidement.</p>
                        </div>
                    </div>
                    <h3>Le matériel optionnel (mais utile)</h3>
                    <ul class="doc-list">
                        <li><strong>Seau ou nasse de gardiennage</strong> — pour garder les poissons vivants avant de les relâcher</li>
                        <li><strong>Siège de pêche pliable</strong> — pour les longues sessions (<a href="https://www.decathlon.fr/search?Ntt=siege+peche+pliable" target="_blank" rel="noopener">voir chez Decathlon</a>)</li>
                        <li><strong>Tapis de réception</strong> — protège le poisson quand vous l'amenez à terre</li>
                        <li><strong>Pince à hameçon</strong> — pour décrocher les hameçons sans blesser le poisson ni vos doigts</li>
                    </ul>
                </section>

                <!-- 03 — Où aller -->
                <section class="doc-section" id="ou-aller">
                    <div class="section-label">03 — Où aller pêcher</div>
                    <h2>Où aller pêcher en France ?</h2>
                    <p>La France est un paradis pour la pêche en eau douce : rivières, lacs, étangs, canaux… Il existe des spots adaptés aux débutants partout sur le territoire.</p>
                    <h3>Les types d'eaux à connaître</h3>
                    <div class="type-eau-grid">
                        <div class="type-eau-card">
                            <div class="type-eau-header type-eau-header--1">
                                <i class="fa-solid fa-droplet"></i>
                                <span>1ère catégorie</span>
                            </div>
                            <p>Eaux rapides, froides, oxygénées. On y trouve surtout des <strong>truites et ombres</strong>. La pêche s'ouvre le 2e samedi de mars et ferme le 3e dimanche de septembre.</p>
                        </div>
                        <div class="type-eau-card">
                            <div class="type-eau-header type-eau-header--2">
                                <i class="fa-solid fa-water"></i>
                                <span>2ème catégorie</span>
                            </div>
                            <p>Eaux plus lentes, plus chaudes. On y trouve <strong>carpes, brochets, perches, sandres</strong>. Pas de saison fermée sauf pour certaines espèces.</p>
                        </div>
                    </div>
                    <div class="doc-callout doc-callout--yellow">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <div>
                            <strong>Utilisez notre carte WebPêche !</strong>
                            <p>Les spots repérés par la communauté sont parfaits pour trouver un endroit près de chez vous. <a href="/accueil">Voir la carte →</a></p>
                        </div>
                    </div>
                    <h3>Comment trouver les plans d'eau autorisés ?</h3>
                    <ul class="doc-list">
                        <li>Le site de votre <strong>AAPPMA locale</strong> liste les parcours accessibles avec votre carte</li>
                        <li>Le site <a href="https://www.cartedepeche.fr" target="_blank" rel="noopener">cartedepeche.fr</a> — carte officielle de toutes les AAPPMA de France</li>
                        <li>Les <strong>étangs communaux</strong> souvent peu fréquentés et idéaux pour débuter</li>
                        <li>Les <strong>canaux navigables</strong> (Voies Navigables de France) — accessibles avec une carte nationale</li>
                    </ul>
                </section>

                <!-- 04 — Techniques -->
                <section class="doc-section" id="techniques">
                    <div class="section-label">04 — Techniques</div>
                    <h2>Techniques de base</h2>
                    <h3>La pêche au coup (la plus simple)</h3>
                    <p>C'est la technique idéale pour débuter. On utilise un flotteur, et on laisse l'appât descendre à une profondeur définie. Quand le flotteur s'enfonce ou s'incline, c'est qu'un poisson mord !</p>
                    <div class="steps-list">
                        <div class="step">
                            <div class="step-num">1</div>
                            <div class="step-body">
                                <strong>Assemblez votre montage</strong>
                                <p>Enfilez les plombs sur le fil, puis le flotteur, nouez l'hameçon. Réglez la profondeur avec le stop-flotteur.</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-num">2</div>
                            <div class="step-body">
                                <strong>Amorcez votre poste</strong>
                                <p>Lancez une petite boule d'amorce ou du pain émietté à l'endroit où vous voulez pêcher pour attirer les poissons.</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-num">3</div>
                            <div class="step-body">
                                <strong>Accrochez l'appât</strong>
                                <p>Piquez le ver sur l'hameçon en le traversant une ou deux fois. Il doit rester bien accroché mais avoir encore du mouvement.</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-num">4</div>
                            <div class="step-body">
                                <strong>Lancez et observez</strong>
                                <p>Lancez délicatement votre ligne, tendez légèrement et observez le flotteur. Patience — c'est tout l'art de la pêche !</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-num">5</div>
                            <div class="step-body">
                                <strong>Ferrez et récupérez</strong>
                                <p>Quand le flotteur plonge, relevez la canne d'un coup sec (le "ferrage"), puis remontez le poisson doucement.</p>
                            </div>
                        </div>
                    </div>
                    <h3>No-kill ou garder le poisson ?</h3>
                    <p>La pratique du <strong>no-kill</strong> (relâcher le poisson après capture) est fortement recommandée pour les débutants et contribue à préserver les populations. Si vous souhaitez garder un poisson pour le consommer, assurez-vous d'abord qu'il n'est pas protégé et qu'il dépasse la taille minimale légale.</p>
                </section>

                <!-- 05 — Réglementation -->
                <section class="doc-section" id="reglementation">
                    <div class="section-label">05 — Réglementation</div>
                    <h2>La réglementation en France</h2>
                    <div class="regle-grid">
                        <div class="regle-card">
                            <div class="regle-icon"><i class="fa-solid fa-id-card"></i></div>
                            <h4>Carte de pêche obligatoire</h4>
                            <p>Toute personne de plus de 12 ans doit être titulaire d'une carte de pêche (adhésion AAPPMA). Sans elle, vous risquez une amende.</p>
                            <a href="https://www.cartedepeche.fr" target="_blank" rel="noopener" class="btn-link btn-link--blue">Acheter en ligne →</a>
                        </div>
                        <div class="regle-card">
                            <div class="regle-icon"><i class="fa-solid fa-calendar-days"></i></div>
                            <h4>Saisons de pêche</h4>
                            <p>En 1ère catégorie, la truite se pêche du 2e samedi de mars au 3e dimanche de septembre. En 2e catégorie, la plupart des espèces sont pêchables toute l'année.</p>
                        </div>
                        <div class="regle-card">
                            <div class="regle-icon"><i class="fa-solid fa-ruler-horizontal"></i></div>
                            <h4>Tailles minimales</h4>
                            <p>Chaque espèce a une taille légale minimale. Ex : truite fario → 23 cm, brochet → 60 cm, sandre → 40 cm. En dessous, relâchez obligatoirement.</p>
                        </div>
                        <div class="regle-card">
                            <div class="regle-icon"><i class="fa-solid fa-list-ol"></i></div>
                            <h4>Nombre de lignes</h4>
                            <p>Avec une carte standard, vous pouvez utiliser jusqu'à 4 lignes simultanément en 2e catégorie, 2 lignes en 1ère catégorie.</p>
                        </div>
                    </div>
                    <div class="doc-callout doc-callout--red">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>
                            <strong>Infractions les plus courantes</strong>
                            <p>Pêcher sans carte, hors saison, conserver un poisson en dessous de la taille légale ou prélever une espèce protégée sont des infractions passibles de plusieurs centaines d'euros d'amende et de confiscation du matériel.</p>
                        </div>
                    </div>
                </section>

                <!-- 06 — Espèces protégées -->
                <section class="doc-section" id="protegees">
                    <div class="section-label">06 — Espèces protégées</div>
                    <h2>Comment savoir si une espèce est protégée ?</h2>
                    <p>Certaines espèces de poissons sont totalement protégées en France : leur capture, même accidentelle, doit être immédiatement suivie d'un relâcher sans manipulation inutile.</p>
                    <h3>Les espèces les plus connues totalement protégées</h3>
                    <div class="espece-list">
                        <div class="espece-row espece-row--protected">
                            <div class="espece-badge espece-badge--protected"><i class="fa-solid fa-shield-halved"></i> Protégée</div>
                            <div class="espece-info">
                                <strong>Bouvière (<em>Rhodeus amarus</em>)</strong>
                                <p>Petit poisson de 5-8 cm fréquentant les eaux douces calmes. Reconnaissable à sa ligne médiane colorée chez le mâle.</p>
                            </div>
                        </div>
                        <div class="espece-row espece-row--protected">
                            <div class="espece-badge espece-badge--protected"><i class="fa-solid fa-shield-halved"></i> Protégée</div>
                            <div class="espece-info">
                                <strong>Apron du Rhône (<em>Zingel asper</em>)</strong>
                                <p>Espèce critique, endémique au Rhône et à ses affluents. Extrêmement rare, strictement protégée.</p>
                            </div>
                        </div>
                        <div class="espece-row espece-row--protected">
                            <div class="espece-badge espece-badge--protected"><i class="fa-solid fa-shield-halved"></i> Protégée</div>
                            <div class="espece-info">
                                <strong>Écrevisses à pattes blanches (<em>Austropotamobius pallipes</em>)</strong>
                                <p>Espèce native française, en fort déclin. Toute capture, même involontaire, doit être signalée.</p>
                            </div>
                        </div>
                        <div class="espece-row espece-row--protected">
                            <div class="espece-badge espece-badge--protected"><i class="fa-solid fa-shield-halved"></i> Protégée</div>
                            <div class="espece-info">
                                <strong>Lamproie de Planer (<em>Lampetra planeri</em>)</strong>
                                <p>Ressemble à une anguille, mais c'est un poisson sans mâchoire. Protégée dans de nombreuses régions.</p>
                            </div>
                        </div>
                    </div>
                    <h3>Comment vérifier ?</h3>
                    <ul class="doc-list">
                        <li>Contactez votre <strong>fédération de pêche locale</strong> — la liste officielle est disponible sur <a href="https://www.federationpeche.fr" target="_blank" rel="noopener">federationpeche.fr</a></li>
                        <li>En cas de doute sur le terrain : <strong>relâchez toujours immédiatement</strong></li>
                    </ul>
                </section>

                <!-- 07 — Espèces invasives -->
                <section class="doc-section" id="invasives">
                    <div class="section-label">07 — Espèces invasives</div>
                    <h2>Les espèces invasives : que faire ?</h2>
                    <p>À l'opposé des espèces protégées, certaines espèces invasives représentent une menace pour les écosystèmes aquatiques français. Leur relâcher dans un milieu naturel est <strong>interdit par la loi</strong>.</p>
                    <div class="doc-callout doc-callout--red">
                        <i class="fa-solid fa-ban"></i>
                        <div>
                            <strong>Interdiction absolue</strong>
                            <p>Il est interdit de relâcher une espèce invasive dans un milieu naturel. En cas de capture, euthanasier le poisson ou le remettre aux autorités compétentes (fédération de pêche locale).</p>
                        </div>
                    </div>
                    <div class="espece-list">
                        <div class="espece-row espece-row--invasive">
                            <div class="espece-badge espece-badge--invasive"><i class="fa-solid fa-triangle-exclamation"></i> Invasive</div>
                            <div class="espece-info">
                                <strong>Poisson-chat (<em>Ameiurus melas</em>)</strong>
                                <p>Originaire d'Amérique du Nord. Reconnaissable à ses "moustaches" (barbillons). Très prolifique, il concurrence les espèces locales. <strong>Ne jamais relâcher.</strong></p>
                            </div>
                        </div>
                        <div class="espece-row espece-row--invasive">
                            <div class="espece-badge espece-badge--invasive"><i class="fa-solid fa-triangle-exclamation"></i> Invasive</div>
                            <div class="espece-info">
                                <strong>Perche soleil (<em>Lepomis gibbosus</em>)</strong>
                                <p>Originaire d'Amérique du Nord également. Très colorée (bleu et orange), elle est envahissante dans de nombreux plans d'eau français.</p>
                            </div>
                        </div>
                        <div class="espece-row espece-row--invasive">
                            <div class="espece-badge espece-badge--invasive"><i class="fa-solid fa-triangle-exclamation"></i> Invasive</div>
                            <div class="espece-info">
                                <strong>Écrevisses américaines &amp; signal</strong>
                                <p>Porteuses de la "peste des écrevisses", elles déciment les populations indigènes. Si vous en capturez, ne les relâchez pas.</p>
                            </div>
                        </div>
                        <div class="espece-row espece-row--invasive">
                            <div class="espece-badge espece-badge--invasive"><i class="fa-solid fa-triangle-exclamation"></i> Invasive</div>
                            <div class="espece-info">
                                <strong>Poisson rouge &amp; carpe koï sauvages</strong>
                                <p>Des poissons d'aquarium relâchés dans la nature perturbent l'écosystème. C'est interdit — ne jamais relâcher vos animaux de compagnie aquatiques.</p>
                            </div>
                        </div>
                    </div>
                    <p>Pour signaler une espèce invasive, contactez votre <a href="https://www.cartedepeche.fr" target="_blank" rel="noopener">fédération de pêche locale</a>.</p>
                </section>

                <!-- 08 — Vidéos -->
                <section class="doc-section" id="videos">
                    <div class="section-label">08 — Vidéos</div>
                    <h2><i class="fa-solid fa-clapperboard section-icon"></i> Vidéos explicatives en français</h2>
                    <p>Ces vidéos sélectionnées vous aideront à visualiser les techniques et à mieux comprendre la pêche en pratique.</p>
                    <div class="videos-grid">
                        <div class="video-card">
                            <div class="video-embed">
                                <iframe
                                    src="https://www.youtube.com/embed/rvJ1kf_Xkhk0"
                                    title="Débuter la pêche au coup"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen loading="lazy">
                                </iframe>
                            </div>
                            <div class="video-info">
                                <h4>Débuter la pêche au coup</h4>
                                <p>Montage de la ligne, amorçage, technique de lancer — les bases expliquées clairement.</p>
                                <span class="video-tag"><i class="fa-solid fa-hand-holding-water"></i> Technique</span>
                            </div>
                        </div>
                        <div class="video-card">
                            <div class="video-embed">
                                <iframe
                                    src="https://www.youtube.com/embed/dT-vTiWicoc"
                                    title="Quel matériel choisir"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen loading="lazy">
                                </iframe>
                            </div>
                            <div class="video-info">
                                <h4>Quel matériel choisir ?</h4>
                                <p>Tour d'horizon du matériel essentiel pour débuter sans se ruiner, avec des conseils concrets.</p>
                                <span class="video-tag"><i class="fa-solid fa-toolbox"></i> Matériel</span>
                            </div>
                        </div>
                        <div class="video-card">
                            <div class="video-embed">
                                <iframe
                                    src="https://www.youtube.com/embed/Fr7r6WpRyXI"
                                    title="La réglementation expliquée"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen loading="lazy">
                                </iframe>
                            </div>
                            <div class="video-info">
                                <h4>La réglementation expliquée</h4>
                                <p>Carte de pêche, saisons, tailles légales — tout ce qu'il faut savoir pour pêcher légalement.</p>
                                <span class="video-tag"><i class="fa-solid fa-scale-balanced"></i> Réglementation</span>
                            </div>
                        </div>
                    </div>
                    <div class="doc-callout doc-callout--blue">
                        <i class="fa-brands fa-youtube"></i>
                        <div>
                            <strong>Playlists YouTube recommandées</strong>
                            <p>Pour aller plus loin : <a href="https://youtube.com/playlist?list=PLxfzG8LoCw720ACkarY_WWMR0tnAMxwSN&si=dI3coNUT_jAfDT5C" target="_blank" rel="noopener">Fishing Chaos</a>, <a href="https://www.youtube.com/@carpcontact" target="_blank" rel="noopener">Carp Contact</a>, et les vidéos officielles de la <a href="https://www.youtube.com/@FedNatPeche" target="_blank" rel="noopener">Fédération Nationale de Pêche</a>.</p>
                        </div>
                    </div>
                </section>

                <!-- 09 — Astuces de pro -->
                <section class="doc-section" id="astuces-pro">
                    <div class="section-label">09 — Astuces de pro</div>
                    <h2><i class="fa-solid fa-trophy section-icon"></i> Astuces de pro</h2>
                    <p>Ces conseils viennent de pêcheurs expérimentés. Ils feront la différence entre une sortie ordinaire et une sortie mémorable.</p>

                    <div class="pro-grid">

                        <div class="pro-card">
                            <div class="pro-card-header">
                                <div class="pro-icon"><i class="fa-solid fa-eye"></i></div>
                                <h4>Observer avant de lancer</h4>
                            </div>
                            <p>Prenez 5 minutes à observer l'eau avant de pêcher : remous, sauts de poissons, végétation immergée. Les pros repèrent les poissons avant de les pêcher.</p>
                            <div class="pro-badge"><i class="fa-solid fa-star"></i> Niveau confirmé</div>
                        </div>

                        <div class="pro-card">
                            <div class="pro-card-header">
                                <div class="pro-icon"><i class="fa-solid fa-clock"></i></div>
                                <h4>Les meilleures heures</h4>
                            </div>
                            <p>L'aube (6h–9h) et le crépuscule (18h–21h) sont les heures d'or. Les poissons montent en surface pour se nourrir et sont bien plus actifs qu'en pleine journée.</p>
                            <div class="pro-badge"><i class="fa-solid fa-star"></i> Niveau confirmé</div>
                        </div>

                        <div class="pro-card">
                            <div class="pro-card-header">
                                <div class="pro-icon"><i class="fa-solid fa-temperature-half"></i></div>
                                <h4>Pêcher par temps couvert</h4>
                            </div>
                            <p>Un ciel nuageux est souvent meilleur qu'un soleil éclatant. La lumière diffuse incite les poissons à s'approcher de la surface. Évitez les journées trop chaudes en été.</p>
                            <div class="pro-badge"><i class="fa-solid fa-star"></i> Niveau confirmé</div>
                        </div>

                        <div class="pro-card">
                            <div class="pro-card-header">
                                <div class="pro-icon"><i class="fa-solid fa-wind"></i></div>
                                <h4>Travailler vent dans le dos</h4>
                            </div>
                            <p>Le vent pousse les insectes et la nourriture vers la rive sous le vent. Installez-vous dos au vent pour bénéficier de cette concentration naturelle d'appâts.</p>
                            <div class="pro-badge"><i class="fa-solid fa-star"></i> Niveau confirmé</div>
                        </div>

                        <div class="pro-card">
                            <div class="pro-card-header">
                                <div class="pro-icon"><i class="fa-solid fa-volume-xmark"></i></div>
                                <h4>Silence et discrétion</h4>
                            </div>
                            <p>Les vibrations se propagent très bien dans l'eau. Évitez de frapper le sol, de parler fort ou de faire du bruit. Les poissons s'enfuient à la moindre alerte.</p>
                            <div class="pro-badge"><i class="fa-solid fa-star"></i> Essentiel</div>
                        </div>

                        <div class="pro-card">
                            <div class="pro-card-header">
                                <div class="pro-icon"><i class="fa-solid fa-droplet-slash"></i></div>
                                <h4>Changer d'appât régulièrement</h4>
                            </div>
                            <p>Un ver qui pend depuis 20 minutes sans touche ? Changez-le. Les appâts frais et mobiles sont bien plus attractifs. Les pros changent toutes les 10–15 minutes.</p>
                            <div class="pro-badge"><i class="fa-solid fa-star"></i> Niveau confirmé</div>
                        </div>

                    </div>

                    <div class="doc-callout doc-callout--yellow">
                        <i class="fa-solid fa-lightbulb"></i>
                        <div>
                            <strong>Le secret des pros</strong>
                            <p>La pêche, c'est 80% d'observation et 20% de technique. Plus vous passez de temps à observer votre environnement, plus vous comprendrez les comportements des poissons et vous adapterez.</p>
                        </div>
                    </div>
                </section>

                <!-- 10 — Pour tout le monde -->
                <section class="doc-section" id="pour-tous">
                    <div class="section-label">10 — Pour tout le monde</div>
                    <h2><i class="fa-solid fa-users section-icon"></i> Techniques pour tout le monde</h2>
                    <p>Ces techniques sont accessibles quel que soit votre niveau ou votre budget. Elles couvrent les situations les plus courantes que vous rencontrerez en bord d'eau.</p>

                    <div class="technique-tabs">
                        <div class="technique-card technique-card--debutant">
                            <div class="technique-card-top">
                                <div class="technique-level technique-level--debutant">
                                    <i class="fa-solid fa-seedling"></i> Débutant
                                </div>
                                <h4>Pêche à l'asticot</h4>
                            </div>
                            <p>L'asticot (larve de mouche) est l'appât le plus polyvalent qui existe. Il attire la brème, la perche, le gardon et même la carpe. Piquez-en 2 ou 3 sur l'hameçon et laissez descendre.</p>
                            <ul class="technique-tips">
                                <li><i class="fa-solid fa-check"></i> Appât facile à trouver (animalerie ou magasin de pêche)</li>
                                <li><i class="fa-solid fa-check"></i> Fonctionne toute l'année</li>
                                <li><i class="fa-solid fa-check"></i> Idéal pour les étangs et rivières calmes</li>
                            </ul>
                        </div>

                        <div class="technique-card technique-card--intermediaire">
                            <div class="technique-card-top">
                                <div class="technique-level technique-level--intermediaire">
                                    <i class="fa-solid fa-fish"></i> Intermédiaire
                                </div>
                                <h4>Pêche au leurre souple</h4>
                            </div>
                            <p>Le leurre souple imite un petit poisson ou un ver. On le fait nager en récupérant le fil par à-coups (technique "stop & go"). Redoutable pour le brochet, la perche et le sandre.</p>
                            <ul class="technique-tips">
                                <li><i class="fa-solid fa-check"></i> Pas d'appât vivant nécessaire</li>
                                <li><i class="fa-solid fa-check"></i> Très efficace en eaux claires</li>
                                <li><i class="fa-solid fa-check"></i> Nécessite une canne avec moulinet</li>
                            </ul>
                        </div>

                        <div class="technique-card technique-card--avance">
                            <div class="technique-card-top">
                                <div class="technique-level technique-level--avance">
                                    <i class="fa-solid fa-trophy"></i> Avancé
                                </div>
                                <h4>Pêche à la mouche</h4>
                            </div>
                            <p>La pêche à la mouche utilise de faux insectes en plume ou en fibres synthétiques. La technique de lancer est complexe mais le résultat est exceptionnel, notamment pour la truite en rivière.</p>
                            <ul class="technique-tips">
                                <li><i class="fa-solid fa-check"></i> Technique noble et très efficace pour la truite</li>
                                <li><i class="fa-solid fa-check"></i> Nécessite un apprentissage du lancer</li>
                                <li><i class="fa-solid fa-check"></i> Matériel spécifique (soie flottante, canne légère)</li>
                            </ul>
                        </div>

                        <div class="technique-card technique-card--debutant">
                            <div class="technique-card-top">
                                <div class="technique-level technique-level--debutant">
                                    <i class="fa-solid fa-seedling"></i> Débutant
                                </div>
                                <h4>Pêche au pain</h4>
                            </div>
                            <p>Une tranche de pain de mie ou une boulette de mie humide est un appât redoutablement efficace pour la carpe et le gardon. Gratuit, simple et naturel.</p>
                            <ul class="technique-tips">
                                <li><i class="fa-solid fa-check"></i> Appât 100% gratuit</li>
                                <li><i class="fa-solid fa-check"></i> Excellent pour les carpes de surface</li>
                                <li><i class="fa-solid fa-check"></i> Visible de loin pour le poisson</li>
                            </ul>
                        </div>

                        <div class="technique-card technique-card--intermediaire">
                            <div class="technique-card-top">
                                <div class="technique-level technique-level--intermediaire">
                                    <i class="fa-solid fa-fish"></i> Intermédiaire
                                </div>
                                <h4>Pêche au feeder</h4>
                            </div>
                            <p>Le feeder est une cage métallique remplie d'amorce qui s'enfonce au fond et libère doucement l'appât. Idéal pour attirer les gros gardons et brèmes sur votre poste.</p>
                            <ul class="technique-tips">
                                <li><i class="fa-solid fa-check"></i> Efficace dans les rivières à courant</li>
                                <li><i class="fa-solid fa-check"></i> Pêche de fond, poissons souvent plus gros</li>
                                <li><i class="fa-solid fa-check"></i> Matériel accessible (~20 € pour débuter)</li>
                            </ul>
                        </div>

                        <div class="technique-card technique-card--avance">
                            <div class="technique-card-top">
                                <div class="technique-level technique-level--avance">
                                    <i class="fa-solid fa-trophy"></i> Avancé
                                </div>
                                <h4>Carpfishing (nuit)</h4>
                            </div>
                            <p>La pêche de la carpe de nuit est une discipline à part entière. On pose 2 à 3 cannes avec des bouillettes (appâts bouillis) et on attend l'alarme de touche. Prises souvent spectaculaires.</p>
                            <ul class="technique-tips">
                                <li><i class="fa-solid fa-check"></i> Carpes de grande taille possibles</li>
                                <li><i class="fa-solid fa-check"></i> Matériel spécifique (bivouac, détecteurs)</li>
                                <li><i class="fa-solid fa-check"></i> Certains plans d'eau autorisent les sessions nuit</li>
                            </ul>
                        </div>

                    </div>
                </section>

                <footer class="doc-footer">
                    <p>Contenu rédigé par l'équipe WebPêche.</p>
                    <a href="/accueil" class="btn-link btn-link--blue"><i class="fa-solid fa-map"></i> Retour à la carte</a>
                </footer>

            </main>
        </div>
    </div>
</div>

<script>
(function () {
    const app   = document.getElementById('app');
    const btnL  = document.getElementById('btn-light');
    const btnD  = document.getElementById('btn-dark');
    const saved = localStorage.getItem('webpeche_theme') || 'light';

    function applyTheme(t) {
        app.classList.toggle('theme-dark',  t === 'dark');
        app.classList.toggle('theme-light', t !== 'dark');
        btnL.classList.toggle('active', t !== 'dark');
        btnD.classList.toggle('active', t === 'dark');
        localStorage.setItem('webpeche_theme', t);
    }
    applyTheme(saved);
    btnL.addEventListener('click', () => applyTheme('light'));
    btnD.addEventListener('click', () => applyTheme('dark'));

    const sidebar  = document.getElementById('sidebar');
    const btnOpen  = document.getElementById('btn-open');
    const btnClose = document.getElementById('btn-close');
    btnOpen.style.display = 'none';

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

    // Surlignage sommaire au scroll
    const sections = document.querySelectorAll('.doc-section[id]');
    const tocLinks = document.querySelectorAll('.toc-link');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                tocLinks.forEach(l => l.classList.remove('active'));
                const a = document.querySelector(`.toc-link[href="#${entry.target.id}"]`);
                if (a) a.classList.add('active');
            }
        });
    }, { rootMargin: '-20% 0px -70% 0px' });
    sections.forEach(s => observer.observe(s));

    // Scroll doux
    tocLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const t = document.querySelector(link.getAttribute('href'));
            if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // ── Mobile : bouton flottant pour ouvrir le sommaire ──
    const tocToggle = document.getElementById('toc-toggle');
    const docToc    = document.getElementById('doc-toc');
    if (tocToggle && docToc) {
        tocToggle.addEventListener('click', () => {
            docToc.classList.toggle('toc-open');
            tocToggle.querySelector('i').className =
                docToc.classList.contains('toc-open')
                    ? 'fa-solid fa-xmark'
                    : 'fa-solid fa-list';
        });
        // Ferme le sommaire quand on clique un lien (mobile)
        tocLinks.forEach(link => {
            link.addEventListener('click', () => docToc.classList.remove('toc-open'));
        });
    }
})();
</script>
</body>
</html>