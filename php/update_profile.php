<?php
session_start();
header('Content-Type: application/json'); // Set content type to JSON for AJAX responses

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    echo json_encode([
        'success' => false,
        'message' => "Vous devez être connecté pour accéder à cette page."
    ]);
    exit;
}

// Define the user data file path
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

// Get current user email for identification (this is the email BEFORE any changes)
$currentUserEmail = $_SESSION['user']['email']; 
// Get original user_id, assuming it might be different from email or you want to preserve original one if not email
$currentUserId = $_SESSION['user_id'];

// Process form data
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : $_SESSION['user']['nom'];
$prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : $_SESSION['user']['prenom'];
$email = isset($_POST['email']) ? trim($_POST['email']) : $_SESSION['user']['email'];
$num = isset($_POST['num']) ? trim($_POST['num']) : ($_SESSION['user']['num'] ?? '');
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

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
if ($email !== $currentUserEmail) {
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
$updatedUserSessionData = null;

foreach ($users as &$userEntry) {
    if ($userEntry['email'] === $currentUserEmail) {
        // Store original values before update
        $originalValues = [
            'nom' => $userEntry['nom'],
            'prenom' => $userEntry['prenom'],
            'email' => $userEntry['email'],
            'num' => $userEntry['num'] ?? ''
        ];
        
        // Update specific fields
        $userEntry['nom'] = $nom;
        $userEntry['prenom'] = $prenom;
        $userEntry['email'] = $email;
        $userEntry['num'] = $num;
        
        // Update password only if a new one is provided
        if (!empty($password)) {
            $userEntry['mdp'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $updatedUserSessionData = $userEntry;
        $userFound = true;
        break;
    }
}

if (!$userFound) {
    echo json_encode([
        'success' => false,
        'message' => "Utilisateur non trouvé. Votre session pourrait être invalide. Veuillez vous reconnecter."
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
    
    // Update the session data with new user info
    $_SESSION['user'] = $updatedUserSessionData;
    
    // Update user_id in session IF it's tied to the email and the email has changed
    if ($email !== $currentUserEmail && $_SESSION['user_id'] === $currentUserEmail) {
        $_SESSION['user_id'] = $email;
    }
    
    // Return success response with updated user data
    echo json_encode([
        'success' => true,
        'message' => "Profil mis à jour avec succès !",
        'user' => [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'num' => $num
        ]
    ]);
    exit;
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de la sauvegarde des données: " . $e->getMessage(),
        'originalValues' => $originalValues ?? null
    ]);
    exit;
}
?>
