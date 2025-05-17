<?php
// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Define the path to the JSON files
$etapes_file = '../json/etapes.json';
$options_file = '../json/options.json';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Check if etape_id is provided
if (!isset($_GET['etape_id'])) {
    $response['message'] = 'Paramètre etape_id manquant';
    echo json_encode($response);
    exit;
}

$etape_id = $_GET['etape_id'];

// Load etapes data
if (!file_exists($etapes_file)) {
    $response['message'] = 'Fichier etapes.json introuvable';
    echo json_encode($response);
    exit;
}

$etapes_json = file_get_contents($etapes_file);
$etapes = json_decode($etapes_json, true);

if ($etapes === null) {
    $response['message'] = 'Erreur de décodage du fichier etapes.json';
    echo json_encode($response);
    exit;
}

// Load options data
if (file_exists($options_file)) {
    $options_json = file_get_contents($options_file);
    $options = json_decode($options_json, true);
    
    if ($options === null) {
        $options = [];
    }
} else {
    $options = [];
}

// Define default options if not found in etapes
$default_options = [
    'activité' => [
        'Visite d\'un parc',
        'Ski ou Snowboard',
        'Randonnée hivernale',
        'Balade dans le parc',
        'Visite des galeries d\'art',
        'Découverte de la faune',
        'Visite guidée de la vieille ville',
        'Visite du quartier historique',
        'Randonnée dans le parc national',
        'Kayak dans le lac',
        'Aucune activité'
    ],
    'hebergement' => [
        'Hotel Luxe',
        'Chalet',
        'Auberge',
        'Camping',
        'Appartement',
        'Aucun hébergement'
    ],
    'transport' => [
        'Voiture',
        'Train',
        'Navette',
        'Bus',
        'Vélo',
        'Aucun transport'
    ]
];

// Define price lists for different options
$prix_options = [
    'activité' => [
        'Visite d\'un parc' => 20,
        'Ski ou Snowboard' => 80,
        'Randonnée hivernale' => 30,
        'Balade dans le parc' => 15,
        'Visite des galeries d\'art' => 25,
        'Découverte de la faune' => 35,
        'Visite guidée de la vieille ville' => 40,
        'Visite du quartier historique' => 30,
        'Randonnée dans le parc national' => 25,
        'Kayak dans le lac' => 45,
        'Survie en forêt' => 70,
        'Séance photo du paysage et des animaux' => 40,
        'Croisière sur le fleuve Niagara' => 120,
        'Visite des grottes' => 35,
        'Observation panoramique en hélicoptère' => 150,
        'Croisière pour observer les baleines' => 90,
        'Croisière dans les fjords' => 80,
        'Visite du centre-ville' => 25,
        'Visite du centre-ville et du musée' => 40,
        'Aucune activité' => 0
    ],
    'hebergement' => [
        'Hotel Luxe' => 200,
        'Hôtel avec vue sur les montagnes' => 150,
        'Chalet de montagne cosy' => 180,
        'Hôtel 3 étoiles' => 120,
        'Auberge de jeunesse' => 50,
        'Appartement en centre-ville' => 130,
        'Maison de vacances au bord de l\'eau' => 170,
        'Appartement avec vue sur la ville' => 140,
        'Hôtel design en centre-ville' => 160,
        'Hôtel familial' => 110,
        'Hôtel avec vue sur les chutes' => 190,
        'Hôtel 4 étoiles' => 170,
        'Hôtel de luxe' => 250,
        'Chalet en montagne' => 160,
        'Chalet' => 150,
        'Auberge' => 70,
        'Camping' => 40,
        'Camping sauvage' => 20,
        'Appartement' => 120,
        'Hôtel design' => 160,
        'Hôtel avec vue sur la mer' => 180,
        'Hôtel avec vue sur les fjords' => 190,
        'Aucun hébergement' => 0
    ],
    'transport' => [
        'Voiture' => 60,
        'Train' => 40,
        'Navette' => 30,
        'Navette privée' => 90,
        'Bus' => 25,
        'Vélo' => 15,
        'Métro' => 20,
        'Ferry' => 35,
        'Voiture de location' => 70,
        'RER B' => 15,
        'ligne 7' => 15,
        'Aucun transport' => 0
    ]
];

