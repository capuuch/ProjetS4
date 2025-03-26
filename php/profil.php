<?php 
    session_start(); 
    if (!isset($_SESSION['user_id'])) {
        header("Location: connexion.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
        exit;
    }
    $user = $_SESSION['user'];

    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: connexion.php");
        exit;
    }
?>



<!DOCTYPE html>
<html>

    <head>
        <link rel="stylesheet" type="text/css" href="../css/projetS4.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">    <!-- pour afficher l'icone du crayon pour modifier-->

        <title>Green Odyssey Profil</title>

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
                    <td><a href="panier.php" class="navi">Panier</td>
                    <td><a href="profil.php"   class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <td><a href="admin.php" class="navi">Admin</a></td>
                <?php endif; ?>
            </tr>
        </table></center></br></br></br>
        
        <center><div class="profil">
            <img src="vavatar.jpeg" alt="Photo de profil" class="avaaatar" height="70" width="70">
            <h2>Profil Utilisateur</h2>
        
            <div class="profil-info">
                Nom : <?php echo htmlspecialchars($user['nom']); ?>
                <button class="btn-modif">
                    <i class="fa fa-pencil-alt"></i>
                </button>
            </div><br>

            <div class="profil-info">
                Prénom : <?php echo htmlspecialchars($user['prenom']); ?>
                <button class="btn-modif">
                    <i class="fa fa-pencil-alt"></i>
                </button>
            </div><br>
        
            <div class="profil-info">
                Email : <?php echo htmlspecialchars($user['email']); ?>
                <button class="btn-modif">
                    <i class="fa fa-pencil-alt"></i>
                </button>
            </div><br>
        
            <div class="profil-info">
                N° Tel : <?php echo htmlspecialchars($user['num'] ?? 'Non renseigné'); ?>
                <button class="btn-modif">
                    <i class="fa fa-pencil-alt"></i>
                </button>
            </div>
            <br>

            <div class="profil-info">
                Mot de passe : ********
                <button class="btn-modif">
                    <i class="fa fa-pencil-alt"></i>
                </button>
            </div>
            <br>
        
            <center>
            <form method="post">
                <button type="submit" name="logout" class="seconnecter">Se déconnecter</button>
            </form>
        </center>
        </div></center>



        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>
