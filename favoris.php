<?php
session_start();

$user_id = $_SESSION['user_id'];
$users_file = 'data1.json';

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
        </table></center><br><br>


    <div class="c1">Favoris</div>
    <table border="1">
        <?php
        foreach ($users as &$user) {
            if ($user['email'] === $user_id) { //parcours des users du data.json
                if (!isset($user['favoris']) || empty($user['favoris'])) {
                    echo "<tr><td colspan='2'>Aucun favori enregistré.</td></tr>";
                } else {
                    foreach ($user['favoris'] as $voyage_nom) {
                        echo "<tr>";
                        echo "<td>$voyage_nom</td>";
                        echo "<td>
                                <form method='POST' action='favoris.php'>
                                    <input type='hidden' name='voyage_nom' value='$voyage_nom'>
                                    <button type='submit' name='supprimer_favori'>❌ Supprimer</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                }
                break; 
            }
        }
        ?>

    </table>
    </body>
    
    <div class="paysage"></div>
</html>