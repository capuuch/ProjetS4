<?php 
    session_start(); 
    date_default_timezone_set("Europe/Paris");
?>
<!DOCTYPE html>
<html>

    <head>
        <link rel="stylesheet" type="text/css" href="../css/projetS4.css">

        <title>Green Odyssey Connexion</title>

        <meta charset="UTF-8">
        <meta name="author" content="Anas_Capucine_Hadil"/>

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
            </tr>
        </table></center></br></br></br>
        
        <form action="connexion.php" method="post"> 
            <center><table class="tabconnexion">
                <tr></tr>
                <tr><th colspan="2" ><h2>Connexion</h2></th>
                </tr>
                <tr><td><label for="email">Email :</label> </td>
                    <td><input type="text" id="email" name="email" placeholder="Entrez votre email" required> </td>
                </tr>
                
                <tr><td><label for="mdp">Mot de passe :</label></td>
                    <td><input type="password" id="mdp" name="mdp" placeholder="****" required> </td>
                </tr>
                <tr><td colspan="2" align="center"><button type="submit" class="seconnecter">Se Connecter</button></td>
                </tr>
                <tr>
                    <td colspan="2" align="center" class="bas-formu">Vous n'avez pas de compte ? <a href="inscription.php" class="lienn">Inscrivez vous </a></td>
                </tr>
            </table></center>
            </form>

            <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $email = htmlspecialchars($_POST["email"]);
                    $password = $_POST["mdp"];
                    

                    // Vérification des identifiants
                    $file = '../json/data1.json';
                    if (file_exists($file)) {
                        $users = json_decode(file_get_contents($file), true);

                        foreach ($users as &$user) {
                            if ($user["email"] === $email && password_verify($password, $user["mdp"])) {
                                // L'utilisateur est authentifié youhouu 

                                $user["derniere_connexion"] = date("Y-m-d H:i:s");

                                file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
                                

                                $_SESSION['user'] = $user; // Enregistrer toutes les informations de l'utilisateur
                                $_SESSION['user_id'] = $user["email"]; // Stocker l'utilisateur connecté
                                $_SESSION['role'] = $user["role"];
                                
                                header("Location: profil.php"); // Rediriger vers le profil
                                exit;
                            }
                        }
                    }

                    echo "<p>Identifiants incorrects.</p>";
                }
            ?>


        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>