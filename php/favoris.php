<?php
    session_start();

    $user_id = $_SESSION['user_id'];
    $users_file = '../json/data1.json';
    $etapes_file = '../json/etapes.json';

    $voyages = json_decode(file_get_contents('../json/voyages.json'), true);

    if (file_exists($etapes_file)) {
        $json_content = file_get_contents($etapes_file);
        $etapes = json_decode($json_content, true);
        
        if (!is_array($etapes)) {
            $etapes = []; 
        }
    } else {
        $etapes = [];
    }

    // Charger les users depuis data1.json
    $users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];


    if ($users === null) {
        die("Erreur : Impossible de lire le fichier JSON. Vérifiez sa syntaxe.");
    }

    foreach ($users as &$user) { //parcours des users du data.json
        if ($user['email'] === $user_id) {
            if (!isset($user['favoris'])) {
                $user['favoris'] = []; //création du tableau favori pour l'user qui sera dans le data1.json
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_favori'])) {    //foncion d'ajout d'un favori
                $voyage_nom = trim($_POST['voyage_nom']);
                if (!empty($voyage_nom) && !in_array($voyage_nom, $user['favoris'])) {
                    $user['favoris'][] = $voyage_nom;
                }
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_favori'])) {    //fonction de suppression d'un favori
                $voyage_nom = trim($_POST['voyage_nom']);
                $user['favoris'] = array_values(array_diff($user['favoris'], [$voyage_nom]));
            }
            break;

            
        }
    }



    // Sauvegarde des modifications
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
    $_SESSION['user'] = $user;

?>


<!DOCTYPE html>
<html>

    <head>
        <link rel="stylesheet" type="text/css" href="../css/projetS4.css" id="theme-style">

        <title>Green Odyssey</title>

        <meta charset="UTF-8">
        <meta name=”author” content=”Anas_Capucine_Hadil”/>

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
                    <td><a href="panier.php" class="navi">Panier</td>
                    <td><a href="profil.php"   class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <td><a href="admin.php" class="navi">Admin</a></td>
                <?php endif; ?>
                <td><button id="theme-button" onclick="switchTheme()" class="navi-button">🌙 Mode Sombre</button></td>
                
            </tr>
        </table></center>
        <p class="gestion">Vos voyages favoris 💖</p>

        <center>
        <?php
            // Verifier si l'utilisateur a des favoris
            $favoris_affiches = [];
            foreach ($users as &$user) {
                if ($user['email'] === $user_id) {
                    if (isset($user['favoris']) && !empty($user['favoris'])) {
                        foreach ($user['favoris'] as $voyage_nom) {
                            foreach ($voyages as $voyage) { 
                                if ($voyage['titre'] === $voyage_nom) {
                                    $favoris_affiches[] = $voyage; 
                                    break; 
                                }
                            }
                        }
                    }
                    break;
                }
            }
        ?>

        <?php if (empty($favoris_affiches)): ?>
            <p class="parag">Aucun favori enregistré.</p>
        <?php else: ?>
            <table class="tabadmin">
                <tr>
                    <th>Voyage</th>
                    <th>Prix</th>
                    <th>Etapes</th>
                    <th>Infos en +</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($favoris_affiches as $voyage): ?>
                    <tr>
                        <td><?= htmlspecialchars($voyage['titre']) ?></td>
                        <td> <?= htmlspecialchars($voyage['prix'] ?? 'Non spécifié') ?> €</td>
                        <td> 
                        <?php
                        // Vérifier si le voyage a des étapes associées
                        if (!empty($voyage['etapes_ids'])) {
                            $etapes_titles = [];

                            // Parcourir toutes les étapes et récupérer celles du voyage en cours
                            foreach ($etapes as $etape) {
                                if (in_array($etape['etape_id'], $voyage['etapes_ids'])) {
                                    $etapes_titles[] = htmlspecialchars($etape['titre']);
                                }
                            }

                            // Afficher les titres des étapes séparés par une virgule
                            echo implode(', ', $etapes_titles);
                        } else {
                            echo "Aucune étape associée";
                        }
                        ?>
                        </td>
                        <td><a href="voyages.php#v<?= htmlspecialchars($voyage['voyage_id']) ?>" >Voir les détails</a></td>
                        <td>
                            <form method="POST" action="favoris.php" class="suppr">
                                <input type="hidden" name="voyage_nom" value="<?= htmlspecialchars($voyage['titre']) ?>">
                                <button type="submit" name="supprimer_favori" >❌ </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        </center>

        
    </body>
    
    <div class="paysage"></div>

    <!-- Pied de page -->
    <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
</html>