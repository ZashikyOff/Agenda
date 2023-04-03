<?php
    session_name("agendor");
    session_start();
    session_destroy();
    header("Location: ../index.php");
