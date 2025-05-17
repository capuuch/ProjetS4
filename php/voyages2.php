<?php
    session_start();
    $isConnected = isset($_SESSION['user_id']);

    $voyages_file = '../json/voyages.json';
    $voyages = [];
    $all_seasons = []; // To store unique seasons for the filter

    if (file_exists($voyages_file)) {
        $json_content = file_get_contents($voyages_file);
        $decoded_voyages = json_decode($json_content, true);

        if (is_array($decoded_voyages)) {
            $voyages = $decoded_voyages;
            // Extract unique seasons
            foreach ($voyages as $voyage) {
                if (isset($voyage['saison']) && !empty($voyage['saison'])) {
                    // Handle cases like "Printemps/Été"
                    $s_parts = explode('/', $voyage['saison']);
                    foreach ($s_parts as $part) {
                        $trimmed_part = trim($part);
                        if (!empty($trimmed_part) && !in_array($trimmed_part, $all_seasons)) {
                            $all_seasons[] = $trimmed_part;
                        }
                    }
                }
            }
            sort($all_seasons); // Optional: sort seasons alphabetically
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../css/projetS4.css" id="theme-style">
        <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">

        <title>Green Odyssey Voyages</title>

        <meta charset="UTF-8">
        <meta name="author" content="Anas_Capucine_Hadil"/>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

        <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- pour la map-->
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/> <!-- pour la map-->
    <script>
            // Function to set a cookie
            function setCookie(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }

            // Function to get a cookie
            function getCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for(var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            // Function to switch the theme
            function switchTheme() {
                var currentTheme = document.getElementById('theme-style').getAttribute('href');
                var newTheme;

                if (currentTheme === '../css/projetS4.css') {
                    newTheme = '../css/projetS4-dark.css';
                    document.getElementById('theme-button').textContent = '☀️ Mode Clair';
                } else {
                    newTheme = '../css/projetS4.css';
                    document.getElementById('theme-button').textContent = '🌙 Mode Sombre';
                }

                document.getElementById('theme-style').setAttribute('href', newTheme);
                setCookie('theme', newTheme, 30); // Save preference for 30 days
            }

            // Check for theme preference when page loads
            window.onload = function() {
                var savedTheme = getCookie('theme');
                if (savedTheme) {
                    if (savedTheme === '../css/projetS4-dark.css') {
                        document.getElementById('theme-style').setAttribute('href', savedTheme);
                        document.getElementById('theme-button').textContent = '☀️ Mode Clair';
                    } else {
                        // If cookie value is incoherent, use default
                        document.getElementById('theme-style').setAttribute('href', '../css/projetS4.css');
                        document.getElementById('theme-button').textContent = '🌙 Mode Sombre';
                    }
                }
                // Initialize maps after theme and other onload tasks
                <?php foreach ($voyages as $voyage): ?>
                    <?php if (isset($voyage['coordonnees']) && isset($voyage['coordonnees']['latitude']) && isset($voyage['coordonnees']['longitude'])): ?>
                        createMap(
                            'map<?= htmlspecialchars($voyage['voyage_id']); ?>',
                            <?= htmlspecialchars($voyage['coordonnees']['latitude']); ?>,
                            <?= htmlspecialchars($voyage['coordonnees']['longitude']); ?>,
                            '<?= htmlspecialchars(addslashes($voyage['titre'])); ?>'
                        );
                    <?php endif; ?>
                <?php endforeach; ?>
            };
        </script>
        <style>
            /* Basic styling for filters */
            .filters-container {
                background-color: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(5px);
                padding: 15px 20px; /* Adjusted padding */
                margin: 20px auto;
                border-radius: 8px;
                max-width: 95%; /* Allow it to take more width */
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                color: #333;
            }
            .filters-container h3 {
                margin-top: 0;
                margin-bottom: 10px; /* Space below heading */
                color: #004080;
                text-align: center; /* Optional: center heading */
            }

            #filterForm {
                display: flex; /* Key change: make form a flex container */
                flex-wrap: wrap; /* Allow items to wrap on smaller screens */
                align-items: center; /* Align items vertically */
                gap: 15px; /* Space between filter groups and button group */
            }

            .filter-group {
                display: flex; /* Keep label and input in a row */
                align-items: center;
                gap: 5px; /* Space between label and input */
            }
            .filter-group label {
                font-weight: bold;
                min-width: auto; /* Adjust from fixed min-width */
                margin-right: 5px; /* Small space after label */
            }
            .filter-group input[type="number"],
            .filter-group select {
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 4px;
                width: 120px; /* Example fixed width, adjust as needed */
            }
             .filter-group select {
                width: 150px; /* Slightly wider for season text */
            }


            .filter-buttons {
                display: flex; /* Align buttons in a row */
                gap: 10px; /* Space between buttons */
                margin-left: auto; 
            }
            .filter-buttons button {
                padding: 8px 12px; 
                border: none;
                border-radius: 4px;
                cursor: pointer;
                background-color: #007bff;
                color: white;
                font-weight: bold;
                white-space: nowrap; 
            }
            .filter-buttons button:hover {
                background-color: #0056b3;
            }
            .filter-buttons button.reset-btn {
                background-color: #6c757d;
            }
            .filter-buttons button.reset-btn:hover {
                background-color: #545b62;
            }

            #noResultsMessage {
                text-align: center;
                padding: 20px;
                font-size: 1.2em;
                color: #777; /* Changed to a more visible color for testing if needed */
                display: none;
            }

            /* Dark mode adjustments */
            body.dark-mode .filters-container {
                background-color: rgba(50, 50, 50, 0.8);
                color: #eee;
            }
            body.dark-mode .filters-container h3 {
                color: #8bf;
            }
            body.dark-mode .filter-group input[type="number"],
            body.dark-mode .filter-group select {
                background-color: #444;
                border-color: #666;
                color: #eee;
            }

            /* Responsive adjustments for smaller screens */
            @media (max-width: 768px) { 
                #filterForm {
                    flex-direction: column; 
                    align-items: stretch; 
                }
                .filter-group {
                    width: 100%; 
                    justify-content: space-between; 
                }
                .filter-group input[type="number"],
                .filter-group select {
                    width: auto; 
                    flex-grow: 1; 
                }
                .filter-buttons {
                    margin-left: 0; 
                    justify-content: flex-start; 
                    margin-top: 10px;
                }
            }
        </style>
    </head>

    <body>
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <div class="paysageV"> <!-- Main content wrapper -->
            <center><h1>Green Odyssey</h1></center>
            <table class="nav">
                <tr>
                    <td><a href="index.php" class="navi">Accueil</a></td>
                    <td><a href="presentation.php"   class="navi">Présentation</a></td>
                    <td><a href="voyages.php"  class="navi">Voyages</a></td>

                    <?php if (!isset($_SESSION['user'])): ?>
                        <td><a href="inscription.php"   class="navi">S'inscrire</a></td>
                        <td><a href="connexion.php"   class="navi">Se Connecter</a></td>

                    <?php else: ?>
                        <td><a href="favoris.php"   class="navi">Favoris</a></td>
                        <td><a href="panier.php" class="navi">Panier</td>
                        <td><a href="profil.php"   class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                        <td><a href="admin.php" class="navi">Admin</a></td>
                    <?php endif; ?>
                    <td><button id="theme-button" onclick="switchTheme()" class="navi-button">🌙 Mode Sombre</button></td>
                </tr>
            </table>

            <div class="center"> <!-- This is not a form, which is fine for its button type -->
                <div class="c1">
                    <p class="titress">Commencez à naviguer pour trouver votre coup de coeur</p>
                    <p class="parag">Vous pouvez ajouter les lieux qui vous intéressent le plus à vos Favoris, </p>
                    <p class="parag">ou alors Commander dès maintenant si vous êtes sur de vous. </p>
                    <p class="parag"> Sinon Swipez !</p>
                    <button type="button" onclick="location.href='#v1'" class="choixClient">Naviguer</button>
                </div>
            </div>

            <!-- FILTERS SECTION -->
            <div class="filters-container">
                <h3>Filtrer les Voyages</h3>
                <form id="filterForm"> 
                    <div class="filter-group">
                        <label for="minPrice">Prix Min (€):</label>
                        <input type="number" id="minPrice" name="minPrice" min="0" placeholder="Min">
                    </div>
                    <div class="filter-group">
                        <label for="maxPrice">Prix Max (€):</label>
                        <input type="number" id="maxPrice" name="maxPrice" min="0" placeholder="Max">
                    </div>
                    <div class="filter-group">
                        <label for="seasonFilter">Saison:</label>
                        <select id="seasonFilter" name="seasonFilter">
                            <option value="">Toutes</option>
                            <?php foreach ($all_seasons as $season_option): ?>
                                <option value="<?= htmlspecialchars(strtolower($season_option)); ?>"><?= htmlspecialchars($season_option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-buttons">
                        <button type="button" onclick="applyFilters()">Appliquer</button>
                        <button type="button" onclick="resetFilters()" class="reset-btn">Réinitialiser</button>
                    </div>
                </form>
            </div>


            <div id="voyagesList"> <!-- Wrapper for voyages for easier selection -->
                <?php if (empty($voyages)): ?>
                    <p style="text-align: center; color: white; padding: 20px;">Aucun voyage disponible pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($voyages as $voyage): ?>
                        <div class="rando" id="v<?= htmlspecialchars($voyage['voyage_id']); ?>"
                            style="background-image: url('<?= htmlspecialchars($voyage['image_fond']); ?>');"
                            data-prix="<?= htmlspecialchars($voyage['prix'] ?? 0); ?>"
                            data-saison="<?= htmlspecialchars(strtolower($voyage['saison'] ?? '')); ?>">

                            <h4><?= htmlspecialchars($voyage['titre']); ?></h4>

                            <div class="interface">
                                <div class="divText">
                                    <p><?= htmlspecialchars($voyage['description']); ?></p>
                                </div>
                                <div id="map<?= htmlspecialchars($voyage['voyage_id']); ?>" style="height: 200px; width: 100%;"></div>
                            </div>

                            <div class="details">
                                <p class="deetails"> Prix : <?= htmlspecialchars($voyage['prix'] ?? 'N/A'); ?> €</p>
                                
                                <p class="deetails"> Période : <?= htmlspecialchars($voyage['saison'] ?? 'Non spécifié'); ?></p>
                            </div>

                            <div class="choiceContainer">
                                <?php if ($isConnected): ?>
                                    <!-- Modified Etapes button with explicit click handling -->
                                    <form method="POST" action="etapes.php" style="display: inline;" id="etapesForm_<?= htmlspecialchars($voyage['voyage_id']); ?>">
                                        <input type="hidden" name="voyage_nom" value="<?= htmlspecialchars($voyage['titre']); ?>">
                                        <button type="submit" name="envoi_voyage" class="choixClient" onclick="document.getElementById('etapesForm_<?= htmlspecialchars($voyage['voyage_id']); ?>').submit();">Etapes</button>
                                    </form>

                                    <form method="POST" action="favoris.php" style="display: inline;">
                                        <input type="hidden" name="voyage_nom" value="<?= htmlspecialchars($voyage['titre']); ?>">
                                        <button type="submit" name="ajouter_favori" class="choixClient btn-favoris">💖</button>
                                    </form>
                                <?php else: ?>
                                    <a href="connexion.php" class="choixClient">ETAPES</a>
                                    <a href="connexion.php" class="choixClient btn-favoris">💖</a>
                                <?php endif; ?>

                                <?php
                                    $next_voyage_id = $voyage['voyage_id'] + 1;
                                    $found_next = false;
                                    foreach ($voyages as $v_check) {
                                        if ($v_check['voyage_id'] == $next_voyage_id) {
                                            $found_next = true;
                                            break;
                                        }
                                    }
                                    if ($found_next) {
                                        echo '<button onclick="scrollToVoyage(\'v' . htmlspecialchars($next_voyage_id) . '\')" class="choixClient">Swipe</button>';
                                    } else if (!empty($voyages)) { 
                                        echo '<button onclick="scrollToVoyage(\'v' . htmlspecialchars($voyages[0]['voyage_id']) . '\')" class="choixClient">Swipe (Début)</button>';
                                    }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div id="noResultsMessage">Aucun voyage ne correspond à vos critères de recherche.</div>
        
        </div> <!-- This closes div.paysageV -->

        <script>
            function createMap(mapId, lat, lon, popupText) {
                var container = L.DomUtil.get(mapId);
                if(container != null){
                    container._leaflet_id = null;
                }
                var map = L.map(mapId).setView([lat, lon], 5); 
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                L.marker([lat, lon]).addTo(map).bindPopup(popupText);
            }

            function applyFilters() {
                const minPrice = parseFloat(document.getElementById('minPrice').value) || 0;
                const maxPrice = parseFloat(document.getElementById('maxPrice').value) || Infinity;
                const selectedSeason = document.getElementById('seasonFilter').value.toLowerCase(); 

                const voyageElements = document.querySelectorAll('#voyagesList .rando');
                let visibleCount = 0;

                voyageElements.forEach(voyageEl => {
                    const prix = parseFloat(voyageEl.dataset.prix);
                    const saisonData = voyageEl.dataset.saison.toLowerCase(); 

                    let show = true;
                    if (prix < minPrice || prix > maxPrice) {
                        show = false;
                    }
                    if (selectedSeason && saisonData.indexOf(selectedSeason) === -1) {
                        show = false;
                    }

                    if (show) {
                        voyageEl.style.display = ''; 
                        visibleCount++;
                    } else {
                        voyageEl.style.display = 'none';
                    }
                });

                const noResultsDiv = document.getElementById('noResultsMessage');
                if (visibleCount === 0) {
                    noResultsDiv.style.display = 'block';
                } else {
                    noResultsDiv.style.display = 'none';
                }
            }

            function resetFilters() {
                document.getElementById('filterForm').reset();
                applyFilters(); 
            }

            function scrollToVoyage(elementId) {
                const element = document.getElementById(elementId);
                if (element) {
                    if (element.style.display === 'none') {
                        console.warn("Tentative de défilement vers un élément masqué:", elementId);
                    }
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
            
            // Ensure filter form submission is handled by JavaScript
            document.addEventListener('DOMContentLoaded', function() {
                const filterForm = document.getElementById('filterForm');
                if (filterForm) {
                    filterForm.addEventListener('submit', function(e) {
                        e.preventDefault(); // Prevent actual form submission
                        applyFilters();
                    });
                }
            });
            // Form handling script
            document.addEventListener('DOMContentLoaded', function() {
                var etapesForms = document.querySelectorAll('form[action="etapes.php"]');
                etapesForms.forEach(function(form) {
                    form.addEventListener('submit', function(e) {
                        console.log('Form submission triggered', form);
                        return true; // Allow normal submission
                    });
                });
            });
        </script>

        <footer>
            <p>© 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
</html>