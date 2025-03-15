<?php
session_start();

/*if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: connexion.php");
    exit;
} */ //cette partie est TRES importante, le site n'est pas censé fonctionner sans pourtant ça fonctionne UNIQUEMENT sans yfgvuyegzur

// Charger les données du fichier JSON
$file = 'data1.json';
$users = json_decode(file_get_contents($file), true);

// Traitement de la promotion en administrateur 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['promote'])) {
    $emailToPromote = $_POST['email'];

    foreach ($users as &$user) {
        if ($user['email'] === $emailToPromote && $user['role'] === "normal") {
            $user['role'] = "admin"; // Change le rôle de l'utilisateur en administrateur
        }
    }

    // Sauvegarder les modifications dans le fichier JSON
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));

    // Mettre à jour les données en mémoire
    $users = json_decode(file_get_contents($file), true);

    echo "<p>L'utilisateur $emailToPromote est maintenant administrateur.</p>";
}
?>

<!DOCTYPE html>
<html>

    <head>
        <link rel="stylesheet" type="text/css" href="projetS4.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">    <!-- pour afficher l'icone du crayon pour modifier-->


        <title>Green Odyssey Administrateur</title>

        <meta charset="UTF-8">
        <meta name=”author” content=”Anas_Capucine_Hadil”/>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    </head>

    <body>
        <h1>Green Odyssey</h1>
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
        </table></center></br></br></br>

            <p class="gestion">Gestion des utilisateurs</p>
            <center><table class="tabadmin">
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>

                <?php foreach ($users as $user): ?>

                <tr>
                    <td><?php echo htmlspecialchars($user['nom']) . ' ' . htmlspecialchars($user['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    
                    <td>
                        <button class="btn-modif">
                            <i class="fa fa-pencil-alt"></i>
                        </button> | 
                        <button class="ressset">Bannir</button>
                        <form method="post">
                            <input type="hidden" name="email" value="<?php echo $user['email']; ?>">
                            <?php if ($user['role'] === "normal"): ?>
                                <button type="submit" name="promote">Promouvoir en admin</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table></center>


        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>