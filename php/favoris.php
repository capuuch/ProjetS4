<?php
session_start();

$user_id = $_SESSION['user_id'];
$users_file = 'data1.json';
$etapes_file = 'etapes.json';

$voyages = json_decode(file_get_contents('voyages.json'), true);

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
        <link rel="stylesheet" type="text/css" href="projetS4.css">

        <title>Green Odyssey Connexion</title>

        <meta charset="UTF-8">
        <meta name=”author” content=”Anas_Capucine_Hadil”/>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
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
                    <td><a href="profil.php"   class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <td><a href="admin.php" class="navi">Admin</a></td>
                <?php endif; ?>
                
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
