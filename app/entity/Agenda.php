<?php

namespace App\Entity;
use DateTime;
use PDO;


class Agenda
{
    public array $lundi;
    public array $mardi;
    public array $mercredi;
    public array $jeudi;
    public array $vendredi;
    public array $samedi;
    public array $dimanche;

    public function __construct()
    {
        $lundi = [];
    }

    public static function TakeIt(string $daterdv)
    {
        // Connexion à la base de données
        $dsn = "mysql:host=localhost;port=3306;dbname=agendor;charset=utf8";
        $dbUser = "root";
        $dbPassword = "";
        $lienDB = new PDO($dsn, $dbUser, $dbPassword);

        // requête SQL
        $sql = "SELECT * FROM rdvs WHERE date_rdv=:date";


        // Préparer la requête
        $query = $lienDB->prepare($sql);

        // Liaison des paramètres de la requête préparée
        $query->bindParam(":date", $daterdv, PDO::PARAM_STR);

        // Exécution de la requête
        if ($query->execute()) {
            // traitement des résultats
            $results = $query->fetch();
            
            // var_dump($results);
            return $results;
        }
    }
}
