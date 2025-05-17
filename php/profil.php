<?php
    session_start();
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
        header("Location: connexion.php");
        exit;
    }
    
    // Ensure $user exists
    if (!isset($_SESSION['user'])) {
        header("Location: connexion.php");
        exit;
    }
    $user = $_SESSION['user']; // Use the user array from the session

    // Display error or success messages
    if (isset($_SESSION['update_error'])) {
        echo "<div class='error-message'>" . $_SESSION['update_error'] . "</div>";
        unset($_SESSION['update_error']); // Clear the message
    }
    
    if (isset($_SESSION['update_success'])) {
        echo "<script>alert('Profil mis à jour avec succès !');</script>";
        unset($_SESSION['update_success']); // Clear the message
    }

    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: connexion.php");
        exit;
    }
    
    // Debug information for file path (optional - can be removed in production)
    $file = '../json/data1.json';
    $fileDebug = "";
    if (!file_exists($file)) {
        $fileDebug = "<div style='background-color: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                      Debug: File not found at path: $file<br>
                      Script directory: " . dirname(__FILE__) . "</div>";
        
        // Try alternative paths
        $alternativePaths = [
            './json/data1.json',
            'json/data1.json',
            '../../json/data1.json',
            dirname(__FILE__) . '/../json/data1.json'
        ];
        
        foreach ($alternativePaths as $altPath) {
            if (file_exists($altPath)) {
                $fileDebug .= "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                             Found at alternative path: $altPath</div>";
                break;
            }
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../css/projetS4.css" id="theme-style">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <title>Green Odyssey Profil</title>
        <meta charset="UTF-8">
        <meta name="author" content="Anas_Capucine_Hadil"/>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
        <style>
            .profil-info input[disabled] { background-color: #eee; border: 1px solid #ccc; color: #555; cursor: not-allowed; }
            .edit-controls { display: inline-block; margin-left: 10px; }
            .edit-controls .save-btn, .edit-controls .cancel-btn { display: none; margin-left: 5px; cursor: pointer; }
            #submit-profile-changes { display: none; margin-top: 20px; padding: 10px 20px; cursor: pointer; }
            .btn-modif { background: none; border: none; cursor: pointer; color: #007bff; margin-left: 5px; }
            .btn-modif:hover { color: #0056b3; }
            .save-btn, .cancel-btn { background-color: #28a745; color: white; border: none; padding: 3px 8px; border-radius: 3px; }
            .cancel-btn { background-color: #dc3545; }
            .save-btn:hover { background-color: #218838; }
            .cancel-btn:hover { background-color: #c82333; }
            /* Message styles */
            .error-message {
                color: #721c24;
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                border-radius: 4px;
                padding: 10px;
                margin: 10px 0;
                text-align: center;
            }
            .success-message {
                color: #155724;
                background-color: #d4edda;
                border: 1px solid #c3e6cb;
                border-radius: 4px;
                padding: 10px;
                margin: 10px 0;
                text-align: center;
                display: none;
            }
            /* Loading spinner */
            .spinner {
                display: none;
                width: 20px;
                height: 20px;
                border: 3px solid rgba(0, 0, 0, 0.1);
                border-radius: 50%;
                border-top-color: #007bff;
                animation: spin 1s ease-in-out infinite;
                margin: 0 auto;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            /* Field validation styles */
            .field-error {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }
            .field-success {
                border-color: #28a745 !important;
                box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
            }
            .field-feedback {
                display: none;
                font-size: 0.8em;
                margin-top: 5px;
            }
            .error-feedback {
                color: #dc3545;
            }
            .success-feedback {
                color: #28a745;
            }
        </style>
        <script>
            // Theme switcher functions
            function setCookie(name, value, days) { var expires = ""; if (days) { var date = new Date(); date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); expires = "; expires=" + date.toUTCString(); } document.cookie = name + "=" + (value || "") + expires + "; path=/"; }
            function getCookie(name) { var nameEQ = name + "="; var ca = document.cookie.split(';'); for(var i = 0; i < ca.length; i++) { var c = ca[i]; while (c.charAt(0) == ' ') c = c.substring(1, c.length); if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length); } return null; }
            function switchTheme() { var currentTheme = document.getElementById('theme-style').getAttribute('href'); var newTheme; if (currentTheme === '../css/projetS4.css') { newTheme = '../css/projetS4-dark.css'; document.getElementById('theme-button').textContent = '☀️ Mode Clair'; } else { newTheme = '../css/projetS4.css'; document.getElementById('theme-button').textContent = '🌙 Mode Sombre'; } document.getElementById('theme-style').setAttribute('href', newTheme); setCookie('theme', newTheme, 30); }
            window.onload = function() { var savedTheme = getCookie('theme'); if (savedTheme) { if (savedTheme === '../css/projetS4-dark.css') { document.getElementById('theme-style').setAttribute('href', savedTheme); document.getElementById('theme-button').textContent = '☀️ Mode Clair'; } else { document.getElementById('theme-style').setAttribute('href', '../css/projetS4.css'); document.getElementById('theme-button').textContent = '🌙 Mode Sombre'; } } };
            // editing
        document.addEventListener('DOMContentLoaded', () => {
        const editButtons = document.querySelectorAll('.edit-btn');
        const formInputs = document.querySelectorAll('#profile-form input');
        const profileForm = document.getElementById('profile-form');
        const submitButton = document.querySelector('button[type="submit"]');
        const cancelButton = document.getElementById('global-cancel');
        const statusMessage = document.createElement('div');
        statusMessage.id = 'status-message';
        statusMessage.style.display = 'none';
        profileForm.appendChild(statusMessage);
        
        // Store original values for reverting if needed
        const originalValues = {};
        formInputs.forEach(input => {
            if (input.name) {
                originalValues[input.name] = input.value;
            }
        });

        // Enable editing when pencil is clicked
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const fieldId = button.dataset.field;
                const input = document.getElementById(fieldId);
                input.disabled = false;
                input.focus();
                if (input.type === 'password') {
                    input.value = '';
                    input.placeholder = 'Entrez le nouveau mot de passe';
                }
            });
        });

        // Reset all inputs to original values and disable editing
        cancelButton.addEventListener('click', () => {
            formInputs.forEach(input => {
                if (input.name && input.name !== 'password') {
                    input.value = originalValues[input.name];
                } else if (input.type === 'password') {
                    input.value = '';
                    input.placeholder = '********';
                }
                input.disabled = true;
                
                // Remove any validation styling
                input.classList.remove('field-error', 'field-success');
                const feedbackElement = document.getElementById(`${input.name}-feedback`);
                if (feedbackElement) {
                    feedbackElement.style.display = 'none';
                }
            });
            
            // Hide status message
            statusMessage.style.display = 'none';
        });

        // Client-side validation
        function validateForm() {
            let isValid = true;
            
            // Validate name
            const nomInput = document.getElementById('nom-input');
            if (!nomInput.value.trim()) {
                showFieldError(nomInput, 'Le nom est requis');
                isValid = false;
            } else {
                showFieldSuccess(nomInput);
            }
            
            // Validate first name
            const prenomInput = document.getElementById('prenom-input');
            if (!prenomInput.value.trim()) {
                showFieldError(prenomInput, 'Le prénom est requis');
                isValid = false;
            } else {
                showFieldSuccess(prenomInput);
            }
            
            // Validate email
            const emailInput = document.getElementById('email-input');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailInput.value.trim()) {
                showFieldError(emailInput, 'L\'email est requis');
                isValid = false;
            } else if (!emailRegex.test(emailInput.value.trim())) {
                showFieldError(emailInput, 'Format d\'email invalide');
                isValid = false;
            } else {
                showFieldSuccess(emailInput);
            }
            
            return isValid;
        }
        
        function showFieldError(inputElement, message) {
            inputElement.classList.add('field-error');
            inputElement.classList.remove('field-success');
            
            // Create or update feedback element
            let feedbackElement = document.getElementById(`${inputElement.name}-feedback`);
            if (!feedbackElement) {
                feedbackElement = document.createElement('div');
                feedbackElement.id = `${inputElement.name}-feedback`;
                feedbackElement.className = 'field-feedback error-feedback';
                inputElement.parentNode.appendChild(feedbackElement);
            }
            
            feedbackElement.textContent = message;
            feedbackElement.style.display = 'block';
            feedbackElement.className = 'field-feedback error-feedback';
        }
        
        function showFieldSuccess(inputElement) {
            inputElement.classList.add('field-success');
            inputElement.classList.remove('field-error');
            
            // Hide error message if exists
            const feedbackElement = document.getElementById(`${inputElement.name}-feedback`);
            if (feedbackElement) {
                feedbackElement.style.display = 'none';
            }
        }

        // Handle form submission with AJAX
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Enable all inputs temporarily for form data collection
            formInputs.forEach(input => {
                if (input.name) {
                    input.disabled = false;
                }
            });
            
            // Validate form before submission
            if (!validateForm()) {
                // Re-disable inputs that were disabled before
                formInputs.forEach(input => {
                    if (input.dataset.originalDisabled === 'true') {
                        input.disabled = true;
                    }
                });
                return;
            }
            
            // Show loading indicator
            statusMessage.className = 'spinner';
            statusMessage.style.display = 'block';
            
            // Collect form data
            const formData = new FormData(profileForm);
            
            // Send AJAX request
            fetch('update_profile.php', {
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
                // Hide loading indicator
                statusMessage.className = '';
                
                if (data.success) {
                    // Update original values with new values
                    Object.keys(data.user).forEach(key => {
                        if (key !== 'mdp') { // Don't update password field
                            const input = document.querySelector(`input[name="${key}"]`);
                            if (input) {
                                input.value = data.user[key];
                                originalValues[key] = data.user[key];
                            }
                        }
                    });
                    
                    // Show success message
                    statusMessage.className = 'success-message';
                    statusMessage.textContent = data.message;
                    statusMessage.style.display = 'block';
                    
                    // Disable all inputs
                    formInputs.forEach(input => {
                        input.disabled = true;
                        input.classList.remove('field-error', 'field-success');
                    });
                    
                    // Hide success message after 3 seconds
                    setTimeout(() => {
                        statusMessage.style.display = 'none';
                    }, 3000);
                } else {
                    // Show error message
                    statusMessage.className = 'error-message';
                    statusMessage.textContent = data.message;
                    statusMessage.style.display = 'block';
                    
                    // Highlight fields with errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = document.querySelector(`input[name="${field}"]`);
                            if (input) {
                                showFieldError(input, data.errors[field]);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusMessage.className = 'error-message';
                statusMessage.textContent = 'Une erreur est survenue lors de la mise à jour du profil. Veuillez réessayer.';
                statusMessage.style.display = 'block';
            });
        });
    });
</script>
    </head>
    <body>
        <center><h1>Green Odyssey</h1></center>
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
        </table></center></br></br></br>

        <?php 
        // Display debug info if set (remove in production)
        echo $fileDebug;
        ?>

        <center>
    <div class="profil">
        <img src="vavatar.jpeg" alt="Photo de profil" class="avaaatar" height="70" width="70">
        <h2>Profil Utilisateur</h2>
        <form id="profile-form" action="update_profile.php" method="post">
            <div class="profil-info">
                Nom : 
                <input type="text" id="nom-input" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" data-original-value="<?php echo htmlspecialchars($user['nom']); ?>" disabled>
                <button type="button" class="btn-modif edit-btn" data-field="nom-input"><i class="fa fa-pencil-alt"></i></button>
            </div><br>

            <div class="profil-info">
                Prénom : 
                <input type="text" id="prenom-input" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" data-original-value="<?php echo htmlspecialchars($user['prenom']); ?>" disabled>
                <button type="button" class="btn-modif edit-btn" data-field="prenom-input"><i class="fa fa-pencil-alt"></i></button>
            </div><br>

            <div class="profil-info">
                Email : 
                <input type="email" id="email-input" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" data-original-value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                <button type="button" class="btn-modif edit-btn" data-field="email-input"><i class="fa fa-pencil-alt"></i></button>
            </div><br>

            <div class="profil-info">
                N° Tel : 
                <input type="tel" id="num-input" name="num" value="<?php echo htmlspecialchars($user['num'] ?? ''); ?>" data-original-value="<?php echo htmlspecialchars($user['num'] ?? ''); ?>" placeholder="Non renseigné" disabled>
                <button type="button" class="btn-modif edit-btn" data-field="num-input"><i class="fa fa-pencil-alt"></i></button>
            </div><br>

            <div class="profil-info">
                Mot de passe : 
                <input type="password" id="password-input" name="password" value="" placeholder="********" data-original-value="" disabled>
                <button type="button" class="btn-modif edit-btn" data-field="password-input"><i class="fa fa-pencil-alt"></i></button>
                <small style="display: block; font-size: 0.8em; margin-top: 5px;">Laissez vide si vous ne souhaitez pas changer le mot de passe.</small>
            </div><br>

            <!-- Global Submit and Cancel Buttons -->
            <center>
                <button type="submit" class="seconnecter">Valider</button>
                <button type="button" id="global-cancel" class="seconnecter" style="background-color: #dc3545; margin-left: 10px;">Annuler</button>
            </center>
        </form>

        <br>
        <center>
            <form method="post" style="margin-top: 15px;">
                <button type="submit" name="logout" class="seconnecter">Se déconnecter</button>
            </form>
        </center>
    </div>
</center>

        <footer class="foooot">
            <p>© 2025 GREEN ODYSSEY Tous droits réservés.</p>
        </footer>
    </body>
    <div class="paysage"></div>
</html>
