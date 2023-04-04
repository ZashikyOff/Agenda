<?php

namespace App;

class Alert
{
    private string $type;
    private string $title;
    private string $content;
    private string $footer;

    public function __construct()
    {
        $this->type = "warning";
        $this->title = "Attention !";
        $this->content = "Faites attention !";
        $this->footer = "";
    }

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

    public static function AlertBox(string $type){
        if($type == "IsAdmin"){
            ?>
            <div class="alert">
                <form action="" method="post">
                <p>Vous etes un admin par conséquent vous n'êtes pa autoriser a prendre rendez vous</p>
                <button type="submit">OK</button>
                </form>
            </div>
            <?php
        }
        if($type == "rdvtakeit"){
            ?>
            <div class="alert">
                <form action="" method="post">
                <p>Cette horaire est deja prit, veuillez choisir un autre horaire</p>
                <a href="?" class="buttonstyle">OK</a>
                </form>
            </div>
            <?php
        }
    }
}
