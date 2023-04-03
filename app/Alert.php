<?php
    namespace App;

    class Alert {
        private string $type;
        private string $title;
        private string $content;
        private string $footer;

        public function __construct() {
            $this-> type = "warning";
            $this-> title = "Attention !";
            $this-> content = "Faites attention !";
            $this-> footer = "";
        }
    }