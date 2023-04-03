<?php
    namespace App\Entity;
    use PDOException;
    use Exception;
    use Error;

    class Utilisateur {
        private int $id;
        private string $pseudo;
        private string $email;
        private string $hash;

        /** Contrustructeur */
        public function __construct($id=0, $pseudo="", $email="", $hash="") {
            $this-> id = $id;
            $this-> pseudo = $pseudo;
            $this-> email = $email;
            $this-> hash = $hash;
        }

        /** Accesseurs */

        /** Setter */
        public function __set($propriete, $valeur) {
            $this-> $propriete = $valeur;
        }

        /** Getter */
        public function __get($propriete) {
            return $this-> $propriete;
        }

        public function findByPseudo(string $pseudo): Utilisateur {
            try {

            } catch (PDOException|Exception|Error $e) {

            }

            return new Utilisateur();
        }

        public function findById(int $id): Utilisateur {
            return new Utilisateur();
        }
    }