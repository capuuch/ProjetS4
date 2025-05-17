<?php 
session_start(); 

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user'])) {
    die("Erreur : utilisateur non connecté.");
}

// Extract username safely (your existing logic)
$username = null;
if (is_string($_SESSION['user'])) {
    $username = $_SESSION['user'];
} elseif (is_array($_SESSION['user']) && isset($_SESSION['user']['username'])) {
    $username = (string)$_SESSION['user']['username'];
} elseif (is_object($_SESSION['user']) && isset($_SESSION['user']->username)) {
    $username = (string)$_SESSION['user']->username;
} elseif (is_array($_SESSION['user']) && isset($_SESSION['user']['nom'])) {
    $username = (string)$_SESSION['user']['nom'];
} elseif (is_object($_SESSION['user']) && isset($_SESSION['user']->nom)) {
    $username = (string)$_SESSION['user']->nom;
} else {
    try {
        $username = (string)$_SESSION['user'];
        if (empty($username)) {
            $username = 'guest_' . session_id();
        }
    } catch (Exception $e) {
        $username = 'guest_' . session_id();
    }
}

$etapes_file = '../json/etapes.json';
$voyages_file = '../json/voyages.json';
$options_file = '../json/options.json';
$users_file = '../json/data1.json';

$voyages = file_exists($voyages_file) ? json_decode(file_get_contents($voyages_file), true) : [];
$etapes_data = file_exists($etapes_file) ? json_decode(file_get_contents($etapes_file), true) : []; // Renamed to avoid conflict
$options = file_exists($options_file) ? json_decode(file_get_contents($options_file), true) : [];
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

if (!is_array($voyages)) $voyages = [];
if (!is_array($etapes_data)) $etapes_data = [];
if (!is_array($options)) $options = [];
if (!is_array($users)) $users = [];

$user_info = null;
if (is_array($users)) {
    foreach ($users as $user) {
        if (isset($user['nom']) && $user['nom'] === $username) {
            $user_info = $user;
            break;
        }
    }
}

$voyage_nom = isset($_POST['voyage_nom']) ? $_POST['voyage_nom'] : (isset($_SESSION['selected_voyage_nom']) ? $_SESSION['selected_voyage_nom'] : null);
$voyage_selectionne = null;

