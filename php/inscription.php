<?php session_start();
    date_default_timezone_set("Europe/Paris");
    if (isset($_POST['nom'])){
        $file_content= file_get_contents("../json/data1.json");
        $users=json_decode($file_content, true);


        foreach ($users as $user) {
            if (strtolower($user["email"]) == strtolower($_POST["email"])) {
                exit;
                header("Location: inscription.php"); // Arrêter l'exécution si un utilisateur avec ce mail existe déjà
            }
            header("Location: profil.php");
        }

    }
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
                <td><button id="theme-button" onclick="switchTheme()" class="navi-button">🌙 Mode Sombre</button></td>
            </tr>
        </table></center></br></br></br>

        <form  method="post" enctype="multipart/form-data">
            <center><table class="inscrire">
                <tr></tr>
            <tr>
                <th colspan="2"><h2>Formulaire d'Inscription</h2></br></th>
            </tr>
            
            <tr>
                <td colspan="2" align="left"> 
                    <input type="radio" name="genre" value="femme" required> Mme
                    <input type="radio" name="genre" value="homme" required> M.
                    <input type="radio" name="genre" value="pasprécisé"required> Non Précisé
                </td>
            </tr>
            <tr><td align="left"> <label for="nom"> Nom :</label></td>
                <td> <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required></td>
            </tr>
            <tr><td> <label for="prenom" > Prénom :</label></td>
                <td> <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" required></td>
            </tr>
            <tr>
                <td><label for="email" > Email :</label></td>
                <td><input type="text" id="email" name="email" placeholder="Entrez votre email" required></td>
            </tr>
            <tr>
                <td><label for="num"> N° Tel :</label></td>
                <td><input type="tel" id="num" name="num" placeholder="06..." pattern="[0-9]*" inputmode="numeric" maxlength="10" required></td>
            </tr>
            <tr>
                <td><label for="mdp"> Mot de passe :</label></td>
                <td><input type="password" id="mdp" name="mdp" placeholder="****" required></td>
            </tr>
            <tr>
                <td colspan="2" align="center"> <button type="submit" class="seconnecter">S'inscrire </button></td>
            </tr>
            <tr>
                <td colspan="2" align="center"> <button type="reset" class="ressset">Reset </button></td>
            </tr>
            <tr>
                <td colspan="2" align="center" class="bas-formu">Déjà inscrit ? <a href="connexion.php" class="lienn">Connectez vous </a></td>
            </tr>
            </table></center>
        </form>
        <br><br>

        <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Récupération des champs du formulaire
        $genre = isset($_POST["genre"]) ? htmlspecialchars($_POST["genre"]) : "Non renseigné";
        $nom = isset($_POST["nom"]) ? htmlspecialchars($_POST["nom"]) : "Non renseigné";
        $prenom = isset($_POST["prenom"]) ? htmlspecialchars($_POST["prenom"]) : "Non renseigné";
        $numero = isset($_POST["num"]) ? htmlspecialchars($_POST["num"]) : "Non renseigné";
        $email = isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : "Non renseigné";
        $password = isset($_POST["mdp"]) ? password_hash($_POST["mdp"], PASSWORD_DEFAULT) : "Non renseigné";
        $role = "normal";

        // Vérifier si l'email existe déjà

       

        // Validation de l'email
        /*if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Email invalide";
            exit;
        }   
            ÇA NE VEUT PAS FONCTIONNER MAIS CE SERAIT CHOUETTE QUE CA FONCTIONNE 
        */
        
        // Création d'un tableau associatif avec les données
        $userData = [
            "genre" => $genre,
            "nom" => $nom,
            "prenom" => $prenom,
            "num" => $numero,
            "email" => $email,
            "mdp" => $password,
            "role" => $role,
            "date_inscription" => date("Y-m-d"),
            "derniere_connexion" => date("Y-m-d H:i:s"),
        ];

        // Lecture des données existantes du fichier JSON
        $file = '../json/data1.json';
        $dataArray = [];

        if (file_exists($file)) {
            $jsonContent = file_get_contents($file);
            $dataArray = json_decode($jsonContent, true); // Convertir JSON en tableau PHP
            if (!is_array($dataArray)) {
                $dataArray = [];
            }
        }

        // Ajouter les nouvelles données
        $dataArray[] = $userData;

        // Convertir en JSON et enregistrer dans le fichier
        file_put_contents($file, json_encode($dataArray, JSON_PRETTY_PRINT));

        // Enregistrer les informations dans la session
        $_SESSION['user'] = $userData;
        $_SESSION['user_id'] = $email; // Identifiant de session
        $_SESSION['role'] = $userData['role'];

        // Rediriger vers la page de profil
        // header("Location: profil.php");
        exit;

        }
        ?>

        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>