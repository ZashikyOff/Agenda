<?php

use App\Autoloader;
use App\Entity\Utilisateur;
use App\Alert;

session_name("agendor");
session_start();

// setlocale(LC_TIME, 'fr_FR'); // Obsolete
date_default_timezone_set("Europe/Paris");

// Chargement de l'Autoloader de classes
require_once "app/autoloader.php";

// Enregistrement du service d'autoload
Autoloader::register();

$alert = new Alert();


if (isset($_POST["emailsignin"]) && isset($_POST["passwordsignin"]) && isset($_POST["confirmpassword"])) {

    require "app/config.php";

    //Valeurs du formaulaire
    //Email du compte
    $email = htmlspecialchars($_POST["emailsignin"]);
    //Password du compte
    $password = htmlspecialchars($_POST["passwordsignin"]);
    //Password de confimation
    $passwordConfirm = htmlspecialchars($_POST["confirmpassword"]);
    // Pseudo du compte
    $pseudo = htmlspecialchars($_POST["pseudo"]);

    $nom = htmlspecialchars($_POST["nom"]);

    $prenom = htmlspecialchars($_POST["prenom"]);

    $telephone = htmlspecialchars($_POST["telephone"]);


    //A Condition que les deux password soit pareil
    if ($_POST["passwordsignin"] == $_POST["confirmpassword"]) {
        $options = [
            'cost' => 12,
        ];
        $hashPass = password_hash($_POST["passwordsignin"], PASSWORD_BCRYPT, $options);

        $sql = "INSERT INTO utilisateurs (email, hash, pseudo, nom, prenom, telephone) VALUES (:email, :hashPass, :pseudo, :nom, :prenom, :telephone)";
        $query = $lienDB->prepare($sql);

        // Liaison des paramètres de la requête préparée
        $query->bindParam(":email", $email);
        $query->bindParam(":hashPass", $hashPass);
        $query->bindParam(":pseudo", $pseudo);
        $query->bindParam(":nom", $nom);
        $query->bindParam(":prenom", $prenom);
        $query->bindParam(":telephone", $telephone);

        // Exécution de la requête
        if ($query->execute()) {
            header('Location: index.php');
        } else {
            echo "<p>Une erreur s'est produite</p>";
        }
    }
}

if (isset($_POST["email"]) && isset($_POST["password"])) {

    require "app/config.php";

    // requête SQL
    $sql = "SELECT * FROM utilisateurs WHERE email=:email";
    $password = $_POST["password"];
    $email = $_POST["email"];


    // Préparer la requête
    $query = $lienDB->prepare($sql);

    // Liaison des paramètres de la requête préparée
    $query->bindParam(":email", $email, PDO::PARAM_STR);

    // Exécution de la requête
    if ($query->execute()) {
        // traitement des résultats
        $results = $query->fetch();

        // débogage des résultats
        if ($results) {
            if (password_verify($password, $results['hash'])) {
                // Connexion réussie
                header('Location: rdv-v2.php');
                echo 'Connexion réussie <br/>';
                echo 'Votre email :  ';
                echo  $_POST["email"];

                $_SESSION["email"] = $_POST["email"];
                $_SESSION["idcompte"] = $results["id"];
            } else {
                echo 'Mot de passe incorrect';
            }
        } else {
            echo 'Email non trouvé';
        }
    }
}


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prise de RDV</title>

    <link rel="stylesheet" href="main.css">
</head>

<body>
    <header>

    </header>

    <main class="index">
        <?php if (!isset($_SESSION["id"])) : ?>
            <?php // Utilisateur non connecté 
            ?>
            <h2>Connexion</h2>
            <form action="" method="post">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Connexion</button>
            </form>

            <h2>Inscription</h2>
            <form action="" method="post">
                <input type="text" name="nom" placeholder="Nom" required>
                <input type="text" name="prenom" placeholder="Prénom" required>
                <input type="text" name="telephone" placeholder="Téléphone" required>
                <input type="text" name="pseudo" placeholder="Pseudo" required>
                <input type="email" name="emailsignin" placeholder="Email" required>
                <input type="password" name="passwordsignin" placeholder="Mot de passe" required>
                <input type="password" name="confirmpassword" placeholder="Confirm Password" required>
                <button type="submit">Inscription</button>
            </form>
        <?php else : ?>
            <?php // Utilisateur connecté 
            ?>

        <?php endif; ?>
    </main>

    <footer>

    </footer>
</body>

</html>