if ($voyage_nom) {
    foreach ($voyages as $voyage) {
        if ($voyage['titre'] === $voyage_nom) {
            $voyage_selectionne = $voyage;
            $_SESSION['selected_voyage_nom'] = $voyage_nom;
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_options']) && isset($_SESSION['user']) && $voyage_selectionne) {
    // ... (your existing save_options logic remains the same)
    // Get user information
    $current_username = null; // Use a different variable to avoid conflict if needed
    if (is_string($_SESSION['user'])) {
        $current_username = $_SESSION['user'];
    } elseif (is_array($_SESSION['user']) && isset($_SESSION['user']['nom'])) {
        $current_username = (string)$_SESSION['user']['nom'];
    } // Add other conditions as per your original logic for $username extraction
    
    if (empty($current_username)) {
        $current_username = 'guest_' . session_id(); // Fallback
    }

    $user_info_to_save = null;
    if (is_array($users)) {
        foreach ($users as $user_item) {
            if (isset($user_item['nom']) && $user_item['nom'] === $current_username) {
                $user_info_to_save = $user_item;
                break;
            }
        }
    }
    
    $voyage_id = isset($voyage_selectionne['voyage_id']) ? (string)$voyage_selectionne['voyage_id'] : null;
    $voyage_titre = isset($voyage_selectionne['titre']) ? $voyage_selectionne['titre'] : 'Voyage sans titre';

    if (!$voyage_id) {
        die("Erreur: L'identifiant du voyage est manquant.");
    }
    if (!isset($_POST['etapes']) || !is_array($_POST['etapes'])) {
        die("Erreur: Aucune étape sélectionnée ou données invalides.");
    }
    if (!is_array($options)) {
        $options = [];
    }
    if (!isset($options[$current_username]) || !is_array($options[$current_username])) {
        $options[$current_username] = []; 
    }
    if (!isset($options[$current_username][$voyage_id]) || !is_array($options[$current_username][$voyage_id])) {
        $options[$current_username][$voyage_id] = [];
    }
    
    $options[$current_username][$voyage_id]['user_info'] = [
        'nom' => $user_info_to_save['nom'] ?? $current_username,
        'prenom' => $user_info_to_save['prenom'] ?? '',
        'email' => $user_info_to_save['email'] ?? '',
    ];
    
    $options[$current_username][$voyage_id]['voyage_titre'] = $voyage_titre;
    $options_generales_posted = $_POST['options_generales'] ?? [];

    $options[$current_username][$voyage_id]['etapes'] = [];
    foreach ($_POST['etapes'] as $etape_id_posted => $choix) {
        $etape_id_key = (string)$etape_id_posted;
        $options[$current_username][$voyage_id]['etapes'][$etape_id_key] = [
            'activité' => isset($choix['activite']) ? $choix['activite'] : null,
            'hebergement' => isset($choix['hebergement']) ? $choix['hebergement'] : null,
            'transport' => isset($choix['transport']) ? $choix['transport'] : null
        ];
    }

    $options[$current_username][$voyage_id]['options_generales'] = [
        'nb_pers' => isset($options_generales_posted['nb_pers']) ? (int)$options_generales_posted['nb_pers'] : 1,
        'date_depart' => $options_generales_posted['date_depart'] ?? '',
        'restauration' => $options_generales_posted['restauration'] ?? 'Aucune',
        'assurance' => isset($options_generales_posted['assurance']) ? true : false,
    ];

    if (file_put_contents($options_file, json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        die("Erreur: Impossible d'enregistrer les choix.");
    } else {
        // echo "<p style='color: green;'>Vos choix ont bien été enregistrés !</p>"; // Avoid echo before header
        header("Location: panier.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../css/projetS4.css" id="theme-style">
    <title>Green Odyssey - Étapes</title>
    <meta charset="UTF-8">
    <meta name="author" content="Anas_Capucine_Hadil"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    
    <style>
        .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(0,0,0,0.1); border-radius: 50%; border-top-color: #007bff; animation: spin 1s ease-in-out infinite; margin-left: 10px; vertical-align: middle; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-options { opacity: 0.5; pointer-events: none; }
        .error-message { color: #dc3545; font-size: 0.8em; margin-top: 5px; }
        
         /* Total Price Container Styling */
        #total-price-container { 
            margin-top: 20px; 
            font-size: 1.2em; 
            font-weight: bold; 
            padding: 10px 15px; 
            border: 1px solid var(--etapes-price-container-border, #ccc); 
            background-color: var(--etapes-price-container-bg, #f9f9f9); 
            text-align: center; 
            border-radius: 8px;
            color: var(--etapes-price-text, #333);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        #total-price-value { 
            color: var(--etapes-price-value-text, #28a745); 
            font-weight: 700;
        }

        /* --- STYLES FOR ETAPES TABLE (table.tabadmin in etapes.php) --- */
        /* This targets the specific table in etapes.php */
        body #etapes-form table.tabadmin {
            width: 95%; 
            margin: 25px auto; 
            border-collapse: collapse; 
            /* OPTION 1: Use a variable for the table's own background if desired */
            background-color: var(--etapes-table-bg, #fff); /* e.g., solid white or dark */
            /* OPTION 2: If you want it to simply not have a background set here (and inherit from body or a parent): */
            /* background-color: transparent; */ /* (but this means page BG image shows, which you want to avoid now) */
            /* So, for no transparency against page BG, ensure a solid color is set */
            font-family: 'Quicksand', sans-serif;
            border-radius: 8px; /* Optional: rounded corners for the whole table */
            overflow: hidden; /* Needed if using border-radius on table with bordered cells */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Optional: a shadow for the table */
        }

        body #etapes-form table.tabadmin th {
            background-color: var(--etapes-table-header-bg, #f0f0f0); /* Solid color from variable */
            color: var(--etapes-table-header-text, #333);
            text-shadow: var(--etapes-table-header-text-shadow, none);
            font-weight: 600; 
            padding: 14px 18px;
            text-align: left; 
            white-space: nowrap; 
            border-bottom: 2px solid var(--etapes-table-border, #ddd);
            /* Remove individual cell borders if table has its own radius and overflow:hidden, or match border color */
            /* border-left: 1px solid var(--etapes-table-border, #ddd);  */
            /* border-right: 1px solid var(--etapes-table-border, #ddd); */
        }
         /* body #etapes-form table.tabadmin th:first-child {
            border-left: none;
         }
         body #etapes-form table.tabadmin th:last-child {
            border-right: none;
         } */


        body #etapes-form table.tabadmin td {
            padding: 12px 18px; 
            border-bottom: 1px solid var(--etapes-table-border, #eee);
            /* border-left: 1px solid var(--etapes-table-border, #eee); */
            /* border-right: 1px solid var(--etapes-table-border, #eee); */
            vertical-align: middle; 
            line-height: 1.6; 
            color: var(--etapes-table-cell-text, #555);
            background-color: var(--etapes-table-cell-bg, #fff); /* Solid color for cells from variable */
        }
        /* body #etapes-form table.tabadmin td:first-child {
            border-left: none;
         }
        body #etapes-form table.tabadmin td:last-child {
            border-right: none;
         } */

        body #etapes-form table.tabadmin tbody tr:last-child td {
            border-bottom: none; /* Keep this for clean look */
        }
        /* If using rounded corners on table and want first/last cells to also be rounded: */
        body #etapes-form table.tabadmin thead tr:first-child th:first-child {
            border-top-left-radius: 8px;
        }
        body #etapes-form table.tabadmin thead tr:first-child th:last-child {
            border-top-right-radius: 8px;
        }
        body #etapes-form table.tabadmin tbody tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }
        body #etapes-form table.tabadmin tbody tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }


        /* Inputs and Selects within this specific table */
        body #etapes-form table.tabadmin td select,
        body #etapes-form table.tabadmin td input[type="text"], 
        body #etapes-form table.tabadmin td input[type="number"] {
            width: 100%; 
            padding: 10px 12px;
            box-sizing: border-box; 
            border: 1px solid var(--etapes-input-border, #ccc);
            border-radius: 6px; 
            background-color: var(--etapes-input-bg, #fff); 
            color: var(--etapes-input-text, #333);
            font-size: 0.9em; 
            line-height: 1.5;
        }
        body #etapes-form table.tabadmin td select option {
            background-color: #fff; /* Fallback for light */
            color: #333;
        }
        [data-theme="dark"] body #etapes-form table.tabadmin td select option {
             background-color: var(--etapes-input-bg, #333); /* Use a dark bg for options */
             color: var(--etapes-input-text, #f0f0f0);
        }


        /* Activity options within this specific table */
        body #etapes-form table.tabadmin td .activity-options {
            padding: 5px 0;
        }
        body #etapes-form table.tabadmin td .activity-option { 
            display: block; 
            margin-bottom: 8px; 
            padding: 10px 15px; 
            background-color: var(--etapes-activity-option-bg, #f9f9f9); /* Solid color */
            border: 1px solid var(--etapes-activity-option-border, #ddd);
            border-radius: 6px; 
            transition: background-color 0.2s ease-in-out;
            color: var(--etapes-activity-label-text, inherit); 
        }
        body #etapes-form table.tabadmin td .activity-option:hover {
            background-color: var(--etapes-activity-option-hover-bg, #efefef); /* Solid color */
        }
        body #etapes-form table.tabadmin td .activity-option:last-child {
            margin-bottom: 0; 
        }
        body #etapes-form table.tabadmin td .activity-option input[type="radio"] {
            margin-right: 10px; 
            vertical-align: middle;
            transform: scale(1.1);
        }
        body #etapes-form table.tabadmin td .activity-option label {
            vertical-align: middle; 
            margin-left: 0;
        }
        /* --- END OF ETAPES TABLE STYLES --- */
        

        
    </style>
     
    <script>
        function setCookie(name, value, days) { /* Your existing function */ 
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }
        function getCookie(name) { /* Your existing function */
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
        function switchTheme() { /* Your existing function */
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
            setCookie('theme', newTheme, 30);
        }

        window.onload = function() {
            var savedTheme = getCookie('theme');
            if (savedTheme) {
                document.getElementById('theme-style').setAttribute('href', savedTheme);
                if (savedTheme === '../css/projetS4-dark.css') {
                    document.getElementById('theme-button').textContent = '☀️ Mode Clair';
                } else {
                    document.getElementById('theme-button').textContent = '🌙 Mode Sombre';
                }
            }
            initializeDynamicOptionsAndPrice();
        };
        
        function loadOptionsForEtape(etapeId) {
            return new Promise((resolve, reject) => {
                const activityContainer = document.getElementById(`activity-container-${etapeId}`);
                const accommodationSelect = document.getElementById(`accommodation-select-${etapeId}`);
                const transportSelect = document.getElementById(`transport-select-${etapeId}`);
                
                if (!activityContainer || !accommodationSelect || !transportSelect) {
                    console.error(`Container elements not found for etape ${etapeId}`);
                    reject(new Error(`Containers not found for etape ${etapeId}`));
                    return;
                }
                
                activityContainer.classList.add('loading-options');
                accommodationSelect.classList.add('loading-options');
                transportSelect.classList.add('loading-options');
                
                // Add spinners (simplified: assume initial HTML has placeholder text)
                activityContainer.innerHTML = '<div class="spinner"></div> Chargement activités...';
                accommodationSelect.innerHTML = '<option value="">Chargement...</option>';
                transportSelect.innerHTML = '<option value="">Chargement...</option>';

                fetch(`get_options.php?etape_id=${etapeId}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`Network response was not ok for get_options.php (etape ${etapeId})`);
                        return response.json();
                    })
                    .then(data => {
                        activityContainer.innerHTML = ''; // Clear spinner before adding content
                        if (data.success) {
                            updateActivityOptions(etapeId, data.data.options.activité, data.data.default.activité);
                            updateSelectOptions(accommodationSelect, data.data.options.hebergement, data.data.default.hebergement, `etapes[${etapeId}][hebergement]`);
                            updateSelectOptions(transportSelect, data.data.options.transport, data.data.default.transport, `etapes[${etapeId}][transport]`);
                            resolve();
                        } else {
                            console.error('Error loading options:', data.message);
                            showErrorMessage(etapeId, data.message || 'Erreur inconnue de chargement.');
                            reject(new Error(data.message));
                        }
                    })
                    .catch(error => {
                        console.error(`Error fetching options for etape ${etapeId}:`, error);
                        showErrorMessage(etapeId, 'Erreur lors du chargement des options. Veuillez réessayer.');
                        // Optionally, provide fallback content or clear the loading state
                        if(activityContainer) activityContainer.innerHTML = 'Erreur chargement activités.';
                        if(accommodationSelect) accommodationSelect.innerHTML = '<option value="">Erreur</option>';
                        if(transportSelect) transportSelect.innerHTML = '<option value="">Erreur</option>';
                        reject(error);
                    })
                    .finally(() => {
                        if(activityContainer) activityContainer.classList.remove('loading-options');
                        if(accommodationSelect) accommodationSelect.classList.remove('loading-options');
                        if(transportSelect) transportSelect.classList.remove('loading-options');
                    });
            });
        }
        
        function updateActivityOptions(etapeId, activities, defaultActivityName) {
            const container = document.getElementById(`activity-container-${etapeId}`);
            if (!container) return;
            container.innerHTML = ''; // Clear previous content/spinner
            
            activities.forEach(activityObj => { // Expecting array of {name, price}
                const div = document.createElement('div');
                div.className = 'activity-option';
                
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = `etapes[${etapeId}][activite]`;
                radio.value = activityObj.name;
                radio.id = `activity-${etapeId}-${activityObj.name.replace(/\s+/g, '-')}`;
                if (activityObj.name === defaultActivityName) {
                    radio.checked = true;
                }
                
                const label = document.createElement('label');
                label.htmlFor = radio.id;
                label.textContent = `${activityObj.name} (${activityObj.price} €)`;
                
                div.appendChild(radio);
                div.appendChild(label);
                container.appendChild(div);
            });
        }
        
        function updateSelectOptions(selectElement, options, defaultOptionName, selectName) {
            if (!selectElement) return;
            selectElement.innerHTML = ''; // Clear previous options/spinner
            selectElement.name = selectName; // Ensure name is set

            const placeholderOption = document.createElement('option');
            placeholderOption.value = ""; // Represents "no selection"
            placeholderOption.textContent = "Choisir...";
            // if (!defaultOptionName) placeholderOption.selected = true; // Select if no default
            selectElement.appendChild(placeholderOption);
            
            options.forEach(optionObj => { // Expecting array of {name, price}
                const optionElement = document.createElement('option');
                optionElement.value = optionObj.name;
                optionElement.textContent = `${optionObj.name} (${optionObj.price} €)`;
                if (optionObj.name === defaultOptionName) {
                    optionElement.selected = true;
                }
                selectElement.appendChild(optionElement);
            });
             // If no default was matched and there is no specific "Aucun..." option selected,
             // ensure "Choisir..." is selected if it exists and is appropriate.
            if (!selectElement.value && placeholderOption.value === "") {
                 placeholderOption.selected = true;
            }
        }
        
        function showErrorMessage(etapeId, message) {
            const container = document.getElementById(`etape-row-${etapeId}`);
            if (!container) return;
            let errorElement = document.getElementById(`error-msg-${etapeId}`);
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'error-message';
                errorElement.id = `error-msg-${etapeId}`;
                // Append it in a visible place, e.g., as a new cell or below the row
                const cellWithError = container.querySelector('td:last-child') || container.insertCell(-1); // Add to last cell or new cell
                cellWithError.appendChild(errorElement);
            }
            errorElement.textContent = message;
            setTimeout(() => { if(errorElement) errorElement.remove(); }, 7000);
        }
        
        async function initializeDynamicOptionsAndPrice() {
            const etapeRows = document.querySelectorAll('[id^="etape-row-"]');
            const loadPromises = [];
            etapeRows.forEach(row => {
                const etapeId = row.id.replace('etape-row-', '');
                if (etapeId) {
                    loadPromises.push(loadOptionsForEtape(etapeId));
                }
            });

            try {
                await Promise.all(loadPromises);
                // All options loaded (or failed individually)
            } catch (error) {
                console.warn("Some options failed to load, proceeding with price calculation if possible.", error);
            } finally {
                 // Attach listeners after all options are supposed to be in the DOM
                attachPriceUpdateListeners();
                // Perform initial price calculation
                updateTotalPrice();
            }
        }

        function attachPriceUpdateListeners() {
            const form = document.getElementById('etapes-form');
            if (!form) return;

            // Use event delegation for dynamically added radio buttons for activities
            form.addEventListener('change', function(event) {
                const target = event.target;
                if (target.matches('select, input[type="radio"], input[type="checkbox"], input[type="number"], input[type="date"]')) {
                    updateTotalPrice();
                }
            });
        }

        async function updateTotalPrice() {
            const form = document.getElementById('etapes-form');
            if (!form) return;

            const totalPriceValueElement = document.getElementById('total-price-value');
            const priceSpinner = document.getElementById('price-calculation-spinner');

            if (totalPriceValueElement) totalPriceValueElement.textContent = 'Calcul en cours...';
            if (priceSpinner) priceSpinner.style.display = 'inline-block';

            const dataForApi = {
                etapes: {},
                options_generales: {}
            };

            const nbPersInput = form.querySelector('[name="options_generales[nb_pers]"]');
            dataForApi.options_generales.nb_pers = nbPersInput ? parseInt(nbPersInput.value, 10) : 1;
            if (isNaN(dataForApi.options_generales.nb_pers) || dataForApi.options_generales.nb_pers < 1) {
                dataForApi.options_generales.nb_pers = 1;
            }

            const dateDepartInput = form.querySelector('[name="options_generales[date_depart]"]');
            dataForApi.options_generales.date_depart = dateDepartInput ? dateDepartInput.value : '';
            
            const restaurationSelect = form.querySelector('[name="options_generales[restauration]"]');
            dataForApi.options_generales.restauration = restaurationSelect ? restaurationSelect.value : 'Aucune';

            const assuranceCheckbox = form.querySelector('[name="options_generales[assurance]"]');
            dataForApi.options_generales.assurance = assuranceCheckbox ? assuranceCheckbox.checked : false;

            const etapeRows = form.querySelectorAll('[id^="etape-row-"]');
            etapeRows.forEach(row => {
                const etapeId = row.id.replace('etape-row-', '');
                if (etapeId) {
                    dataForApi.etapes[etapeId] = {};
                    const activityRadio = form.querySelector(`input[name="etapes[${etapeId}][activite]"]:checked`);
                    dataForApi.etapes[etapeId].activite = activityRadio ? activityRadio.value : ""; // Send empty if none, or a default like 'Aucune activité'

                    const accommodationSelect = form.querySelector(`select[name="etapes[${etapeId}][hebergement]"]`);
                    dataForApi.etapes[etapeId].hebergement = accommodationSelect ? accommodationSelect.value : "";

                    const transportSelect = form.querySelector(`select[name="etapes[${etapeId}][transport]"]`);
                    dataForApi.etapes[etapeId].transport = transportSelect ? transportSelect.value : "";
                }
            });
            
            // console.log("Data for price calculation:", JSON.stringify(dataForApi, null, 2));

            try {
                // Ensure the path to calculate_price.php is correct from this file's location
                const response = await fetch('calculate_price.php', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataForApi),
                });
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, response: ${errorText}`);
                }
                const result = await response.json();

                if (result.success) {
                    if (totalPriceValueElement) {
                        totalPriceValueElement.textContent = `${parseFloat(result.total).toFixed(2)} €`;
                    }
                } else {
                    if (totalPriceValueElement) totalPriceValueElement.textContent = 'Erreur calcul';
                    console.error('Error calculating price:', result.message);
                }
            } catch (error) {
                if (totalPriceValueElement) totalPriceValueElement.textContent = 'Erreur réseau';
                console.error('Failed to fetch price:', error);
            } finally {
                if (priceSpinner) priceSpinner.style.display = 'none';
            }
        }



        function updateActivityOptions(etapeId, activities, defaultActivityName) {
        const listContainer = document.getElementById(`activity-container-${etapeId}`); // This is the inner list
        const selectedDisplayContainer = document.getElementById(`selected-activity-display-${etapeId}`);
        const allActivitiesWrapper = document.getElementById(`all-activities-container-${etapeId}`);
        const toggleBtn = document.querySelector(`.toggle-activities-btn[data-etape-id="${etapeId}"]`);

        if (!listContainer || !selectedDisplayContainer || !allActivitiesWrapper || !toggleBtn) {
            console.error("Activity containers not found for etape:", etapeId);
            return;
        }
        listContainer.innerHTML = ''; // Clear previous radio buttons
        selectedDisplayContainer.innerHTML = ''; // Clear previous selected display

        if (!activities || activities.length === 0) {
            selectedDisplayContainer.textContent = "Aucune activité disponible";
            toggleBtn.style.display = 'none'; // Hide button if no options
            allActivitiesWrapper.style.display = 'none';
            return;
        }
        
        toggleBtn.style.display = 'inline-block'; // Ensure button is visible if there are options

        let defaultActivityFound = false;

        activities.forEach((activityObj, index) => {
            const div = document.createElement('div');
            div.className = 'activity-option'; // Keep your existing class for styling
            
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = `etapes[${etapeId}][activite]`;
            radio.value = activityObj.name;
            radio.id = `activity-${etapeId}-${activityObj.name.replace(/\s+/g, '-')}`;
            radio.dataset.price = activityObj.price; // Store price for display if needed
            radio.dataset.etapeId = etapeId; // For easier event handling

            if (activityObj.name === defaultActivityName) {
                radio.checked = true;
                defaultActivityFound = true;
            }
            
            // If no default is specified or found yet, and this is the first item, check it.
            if (!defaultActivityFound && index === 0 && !defaultActivityName) {
                 radio.checked = true;
            }


            const label = document.createElement('label');
            label.htmlFor = radio.id;
            label.textContent = `${activityObj.name} (${activityObj.price} €)`;
            
            div.appendChild(radio);
            div.appendChild(label);
            listContainer.appendChild(div);

            // Add event listener to update display and collapse when an option is chosen
            radio.addEventListener('change', function() {
                updateSelectedActivityDisplay(this.dataset.etapeId, this.value, this.dataset.price);
                // Collapse the list after selection
                const containerToCollapse = document.getElementById(`all-activities-container-${this.dataset.etapeId}`);
                const btn = document.querySelector(`.toggle-activities-btn[data-etape-id="${this.dataset.etapeId}"]`);
                if (containerToCollapse) containerToCollapse.style.display = 'none';
                if (btn) btn.textContent = 'Modifier l\'activité'; // Or "Voir les options"
                updateTotalPrice(); // Ensure price updates
            });
        });

        // Initial display of selected/default activity
        const initiallySelectedRadio = listContainer.querySelector('input[type="radio"]:checked');
        if (initiallySelectedRadio) {
            updateSelectedActivityDisplay(etapeId, initiallySelectedRadio.value, initiallySelectedRadio.dataset.price);
        } else if (activities.length > 0) {
            // If somehow nothing is checked but there are activities, display the first one
            updateSelectedActivityDisplay(etapeId, activities[0].name, activities[0].price);
            // And check the first radio
            const firstRadio = listContainer.querySelector('input[type="radio"]');
            if (firstRadio) firstRadio.checked = true;
        }
    }

    function updateSelectedActivityDisplay(etapeId, activityName, activityPrice) {
        const selectedDisplayContainer = document.getElementById(`selected-activity-display-${etapeId}`);
        if (selectedDisplayContainer) {
            if (activityName) {
                selectedDisplayContainer.innerHTML = `<strong>Activité :</strong> ${activityName} (${activityPrice} €)`;
            } else {
                selectedDisplayContainer.innerHTML = `<strong>Activité :</strong> Aucune sélectionnée`;
            }
        }
    }

    function setupToggleButtons() {
        const toggleButtons = document.querySelectorAll('.toggle-activities-btn');
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const etapeId = this.dataset.etapeId;
                const activitiesContainer = document.getElementById(`all-activities-container-${etapeId}`);
                if (activitiesContainer) {
                    const isVisible = activitiesContainer.style.display === 'block';
                    activitiesContainer.style.display = isVisible ? 'none' : 'block';
                    this.textContent = isVisible ? 'Voir les options' : 'Masquer les options';
                }
            });
        });
    }


    async function initializeDynamicOptionsAndPrice() {
        const etapeRows = document.querySelectorAll('[id^="etape-row-"]');
        const loadPromises = [];
        etapeRows.forEach(row => {
            const etapeId = row.id.replace('etape-row-', '');
            if (etapeId) {
                loadPromises.push(loadOptionsForEtape(etapeId));
            }
        });

        try {
            await Promise.all(loadPromises);
            // All options loaded (or failed individually)
        } catch (error) {
            console.warn("Some options failed to load, proceeding.", error);
        } finally {
            // Setup toggle buttons AFTER options are loaded and radio buttons created
            setupToggleButtons(); 
            // Attach listeners for price updates
            attachPriceUpdateListeners();
            // Perform initial price calculation
            updateTotalPrice();
        }
    }

    // ... (attachPriceUpdateListeners and updateTotalPrice remain largely the same, 
    //      but ensure updateTotalPrice is called when an activity radio changes) ...

    // Make sure to call initializeDynamicOptionsAndPrice on window.onload
    window.onload = function() {
        var savedTheme = getCookie('theme');
        if (savedTheme) {
            document.getElementById('theme-style').setAttribute('href', savedTheme);
            if (savedTheme === '../css/projetS4-dark.css') {
                document.getElementById('theme-button').textContent = '☀️ Mode Clair';
            } else {
                document.getElementById('theme-button').textContent = '🌙 Mode Sombre';
            }
        }
        initializeDynamicOptionsAndPrice(); // This now also sets up toggle buttons
    };

    </script>
</head>
<body>
    <h1>Green Odyssey</h1>
    <center>
        <table class="nav">
        <tr>
            <td><a href="index.php" class="navi">Accueil</a></td>  
            <td><a href="presentation.php" class="navi">Présentation</a></td>
            <td><a href="voyages.php" class="navi">Voyages</a></td>
            <?php if (!isset($_SESSION['user']) ): ?>
                <td><a href="inscription.php" class="navi">S'inscrire</a></td>
                <td><a href="connexion.php" class="navi">Se Connecter</a></td>
            <?php else: ?>
                <td><a href="favoris.php" class="navi">Favoris</a></td>
                <td><a href="panier.php" class="navi">Panier</td>
                <td><a href="profil.php" class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
            <?php endif; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                <td><a href="admin.php" class="navi">Admin</a></td>
            <?php endif; ?>
            <td><button id="theme-button" onclick="switchTheme()" class="navi-button">🌙 Mode Sombre</button></td>
        </tr>
        </table>
    </center><br><br>
        
    <h2>Étapes du Voyage</h2>
    <center>
    <?php if ($voyage_selectionne): ?>
        <h2><?= htmlspecialchars($voyage_selectionne['titre']) ?></h2>
        
        <?php if ($user_info): ?>
        <div class="user-info">
            <p>Utilisateur: <?= htmlspecialchars($user_info['prenom'] ?? '') ?> <?= htmlspecialchars($user_info['nom'] ?? '') ?></p>
            <p>Email: <?= htmlspecialchars($user_info['email'] ?? '') ?></p>
        </div>
        <?php endif; ?>

        <h2>Liste des Étapes</h2>
        <form method="post" id="etapes-form">
            <input type="hidden" name="voyage_nom" value="<?= htmlspecialchars($voyage_selectionne['titre']) ?>">
            
            <table class="tabadmin">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Prix Base</th>
                        <th>Durée</th>
                        <th>Activité</th>
                        <th>Hébergement</th>
                        <th>Transport</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if (isset($voyage_selectionne['etapes_ids']) && is_array($voyage_selectionne['etapes_ids'])): 
                    $etapes_by_id = [];
                    foreach ($etapes_data as $e) {
                        $etapes_by_id[(string)$e['etape_id']] = $e;
                    }
                    
                    foreach ($voyage_selectionne['etapes_ids'] as $etape_id_from_voyage): 
                        $current_etape_id_str = (string)$etape_id_from_voyage;
                        if (isset($etapes_by_id[$current_etape_id_str])):
                            $etape_details = $etapes_by_id[$current_etape_id_str];
                ?>
                            <tr id="etape-row-<?= htmlspecialchars($current_etape_id_str) ?>">
                                <td><?= htmlspecialchars($etape_details['titre']) ?></td>
                                <td><?= htmlspecialchars($etape_details['prix']) ?> €</td>
                                <td><?= htmlspecialchars($etape_details['duree']) ?></td>
                                <td class="activity-cell"> 
                                    <div class="selected-activity-display" id="selected-activity-display-<?= htmlspecialchars($current_etape_id_str) ?>">
                                        
                                        <div class="spinner"></div> Chargement...
                                    </div>
                                    <div class="all-activities-container" id="all-activities-container-<?= htmlspecialchars($current_etape_id_str) ?>" style="display: none; margin-top: 10px; padding:10px; border: 1px solid var(--etapes-table-border, #ddd); border-radius: 6px; background-color: var(--etapes-activity-option-bg, #f9f9f9);">
                                        
                                         <div id="activity-container-<?= htmlspecialchars($current_etape_id_str) ?>" class="activity-options-list">
                                           
                                         </div>
                                    </div>
                                    <button type="button" class="toggle-activities-btn" data-etape-id="<?= htmlspecialchars($current_etape_id_str) ?>" style="margin-top: 8px; font-size: 0.85em; padding: 5px 8px;">
                                        Voir les options
                                    </button>
                                </td>
                                <td>
                                    <select id="accommodation-select-<?= htmlspecialchars($current_etape_id_str) ?>" name="etapes[<?= htmlspecialchars($current_etape_id_str) ?>][hebergement]" class="loading-options">
                                        <option value="">Chargement...</option>
                                    </select>
                                </td>
                                <td>
                                    <select id="transport-select-<?= htmlspecialchars($current_etape_id_str) ?>" name="etapes[<?= htmlspecialchars($current_etape_id_str) ?>][transport]" class="loading-options">
                                        <option value="">Chargement...</option>
                                    </select>
                                </td>
                            </tr>
                <?php 
                        else:
                            echo "<tr><td colspan='6'>Étape ID " . htmlspecialchars($current_etape_id_str) . " non trouvée dans les données d'étapes.</td></tr>";
                        endif;
                    endforeach;
                else:
                    echo "<tr><td colspan='6'>Aucune étape ID trouvée pour ce voyage.</td></tr>";
                endif;
                ?>
                </tbody>
            </table>

            <h3>Options générales</h3>
            <div class="options-generales">
                <label for="nb_pers">Nombre de personnes:</label>
                <input type="number" id="nb_pers" name="options_generales[nb_pers]" min="1" value="1"><br>

                <label for="date_depart">Date de départ:</label>
                <input type="date" id="date_depart" name="options_generales[date_depart]"><br>

                <label for="restauration">Restauration:</label>
                <select id="restauration" name="options_generales[restauration]">
                    <option value="Aucune" selected>Aucune</option>
                    <option value="Petit-déjeuner">Petit-déjeuner</option>
                    <option value="Demi-pension">Demi-pension</option>
                    <option value="Pension complète">Pension complète</option>
                </select><br>

                <label for="assurance">Assurance:</label>
                <input type="checkbox" id="assurance" name="options_generales[assurance]" value="1"><br>
            </div>

            <div id="total-price-container">
                Prix Total: <span id="total-price-value">0.00 €</span>
                <div id="price-calculation-spinner" class="spinner" style="display: none;"></div>
            </div>

            <button type="submit" name="save_options" class="seconnecter">Enregistrer mes choix et aller au panier</button>
        </form>
    <?php else: ?>
        <p>Aucun voyage sélectionné. Veuillez retourner à la <a href="voyages.php">liste des voyages</a>.</p>
    <?php endif; ?>
    </center>

    <footer class="foooot">
        <p>© 2025 GREEN ODYSSEY Tous droits réservés.</p>
    </footer>
    <div class="paysage"></div>
</body>
</html>