<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) { // Check both as per your original
    $_SESSION['update_error'] = "Vous devez être connecté pour accéder à cette page.";
    header("Location: connexion.php");
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
        $_SESSION['update_error'] = "Erreur critique: Impossible de trouver le fichier de données utilisateurs.";
        header("Location: profil.php");
        exit;
    }
}

// Check if the file is readable
if (!is_readable($file)) {
    $_SESSION['update_error'] = "Erreur: Le fichier de données n'est pas lisible. Veuillez vérifier les permissions.";
    header("Location: profil.php");
    exit;
}

// Check if the file is writable
if (!is_writable($file)) {
    $_SESSION['update_error'] = "Erreur: Le fichier de données n'est pas modifiable. Veuillez vérifier les permissions du fichier.";
    header("Location: profil.php");
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
    $_SESSION['update_error'] = "Erreur lors du chargement des données utilisateurs: " . $e->getMessage();
    header("Location: profil.php");
    exit;
}

// Get current user email for identification (this is the email BEFORE any changes)
$currentUserEmail = $_SESSION['user']['email']; 
// Get original user_id, assuming it might be different from email or you want to preserve original one if not email
$currentUserId = $_SESSION['user_id'];


// Process form data
// Use null coalescing operator for cleaner fetching and default to current value if not submitted
// This is important because disabled fields are not typically submitted by browsers.
// However, your JS enables them before potential submission, so $_POST should contain them if modified.
// If your JS ensures all fields (nom, prenom, email, num) are always part of the POST, then this is fine.
// If a field could be missing from POST, you'd want to retain its old value from $_SESSION['user'].

$nom = isset($_POST['nom']) ? trim($_POST['nom']) : $_SESSION['user']['nom'];
$prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : $_SESSION['user']['prenom'];
$email = isset($_POST['email']) ? trim($_POST['email']) : $_SESSION['user']['email'];
$num = isset($_POST['num']) ? trim($_POST['num']) : ($_SESSION['user']['num'] ?? ''); // Handle if num might not exist
$password = isset($_POST['password']) ? trim($_POST['password']) : ''; // Password is only processed if not empty

// Basic validation
if (empty($nom) || empty($prenom) || empty($email)) {
    $_SESSION['update_error'] = "Les champs nom, prénom et email sont obligatoires.";
    header("Location: profil.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['update_error'] = "Format d'email invalide.";
    header("Location: profil.php");
    exit;
}

// Check if new email already exists for a DIFFERENT user
if ($email !== $currentUserEmail) {
    foreach ($users as $existingUser) {
        if ($existingUser['email'] === $email) {
            $_SESSION['update_error'] = "Cette adresse email est déjà utilisée par un autre compte.";
            header("Location: profil.php");
            exit;
        }
    }
}

// Find and update the user
$userFound = false;
$updatedUserSessionData = null; // To store the complete updated user for the session

foreach ($users as &$userEntry) { // Use a different variable name to avoid confusion with $_SESSION['user']
    if ($userEntry['email'] === $currentUserEmail) {
        // Update specific fields
        $userEntry['nom'] = $nom;
        $userEntry['prenom'] = $prenom;
        $userEntry['email'] = $email; // Update to the new email
        $userEntry['num'] = $num;
        
        // Update password only if a new one is provided
        if (!empty($password)) {
            $userEntry['mdp'] = password_hash($password, PASSWORD_DEFAULT);
        }
        // All other fields like 'role', 'date_inscription', 'derniere_connexion', 'favoris' remain untouched in $userEntry

        $updatedUserSessionData = $userEntry; // This now contains all fields, modified and original
        $userFound = true;
        break;
    }
}

if (!$userFound) {
    // This case should ideally not happen if the session is valid and synchronized with the JSON
    $_SESSION['update_error'] = "Utilisateur non trouvé. Votre session pourrait être invalide. Veuillez vous reconnecter.";
    // Potentially destroy session here if it's out of sync
    // session_destroy(); 
    header("Location: connexion.php");
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
        // More detailed error check
        $error = error_get_last();
        throw new Exception("Échec de l'écriture dans le fichier. " . ($error['message'] ?? 'Raison inconnue. Vérifiez les logs du serveur.'));
    }
    
    // Update the session data with new user info
    $_SESSION['user'] = $updatedUserSessionData;
    
    // Update user_id in session IF it's tied to the email and the email has changed
    if ($email !== $currentUserEmail && $_SESSION['user_id'] === $currentUserEmail) {
        $_SESSION['user_id'] = $email;
    }
    // Or, if user_id is a distinct field that shouldn't change, ensure it's preserved:
    // $_SESSION['user_id'] = $currentUserId; // Or ensure $updatedUserSessionData['id_field'] is correct

    // $_SESSION['update_success'] = "Profil mis à jour avec succès !"; // Store message directly
    // header("Location: profil.php"); // Redirect back to profile to see changes and message
    // header("Location: profil.php?refresh=" . time());
    header("Location: profil.php"); 
    exit;
    
} catch (Exception $e) {
    $_SESSION['update_error'] = "Erreur lors de la sauvegarde des données: " . $e->getMessage();
    header("Location: profil.php");
    exit;
}
?>