<?php 
    session_start(); 
    $isConnected = isset($_SESSION['user_id']);

    $voyages_file = 'voyages.json';

    if (file_exists($voyages_file)) {
        $json_content = file_get_contents($voyages_file);
        $voyages = json_decode($json_content, true);
        
        if (!is_array($voyages)) {
            $voyages = []; 
        }
    } else {
        $voyages = [];
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="projetS4.css">
        <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">

        <title>Green Odyssey Voyages</title>

        <meta charset="UTF-8">
        <meta name="author" content="Anas_Capucine_Hadil"/>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- pour la map-->
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/> <!-- pour la map-->
       
    </head>

    <body>
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <div class="paysageV">
        <center><h1>Green Odyssey</h1></center>
        <table class="nav">
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
        </table>

        <form class="center">
            <div class="c1">
                <p class="titress">Commencez à naviguer pour trouver votre coup de coeur</p>
                <p class="parag">Vous pouvez ajouter les lieux qui vous intéressent le plus à vos Favoris, </p>
                <p class="parag">ou alors Commander dès maintenant si vous êtes sur de vous. </p>
                <p class="parag"> Sinon Swipez !</p>
                <button onclick="location.href='#v1'" class="choixClient">Naviguer</button>
            </div>
            </div>
        </form>

        <?php foreach ($voyages as $voyage): ?>
            
            <div class="rando" id="v<?= htmlspecialchars($voyage['voyage_id']); ?>" 
                style="background-image: url('<?= htmlspecialchars($voyage['image_fond']); ?>');">
                
                <h4><?= htmlspecialchars($voyage['titre']); ?></h4>

                <div class="interface">
                    <div class="divText">
                        <p><?= htmlspecialchars($voyage['description']); ?></p>
                    </div>
                    <div id="map<?= htmlspecialchars($voyage['voyage_id']); ?>" ></div>
                </div>

                <div class="details">
                    <p class="deetails"> Prix : <?= htmlspecialchars($voyage['prix'] ?? 'N/A'); ?></p>
                    <p class="deetails"> Etapes : 
                        
                    </p>
                    <p class="deetails"> Période : <?= htmlspecialchars($voyage['saison'] ?? 'Non spécifié'); ?></p>
                </div>

                <div class = choiceContainer>
                    
                    <?php if ($isConnected): ?>
                        <a href="etapes.php" class="choixClient">ETAPES</a>
                        <form method="POST" action="favoris.php">
                            <input type="hidden" name="voyage_nom" value="<?= htmlspecialchars($voyage['titre']); ?>">
                            <button type="submit" name="ajouter_favori" class="choixClient btn-favoris">💖</button>
                        </form>
                    <?php else: ?>
                        <a href="connexion.php" class="choixClient">ETAPES</a>
                        <a href="connexion.php" class="choixClient btn-favoris">💖</a>
                    <?php endif; ?>

                    <!-- Bouton Swipe : évite une erreur si c'est le dernier voyage -->
                    <?php if ($voyage !== end($voyages)): ?>
                        <button onclick="location.href='#v<?= htmlspecialchars($voyage['voyage_id'] + 1); ?>'" class="choixClient">Swipe</button>
                    <?php else: ?>
                        <button onclick="location.href='#v1'" class="choixClient">Swipe</button>
                    <?php endif; ?>
                </div>
                
            </div>
        <?php endforeach; ?>

        <script>
            function createMap(mapId, lat, lon, popupText) {
                var map = L.map(mapId).setView([lat, lon], 5);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                L.marker([lat, lon]).addTo(map).bindPopup(popupText);
            }

            createMap('map1', 48.54, -124.43, 'Juan de Fuca : Marine Trail'); 
            createMap('map2', 47.08, -71.40, 'Jacques-Cartier National Park'); 
            createMap('map3', 43.70, -79.42, 'Toronto'); 
            createMap('map4', 43.08, -79.08, 'Chutes du Niagara'); 
            createMap('map5', 53.93, -116.58, 'Jasper National Park, Alberta'); 
            createMap('map6', 51.43, -116.18, 'Rocky Mountains - Banff'); 
            createMap('map7', 49.58, -57.52, 'Parc national de Gros Morne');
            createMap('map8', 46.68, -60.84, 'Cape Breton Highlands National Park');

        </script> 
   
        <!-- Pied de page -->
        <footer>
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    
</html>
