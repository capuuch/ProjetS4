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

<?php
function getAPIKey($vendeur)
{
	if(in_array($vendeur, array('MI-1_A', 'MI-1_B', 'MI-1_C', 'MI-1_D', 'MI-1_E', 'MI-1_F', 'MI-1_G', 'MI-1_H', 'MI-1_I', 'MI-1_J', 'MI-2_A', 'MI-2_B', 'MI-2_C', 'MI-2_D', 'MI-2_E', 'MI-2_F', 'MI-2_G', 'MI-2_H', 'MI-2_I', 'MI-2_J', 'MI-3_A', 'MI-3_B', 'MI-3_C', 'MI-3_D', 'MI-3_E', 'MI-3_F', 'MI-3_G', 'MI-3_H', 'MI-3_I', 'MI-3_J', 'MI-4_A', 'MI-4_B', 'MI-4_C', 'MI-4_D', 'MI-4_E', 'MI-4_F', 'MI-4_G', 'MI-4_H', 'MI-4_I', 'MI-4_J', 'MI-5_A', 'MI-5_B', 'MI-5_C', 'MI-5_D', 'MI-5_E', 'MI-5_F', 'MI-5_G', 'MI-5_H', 'MI-5_I', 'MI-5_J', 'MEF-1_A', 'MEF-1_B', 'MEF-1_C', 'MEF-1_D', 'MEF-1_E', 'MEF-1_F', 'MEF-1_G', 'MEF-1_H', 'MEF-1_I', 'MEF-1_J', 'MEF-2_A', 'MEF-2_B', 'MEF-2_C', 'MEF-2_D', 'MEF-2_E', 'MEF-2_F', 'MEF-2_G', 'MEF-2_H', 'MEF-2_I', 'MEF-2_J', 'MIM_A', 'MIM_B', 'MIM_C', 'MIM_D', 'MIM_E', 'MIM_F', 'MIM_G', 'MIM_H', 'MIM_I', 'MIM_J', 'SUPMECA_A', 'SUPMECA_B', 'SUPMECA_C', 'SUPMECA_D', 'SUPMECA_E', 'SUPMECA_F', 'SUPMECA_G', 'SUPMECA_H', 'SUPMECA_I', 'SUPMECA_J', 'TEST'))) {
		return substr(md5($vendeur), 1, 15);
	}
	return "zzzz";
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

        <!--Page de paiement-->
        <?php
        $apikey = getAPIKey('MI-4_F');
        $control=md5( $apikey
            . "#" . '154632ABCD'
            . "#" . '18000.99'
            . "#" . 'MI-4_F'
            . "#" . 'http://localhost/php/profil.php?session=s' . "#" );
        ?>


        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>
