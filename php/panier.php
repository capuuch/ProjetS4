<?php 
    session_start(); 

    if (!isset($_SESSION['user'])) {
        header("Location: connexion.php");
        exit;
    }

    // Include payment functions
    require_once 'payment_functions.php';

    // Extract username safely - handle any possible data type
    $username = null;
    if (is_string($_SESSION['user'])) {
        $username = $_SESSION['user']; // Already a string
    } elseif (is_array($_SESSION['user']) && isset($_SESSION['user']['username'])) {
        $username = (string)$_SESSION['user']['username']; // Extract username from array
    } elseif (is_object($_SESSION['user']) && isset($_SESSION['user']->username)) {
        $username = (string)$_SESSION['user']->username; // Extract username from object
    } elseif (is_array($_SESSION['user']) && isset($_SESSION['user']['nom'])) {
        $username = (string)$_SESSION['user']['nom']; // Try 'nom' as alternative
    } elseif (is_object($_SESSION['user']) && isset($_SESSION['user']->nom)) {
        $username = (string)$_SESSION['user']->nom; // Try 'nom' as alternative
    } else {
        // Last resort - convert to string or use session ID as fallback
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
    $voyages_file = '../json/voyages.json';
    $etapes_file = '../json/etapes.json';
    $users_file = '../json/users.json';

    $options = file_exists($options_file) ? json_decode(file_get_contents($options_file), true) : [];
    $voyages = file_exists($voyages_file) ? json_decode(file_get_contents($voyages_file), true) : [];
    $etapes = file_exists($etapes_file) ? json_decode(file_get_contents($etapes_file), true) : [];
    $users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

    // Get user information
    $user_info = null;
    if (isset($users[$username]) && is_array($users[$username])) {
        $user_info = $users[$username];
    }

    // Process removal if requested
    if (isset($_POST['remove_voyage']) && isset($_POST['voyage_id'])) {
        $voyage_id_to_remove = $_POST['voyage_id'];
        if (isset($options[$username][$voyage_id_to_remove])) {
            unset($options[$username][$voyage_id_to_remove]);
            file_put_contents($options_file, json_encode($options, JSON_PRETTY_PRINT));
        }
    }

    // Get user's selected options
    $user_options = isset($options[$username]) ? $options[$username] : [];

    // Calculate total price
    function calculateTotalPrice($voyage_id, $etapes_data, $etapes) {
        $total = 0;
        if (isset($etapes_data['etapes']) && is_array($etapes_data['etapes'])) {
            foreach ($etapes_data['etapes'] as $etape_id => $choix) {
                // Find the etape in the etapes array
                foreach ($etapes as $etape) {
                    if ($etape['etape_id'] == $etape_id) {
                        $total += $etape['prix'];
                        break;
                    }
                }
            }
        }
        return $total;
    }

    // Process payment if requested
    $payment_form = '';
    if (isset($_POST['checkout']) && !empty($user_options)) {
        // Calculate grand total
        $grand_total = 0;
        foreach ($user_options as $voyage_id => $voyage_data) {
            $grand_total += calculateTotalPrice($voyage_id, $voyage_data, $etapes);
        }
        
        // Format amount for CY Bank
        $montant = formatAmount($grand_total);
        
        // Generate transaction ID
        $transaction = generateTransactionId();
        
        // Set vendor code - using TEST which is in the valid vendor list
        $vendeur = 'TEST';
        
        // Set return URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $retour = $protocol . $host . '/hadil_project/php/payment_return.php?username=' . urlencode($username) . '&transaction=' . $transaction;
        
        // Calculate control value
        $control = calculateControlValue($transaction, $montant, $vendeur, $retour);
        
        // Save transaction
        savePaymentTransaction($transaction, $montant, $vendeur, $username, $user_options);
        
        // Create payment form
        $payment_form = '
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
            // Auto-submit form after 2 seconds
            setTimeout(function() {
                document.getElementById("payment-form").submit();
            }, 2000);
        </script>';
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
            };
        </script>
        <style>
            .panier-container {
                width: 80%;
                margin: 0 auto;
                padding: 20px;
            }
            .panier-item {
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .panier-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
                margin-bottom: 10px;
            }
            .panier-title {
                font-size: 1.5em;
                color: #2c3e50;
            }
            .panier-price {
                font-size: 1.2em;
                font-weight: bold;
                color: #27ae60;
            }
            .panier-details {
                margin-top: 15px;
            }
            .panier-etape {
                background-color: #fff;
                border: 1px solid #eee;
                border-radius: 5px;
                padding: 10px;
                margin-bottom: 10px;
            }
            .panier-etape-title {
                font-weight: bold;
                color: #3498db;
                margin-bottom: 5px;
            }
            .panier-options {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .panier-option {
                background-color: #e8f4f8;
                border-radius: 4px;
                padding: 5px 10px;
                font-size: 0.9em;
            }
            .panier-total {
                text-align: right;
                font-size: 1.5em;
                font-weight: bold;
                margin-top: 20px;
                padding: 10px;
                background-color: #f5f5f5;
                border-radius: 5px;
            }
            .panier-empty {
                text-align: center;
                padding: 50px;
                font-size: 1.2em;
                color: #7f8c8d;
            }
            .remove-btn {
                background-color: #e74c3c;
                color: white;
                border: none;
                padding: 5px 10px;
                border-radius: 4px;
                cursor: pointer;
            }
            .remove-btn:hover {
                background-color: #c0392b;
            }
            .checkout-btn {
                display: block;
                width: 200px;
                margin: 20px auto;
                padding: 10px;
                background-color: #2ecc71;
                color: white;
                text-align: center;
                border: none;
                border-radius: 5px;
                font-size: 1.1em;
                cursor: pointer;
                text-decoration: none;
            }
            .checkout-btn:hover {
                background-color: #27ae60;
            }
            .payment-redirect-message {
                text-align: center;
                padding: 20px;
                background-color: #f8f9fa;
                border-radius: 8px;
                margin: 20px 0;
            }
            .payment-info {
                background-color: #f0f8ff;
                border: 1px solid #b3d7ff;
                border-radius: 5px;
                padding: 15px;
                margin: 20px 0;
            }
            .payment-info h3 {
                color: #0056b3;
                margin-top: 0;
            }
            .payment-info p {
                margin: 5px 0;
            }
        </style>
    </head>
    <body>

        <center><h1>Green Odyssey</h1></center>
        <center><table class="nav">
            <tr>
                <td><a href="index.php" class="navi">Accueil</a></td>  
                <td><a href="presentation.php"   class="navi">Présentation</a></td>
                <td><a href="voyages.php"  class="navi">Voyages</a></td>
                
                <?php if (!isset($_SESSION['user'])): ?>
                    <td><a href="inscription.php"   class="navi">S'inscrire</a></td>
                    <td><a href="connexion.php"   class="navi">Se Connecter</a></td>
                
                <?php else: ?>
                    <td><a href="favoris.php"   class="navi">Favoris</a></td>
                    <td><a href="panier.php" class="navi"><img src="panier2.png" alt="Panier" height="40" width="40" class="avaaatar"></a></td>
                    <td><a href="profil.php"   class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <td><a href="admin.php" class="navi">Admin</a></td>
                <?php endif; ?>
                <td><button id="theme-button" onclick="switchTheme()" class="navi-button">🌙 Mode Sombre</button></td>
            </tr>
        </table></center></br></br></br>
        
        <div class="panier-container">
            <?php if (!empty($payment_form)): ?>
                <?php echo $payment_form; ?>
            <?php else: ?>
                <h2>Mon Panier</h2>
                
                <?php if ($user_info): ?>
                <div class="user-info" style="margin-bottom: 20px;">
                    <p><strong>Utilisateur:</strong> <?= htmlspecialchars($user_info['prenom'] ?? '') ?> <?= htmlspecialchars($user_info['nom'] ?? '') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user_info['email'] ?? '') ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (empty($user_options)): ?>
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
                    $grand_total = 0;
                    foreach ($user_options as $voyage_id => $voyage_data): 
                        $voyage_title = $voyage_data['voyage_titre'] ?? 'Voyage sans titre';
                        $total_price = calculateTotalPrice($voyage_id, $voyage_data, $etapes);
                        $grand_total += $total_price;
                    ?>
                        <div class="panier-item">
                            <div class="panier-header">
                                <div class="panier-title"><?= htmlspecialchars($voyage_title) ?></div>
                                <div class="panier-price"><?= $total_price ?> €</div>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="voyage_id" value="<?= htmlspecialchars($voyage_id) ?>">
                                    <button type="submit" name="remove_voyage" class="remove-btn">Supprimer</button>
                                </form>
                            </div>
                            
                            <div class="panier-details">
                                <?php if (isset($voyage_data['etapes']) && is_array($voyage_data['etapes'])): ?>
                                    <?php foreach ($voyage_data['etapes'] as $etape_id => $choix): 
                                        // Find etape details
                                        $etape_details = null;
                                        foreach ($etapes as $etape) {
                                            if ($etape['etape_id'] == $etape_id) {
                                                $etape_details = $etape;
                                                break;
                                            }
                                        }
                                        if (!$etape_details) continue;
                                    ?>
                                        <div class="panier-etape">
                                            <div class="panier-etape-title"><?= htmlspecialchars($etape_details['titre']) ?> - <?= htmlspecialchars($etape_details['prix']) ?> €</div>
                                            <div class="panier-options">
                                                <?php if (!empty($choix['activité'])): ?>
                                                    <div class="panier-option">Activité: <?= htmlspecialchars($choix['activité']) ?></div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($choix['hebergement'])): ?>
                                                    <div class="panier-option">Hébergement: <?= htmlspecialchars($choix['hebergement']) ?></div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($choix['transport'])): ?>
                                                    <div class="panier-option">Transport: <?= htmlspecialchars($choix['transport']) ?></div>
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
                        Total: <?= $grand_total ?> €
                    </div>
                    
                    <form method="post">
                        <button type="submit" name="checkout" class="checkout-btn">Procéder au paiement</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>
