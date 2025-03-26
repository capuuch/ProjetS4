<?php 
    session_start(); 
    $etapes_file='etapes.json';
    $voyages = json_decode(file_get_contents('voyages.json'), true);
    $etapes = json_decode(file_get_contents('etapes.json'), true);
    

    // Recuperer le mot-cle
    $motCle = isset($_GET['recherche']) ? strtolower(trim($_GET['recherche'])) : '';

    // Initialiser un tableau pour stocker les resultats
    $resultats = [];

    // Verifier si l'utilisateur a appuye sur le bouton "Rechercher"
    $formulaireSoumis = isset($_GET['rechercher']) || isset($_GET['recherche']);

    if ($formulaireSoumis) {
        if ($motCle !== '') {

            $mots = explode(' ', $motCle);// Séparer le mot-clé en mots individuels
            $voyagesAjoutes = [];  // Tableau pour suivre les voyages déjà ajoutés aux résultats

            foreach ($mots as $mot) {
                // Vérification si la clé existe avant d'utiliser la valeur
                foreach ($voyages as $voyage) {
                    // Si ce voyage n'a pas déjà été ajouté aux résultats, on continue à chercher
                    if (!in_array($voyage, $voyagesAjoutes)) {
                        // Vérification si le mot-clé correspond à l'un des champs du voyage
                        $titre = isset($voyage['titre']) ? strtolower($voyage['titre']) : '';
                        $saison = isset($voyage['saison']) ? strtolower($voyage['saison']) : '';
                        $prix = isset($voyage['prix']) ? strtolower($voyage['prix']) : '';
                        if (file_exists($etapes_file)) {
                            $json_content = file_get_contents($etapes_file);
                            $etapes = json_decode($json_content, true);
                            
                            if (!is_array($etapes)) {
                                $etapes = []; 
                            }
                        } else {
                            $etapes = [];
                        }
                        $description = isset($voyage['description']) ? strtolower($voyage['description']) : '';

                        // Si un des champs du voyage correspond au mot-clé
                        if (
                            strpos($titre, $mot) !== false ||
                            strpos($saison, $mot) !== false ||
                            strpos($prix, $mot) !== false ||
                            strpos($description, $mot) !== false
                        ) {
                            // Ajouter ce voyage aux résultats
                            $resultats[] = $voyage;
                            // Ajouter ce voyage à la liste des voyages ajoutés pour éviter les doublons
                            $voyagesAjoutes[] = $voyage;
                        }
                    }
                }
            }
        } else {
            // Si aucun mot-clé, on affiche une sélection aléatoire de 5 voyages
            shuffle($voyages);
            $resultats = array_slice($voyages, 0, 5);
            $motCle = '';
        }

        if (empty($resultats)) {
            shuffle($voyages);
            $resultats = array_slice($voyages, 0, 5);
            $motCle = '';
        }
    }

    //pagination
    $voyagesPerPage = 3; // Nb max d’utilisateurs par page
    $totalvoyages = count($resultats);
    $totalPages = ceil($totalvoyages / $voyagesPerPage);
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $start = ($page - 1) * $voyagesPerPage;
    $voyagesToShow = array_slice($resultats, $start, $voyagesPerPage);

?>
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
                <form action="" method="GET">
                    <label for="recherche"></label>
                    <input type="search" id="recherche" name="recherche" placeholder="Saisir vos envies... " class="searchh" value="<?= htmlspecialchars($motCle) ?>">
                    <button type="submit" name="rechercher" class="btn_search">Rechercher</button><br>
                </form>
                <p class="press"> Une destination, un voyage...</p>
            </div>

            <?php if ($formulaireSoumis): ?>
                <?php if ($motCle !== ''): ?>
                    <h2>Résultats pour "<?= htmlspecialchars($motCle) ?>"</h2>
                <?php else: ?>
                    <h2>Voyages suggérés</h2>
                <?php endif; ?>

                <table class="suggestion">
                    <?php if (empty($resultats)): ?>
                            <p>Aucun voyage trouvé.</p>
                    <?php else: ?>
                        
                        <?php foreach ($voyagesToShow as $voyage): ?>
                            <tr class="suggestions">
                                <td><strong><?= htmlspecialchars($voyage['titre']) ?></strong></td>
                                <td>Saison : <?= htmlspecialchars($voyage['saison']) ?></td>
                                <td>Prix : <?= htmlspecialchars($voyage['prix']) ?>€</td>
                                <td>Étapes : 
                                    <?php
                                        // Vérifier si le voyage a des étapes associées
                                        if (!empty($voyage['etapes_ids'])) {
                                            $etapes_titles = [];

                                            // Parcourir toutes les étapes et récupérer celles du voyage en cours
                                            foreach ($etapes as $etape) {
                                                if (in_array($etape['etape_id'], $voyage['etapes_ids'])) {
                                                    $etapes_titles[] = htmlspecialchars($etape['titre']);
                                                }
                                            }

                                            // Afficher les titres des étapes séparés par une virgule
                                            echo implode(', ', $etapes_titles);
                                        } else {
                                            echo "Aucune étape associée";
                                        }
                                    ?>
                                </td>
                                <td><a href="voyages.php#v<?= $voyage['voyage_id'] ?>">Voir les détails</a></td>
                            </tr>
                        <?php endforeach; ?>
                        
                    <?php endif; ?>
                </table>

                <center>
                <span class="pagee">
                    <?php if ($page > 1): ?>
                        <a href="?recherche=<?= urlencode($motCle) ?>&page=<?= $page - 1; ?>" class="barre">Précédent</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?recherche=<?= urlencode($motCle) ?>&page=<?= $i; ?>" class="<?php if ($i === $page) echo 'active'; else echo 'barre'; ?>"><?= $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?recherche=<?= urlencode($motCle) ?>&page=<?= $page + 1; ?>" class="barre">Suivant</a>
                    <?php endif; ?>
                </span>
                </center><br><br><br>
            <?php endif; ?>

            <div class="normalA">
                <p class="titress">Vous souhaitez découvrir le Canada 🇨🇦</p>
                <p class="parag">Ce site vous propose un itinéraire idéal au Canada. Nous espérons qu'il vous plaira ! </p><br>
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
