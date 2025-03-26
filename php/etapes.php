<?php 
session_start(); 

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user'])) {
    die("Erreur : utilisateur non connecté.");
}

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

$etapes_file = '../json/etapes.json';
$voyages_file = '../json/voyages.json';
$options_file = '../json/options.json';
$users_file = '../json/users.json';

// Chargement des fichiers JSON
$voyages = file_exists($voyages_file) ? json_decode(file_get_contents($voyages_file), true) : [];
$etapes = file_exists($etapes_file) ? json_decode(file_get_contents($etapes_file), true) : [];
$options = file_exists($options_file) ? json_decode(file_get_contents($options_file), true) : [];
$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];

// Ensure arrays are initialized
if (!is_array($voyages)) $voyages = [];
if (!is_array($etapes)) $etapes = [];
if (!is_array($options)) $options = [];
if (!is_array($users)) $users = [];

// Get user information
$user_info = null;
if (isset($users[$username]) && is_array($users[$username])) {
    $user_info = $users[$username];
}

// Récupération du voyage sélectionné depuis le formulaire
$voyage_nom = isset($_POST['voyage_nom']) ? $_POST['voyage_nom'] : null;
$voyage_selectionne = null;

if ($voyage_nom) {
    foreach ($voyages as $voyage) {
        if ($voyage['titre'] === $voyage_nom) {
            $voyage_selectionne = $voyage;
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_options']) && isset($_SESSION['user']) && $voyage_selectionne) {
    // Use the same username extraction logic as above
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
    
    // Get user information
    $user_info = null;
    if (isset($users[$username]) && is_array($users[$username])) {
        $user_info = $users[$username];
    }
    
    $voyage_id = isset($voyage_selectionne['voyage_id']) ? (string)$voyage_selectionne['voyage_id'] : null;
    $voyage_titre = isset($voyage_selectionne['titre']) ? $voyage_selectionne['titre'] : 'Voyage sans titre';

    if (!$voyage_id) {
        die("Erreur: L'identifiant du voyage est manquant.");
    }

    // Validate etapes data
    if (!isset($_POST['etapes']) || !is_array($_POST['etapes'])) {
        die("Erreur: Aucune étape sélectionnée ou données invalides.");
    }

    // Ensure options structure exists with explicit type checking
    if (!is_array($options)) {
        $options = [];
    }
    
    // Make sure username is a valid string for array key
    if (!is_string($username) || empty($username)) {
        $username = 'guest_' . session_id();
    }
    
    if (!isset($options[$username]) || !is_array($options[$username])) {
        $options[$username] = []; 
    }
    
    // Use explicit array access with type checking
    if (!isset($options[$username][$voyage_id]) || !is_array($options[$username][$voyage_id])) {
        $options[$username][$voyage_id] = [];
    }
    
    // Add user information and voyage title to options
    $options[$username][$voyage_id]['user_info'] = [
        'nom' => $user_info['nom'] ?? $username,
        'prenom' => $user_info['prenom'] ?? '',
        'email' => $user_info['email'] ?? '',
    ];
    
    $options[$username][$voyage_id]['voyage_titre'] = $voyage_titre;
    $options_generales = $_POST['options_generales'] ?? [];

    // More robust processing of etapes
    foreach ($_POST['etapes'] as $etape_id => $choix) {
        // Convert etape_id to string to ensure it's a valid array key
        $etape_id_key = (string)$etape_id;
        
        // Ensure each key exists and is not null
        $options[$username][$voyage_id]['etapes'][$etape_id_key] = [
            'activité' => isset($choix['activite']) ? $choix['activite'] : null,
            'hebergement' => isset($choix['hebergement']) ? $choix['hebergement'] : null,
            'transport' => isset($choix['transport']) ? $choix['transport'] : null
        ];
    }

    // Vérification et ajout des options générales
    

    $options[$username][$voyage_id]['options_generales'] = [
        'nb_pers' => isset($options_generales['nb_pers']) ? (int)$options_generales['nb_pers'] : 1,
        'date_depart' => $options_generales['date_depart'] ?? '',
        'restauration' => $options_generales['restauration'] ?? 'Aucune',
        'assurance' => isset($options_generales['assurance']) ? true : false,
    ];

    // Sauvegarde dans options.json
    if (file_put_contents($options_file, json_encode($options, JSON_PRETTY_PRINT)) === false) {
        die("Erreur: Impossible d'enregistrer les choix.");
    } else {
        echo "<p style='color: green;'>Vos choix ont bien été enregistrés !</p>";
        header("Location: panier.php");
    }
}


?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../css/projetS4.css">
        <title>Green Odyssey</title>
        <meta charset="UTF-8">
        <meta name="author" content="Anas_Capucine_Hadil"/>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
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
            </tr>
            </table>
        </center></br></br>
            
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
            <form method="post">
                <table class="tabadmin">
                    <tr>
                        <th>Titre</th>
                        <th>Prix</th>
                        <th>Durée</th>
                        <th>Activité</th>
                        <th>Hébergement</th>
                        <th>Transport</th>
                    </tr>
                    <?php foreach ($voyage_selectionne['etapes_ids'] as $etape_id): ?>
                        <?php foreach ($etapes as $etape): ?>
                            <?php if ($etape['etape_id'] === $etape_id): ?>
                                <tr>
                                    <td><?= htmlspecialchars($etape['titre']) ?></td>
                                    <td><?= htmlspecialchars($etape['prix']) ?></td>
                                    <td><?= htmlspecialchars($etape['duree']) ?></td>
                                    <td>
                                        <input type="checkbox" 
                                               name="etapes[<?= $etape_id ?>][activite]" 
                                               value="<?= htmlspecialchars($etape['options']['activité'] ?? '') ?>"
                                               <?= isset($_POST['etapes'][$etape_id]['activite']) ? 'checked' : '' ?>>
                                        <?= htmlspecialchars($etape['options']['activité'] ?? 'Non spécifié') ?>
                                    </td>
                                    <td>
                                        <select name="etapes[<?= $etape_id ?>][hebergement]">
                                            <option value="">Choisir...</option>
                                            <option value="Hotel Luxe" 
                                                <?= isset($_POST['etapes'][$etape_id]['hebergement']) && $_POST['etapes'][$etape_id]['hebergement'] === 'Hotel Luxe' ? 'selected' : '' ?>>
                                                Hôtel de Luxe
                                            </option>
                                            <option value="Chalet" 
                                                <?= isset($_POST['etapes'][$etape_id]['hebergement']) && $_POST['etapes'][$etape_id]['hebergement'] === 'Chalet' ? 'selected' : '' ?>>
                                                Chalet de montagne
                                            </option>
                                            <option value="Auberge" 
                                                <?= isset($_POST['etapes'][$etape_id]['hebergement']) && $_POST['etapes'][$etape_id]['hebergement'] === 'Auberge' ? 'selected' : '' ?>>
                                                Auberge conviviale
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="etapes[<?= $etape_id ?>][transport]">
                                            <option value="">Choisir...</option>
                                            <option value="Voiture" 
                                                <?= isset($_POST['etapes'][$etape_id]['transport']) && $_POST['etapes'][$etape_id]['transport'] === 'Voiture' ? 'selected' : '' ?>>
                                                Voiture privée
                                            </option>
                                            <option value="Navette" 
                                                <?= isset($_POST['etapes'][$etape_id]['transport']) && $_POST['etapes'][$etape_id]['transport'] === 'Navette' ? 'selected' : '' ?>>
                                                Navette collective
                                            </option>
                                            <option value="Train" 
                                                <?= isset($_POST['etapes'][$etape_id]['transport']) && $_POST['etapes'][$etape_id]['transport'] === 'Train' ? 'selected' : '' ?>>
                                                Train rapide
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </table>
                <table class="tabadmin">
                <tr>
                        <th>Nombre de personnes</th>
                        <th>Date de départ</th>
                        <th>Restauration</th>
                        <th>Assurance</th>
                    </tr>
                    <tr>
                        <td>
                            <select name="options_generales[nb_pers]">
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?= $i ?>" 
                                        <?= (isset($_POST['options_generales']['nb_pers']) && $_POST['options_generales']['nb_pers'] == $i) ? 'selected' : '' ?>>
                                        <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </td>
                        <td>
                            <input type="date" name="options_generales[date_depart]" 
                                value="<?= htmlspecialchars($_POST['options_generales']['date_depart'] ?? '') ?>">
                        </td>
                        <td>
                            <select name="options_generales[restauration]">
                                <option value="">Aucune</option>
                                <option value="Petit-déjeuner" 
                                    <?= (isset($_POST['options_generales']['restauration']) && $_POST['options_generales']['restauration'] === 'Petit-déjeuner') ? 'selected' : '' ?>>
                                    Petit-déjeuner
                                </option>
                                <option value="Demi-pension" 
                                    <?= (isset($_POST['options_generales']['restauration']) && $_POST['options_generales']['restauration'] === 'Demi-pension') ? 'selected' : '' ?>>
                                    Demi-pension
                                </option>
                                <option value="Pension complète" 
                                    <?= (isset($_POST['options_generales']['restauration']) && $_POST['options_generales']['restauration'] === 'Pension complète') ? 'selected' : '' ?>>
                                    Pension complète
                                </option>
                            </select>
                        </td>
                        <td>
                            <input type="checkbox" name="options_generales[assurance]" value="1" 
                                <?= isset($_POST['options_generales']['assurance']) ? 'checked' : '' ?>>
                            Souscrire à une assurance
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="voyage_nom" value="<?= htmlspecialchars($voyage_nom) ?>">
                <button type="submit" name="save_options">Enregistrer les choix</button>
            </form>

        <?php else: ?>
            <p>Aucun voyage sélectionné.</p>
        <?php endif; ?>
        </center>

        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>
