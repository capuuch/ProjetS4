<?php session_start(); ?>
<!DOCTYPE html>
<html>

    <head>
        <link rel="stylesheet" type="text/css" href="projetS4.css">

        <title>Green Odyssey Présentation</title>

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
        </table></center></br></br></br>

        <center><div class="conteneur">

            <div class="normalB">
                <label for="recherche"></label>
                <input type="search" id="recherche" name="recherche" placeholder="Saisir vos envies... " class="searchh">
                <button class="btn_search">Rechercher</button><br>
                <p class="press"> Une destination, un voyage...</p>
            </div>

            <div class="normalA">
                <p class="titress">Vous souhaitez partir à la découverte du Canada ? 🇨🇦</p>
                <p class="parag">Ce site vous propose un itinéraire idéal de randonnée au Canada. Nous espérons qu'il vous plaira ! </p><br>
                <p class="titress">Options personnalisables </p>
                <p class="parag">Adaptez les hébergements, activités et transports selon vos envies.</p><br>
                <p class="titress">Paiement sécurisé 🤑</p>
                <p class="parag">Réservez en toute confiance grâce à notre plateforme sécurisée.</p>
            </div>
        </div></center>

        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>
