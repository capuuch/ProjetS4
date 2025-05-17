<?php
session_start();
header('Content-Type: application/json'); // Set content type to JSON for AJAX responses

// Security check - Uncomment this in production
/*if (empty($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    echo json_encode([
        'success' => false,
        'message' => "Accès non autorisé. Vous devez être administrateur pour effectuer cette action."
    ]);
    exit;
}*/

// Simulate server latency (2-3 seconds)
sleep(2);

// Load user data from JSON file
$file = '../json/data1.json';

// Check for alternative paths if file doesn't exist
if (!file_exists($file)) {
    $alternativePaths = [
        './json/data1.json',
        'json/data1.json',
        '../../json/data1.json',
        dirname(__FILE__) . '/../json/data1.json'
    ];
    
    $foundPath = false;
    foreach ($alternativePaths as $altPath) {
        if (file_exists($altPath)) {
            $file = $altPath;
            $foundPath = true;
            break;
        }
    }
    if (!$foundPath) {
        echo json_encode([
            'success' => false,
            'message' => "Erreur critique: Impossible de trouver le fichier de données utilisateurs."
        ]);
        exit;
    }
}

// Check if the file is readable
if (!is_readable($file)) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur: Le fichier de données n'est pas lisible. Veuillez vérifier les permissions."
    ]);
    exit;
}

// Check if the file is writable
if (!is_writable($file)) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur: Le fichier de données n'est pas modifiable. Veuillez vérifier les permissions du fichier."
    ]);
    exit;
}

// Get existing user data
try {
    $jsonContent = file_get_contents($file);
    if ($jsonContent === false) {
        throw new Exception("Impossible de lire le contenu du fichier.");
    }
    
    $users = json_decode($jsonContent, true);
    if ($users === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Erreur de décodage JSON: " . json_last_error_msg());
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors du chargement des données utilisateurs: " . $e->getMessage()
    ]);
    exit;
}

// Get current user email for identification
$originalEmail = isset($_POST['original_email']) ? trim($_POST['original_email']) : '';

// Process form data
$genre = isset($_POST['genre']) ? trim($_POST['genre']) : '';
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
$num = isset($_POST['num']) ? trim($_POST['num']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';

// Validation errors array
$errors = [];

// Basic validation
if (empty($nom)) {
    $errors['nom'] = "Le nom est obligatoire.";
}

if (empty($prenom)) {
    $errors['prenom'] = "Le prénom est obligatoire.";
}

if (empty($email)) {
    $errors['email'] = "L'email est obligatoire.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Format d'email invalide.";
}

// Check if new email already exists for a DIFFERENT user
if ($email !== $originalEmail) {
    foreach ($users as $existingUser) {
        if ($existingUser['email'] === $email) {
            $errors['email'] = "Cette adresse email est déjà utilisée par un autre compte.";
            break;
        }
    }
}

// If there are validation errors, return them
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => "Des erreurs ont été détectées dans le formulaire.",
        'errors' => $errors
    ]);
    exit;
}

// Find and update the user
$userFound = false;
$updatedUser = null;
$originalValues = null;

foreach ($users as &$user) {
    if ($user['email'] === $originalEmail) {
        // Store original values before update
        $originalValues = [
            'genre' => $user['genre'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'num' => $user['num'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        
        // Update specific fields
        $user['genre'] = $genre;
        $user['nom'] = $nom;
        $user['prenom'] = $prenom;
        $user['num'] = $num;
        $user['email'] = $email;
        $user['role'] = $role;
        
        // Keep other fields unchanged
        $updatedUser = $user;
        $userFound = true;
        break;
    }
}

if (!$userFound) {
    echo json_encode([
        'success' => false,
        'message' => "Utilisateur non trouvé. Veuillez rafraîchir la page et réessayer."
    ]);
    exit;
}

// Write the updated data back to the JSON file
try {
    $jsonOutput = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($jsonOutput === false) {
        throw new Exception("Erreur d'encodage JSON: " . json_last_error_msg());
    }

    $result = file_put_contents($file, $jsonOutput);
    if ($result === false) {
        $error = error_get_last();
        throw new Exception("Échec de l'écriture dans le fichier. " . ($error['message'] ?? 'Raison inconnue. Vérifiez les logs du serveur.'));
    }
    
    // Return success response with updated user data
    echo json_encode([
        'success' => true,
        'message' => "Informations utilisateur mises à jour avec succès !",
        'user' => $updatedUser
    ]);
    exit;
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de la sauvegarde des données: " . $e->getMessage(),
        'originalValues' => $originalValues
    ]);
    exit;
}
?>
