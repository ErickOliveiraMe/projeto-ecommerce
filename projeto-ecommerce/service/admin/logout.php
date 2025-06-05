<?php
    session_start(['cookie_path' => '/Projetos/nexcommerce']);
    session_unset();
    session_destroy();
    header("Location: ../login/login.php");
    exit;
?>