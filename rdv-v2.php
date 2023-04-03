<?php

require "app/entity/Agenda.php";

use App\Autoloader;
use App\Entity\Utilisateur;
use App\Alert;
use App\Entity\Agenda;

// Attention à ne pas oublier d'activer le mécanisme de session
session_name("agendor");
session_start();

// Fuseau horaire par défaut du serveur
// setlocale(LC_TIME, "fr_FR", "French"); // Déprécié !
date_default_timezone_set("Europe/Paris");

// Objet permettant de formater une date en français
$dateFormater = new IntlDateFormatter(
    'fr-FR',                    // Locale
    IntlDateFormatter::FULL,    // Date type
    IntlDateFormatter::FULL,    // Time type
    'Europe/Paris',             // Timezone
    IntlDateFormatter::GREGORIAN, // Calendrier
    "EEEE dd MMMM yyyy"         // Pattern
);
/* Patterns:
    https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
     */

// Traitement des date de réservations

// Réception de la date de réservation souhaitée
// if (!empty($_POST)) {
//     var_dump($_POST);
// }

if (isset($_GET["start-date"])) {
    // Un des liens précédent ou suivant du calendrier a été séléctionné
    // La date de départ doit y être adaptée
    try {
        $startDate = new DateTime($_GET["start-date"]);
    } catch (Exception | Error $e) {
        // Un petit malin à bidouiller le front ou l'url
        // Ignorer et reprendre la date d'aujourdhui
        $startDate = new DateTime();
    }
} else {
    // Aucune date de début demandée => date du jour
    $startDate = new DateTime();
}

$stringDate = $startDate->format("Y-m-d");

