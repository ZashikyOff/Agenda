<?php

namespace App\Entity;

use PDOException;
use Exception;
use Error;
use PDO;

class Utilisateur
{
    private int $id;
    private string $pseudo;
    private string $email;
    private string $hash;

    /** Contrustructeur */
    public function __construct($id = 0, $pseudo = "", $email = "", $hash = "")
    {
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->email = $email;
        $this->hash = $hash;
    }

    /** Accesseurs */

    /** Setter */
    public function __set($propriete, $valeur)
    {
        $this->$propriete = $valeur;
    }

    /** Getter */
    public function __get($propriete)
    {
        return $this->$propriete;
    }

    public function findByPseudo(string $pseudo): Utilisateur
    {
        try {
        } catch (PDOException | Exception | Error $e) {
        }

        return new Utilisateur();
    }

    public function findById(int $id): Utilisateur
    {
        return new Utilisateur();
    }

    public static function IsAdmin(string $email)
    {
        // Connexion à la base de données
        $dsn = "mysql:host=localhost;port=3306;dbname=agendor;charset=utf8";
        $dbUser = "root";
        $dbPassword = "";
        $lienDB = new PDO($dsn, $dbUser, $dbPassword);

        // requête SQL
        $sql = "SELECT admin FROM utilisateurs WHERE email=:email";


        // Préparer la requête
        $query = $lienDB->prepare($sql);

        // Liaison des paramètres de la requête préparée
        $query->bindParam(":email", $email, PDO::PARAM_STR);

        // Exécution de la requête
        if ($query->execute()) {
            // traitement des résultats
            $results = $query->fetch();

            // var_dump($results);
            return $results["admin"];
        }
    }
}
