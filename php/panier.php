<?php 
    session_start(); 

    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if (!isset($_SESSION['user'])) {
        header("Location: connexion.php");
        exit;
    }

    // Include payment functions
    require_once 'payment_functions.php'; // Assuming this file exists and is correct

    // Extract username safely - (Your existing robust logic)
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

    // Load JSON files
    $options_file = '../json/options.json';
    $voyages_file = '../json/voyages.json'; // May not be strictly needed if titles are in options.json
    $etapes_file = '../json/etapes.json';
    $users_file = '../json/data1.json';

    $options_data = file_exists($options_file) ? json_decode(file_get_contents($options_file), true) : [];
    // $voyages = file_exists($voyages_file) ? json_decode(file_get_contents($voyages_file), true) : [];
    $etapes_list = file_exists($etapes_file) ? json_decode(file_get_contents($etapes_file), true) : []; // Renamed for clarity
    $users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

    if (!is_array($options_data)) $options_data = [];
    if (!is_array($etapes_list)) $etapes_list = [];
    if (!is_array($users)) $users = [];

    // Get user information - consistent with etapes.php
    $user_info = null;
    if (is_array($users)) {
        foreach ($users as $user_item) {
            if (isset($user_item['nom']) && $user_item['nom'] === $username) {
                $user_info = $user_item;
                break;
            }
        }
    }


    // Process removal if requested
    if (isset($_POST['remove_voyage']) && isset($_POST['voyage_id'])) {
        $voyage_id_to_remove = $_POST['voyage_id'];
        if (isset($options_data[$username][$voyage_id_to_remove])) {
            unset($options_data[$username][$voyage_id_to_remove]);
            // If the user has no more voyages, remove the user entry itself
            if (empty($options_data[$username])) {
                unset($options_data[$username]);
            }
            file_put_contents($options_file, json_encode($options_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            // Redirect to refresh the page and prevent resubmission
            header("Location: panier.php");
            exit;
        }
    }

    // Get user's selected options
    $user_voyages_in_cart = isset($options_data[$username]) ? $options_data[$username] : [];

    /**
     * Calculates the total price of a single voyage by calling the calculate_price.php API.
     *
     * @param array $voyage_options_data The specific voyage data containing 'etapes' and 'options_generales'.
     * @return float The total price of the voyage, or 0.0 on error.
     */
    function calculate_voyage_price_via_api(array $voyage_options_data): float {
        $payload = [];
        if (isset($voyage_options_data['etapes']) && is_array($voyage_options_data['etapes'])) {
            $payload['etapes'] = $voyage_options_data['etapes'];
        } else {
            $payload['etapes'] = []; // Ensure 'etapes' key exists
        }

        if (isset($voyage_options_data['options_generales']) && is_array($voyage_options_data['options_generales'])) {
            $payload['options_generales'] = $voyage_options_data['options_generales'];
        } else {
            $payload['options_generales'] = ['nb_pers' => 1]; // Default if missing
        }
        
        // Determine the URL for calculate_price.php
        // Assuming calculate_price.php is in the same directory as panier.php
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        // Construct the path. If SCRIPT_NAME is /php/panier.php, dirname is /php
        $script_dir = dirname($_SERVER['SCRIPT_NAME']); 
        $api_url = $protocol . $host . $script_dir . '/calculate_price.php';

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($payload),
                'ignore_errors' => true // To get error response bodies
            ],
            // For HTTPS, if you have SSL verification issues (local dev):
            // 'ssl' => [
            //     'verify_peer' => false,
            //     'verify_peer_name' => false,
            // ],
        ];
        $context  = stream_context_create($options);
        $result_json = @file_get_contents($api_url, false, $context);

        if ($result_json === false) {
            error_log("Panier: Failed to call calculate_price.php. URL: " . $api_url . " Payload: " . json_encode($payload));
            return 0.0;
        }

        $response_data = json_decode($result_json, true);

        // Check HTTP status code if possible (requires more advanced HTTP client or parsing $http_response_header)
        // For simplicity, we rely on the success flag from calculate_price.php
        if ($response_data && isset($response_data['success']) && $response_data['success'] && isset($response_data['total'])) {
            return (float)$response_data['total'];
        } else {
            error_log("Panier: Error response from calculate_price.php. Response: " . $result_json . " Payload: " . json_encode($payload));
            return 0.0; 
        }
    }


    // Process payment if requested
    $payment_form_html = ''; // Renamed to avoid conflict with any $payment_form var from includes
    if (isset($_POST['checkout']) && !empty($user_voyages_in_cart)) {
        $grand_total_for_payment = 0;
        foreach ($user_voyages_in_cart as $voyage_id => $voyage_data_item) {
            $grand_total_for_payment += calculate_voyage_price_via_api($voyage_data_item);
        }
        
        if ($grand_total_for_payment > 0) {
            $montant = formatAmount($grand_total_for_payment); // from payment_functions.php
            $transaction = generateTransactionId(); // from payment_functions.php
            $vendeur = 'TEST'; // As per your existing logic
            
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            // Ensure the path to payment_return.php is correct
            $script_dir = dirname($_SERVER['SCRIPT_NAME']); // e.g. /hadil_project/php
            $retour = $protocol . $host . $script_dir . '/payment_return.php?username=' . urlencode($username) . '&transaction=' . $transaction;
            
            $control = calculateControlValue($transaction, $montant, $vendeur, $retour); // from payment_functions.php
            
            savePaymentTransaction($transaction, $montant, $vendeur, $username, $user_voyages_in_cart); // from payment_functions.php
            
            $payment_form_html = '
            <form id="payment-form" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
                <input type="hidden" name="transaction" value="' . htmlspecialchars($transaction) . '">
                <input type="hidden" name="montant" value="' . htmlspecialchars($montant) . '">
                <input type="hidden" name="vendeur" value="' . htmlspecialchars($vendeur) . '">
                <input type="hidden" name="retour" value="' . htmlspecialchars($retour) . '">
                <input type="hidden" name="control" value="' . htmlspecialchars($control) . '">
                <div class="payment-redirect-message">
                    <p>Vous allez être redirigé vers la page de paiement CY Bank...</p>
                    <p>Si vous n\'êtes pas redirigé automatiquement, veuillez cliquer sur le bouton ci-dessous.</p>
                    <button type="submit" class="checkout-btn">Procéder au paiement</button>
                </div>
            </form>
            <script>
                setTimeout(function() {
                    if(document.getElementById("payment-form")) {
                         document.getElementById("payment-form").submit();
                    }
                }, 2000);
            </script>';
        } else {
             // Handle case where total is 0, maybe display a message instead of proceeding to payment
            $payment_form_html = "<p class='error-message'>Le montant total de votre panier est de 0€. Impossible de procéder au paiement.</p>";
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../css/projetS4.css" id="theme-style">
    <title>Green Odyssey - Panier</title>
    <meta charset="UTF-8">
    <meta name="author" content="Anas_Capucine_Hadil"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <script>
        // Theme functions (setCookie, getCookie, switchTheme, window.onload) remain the same
        function setCookie(name, value, days) {var expires = "";if (days) {var date = new Date();date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));expires = "; expires=" + date.toUTCString();}document.cookie = name + "=" + (value || "") + expires + "; path=/";}
        function getCookie(name) {var nameEQ = name + "=";var ca = document.cookie.split(';');for(var i = 0; i < ca.length; i++) {var c = ca[i];while (c.charAt(0) == ' ') c = c.substring(1, c.length);if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);}return null;}
        function switchTheme() {var currentTheme = document.getElementById('theme-style').getAttribute('href');var newTheme;if (currentTheme === '../css/projetS4.css') {newTheme = '../css/projetS4-dark.css';document.getElementById('theme-button').textContent = '☀️ Mode Clair';} else {newTheme = '../css/projetS4.css';document.getElementById('theme-button').textContent = '🌙 Mode Sombre';}document.getElementById('theme-style').setAttribute('href', newTheme);setCookie('theme', newTheme, 30);}
        window.onload = function() {var savedTheme = getCookie('theme');if (savedTheme) {document.getElementById('theme-style').setAttribute('href', savedTheme);if (savedTheme === '../css/projetS4-dark.css') {document.getElementById('theme-button').textContent = '☀️ Mode Clair';} else {document.getElementById('theme-button').textContent = '🌙 Mode Sombre';}}};
    </script>
    <style>
        /* Your existing CSS styles ... */
        .panier-container { width: 80%; margin: 0 auto; padding: 20px; }
        .panier-item { background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .panier-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 10px; }
        .panier-title { font-size: 1.5em; color: #2c3e50; }
        .panier-price { font-size: 1.2em; font-weight: bold; color: #27ae60; }
        .panier-details { margin-top: 15px; }
        .panier-etape { background-color: #fff; border: 1px solid #eee; border-radius: 5px; padding: 10px; margin-bottom: 10px; }
        .panier-etape-title { font-weight: bold; color: #3498db; margin-bottom: 5px; }
        .panier-options { display: flex; flex-wrap: wrap; gap: 10px; }
        .panier-option { background-color: #e8f4f8; border-radius: 4px; padding: 5px 10px; font-size: 0.9em; }
        .panier-total { text-align: right; font-size: 1.5em; font-weight: bold; margin-top: 20px; padding: 10px; background-color: #f5f5f5; border-radius: 5px; }
        .panier-empty { text-align: center; padding: 50px; font-size: 1.2em; color: #7f8c8d; }
        .remove-btn { background-color: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .remove-btn:hover { background-color: #c0392b; }
        .checkout-btn { display: block; width: 200px; margin: 20px auto; padding: 10px; background-color: #2ecc71; color: white; text-align: center; border: none; border-radius: 5px; font-size: 1.1em; cursor: pointer; text-decoration: none; }
        .checkout-btn:hover { background-color: #27ae60; }
        .payment-redirect-message { text-align: center; padding: 20px; background-color: #f8f9fa; border-radius: 8px; margin: 20px 0; }
        .payment-info { background-color: #f0f8ff; border: 1px solid #b3d7ff; border-radius: 5px; padding: 15px; margin: 20px 0; }
        .payment-info h3 { color: #0056b3; margin-top: 0; }
        .payment-info p { margin: 5px 0; }
        .user-info { padding: 10px; background-color: #e9ecef; border-radius: 5px; margin-bottom: 15px; }
        .error-message { color: red; font-weight: bold; text-align: center; padding: 10px; }
    </style>
</head>
<body>

    <center><h1>Green Odyssey</h1></center>
    <center><table class="nav">
        <tr>
            <td><a href="index.php" class="navi">Accueil</a></td>  
            <td><a href="presentation.php" class="navi">Présentation</a></td>
            <td><a href="voyages.php" class="navi">Voyages</a></td>
            <?php if (!isset($_SESSION['user'])): ?>
                <td><a href="inscription.php" class="navi">S'inscrire</a></td>
                <td><a href="connexion.php" class="navi">Se Connecter</a></td>
            <?php else: ?>
                <td><a href="favoris.php" class="navi">Favoris</a></td>
                <td><a href="panier.php" class="navi"><img src="panier2.png" alt="Panier" height="40" width="40" class="avaaatar"></a></td>
                <td><a href="profil.php" class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
            <?php endif; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                <td><a href="admin.php" class="navi">Admin</a></td>
            <?php endif; ?>
            <td><button id="theme-button" onclick="switchTheme()" class="navi-button">🌙 Mode Sombre</button></td>
        </tr>
    </table></center><br><br><br>
    
    <div class="panier-container">
        <?php if (!empty($payment_form_html)): ?>
            <?php echo $payment_form_html; // This will display redirect message or error ?>
        <?php else: ?>
            <h2>Mon Panier</h2>
            
            <?php if ($user_info): ?>
            <div class="user-info">
                <p><strong>Utilisateur:</strong> <?= htmlspecialchars($user_info['prenom'] ?? $username) ?> <?= htmlspecialchars($user_info['nom'] ?? '') ?></p>
                <?php if(!empty($user_info['email'])): ?>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user_info['email']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (empty($user_voyages_in_cart)): ?>
                <div class="panier-empty">
                    <p>Votre panier est vide.</p>
                    <p>Explorez nos <a href="voyages.php">voyages</a> pour commencer votre aventure!</p>
                </div>
            <?php else: ?>
                <div class="payment-info">
                    <h3>Information de Paiement</h3>
                    <p>Pour tester le paiement, utilisez la carte bancaire suivante:</p>
                    <p><strong>Numéro de carte:</strong> 5555 1234 5678 9000</p>
                    <p><strong>Cryptogramme:</strong> 555</p>
                    <p><strong>Date d'expiration:</strong> N'importe quelle date future</p>
                    <p><strong>Titulaire:</strong> N'importe quel nom</p>
                </div>
                
                <?php 
                $grand_total_display = 0;
                foreach ($user_voyages_in_cart as $voyage_id => $voyage_data_item): 
                    $voyage_title = $voyage_data_item['voyage_titre'] ?? 'Voyage sans titre (' . $voyage_id . ')';
                    // Calculate price for this specific voyage using the API call
                    $voyage_total_price = calculate_voyage_price_via_api($voyage_data_item);
                    $grand_total_display += $voyage_total_price;
                ?>
                    <div class="panier-item">
                        <div class="panier-header">
                            <div class="panier-title"><?= htmlspecialchars($voyage_title) ?></div>
                            <div class="panier-price"><?= number_format($voyage_total_price, 2, ',', ' ') ?> €</div>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="voyage_id" value="<?= htmlspecialchars($voyage_id) ?>">
                                <button type="submit" name="remove_voyage" class="remove-btn">Supprimer</button>
                            </form>
                        </div>
                        
                        <div class="panier-details">
                            <?php if (isset($voyage_data_item['options_generales']) && is_array($voyage_data_item['options_generales'])): 
                                $opts_gen = $voyage_data_item['options_generales'];    
                            ?>
                                <div class="panier-etape" style="background-color: #f0f0f0;"> <!-- Visually distinct for general options -->
                                    <div class="panier-etape-title">Options Générales</div>
                                    <div class="panier-options">
                                        <div class="panier-option">Personnes: <?= htmlspecialchars($opts_gen['nb_pers'] ?? 1) ?></div>
                                        <?php if (!empty($opts_gen['date_depart'])): ?>
                                            <div class="panier-option">Départ: <?= htmlspecialchars($opts_gen['date_depart']) ?></div>
                                        <?php endif; ?>
                                        <div class="panier-option">Restauration: <?= htmlspecialchars($opts_gen['restauration'] ?? 'Aucune') ?></div>
                                        <div class="panier-option">Assurance: <?= isset($opts_gen['assurance']) && $opts_gen['assurance'] ? 'Oui' : 'Non' ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($voyage_data_item['etapes']) && is_array($voyage_data_item['etapes'])): ?>
                                <?php foreach ($voyage_data_item['etapes'] as $etape_id_key => $choix): 
                                    $etape_display_details = null;
                                    foreach ($etapes_list as $etape_from_list) {
                                        if ((string)$etape_from_list['etape_id'] === (string)$etape_id_key) {
                                            $etape_display_details = $etape_from_list;
                                            break;
                                        }
                                    }
                                    if (!$etape_display_details) continue; // Should not happen if data is consistent
                                ?>
                                    <div class="panier-etape">
                                        <div class="panier-etape-title">
                                            Étape: <?= htmlspecialchars($etape_display_details['titre']) ?> 
                                            (Base: <?= number_format($etape_display_details['prix'], 2, ',', ' ') ?> €)
                                        </div>
                                        <div class="panier-options">
                                            <?php if (!empty($choix['activité']) && $choix['activité'] !== 'Aucune activité'): ?>
                                                <div class="panier-option">Activité: <?= htmlspecialchars($choix['activité']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($choix['hebergement']) && $choix['hebergement'] !== 'Aucun hébergement'): ?>
                                                <div class="panier-option">Hébergement: <?= htmlspecialchars($choix['hebergement']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($choix['transport']) && $choix['transport'] !== 'Aucun transport'): ?>
                                                <div class="panier-option">Transport: <?= htmlspecialchars($choix['transport']) ?></div>
                                            <?php endif; ?>
                                             <?php if (empty($choix['activité']) && empty($choix['hebergement']) && empty($choix['transport'])): ?>
                                                <div class="panier-option">Aucune option sélectionnée pour cette étape.</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Aucune étape sélectionnée pour ce voyage.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="panier-total">
                    Total Général: <?= number_format($grand_total_display, 2, ',', ' ') ?> €
                </div>
                
                <?php if ($grand_total_display > 0): // Only show checkout if there's something to pay ?>
                <form method="post">
                    <button type="submit" name="checkout" class="checkout-btn">Procéder au paiement</button>
                </form>
                <?php else: ?>
                <p style="text-align:center; margin-top:20px;">Votre panier est vide ou le total est de 0€.</p>
                <?php endif; ?>

            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer class="foooot">
        <p>© 2025 GREEN ODYSSEY Tous droits réservés.</p>
    </footer>
    <div class="paysage"></div>
</body>
</html>