// Déterminer le jour de la semaine pour faire partir l'agenda du lundi
// Suivant pour Dimanche...
// Précendent pour les autre jours, hors lundi...
switch ($startDate->format("w")) {
    case 0:
        // Dimanche
        $calendrier["lundi"] = (new DateTime($stringDate))->modify("next monday");
        $calendrier["mardi"] = (new DateTime($stringDate))->modify("next thursday");
        $calendrier["mercredi"] = (new DateTime($stringDate))->modify("next wednesday");
        $calendrier["jeudi"] = (new DateTime($stringDate))->modify("next tuesday");
        $calendrier["vendredi"] = (new DateTime($stringDate))->modify("next friday");
        $calendrier["samedi"] = (new DateTime($stringDate))->modify("next saturday");
        $calendrier["dimanche"] = (new DateTime($stringDate))->modify("next sunday");
        break;
    case 1:
        // lundi
        $calendrier["lundi"] = new DateTime($stringDate);
        $calendrier["mardi"] = (new DateTime($stringDate))->modify("+1 day");
        $calendrier["mercredi"] = (new DateTime($stringDate))->modify("+2 days");
        $calendrier["jeudi"] = (new DateTime($stringDate))->modify("+3 days");
        $calendrier["vendredi"] = (new DateTime($stringDate))->modify("+4 days");
        $calendrier["samedi"] = (new DateTime($stringDate))->modify("+5 days");
        $calendrier["dimanche"] = (new DateTime($stringDate))->modify("+6 days");
        break;
    case 2:
        // mardi
        $calendrier["lundi"] = (new DateTime($stringDate))->modify("previous monday");
        $calendrier["mardi"] = new DateTime($stringDate);
        $calendrier["mercredi"] = (new DateTime($stringDate))->modify("+1 day");
        $calendrier["jeudi"] = (new DateTime($stringDate))->modify("+2 days");
        $calendrier["vendredi"] = (new DateTime($stringDate))->modify("+3 days");
        $calendrier["samedi"] = (new DateTime($stringDate))->modify("+4 days");
        $calendrier["dimanche"] = (new DateTime($stringDate))->modify("+5 days");
        break;
    case 3:
        // mercredi
        $calendrier["lundi"] = (new DateTime($stringDate))->modify("previous monday");
        $calendrier["mardi"] = (new DateTime($stringDate))->modify("-1 day");
        $calendrier["mercredi"] = new DateTime($stringDate);
        $calendrier["jeudi"] = (new DateTime($stringDate))->modify("+1 day");
        $calendrier["vendredi"] = (new DateTime($stringDate))->modify("+2 days");
        $calendrier["samedi"] = (new DateTime($stringDate))->modify("+3 days");
        $calendrier["dimanche"] = (new DateTime($stringDate))->modify("+4 days");
        break;
    case 4:
        // jeudi
        $calendrier["lundi"] = (new DateTime($stringDate))->modify("previous monday");
        $calendrier["mardi"] = (new DateTime($stringDate))->modify("-2 days");
        $calendrier["mercredi"] = (new DateTime($stringDate))->modify("-1 day");
        $calendrier["jeudi"] = new DateTime($stringDate);
        $calendrier["vendredi"] = (new DateTime($stringDate))->modify("next friday");
        $calendrier["samedi"] = (new DateTime($stringDate))->modify("next saturday");
        $calendrier["dimanche"] = (new DateTime($stringDate))->modify("next sunday");
        break;
    case 5:
        // vendredi
        $calendrier["lundi"] = (new DateTime($stringDate))->modify("previous monday");
        $calendrier["mardi"] = (new DateTime($stringDate))->modify("-3 days");
        $calendrier["mercredi"] = (new DateTime($stringDate))->modify("-2 days");
        $calendrier["jeudi"] = (new DateTime($stringDate))->modify("-1 day");
        $calendrier["vendredi"] = new DateTime($stringDate);
        $calendrier["samedi"] = (new DateTime($stringDate))->modify("+1 day");
        $calendrier["dimanche"] = (new DateTime($stringDate))->modify("+2 days");
        break;
    case 6:
        // samedi
        $calendrier["lundi"] = (new DateTime($stringDate))->modify("previous monday");
        $calendrier["mardi"] = (new DateTime($stringDate))->modify("-4 day");
        $calendrier["mercredi"] = (new DateTime($stringDate))->modify("-3 day");
        $calendrier["jeudi"] = (new DateTime($stringDate))->modify("-2 day");
        $calendrier["vendredi"] = (new DateTime($stringDate))->modify("-1 day");
        $calendrier["samedi"] = new DateTime($stringDate);
        $calendrier["dimanche"] = (new DateTime($stringDate))->modify("next sunday");
        break;
}

$debutSemainePrecedente = (new DateTime($calendrier["lundi"]->format("Y-m-d")))->modify("previous monday");
$debutSemaineSuivante = (new DateTime($calendrier["dimanche"]->format("Y-m-d")))->modify("next monday");

if (isset($_POST["date-resa"])) {
    require "app/config.php";

    // requête SQL
    $sql = "INSERT INTO rdvs (utilisateur, date_rdv) VALUES (:email, :rdv)";
    $email = $_SESSION["email"];
    $rdv = $_POST["date-resa"];


    // Préparer la requête
    $query = $lienDB->prepare($sql);

    // Liaison des paramètres de la requête préparée
    $query->bindParam(":email", $email, PDO::PARAM_STR);
    $query->bindParam(":rdv", $rdv, PDO::PARAM_STR);

    // Exécution de la requête
    if ($query->execute()) {
        // traitement des résultats
        $results = $query->fetch();
    }
}

if (isset($_SESSION["email"])) {

    require "app/config.php";

    // requête SQL
    $sql = "SELECT * FROM rdvs WHERE utilisateur=:email";


    // Préparer la requête
    $query = $lienDB->prepare($sql);

    // Liaison des paramètres de la requête préparée
    $query->bindParam(":email", $_SESSION["email"], PDO::PARAM_STR);

    // Exécution de la requête
    if ($query->execute()) {
        // traitement des résultats
        $resultsrdvlist = $query->fetchAll();
        // var_dump($resultsrdvlist);
    }
}

