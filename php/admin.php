<?php
session_start();

// Security check - Uncomment this in production
/*if (empty($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: connexion.php");
    exit;
} */

// Load user data from JSON file with path debugging
$file = '../json/data1.json';
$absolutePath = realpath($file);

// Debug file path information
$debugInfo = "";
if (!file_exists($file)) {
    $debugInfo .= "<div style='background-color: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                  Warning: File not found at path: $file<br>
                  Current script directory: " . dirname(__FILE__) . "<br>
                  Attempting to use absolute path: $absolutePath</div>";
    
    // Try alternative paths
    $alternativePaths = [
        './json/data1.json',
        'json/data1.json',
        '../../json/data1.json',
        dirname(__FILE__) . '/../json/data1.json'
    ];
    
    foreach ($alternativePaths as $altPath) {
        if (file_exists($altPath)) {
            $debugInfo .= "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                         Found file at alternative path: $altPath</div>";
            $file = $altPath;
            break;
        }
    }
}

// Load the user data
try {
    if (file_exists($file)) {
        $jsonContent = file_get_contents($file);
        if ($jsonContent === false) {
            throw new Exception("Unable to read file contents");
        }
        $users = json_decode($jsonContent, true);
        if ($users === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON decode error: " . json_last_error_msg());
        }
    } else {
        throw new Exception("File not found");
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
          Error loading user data: $errorMessage</div>";
    // Initialize with empty array if file can't be loaded
    $users = [];
}

// Display debug info if any was generated
echo $debugInfo;

// Handle form submissions for different actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's an AJAX request for user update
    if (isset($_POST['ajax_update']) && $_POST['ajax_update'] === 'true') {
        // This will be handled by update_admin_user.php
        // No action needed here as we're using AJAX
    } else {
        // Handle traditional form submissions for other actions
        
        // Promote user to admin
        if (isset($_POST['promote'])) {
            $emailToPromote = $_POST['email'];
            foreach ($users as &$user) {
                if ($user['email'] === $emailToPromote && $user['role'] === "normal") {
                    $user['role'] = "admin";
                }
            }
            saveUsers($file, $users);
        }
        
        // Delete user
        if (isset($_POST['delete'])) {
            $emailToDelete = $_POST['email'];
            foreach ($users as $key => $user) {
                if ($user['email'] === $emailToDelete) {
                    unset($users[$key]);
                    break;
                }
            }
            // Re-index array after deletion
            $users = array_values($users);
            saveUsers($file, $users);
        }
        
        // Update user information (traditional non-AJAX method)
        if (isset($_POST['update']) && !isset($_POST['ajax_update'])) {
            $emailToUpdate = $_POST['original_email'];
            $updatedData = [
                'genre' => $_POST['genre'],
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'num' => $_POST['num'],
                'email' => $_POST['email'],
                'role' => $_POST['role']
                // Password is not updated via this form
            ];
            
            foreach ($users as &$user) {
                if ($user['email'] === $emailToUpdate) {
                    // Keep existing password and other fields that should not be modified
                    $updatedData['mdp'] = $user['mdp'];
                    $updatedData['date_inscription'] = $user['date_inscription'];
                    $updatedData['derniere_connexion'] = $user['derniere_connexion'];
                    $updatedData['favoris'] = $user['favoris'] ?? [];
                    
                    // Update user with new data
                    $user = $updatedData;
                    break;
                }
            }
            saveUsers($file, $users);
        }
    }
    
    // Refresh user data after changes
    if (file_exists($file)) {
        $jsonContent = file_get_contents($file);
        if ($jsonContent !== false) {
            $refreshedUsers = json_decode($jsonContent, true);
            if ($refreshedUsers !== null) {
                $users = $refreshedUsers;
            } else {
                echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                      Error refreshing data: Invalid JSON format</div>";
            }
        } else {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                  Error refreshing data: Could not read file</div>";
        }
    }
}

// Function to save users to JSON file with error handling
function saveUsers($file, $users) {
    // Check if file is writable
    if (!is_writable($file) && file_exists($file)) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
              Error: File $file is not writable. Please check file permissions.</div>";
        return false;
    }
    
    // Check if directory is writable if file doesn't exist
    if (!file_exists($file)) {
        $dir = dirname($file);
        if (!is_writable($dir)) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                  Error: Directory $dir is not writable. Please check directory permissions.</div>";
            return false;
        }
    }
    
    // Attempt to write the file
    $result = file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
              Error: Failed to write to $file.</div>";
        return false;
    }
    
    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>
          User data successfully saved!</div>";
    return true;
}

