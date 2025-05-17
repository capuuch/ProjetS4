<?php session_start(); ?>
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
                    <td><a href="panier.php" class="navi">Panier</td>
                    <td><a href="profil.php"   class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <td><a href="admin.php" class="navi">Admin</a></td>
                <?php endif; ?>
                <td><button id="theme-button" onclick="switchTheme()" class="navi-button">🌙 Mode Sombre</button></td>

            </tr>
            </table></center>
        </br></br></br></br></br></br>
        
        <div class="c1">
            <span class="bi">Bienvenue sur notre site de Randonnée</span><br/>
            <span class="bi">au </span>  <a href="https://www.canada.ca/fr.html" target="_blank" class="canadaa"><span class="ca"> Canada</span><!--<span class="na">na</span><span class="ca">da</span>--></a>
            <img src="https://usagif.com/wp-content/uploads/gifs/canada-flag-24.gif" alt="drapeau du canada" height="70px" width="80px">
        </div>

        <div class="c1">
            <p>Explorez les merveilles du Grand Nord</p><br>
            <p class="parag">Que vous rêviez de paysages montagneux spectaculaires, de villes dynamiques ou de vastes étendues sauvages, le Canada a tout pour vous émerveiller. Découvrez des destinations inoubliables comme Toronto, les Rocheuses, les chutes du Niagara, Vancouver et bien plus encore !
            </p>
        </div>

        <div class="c1">
            <p>Préparez votre voyage facilement</p><br>
                <p class="parag">
                    ✈️ Recherchez les meilleures destinations<br>
                      Trouvez des conseils et astuces pour votre séjour<br>
                     Planifiez avec nous votre itinéraire idéal grâce à notre map 📍<br>
                </p>
        </div>

        <div class="c1">
            <p>Votre aventure commence <a href="voyages.php" class="ici">ici</a> ! </p><br>
            <p class="parag"> 🏔️ Laissez-vous inspirer et commencez à explorer dès maintenant ! 🌲</p>
        </div>

        <div class="avistitre">
            <p>⭐️ Avis de nos voyageurs ⭐️</p><br><br>
        </div>
        <center><div class="etoiles">
                <div class="avis">
                    <p class="blabla">"An amazing trip! We had a dream vacation"</p>
                    <p> ⭐️⭐️⭐️⭐️⭐️ </p>
                    <div class="author-info">
                        <h3>Eminem</h3>
                        <p class="author-location">United-states</p>
                    </div>
                </div>
                <div class="avis">
                    <p class="blabla">"Incroyage parfaitement organisé ! J'ai adoré chaque moment de mon séjour !"</p>
                    <p> ⭐️⭐️⭐️⭐️⭐️ </p>    
                    <div class="author-info">
                        <h3>Jonathan Cohen</h3>
                        <p class="author-location">France</p>
                    </div>
                </div>
                <div class="avis">
                    <p class="blabla">"I highly recommend it! Unforgettable experience!" </p>
                    <p> ⭐️⭐️⭐️⭐️⭐️ </p>
                    <div class="author-info">
                        <h3> Serena Williams </h3>
                        <p class="author-location">United-states</p>
                    </div>
                </div>
        </div></center>
        
        <div class="c1">
            <p><a href="presentation.php" class="presentationn"> Rechercher un voyage</a> | <a href="inscription.php" class="presentationn">S'inscrire</a></p>
        </div>


        <!-- Pied de page -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>

</html>