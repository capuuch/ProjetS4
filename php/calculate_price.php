<?php
// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'total' => 0,
    'details' => []
];

// Check if the request is POST and has JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Méthode non autorisée. Utilisez POST.';
    echo json_encode($response);
    exit;
}

// Get JSON data from the request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data === null) {
    $response['message'] = 'Données JSON invalides';
    echo json_encode($response);
    exit;
}

// Define the path to the JSON files
$etapes_file = '../json/etapes.json';

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

// Variables for calculation
$total = 0;
$details = [];
$nb_personnes = isset($data['options_generales']['nb_pers']) ? (int)$data['options_generales']['nb_pers'] : 1;
if ($nb_personnes < 1) $nb_personnes = 1;

// Calculate base price for each etape and add option prices
foreach ($data['etapes'] as $etape_id => $options) {
    // Find the etape in the database
    $etape_found = null;
    foreach ($etapes as $etape) {
        if ((string)$etape['etape_id'] === (string)$etape_id) {
            $etape_found = $etape;
            break;
        }
    }
    
    if ($etape_found) {
        $etape_price = isset($etape_found['prix']) ? (float)$etape_found['prix'] : 0;
        $etape_total = $etape_price;
        
        // Add price for activity option
        $activity = isset($options['activite']) ? $options['activite'] : '';
        if (!empty($activity) && isset($prix_options['activité'][$activity])) {
            $activity_price = $prix_options['activité'][$activity];
            $etape_total += $activity_price * $nb_personnes;
        }
        
        // Add price for accommodation option
        $accommodation = isset($options['hebergement']) ? $options['hebergement'] : '';
        if (!empty($accommodation) && isset($prix_options['hebergement'][$accommodation])) {
            $accommodation_price = $prix_options['hebergement'][$accommodation];
            $etape_total += $accommodation_price;
        }
        
        // Add price for transport option
        $transport = isset($options['transport']) ? $options['transport'] : '';
        if (!empty($transport) && isset($prix_options['transport'][$transport])) {
            $transport_price = $prix_options['transport'][$transport];
            $etape_total += $transport_price * $nb_personnes;
        }
        
        // Add to total
        $total += $etape_total;
        
        // Store details for display
        $details[$etape_id] = [
            'titre' => $etape_found['titre'],
            'prix_base' => $etape_price,
            'prix_activite' => !empty($activity) && isset($prix_options['activité'][$activity]) ? $prix_options['activité'][$activity] * $nb_personnes : 0,
            'prix_hebergement' => !empty($accommodation) && isset($prix_options['hebergement'][$accommodation]) ? $prix_options['hebergement'][$accommodation] : 0,
            'prix_transport' => !empty($transport) && isset($prix_options['transport'][$transport]) ? $prix_options['transport'][$transport] * $nb_personnes : 0,
            'total_etape' => $etape_total
        ];
    }
}

// Calculate additional costs from general options
$options_generales = $data['options_generales'] ?? [];

// Additional cost for insurance
$assurance_price = 0;
if (isset($options_generales['assurance']) && $options_generales['assurance']) {
    // Calculate insurance as 5% of total cost
    $assurance_price = $total * 0.05;
    $total += $assurance_price;
}

// Additional cost for meals
$restauration_price = 0;
if (isset($options_generales['restauration'])) {
    switch($options_generales['restauration']) {
        case 'Petit-déjeuner':
            $restauration_price = 15 * $nb_personnes * count($data['etapes']);
            break;
        case 'Demi-pension':
            $restauration_price = 40 * $nb_personnes * count($data['etapes']);
            break;
        case 'Pension complète':
            $restauration_price = 70 * $nb_personnes * count($data['etapes']);
            break;
        default:
            $restauration_price = 0;
    }
    $total += $restauration_price;
}

// Build response
$response = [
    'success' => true,
    'message' => 'Prix calculé avec succès',
    'total' => $total,
    'details' => $details,
    'nb_personnes' => $nb_personnes,
    'assurance' => $assurance_price,
    'restauration' => [
        'type' => $options_generales['restauration'] ?? 'Aucune',
        'prix' => $restauration_price
    ]
];

echo json_encode($response);
exit;
?>
