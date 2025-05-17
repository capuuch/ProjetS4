<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: connexion.php");
    exit;
}

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: connexion.php");
    exit;
}
$user = $_SESSION['user']; // Utilisation des données utilisateur en session

// Affiche les messages d'erreur ou de succès
if (isset($_SESSION['update_error'])) {
    echo "<div class='error-message'>" . $_SESSION['update_error'] . "</div>";
    unset($_SESSION['update_error']);
}
if (isset($_SESSION['update_success'])) {
    echo "<script>alert('Profil mis à jour avec succès !');</script>";
    unset($_SESSION['update_success']);
}
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: connexion.php");
    exit;
}

// Debug : vérification du fichier JSON (optionnel - à retirer en prod)
$file = '../json/data1.json';
$fileDebug = "";
if (!file_exists($file)) {
    $fileDebug = "<div style='background-color: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;'>Debug: Fichier introuvable à l'adresse : $file</div>";

    $alternativePaths = [
        './json/data1.json',
        'json/data1.json',
        '../../json/data1.json',
        dirname(__FILE__) . '/../json/data1.json'
    ];
    foreach ($alternativePaths as $altPath) {
        if (file_exists($altPath)) {
            $fileDebug .= "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>Trouvé à l'adresse alternative : $altPath</div>";
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Green Odyssey Profil</title>
    <link rel="stylesheet" type="text/css" href="../css/projetS4.css" id="theme-style">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css ">

    <meta name="author" content="Anas_Capucine_Hadil"/>
    <link rel="preconnect" href="https://fonts.googleapis.com ">
    <link rel="preconnect" href="https://fonts.gstatic.com " crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat :ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

    <style>
        /* Style responsive */
        .profil-info input[disabled] {
            background-color: #eee;
            border: 1px solid #ccc;
            color: #555;
            cursor: not-allowed;
        }

        .btn-modif {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--accent-color);
            margin-left: 5px;
        }

        .btn-modif:hover {
            opacity: 0.8;
        }

        .seconnecter {
            text-align: center;
            font-family: quicksand, sans-serif;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .seconnecter:hover {
            opacity: 0.9;
            transform: scale(1.03);
        }

        .seconnecter.red {
            background-color: #dc3545;
        }

        .profil-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 15px;
            font-size: 16px;
            color: var(--text-color);
        }

        .profil-info input[type="text"],
        .profil-info input[type="password"],
        .profil-info input[type="email"],
        .profil-info input[type="tel"] {
            width: 100%;
            padding: 10px 12px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid var(--input-border);
            background-color: var(--input-bg);
            color: var(--input-text);
            box-sizing: border-box;
        }

        .form-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .error-message, .success-message {
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>

    <script>
        // Active/Désactive le thème sombre + change le texte du bouton
        function switchTheme() {
            document.documentElement.classList.toggle('dark-theme');
            const theme = document.documentElement.classList.contains('dark-theme') ?
                '../css/projetS4-dark.css' :
                '../css/projetS4.css';

            document.getElementById('theme-style').setAttribute('href', theme);
            localStorage.setItem('theme', theme);

            document.getElementById('theme-button').textContent =
                theme.includes('-dark') ? '☀️ Mode Clair' : '🌙 Mode Sombre';
        }

        // Charge le thème sauvegardé
        window.onload = function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.getElementById('theme-style').setAttribute('href', savedTheme);
                document.getElementById('theme-button').textContent =
                    savedTheme.includes('-dark') ? '☀️ Mode Clair' : '🌙 Mode Sombre';
            }
        };

        // Active l'édition des champs
        document.addEventListener('DOMContentLoaded', () => {
            const editButtons = document.querySelectorAll('.edit-btn');
            const formInputs = document.querySelectorAll('#profile-form input');

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

            document.getElementById('global-cancel').addEventListener('click', () => {
                formInputs.forEach(input => {
                    if (input.type !== 'password') {
                        input.value = input.dataset.originalValue;
                    } else {
                        input.value = '';
                        input.placeholder = '********';
                    }
                    input.disabled = true;
                });
            });
        });
    </script>
</head>
<body>
    <center><h1>Green Odyssey</h1></center>
    <center>
        <table class="nav">
            <tr>
                <td><a href="index.php" class="navi">Accueil</a></td>
                <td><a href="presentation.php" class="navi">Présentation</a></td>
                <td><a href="voyages.php" class="navi">Voyages</a></td>
                <?php if (!isset($_SESSION['user'])): ?>
                    <td><a href="inscription.php" class="navi">S'inscrire</a></td>
                    <td><a href="connexion.php" class="navi">Se Connecter</a></td>
                <?php else: ?>
                    <td><a href="favoris.php" class="navi">Favoris</a></td>
                    <td><a href="panier.php" class="navi">Panier</a></td>
                    <td><a href="profil.php" class="navi"><img src="vavatar.jpeg" alt="Profil" height="30" width="30" class="avaaatar"></a></td>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <td><a href="admin.php" class="navi">Admin</a></td>
                <?php endif; ?>

                <td>
                    <button id="theme-button" onclick="switchTheme()" class="navi-button">🌙 Mode Sombre</button>
                </td>
            </tr>
        </table>
    </center>

    <br><br><br><br>

    <?= $fileDebug ?> <!-- Pour le debug uniquement -->

    <center>
        <div class="profil">
            <img src="vavatar.jpeg" alt="Photo de profil" class="avaaatar" height="70" width="70">
            <h2>Profil Utilisateur</h2>
            <form id="profile-form" action="update_profile.php" method="post">
                <div class="profil-info">
                    Nom :
                    <input type="text" id="nom-input" name="nom"
                           value="<?= htmlspecialchars($user['nom']) ?>"
                           data-original-value="<?= htmlspecialchars($user['nom']) ?>" disabled>
                    <button type="button" class="btn-modif edit-btn" data-field="nom-input"><i class="fa fa-pencil-alt"></i></button>
                </div><br>

                <div class="profil-info">
                    Prénom :
                    <input type="text" id="prenom-input" name="prenom"
                           value="<?= htmlspecialchars($user['prenom']) ?>"
                           data-original-value="<?= htmlspecialchars($user['prenom']) ?>" disabled>
                    <button type="button" class="btn-modif edit-btn" data-field="prenom-input"><i class="fa fa-pencil-alt"></i></button>
                </div><br>

                <div class="profil-info">
                    Email :
                    <input type="email" id="email-input" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>"
                           data-original-value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    <button type="button" class="btn-modif edit-btn" data-field="email-input"><i class="fa fa-pencil-alt"></i></button>
                </div><br>

                <div class="profil-info">
                    N° Tel :
                    <input type="tel" id="num-input" name="num"
                           value="<?= htmlspecialchars($user['num'] ?? '') ?>"
                           data-original-value="<?= htmlspecialchars($user['num'] ?? '') ?>" placeholder="Non renseigné" disabled>
                    <button type="button" class="btn-modif edit-btn" data-field="num-input"><i class="fa fa-pencil-alt"></i></button>
                </div><br>

                <div class="profil-info">
                    Mot de passe :
                    <input type="password" id="password-input" name="password" placeholder="********" data-original-value="" disabled>
                    <button type="button" class="btn-modif edit-btn" data-field="password-input"><i class="fa fa-pencil-alt"></i></button>
                    <small style="display: block; font-size: 0.8em; margin-top: 5px;">
                        Laissez vide si vous ne souhaitez pas changer le mot de passe.
                    </small>
                </div><br>

                <!-- Boutons -->
                <div class="form-actions">
                    <button type="submit" class="seconnecter">Valider</button>
                    <button type="button" id="global-cancel" class="seconnecter red">Annuler</button>
                </div>
            </form>

            <br>

            <center>
                <form method="post" style="margin-top: 15px;">
                    <button type="submit" name="logout" class="seconnecter red">Se déconnecter</button>
                </form>
            </center>
        </div>
    </center>

    <footer class="foooot">
        <p>&copy; 2025 GREEN ODYSSEY Tous droits réservés.</p>
    </footer>

    <div class="paysage"></div>
</body>
</html>