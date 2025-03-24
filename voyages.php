<?php session_start(); 
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

        <div class="rando1">
            <h4 id="v1">Vancouver</h4>
            <br></br>
            <br></br>
            <br></br>
            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

            <div class="interface">
                
                <div class="divText"><u>Juan de Fuca : Marine Trail<br></br></u>
                    Si vous rêvez de nature brute, d’air marin vivifiant et de sentiers bordés de forêts majestueuses, 
                    <br></br>
                    alors le Juan de Fuca Marine Trail est fait pour vous ! Niché sur la côte ouest de l'île de
                    <br></br>
                    Vancouver, ce sentier de 47 km vous promet une aventure inoubliable entre 
                    <br></br>
                    plages sauvages, falaises escarpées et forêts anciennes.
                    <br></br>
                    <div class="photoContainer"><div class="divTextLeft">Que vous soyez un randonneur chevronné en quête de défi  
                        <br></br>ou un amoureux de la nature à la recherche de panoramas <br></br> grandioses, le Juan de Fuca Marine Trail est une immersion 
                        <br></br>totale dans la beauté sauvage de la Colombie-Britannique. <br></br>Commandez, ajoutez aux favoris ou passez !</div>
                        <div class="buttonPhotos">
                            <button onclick="location.href='...'" class="seconnecter"><</button>
                            <img  src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/e8/6e/13/hiking-to-mystic-beach.jpg?w=1200&h=-1&s=1" class="divPhotos">
                            <button onclick="location.href='...'" class="seconnecter">></button>
                        </div>
                    </div>
                </div>
                <div id="map"></div>
            </div>
            <div class="details">prix : 549$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nb de personnes (max) : 15&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Periode d'activié : toute l'année</div>
                <div class = choiceContainer>
                    <a href="options.html" class="choixClient">COMMANDER</a>
                    <?php foreach ($voyages as $voyage): ?>
                        <?php if (isset($voyage['titre']) && $voyage['titre'] === 'Vancouver'): ?> 
                        <?php if (isset($voyage['titre'])): ?> 
                                <div class="voyage">
                                    <?php if ($isConnected): ?>
                                        <form method="POST" action="favoris.php">
                                            <input type="hidden" name="voyage_nom" value="<?= $voyage['titre']; ?>">
                                            <button type="submit" name="ajouter_favori" class="choixClient">💖</button>
                                        </form>
                                    <?php else: ?>
                                        <a href="connexion.php"><button>💖</button></a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <button onclick="location.href='#v2'" class="choixClient">Swipe</button>
                </div>
        </div>
        
        <div class="rando2">
            <h4 id="v2">Quebec</h4>
            <br></br>
            <br></br>
            <br></br>

            <div class="interface">
                
                <div class="divText"><u>Jacques-Cartier National Park<br></br></u>
                    Si vous rêvez de paysages enchanteurs, d’air pur et de balades au cœur de forêts boréales, 
                    <br></br>
                    alors le parc national de la Jacques-Cartier est fait pour vous ! Situé à quelques kilomètres
                    <br></br>
                    de la ville de Québec, ce joyau naturel vous offre une aventure inoubliable entre vallées glaciaires, 
                    <br></br>
                    rivières tumultueuses et montagnes boisées.
                    <br></br>
                    <div class="photoContainer"><div class="divTextLeft">Si vous êtes un passionné de randonnée à la recherche  
                        <br></br>de sentiers immersifs ou un amateur de plein air <br></br> souhaitant pagayer sur la majestueuse rivière Jacques-Cartier, 
                        <br></br>ce parc est une invitation à la découverte de la nature sauvage <br></br>Commandez, ajoutez aux favoris ou passez !</div>
                        <div class="buttonPhotos">
                            <button onclick="location.href='...'" class="seconnecter"></button>
                            <img  src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/e8/6e/13/hiking-to-mystic-beach.jpg?w=1200&h=-1&s=1" class="divPhotos">
                            <button onclick="location.href='...'" class="seconnecter"></button>
                        </div>
                    </div>
                </div>
                <div id="map2"></div>
            </div>
            <div class="details">prix : 1229$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nb de personnes (max) : 15&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Periode d'activié : toute l'année</div>
                <div class = choiceContainer>
                    <a href="options.html" class="choixClient">COMMANDER</a>
                    <form method="post" action="favoris.php">
                        <input type="hidden" name="voyage" value="Quebec"> 
                        <button type="submit" class="choixClient">💖</button>
                    </form>

                    <button onclick="location.href='#v3'" class="choixClient">Swipe</button>
                </div>
        </div>

        <div class="rando3">
            <h4 id="v3">Toronto</h4>
            <br></br>
            <br></br>
            <br></br>

            <div class="interface">
                <div class="divText"><u>Îles de Toronto : Évasion Nature<br></br></u>
                    Si vous cherchez un havre de paix à quelques minutes du tumulte urbain, 
                    <br></br>
                    les îles de Toronto sont faites pour vous ! Situé sur le lac Ontario, cet archipel offre 
                    <br></br>
                    une escapade idyllique entre plages paisibles, sentiers boisés et vues imprenables sur la skyline de la ville.
                    <br></br>
                    <div class="photoContainer"><div class="divTextLeft">Que vous soyez un amateur de balades à vélo,  
                        <br></br>un passionné de kayak ou simplement en quête d’un moment de détente en pleine nature,<br></br> es îles de Toronto sont une invitation à la découverte et à la sérénité au cœur de l’Ontario.
                        <br></br>Commandez, ajoutez aux favoris ou passez !</div>
                        <div class="buttonPhotos">
                            <button onclick="location.href='...'" class="seconnecter"><</button>
                            <img  src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/e8/6e/13/hiking-to-mystic-beach.jpg?w=1200&h=-1&s=1" class="divPhotos">
                            <button onclick="location.href='...'" class="seconnecter">></button>
                        </div>
                    </div>
                </div>
                <div id="map3"></div>
            </div>

            <div class="details">prix : 979$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nb de personnes (max) : 15&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Periode d'activié : toute l'année</div>
                <div class = choiceContainer>
                    <a href="options.html" class="choixClient">COMMANDER</a>
                    <form method="post" action="favoris.php">
                        <input type="hidden" name="voyage" value="Toronto"> 
                        <button type="submit" class="choixClient">💖</button>
                    </form>

                    <button onclick="location.href='#v4'" class="choixClient">Swipe</button>
                </div>
        </div>

        <div class="rando4">
            <h4 id="v4">Niagara</h4>
            <br></br>
            <br></br>
            <br></br>

            <div class="interface">
                <div class="divText"><u>Juan de Fuca : Marine Trail<br></br></u>
                    Si vous rêvez de nature brute, d’air marin vivifiant et de sentiers bordés de forêts majestueuses, 
                    <br></br>
                    alors le Juan de Fuca Marine Trail est fait pour vous ! Niché sur la côte ouest de l'île de
                    <br></br>
                    Vancouver, ce sentier de 47 km vous promet une aventure inoubliable entre plages sauvages, falaises 
                    <br></br>
                    escarpées et forêts anciennes.
                    <br></br>
                    <div class="photoContainer"><div class="divTextLeft">Que vous soyez un randonneur chevronné en quête de défi  
                        <br></br>ou un amoureux de la nature à la recherche de panoramas <br></br> grandioses, le Juan de Fuca Marine Trail est une immersion 
                        <br></br>totale dans la beauté sauvage de la Colombie-Britannique. <br></br>Commandez, ajoutez aux favoris ou passez !</div>
                        <div class="buttonPhotos">
                            <button onclick="location.href='...'" class="seconnecter"><</button>
                            <img  src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/e8/6e/13/hiking-to-mystic-beach.jpg?w=1200&h=-1&s=1" class="divPhotos">
                            <button onclick="location.href='...'" class="seconnecter">></button>
                        </div>
                    </div>
                </div>
                <div id="map4"></div>
            </div>

            <div class="details">prix : 1379$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nb de personnes (max) : 15&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Periode d'activié : toute l'année</div>
                <div class = choiceContainer>
                    <a href="options.html" class="choixClient">COMMANDER</a>
                    <form method="post" action="favoris.php">
                        <input type="hidden" name="voyage" value="Niagara"> 
                        <button type="submit" class="choixClient">💖</button>
                    </form>

                    <button onclick="location.href='#v5'" class="choixClient">Swipe</button>
                </div>
        </div>

        <div class="rando5">
            <h4 id="v5">Alberta</h4>
            <br></br>
            <br></br>
            <br></br>

            <div class="interface">
                <div class="divText"><u>Jasper National Park<br></br></u>
                    Si vous rêvez de nature brute, d’air marin vivifiant et de sentiers bordés de forêts majestueuses, 
                    <br></br>
                    alors le Juan de Fuca Marine Trail est fait pour vous ! Niché sur la côte ouest de l'île de
                    <br></br>
                    Vancouver, ce sentier de 47 km vous promet une aventure inoubliable entre plages sauvages, falaises 
                    <br></br>
                    escarpées et forêts anciennes.
                    <br></br>
                    <div class="photoContainer"><div class="divTextLeft">Que vous soyez un randonneur chevronné en quête de défi  
                        <br></br>ou un amoureux de la nature à la recherche de panoramas <br></br> grandioses, le Juan de Fuca Marine Trail est une immersion 
                        <br></br>totale dans la beauté sauvage de la Colombie-Britannique. <br></br>Commandez, ajoutez aux favoris ou passez !</div>
                        <div class="buttonPhotos">
                            <button onclick="location.href='...'" class="seconnecter"><</button>
                            <img  src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/e8/6e/13/hiking-to-mystic-beach.jpg?w=1200&h=-1&s=1" class="divPhotos">
                            <button onclick="location.href='...'" class="seconnecter">></button>
                        </div>
                    </div>
                </div>
                <div id="map5"></div>
            </div>

            <div class="details">prix : 1569$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nb de personnes (max) : 15&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Periode d'activié : toute l'année</div>
                <div class = choiceContainer>
                    <a href="options.html" class="choixClient">COMMANDER</a>
                    <form method="post" action="favoris.php">
                        <input type="hidden" name="voyage" value="Alberta"> 
                        <button type="submit" class="choixClient">💖</button>
                    </form>

                    <button onclick="location.href='#v6'" class="choixClient">Swipe</button>
                </div>
        </div>

        <div class="rando6">
            <h4 id="v6">Rocky Mountains</h4>
            <br></br>
            <br></br>
            <br></br>

            <div class="interface">
                <div class="divText"><u>Je sais pas<br></br></u>
                    Si vous rêvez de nature brute, d’air marin vivifiant et de sentiers bordés de forêts majestueuses, 
                    <br></br>
                    alors le Juan de Fuca Marine Trail est fait pour vous ! Niché sur la côte ouest de l'île de
                    <br></br>
                    Vancouver, ce sentier de 47 km vous promet une aventure inoubliable entre plages sauvages, falaises 
                    <br></br>
                    escarpées et forêts anciennes.
                    <br></br>
                    <div class="photoContainer"><div class="divTextLeft">Que vous soyez un randonneur chevronné en quête de défi  
                        <br></br>ou un amoureux de la nature à la recherche de panoramas <br></br> grandioses, le Juan de Fuca Marine Trail est une immersion 
                        <br></br>totale dans la beauté sauvage de la Colombie-Britannique. <br></br>Commandez, ajoutez aux favoris ou passez !</div>
                        <div class="buttonPhotos">
                            <button onclick="location.href='...'" class="seconnecter"><</button>
                            <img  src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/e8/6e/13/hiking-to-mystic-beach.jpg?w=1200&h=-1&s=1" class="divPhotos">
                            <button onclick="location.href='...'" class="seconnecter">></button>
                        </div>
                    </div>
                </div>
                <div id="map6"></div>
            </div>

            <div class="details">prix : 949$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nb de personnes (max) : 15&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Periode d'activié : toute l'année</div>
                <div class = choiceContainer>
                    <a href="options.html" class="choixClient">COMMANDER</a>
                    <form method="post" action="favoris.php">
                        <input type="hidden" name="voyage" value="Rocky Mountains"> 
                        <button type="submit" class="choixClient">💖</button>
                    </form>

                    <button onclick="location.href='#v7'" class="choixClient">Swipe</button>
                </div>
        </div>

        <div class="rando7">
            <h4 id="v7">Terre-Neuve-et-Labrador</h4>
            <br></br>
            <br></br>
            <br></br>

            <div class="interface">
                <div class="divText"><u>Parc national de Gros Morne<br></br></u>
                    Si vous rêvez de nature brute, d’air marin vivifiant et de sentiers bordés de forêts majestueuses, 
                    <br></br>
                    alors le Juan de Fuca Marine Trail est fait pour vous ! Niché sur la côte ouest de l'île de
                    <br></br>
                    Vancouver, ce sentier de 47 km vous promet une aventure inoubliable entre plages sauvages, falaises 
                    <br></br>
                    escarpées et forêts anciennes.
                    <br></br>
                    <div class="photoContainer"><div class="divTextLeft">Que vous soyez un randonneur chevronné en quête de défi  
                        <br></br>ou un amoureux de la nature à la recherche de panoramas <br></br> grandioses, le Juan de Fuca Marine Trail est une immersion 
                        <br></br>totale dans la beauté sauvage de la Colombie-Britannique. <br></br>Commandez, ajoutez aux favoris ou passez !</div>
                        <div class="buttonPhotos">
                            <button onclick="location.href='...'" class="seconnecter"><</button>
                            <img  src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/e8/6e/13/hiking-to-mystic-beach.jpg?w=1200&h=-1&s=1" class="divPhotos">
                            <button onclick="location.href='...'" class="seconnecter">></button>
                        </div>
                    </div>
                </div>
                <div id="map7"></div>
            </div>

            <div class="details">prix : 1299$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nb de personnes (max) : 15&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Periode d'activié : toute l'année</div>
                <div class = choiceContainer>
                    <a href="options.html" class="choixClient">COMMANDER</a>
                    <form method="post" action="favoris.php">
                        <input type="hidden" name="voyage" value="Terre-Neuve-et-Labrador"> 
                        <button type="submit" class="choixClient">💖</button>
                    </form>

                    <button onclick="location.href='#v8'" class="choixClient">Swipe</button>
                </div>
        </div>

        <div class="rando8">
            <h4 id="v8">Cape Breton Highlands National Park</h4>
            <br></br>
            <br></br>
            <br></br>

            <div class="interface">
                <div class="divText"><u>Parc national de Gros Morne<br></br></u>
                    Si vous rêvez de nature brute, d’air marin vivifiant et de sentiers bordés de forêts majestueuses, 
                    <br></br>
                    alors le Juan de Fuca Marine Trail est fait pour vous ! Niché sur la côte ouest de l'île de
                    <br></br>
                    Vancouver, ce sentier de 47 km vous promet une aventure inoubliable entre plages sauvages, falaises 
                    <br></br>
                    escarpées et forêts anciennes.
                    <br></br>
                    <div class="photoContainer"><div class="divTextLeft">Que vous soyez un randonneur chevronné en quête de défi  
                        <br></br>ou un amoureux de la nature à la recherche de panoramas <br></br> grandioses, le Juan de Fuca Marine Trail est une immersion 
                        <br></br>totale dans la beauté sauvage de la Colombie-Britannique. <br></br>Commandez, ajoutez aux favoris ou passez !</div>
                        <div class="buttonPhotos">
                            <button onclick="location.href='...'" class="seconnecter"><</button>
                            <img  src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/e8/6e/13/hiking-to-mystic-beach.jpg?w=1200&h=-1&s=1" class="divPhotos">
                            <button onclick="location.href='...'" class="seconnecter">></button>
                        </div>
                    </div>
                </div>
                <div id="map8"></div>
            </div>

            <div class="details">prix : 879$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;nb de personnes (max) : 15&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Periode d'activié : toute l'année</div>
                <div class = choiceContainer>
                    <a href="options.html" class="choixClient">COMMANDER</a>
                    <form method="post" action="favoris.php">
                        <input type="hidden" name="voyage" value="Cape Breton Highlands National Park"> 
                        <button type="submit" class="choixClient">💖</button>
                    </form>

                    <button onclick="location.href='#v1'" class="choixClient">Swipe</button>
                </div>
            </div>

        <script>
            function createMap(mapId, lat, lon, popupText) {
                var map = L.map(mapId).setView([lat, lon], 5);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                L.marker([lat, lon]).addTo(map).bindPopup(popupText);
            }

            createMap('map', 48.54, -124.43, 'Juan de Fuca : Marine Trail'); 
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