if (isset($_GET["deleterdv"])) {

    require "app/config.php";

    // requête SQL
    $sql = "DELETE FROM rdvs WHERE date_rdv = :daterdv";


    // Préparer la requête
    $query = $lienDB->prepare($sql);

    // Liaison des paramètres de la requête préparée
    $query->bindParam(":daterdv", $_GET["deleterdv"], PDO::PARAM_STR);

    // Exécution de la requête
    if ($query->execute()) {
        // traitement des résultats
        header('Location: rdv-v2.php');
        $results = $query->fetchAll();
        // var_dump($resultsrdvlist);
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prise de RDV v2</title>

    <link rel="stylesheet" href="main.css">
</head>

<body>
    <a href="app/logout.php">Home</a>
    <div class="agenda">
        <div class="agenda-nav">
            <div class="previous">
                <a href="?start-date=<?= $debutSemainePrecedente->format("Y-m-d"); ?>">&lt;&lt; <?= $dateFormater->format($debutSemainePrecedente); ?></a>
            </div>
            <div class="current">
                <a href="?start-date=<?= (new DateTime())->format("Y-m-d"); ?>"><?= $dateFormater->format($startDate); ?></a>
            </div>
            <div class="next">
                <a href="?start-date=<?= $debutSemaineSuivante->format("Y-m-d"); ?>"><?= $dateFormater->format($debutSemaineSuivante); ?> &gt;&gt;</a>
            </div>
        </div>

        <div class="agenda-header">
            <div class="hours-header">
                <!-- En-tête de la colonne des créneaux horaires -->
            </div>

            <?php foreach ($calendrier as $jour) :
                // Commencer les créneaux à 8h00
                $jour->setTime(10, 0); ?>
                <div class="day">
                    <?= $dateFormater->format($jour); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="hr">

        </div>

        <div class="agenda-body">
            <div class="hours">
                <?php $hour = new DateTime($calendrier["lundi"]->format("Y-m-d H:i")); ?>
                <?php for ($i = 0; $i < 5; $i++) : ?>
                    <div class="hour">
                        <?= $hour->format("H:i"); ?>
                    </div>
                    <?php $hour->modify("+120 minute"); ?>
                <?php endfor; ?>
                <?php $hour->setTime(10, 0); ?>
            </div>

            <?php
            /** Pseudo code */

            ?>
            <?php foreach ($calendrier as $jour) : ?>
                <div class="slots">
                    <?php $jour->setTime(10, 0); ?>
                    <?php for ($i = 0; $i < 5; $i++) : ?>
                        <div class="slot">
                            <?php if ((new DateTime()) < $jour) : ?>
                                <?php if (Agenda::TakeIt($jour->format("Y-m-d H:i")) == null || Agenda::TakeIt($jour->format("Y-m-d H:i")) == "") : ?>
                                    <form action="" method="post">
                                    <input type="hidden" name="date-resa" value="<?= $jour->format("Y-m-d H:i"); ?>">
                                    <button>Réserver pour <?= $jour->format("H:i"); ?></button>
                                </form>
                                <?php else : ?>
                                <button class="reserved">Reserver</button>
                                    <?php endif; ?>
                            <?php else : ?>
                                <button class="reserved"></button>
                            <?php endif; ?>
                        </div>
                        <?php $jour->modify("+120 minutes"); ?>
                    <?php endfor; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="agenda-footer">

        </div>
    </div>
    <div class="listrdv">
    <?php
                        $x = 0;
                        while ($x < (count($resultsrdvlist))) { ?>
                            <p value="<?= $resultsrdvlist[$x]["date_rdv"] ?>"><?= $resultsrdvlist[$x]["date_rdv"] ?></p>
                            <a href="?deleterdv=<?= $resultsrdvlist[$x]["date_rdv"] ?>">Delete</a>
                            <?php
                                                                                                                        $x++;
                                                                                                                    } ?>
    </div>
</body>

</html>