// Find the specific etape
$etape_found = false;
$etape_options = [];

foreach ($etapes as $etape) {
    if ((string)$etape['etape_id'] === (string)$etape_id) {
        $etape_found = true;
        
        // Get options from the etape if available
        if (isset($etape['options'])) {
            $etape_options = $etape['options'];
        }
        
        break;
    }
}

if (!$etape_found) {
    $response['message'] = 'Étape non trouvée';
    echo json_encode($response);
    exit;
}

// Collect all unique options from the options.json file
$all_activities = [];
$all_accommodations = [];
$all_transports = [];

foreach ($options as $username => $user_voyages) {
    foreach ($user_voyages as $voyage_id => $voyage_data) {
        if (isset($voyage_data['etapes']) && is_array($voyage_data['etapes'])) {
            foreach ($voyage_data['etapes'] as $e_id => $e_options) {
                if ((string)$e_id === (string)$etape_id) {
                    if (!empty($e_options['activité']) && !in_array($e_options['activité'], $all_activities)) {
                        $all_activities[] = $e_options['activité'];
                    }
                    if (!empty($e_options['hebergement']) && !in_array($e_options['hebergement'], $all_accommodations)) {
                        $all_accommodations[] = $e_options['hebergement'];
                    }
                    if (!empty($e_options['transport']) && !in_array($e_options['transport'], $all_transports)) {
                        $all_transports[] = $e_options['transport'];
                    }
                }
            }
        }
    }
}

// Merge with default options
$available_options = [
    'activité' => array_unique(array_merge($all_activities, $default_options['activité'])),
    'hebergement' => array_unique(array_merge($all_accommodations, $default_options['hebergement'])),
    'transport' => array_unique(array_merge($all_transports, $default_options['transport']))
];

// Add the etape's specific options if they exist
if (isset($etape_options['activité']) && !empty($etape_options['activité']) && !in_array($etape_options['activité'], $available_options['activité'])) {
    $available_options['activité'][] = $etape_options['activité'];
}
if (isset($etape_options['hebergement']) && !empty($etape_options['hebergement']) && !in_array($etape_options['hebergement'], $available_options['hebergement'])) {
    $available_options['hebergement'][] = $etape_options['hebergement'];
}
if (isset($etape_options['transport']) && !empty($etape_options['transport']) && !in_array($etape_options['transport'], $available_options['transport'])) {
    $available_options['transport'][] = $etape_options['transport'];
}

// Sort options alphabetically
sort($available_options['activité']);
sort($available_options['hebergement']);
sort($available_options['transport']);

// Add price information to each option
$options_with_prices = [
    'activité' => [],
    'hebergement' => [],
    'transport' => []
];

foreach ($available_options['activité'] as $activity) {
    $price = isset($prix_options['activité'][$activity]) ? $prix_options['activité'][$activity] : 0;
    $options_with_prices['activité'][] = [
        'name' => $activity,
        'price' => $price
    ];
}

foreach ($available_options['hebergement'] as $accommodation) {
    $price = isset($prix_options['hebergement'][$accommodation]) ? $prix_options['hebergement'][$accommodation] : 0;
    $options_with_prices['hebergement'][] = [
        'name' => $accommodation,
        'price' => $price
    ];
}

foreach ($available_options['transport'] as $transport) {
    $price = isset($prix_options['transport'][$transport]) ? $prix_options['transport'][$transport] : 0;
    $options_with_prices['transport'][] = [
        'name' => $transport,
        'price' => $price
    ];
}

// Build response
$response = [
    'success' => true,
    'message' => 'Options récupérées avec succès',
    'data' => [
        'etape_id' => $etape_id,
        'options' => $options_with_prices,
        'default' => $etape_options,
        'base_price' => isset($etape['prix']) ? (float)$etape['prix'] : 0
    ]
];

// Add a small delay to simulate server processing (for demonstration purposes)
usleep(300000); // 300ms delay

echo json_encode($response);
exit;
?>