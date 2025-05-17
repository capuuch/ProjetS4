<?php 
    session_start(); 
    date_default_timezone_set("Europe/Paris");
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

        // If credentials are incorrect, set a message to be displayed later
        $errorMessage = "Identifiants incorrects.";
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
                <?php if (isset($errorMessage)): ?>
                <tr>
                    <td colspan="2" align="center" style="color: red;"><?php echo $errorMessage; ?></td>
                </tr>
                <?php endif; ?>
            </table></center>
            </form>

           

        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>