// Pagination settings
$usersPerPage = 10;
$totalUsers = count($users);
$totalPages = ceil($totalUsers / $usersPerPage);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $usersPerPage;
$usersToShow = array_slice($users, $start, $usersPerPage);
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../css/projetS4.css" id="theme-style">

        <title>Green Odyssey - Administration</title>

        <meta charset="UTF-8">
        <meta name="author" content="Anas_Capucine_Hadil"/>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <style>
            /* Modal styles */
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.4);
            }
            
            .modal-content {
                background-color: #fefefe;
                margin: 10% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 60%;
                max-width: 600px;
                border-radius: 8px;
                position: relative;
            }
            
            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            
            .close:hover {
                color: black;
            }
            
            .edit-form label {
                display: block;
                margin-top: 10px;
                font-weight: bold;
            }
            
            .edit-form input, .edit-form select {
                width: 100%;
                padding: 8px;
                margin-top: 5px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            
            .btn-update {
                background-color: #4CAF50;
                color: white;
                padding: 10px 15px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
            }
            
            .btn-update:hover {
                background-color: #45a049;
            }
            
            .action-buttons {
                display: flex;
                gap: 5px;
            }
            
            .btn-edit {
                background-color: #2196F3;
                color: white;
                border: none;
                border-radius: 4px;
                padding: 5px 10px;
                cursor: pointer;
            }
            
            .btn-delete {
                background-color: #f44336;
                color: white;
                border: none;
                border-radius: 4px;
                padding: 5px 10px;
                cursor: pointer;
            }
            
            .btn-promote {
                background-color: #ff9800;
                color: white;
                border: none;
                border-radius: 4px;
                padding: 5px 10px;
                cursor: pointer;
            }
            
            .tabadmin {
                width: 90%;
                border-collapse: collapse;
            }
            
            .tabadmin th, .tabadmin td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            
            .pagee {
                margin-top: 20px;
                margin-bottom: 20px;
            }
            
            /* Loading spinner styles */
            .loading-overlay {
                display: none;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.8);
                border-radius: 8px;
                z-index: 1001;
                justify-content: center;
                align-items: center;
            }
            
            .spinner {
                width: 50px;
                height: 50px;
                border: 5px solid rgba(0, 0, 0, 0.1);
                border-radius: 50%;
                border-top-color: #2196F3;
                animation: spin 1s ease-in-out infinite;
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            
            /* Status message styles */
            .status-message {
                display: none;
                padding: 10px;
                margin: 10px 0;
                border-radius: 4px;
                text-align: center;
            }
            
            .success-message {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            
            .error-message {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            
            /* Field validation styles */
            .field-error {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }
            
            .field-feedback {
                display: none;
                font-size: 0.8em;
                margin-top: -10px;
                margin-bottom: 10px;
                color: #dc3545;
            }
        </style>
        
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
                
                // Set up the edit form submission handler
                document.getElementById('edit-user-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitUserUpdate();
                });
            };
            
            // Function to open the edit modal
            function openEditModal(userJson) {
                const user = JSON.parse(decodeURIComponent(userJson));
                document.getElementById('editModal').style.display = 'block';
                
                // Fill form with user data
                document.getElementById('original_email').value = user.email;
                document.getElementById('edit_genre').value = user.genre;
                document.getElementById('edit_nom').value = user.nom;
                document.getElementById('edit_prenom').value = user.prenom;
                document.getElementById('edit_num').value = user.num;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_role').value = user.role;
                
                // Reset any previous validation styling
                resetValidationStyles();
                
                // Hide any previous status messages
                document.getElementById('status-message').style.display = 'none';
            }
            
            // Function to close the modal
            function closeModal() {
                document.getElementById('editModal').style.display = 'none';
                resetValidationStyles();
            }
            
            // Reset validation styles
            function resetValidationStyles() {
                const inputs = document.querySelectorAll('.edit-form input, .edit-form select');
                inputs.forEach(input => {
                    input.classList.remove('field-error');
                    const feedbackElement = document.getElementById(`${input.id}-feedback`);
                    if (feedbackElement) {
                        feedbackElement.style.display = 'none';
                    }
                });
            }
            
            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('editModal');
                if (event.target === modal) {
                    closeModal();
                }
            }
            
            // Confirm user deletion
            function confirmDelete(email, name) {
                return confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${name} (${email}) ?`);
            }
            
            // Validate the edit form
            function validateEditForm() {
                let isValid = true;
                
                // Validate name
                const nomInput = document.getElementById('edit_nom');
                if (!nomInput.value.trim()) {
                    showFieldError(nomInput, 'Le nom est requis');
                    isValid = false;
                }
                
                // Validate first name
                const prenomInput = document.getElementById('edit_prenom');
                if (!prenomInput.value.trim()) {
                    showFieldError(prenomInput, 'Le prénom est requis');
                    isValid = false;
                }
                
                // Validate email
                const emailInput = document.getElementById('edit_email');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailInput.value.trim()) {
                    showFieldError(emailInput, 'L\'email est requis');
                    isValid = false;
                } else if (!emailRegex.test(emailInput.value.trim())) {
                    showFieldError(emailInput, 'Format d\'email invalide');
                    isValid = false;
                }
                
                return isValid;
            }
            
            // Show field error
            function showFieldError(inputElement, message) {
                inputElement.classList.add('field-error');
                
                // Create or update feedback element
                let feedbackElement = document.getElementById(`${inputElement.id}-feedback`);
                if (!feedbackElement) {
                    feedbackElement = document.createElement('div');
                    feedbackElement.id = `${inputElement.id}-feedback`;
                    feedbackElement.className = 'field-feedback';
                    inputElement.parentNode.insertBefore(feedbackElement, inputElement.nextSibling);
                }
                
                feedbackElement.textContent = message;
                feedbackElement.style.display = 'block';
            }
            
            // Submit user update via AJAX
            function submitUserUpdate() {
                // Validate form before submission
                if (!validateEditForm()) {
                    return;
                }
                
                // Show loading overlay
                document.getElementById('loading-overlay').style.display = 'flex';
                
                // Hide any previous status messages
                document.getElementById('status-message').style.display = 'none';
                
                // Get form data
                const form = document.getElementById('edit-user-form');
                const formData = new FormData(form);
                formData.append('ajax_update', 'true');
                
                // Send AJAX request
                fetch('update_admin_user.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading overlay
                    document.getElementById('loading-overlay').style.display = 'none';
                    
                    // Show status message
                    const statusMessage = document.getElementById('status-message');
                    statusMessage.textContent = data.message;
                    
                    if (data.success) {
                        // Success case
                        statusMessage.className = 'status-message success-message';
                        statusMessage.style.display = 'block';
                        
                        // Update the user row in the table
                        updateUserRow(data.user);
                        
                        // Close modal after a delay
                        setTimeout(() => {
                            closeModal();
                        }, 2000);
                    } else {
                        // Error case
                        statusMessage.className = 'status-message error-message';
                        statusMessage.style.display = 'block';
                        
                        // Show field-specific errors if available
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const inputId = `edit_${field}`;
                                const input = document.getElementById(inputId);
                                if (input) {
                                    showFieldError(input, data.errors[field]);
                                }
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Hide loading overlay
                    document.getElementById('loading-overlay').style.display = 'none';
                    
                    // Show error message
                    const statusMessage = document.getElementById('status-message');
                    statusMessage.textContent = 'Une erreur est survenue lors de la mise à jour. Veuillez réessayer.';
                    statusMessage.className = 'status-message error-message';
                    statusMessage.style.display = 'block';
                });
            }
            
            // Update user row in the table
            function updateUserRow(user) {
                // Find the row with the matching email (either original or updated)
                const originalEmail = document.getElementById('original_email').value;
                const rows = document.querySelectorAll('.tabadmin tr');
                
                for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header row
                    const cells = rows[i].querySelectorAll('td');
                    if (cells.length > 0) {
                        const emailCell = cells[1]; // Email is in the second column
                        
                        if (emailCell.textContent.trim() === originalEmail) {
                            // Update the row with new user data
                            cells[0].textContent = `${user.nom} ${user.prenom}`; // Name
                            cells[1].textContent = user.email; // Email
                            cells[2].textContent = user.num; // Phone
                            cells[3].textContent = user.genre; // Genre
                            cells[4].textContent = user.role; // Role
                            
                            // Update the edit button with new user data
                            const editButton = rows[i].querySelector('.btn-edit');
                            if (editButton) {
                                const userData = {
                                    genre: user.genre,
                                    nom: user.nom,
                                    prenom: user.prenom,
                                    num: user.num,
                                    email: user.email,
                                    role: user.role,
                                    date_inscription: user.date_inscription,
                                    derniere_connexion: user.derniere_connexion
                                };
                                
                                const encodedUserData = encodeURIComponent(JSON.stringify(userData));
                                editButton.setAttribute('onclick', `openEditModal('${encodedUserData}')`);
                            }
                            
                            // Update other action buttons if needed
                            const deleteForm = rows[i].querySelector('form[onsubmit*="confirmDelete"]');
                            if (deleteForm) {
                                const emailInput = deleteForm.querySelector('input[name="email"]');
                                if (emailInput) {
                                    emailInput.value = user.email;
                                }
                                deleteForm.setAttribute('onsubmit', `return confirmDelete('${user.email}', '${user.nom} ${user.prenom}')`);
                            }
                            
                            // Update promote button if exists and role changed
                            const promoteForm = rows[i].querySelector('form:not([onsubmit])');
                            if (promoteForm) {
                                if (user.role === "admin") {
                                    // Remove promote button if user is now admin
                                    promoteForm.remove();
                                } else {
                                    const emailInput = promoteForm.querySelector('input[name="email"]');
                                    if (emailInput) {
                                        emailInput.value = user.email;
                                    }
                                }
                            }
                            
                            break;
                        }
                    }
                }
            }
        </script>
    </head>

    <body>
        <h1>Green Odyssey</h1>
        <center><table class="nav">
            <tr>
                <td><a href="index.php" class="navi">Accueil</a></td>  
                <td><a href="presentation.php" class="navi">Présentation</a></td>
                <td><a href="voyages.php" class="navi">Voyages</a></td>
                
                <?php if (!isset($_SESSION['user'])): ?>
                    <td><a href="inscription.php" class="navi">S'inscrire</a></td>
                    <td><a href="connexion.php" class="navi">Se Connecter</a></td>
                
                <?php else: ?>
                    <td><a href="favoris.php" class="navi">Favoris</a></td>
                    <td><a href="panier.php" class="navi">Panier</td>
                    <td><a href="profil.php" class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <td><a href="admin.php" class="navi">Admin</a></td>
                <?php endif; ?>
                <td><button id="theme-button" onclick="switchTheme()" class="navi-button">🌙 Mode Sombre</button></td>
            </tr>
        </table></center>

        <p class="gestion">Gestion des utilisateurs</p>
        <center><table class="tabadmin">
            <tr>
                <th>Nom complet</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Genre</th>
                <th>Statut</th>
                <th>Date d'inscription</th>
                <th>Dernière connexion</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($usersToShow as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['nom']) . ' ' . htmlspecialchars($user['prenom']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['num']); ?></td>
                <td><?php echo htmlspecialchars($user['genre']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo htmlspecialchars($user['date_inscription']); ?></td>
                <td><?php echo htmlspecialchars($user['derniere_connexion']); ?></td>
                <td class="action-buttons">
                    <?php 
                        // Prepare user data for JavaScript
                        $userData = json_encode($user);
                        $encodedUserData = htmlspecialchars(urlencode($userData));
                    ?>
                    <button class="btn-edit" onclick="openEditModal('<?php echo $encodedUserData; ?>')">
                        <i class="fas fa-pen"></i> Modifier
                    </button>
                    
                    <form method="post" style="display:inline;" onsubmit="return confirmDelete('<?php echo $user['email']; ?>', '<?php echo $user['nom'] . ' ' . $user['prenom']; ?>')">
                        <input type="hidden" name="email" value="<?php echo $user['email']; ?>">
                        <button type="submit" name="delete" class="btn-delete">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </form>
                    
                    <?php if ($user['role'] === "normal"): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="email" value="<?php echo $user['email']; ?>">
                            <button type="submit" name="promote" class="btn-promote">
                                <i class="fas fa-user-shield"></i> Promouvoir
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table></center>
        
        <!-- Pagination -->
        <center>
        <div class="pagee">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="barre">Précédent</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php if ($i === $page) echo 'active'; else echo 'barre'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="barre">Suivant</a>
            <?php endif; ?>
        </div>
        </center>
        
        <!-- Edit User Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Modifier les informations utilisateur</h2>
                
                <!-- Status message container -->
                <div id="status-message" class="status-message"></div>
                
                <!-- Loading overlay with spinner -->
                <div id="loading-overlay" class="loading-overlay">
                    <div class="spinner"></div>
                </div>
                
                <form id="edit-user-form" class="edit-form">
                    <input type="hidden" id="original_email" name="original_email">
                    
                    <label for="edit_genre">Genre:</label>
                    <select id="edit_genre" name="genre">
                        <option value="homme">Homme</option>
                        <option value="femme">Femme</option>
                        <option value="autre">Autre</option>
                    </select>
                    
                    <label for="edit_nom">Nom:</label>
                    <input type="text" id="edit_nom" name="nom" required>
                    
                    <label for="edit_prenom">Prénom:</label>
                    <input type="text" id="edit_prenom" name="prenom" required>
                    
                    <label for="edit_num">Téléphone:</label>
                    <input type="text" id="edit_num" name="num" required>
                    
                    <label for="edit_email">Email:</label>
                    <input type="email" id="edit_email" name="email" required>
                    
                    <label for="edit_role">Rôle:</label>
                    <select id="edit_role" name="role">
                        <option value="normal">Normal</option>
                        <option value="admin">Admin</option>
                    </select>
                    
                    <button type="submit" class="btn-update">Enregistrer les modifications</button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="foooot">
            <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>
