<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Simpele login check (zoals in het boek met STATUS)
function checkLogin(){
    if(!isset($_SESSION["ID"]) || $_SESSION["STATUS"] != "ACTIEF"){
        echo "<script>
            alert('U heeft geen toegang tot deze pagina.');
            location.href='index.php?page=inloggen';
        </script>";
        exit;
    }
}

// Welke pagina wil je openen?
$page = "dashboard";
if(isset($_GET["page"])){
    $page = $_GET["page"];
}

// Router / switch (boek-stijl)
switch($page){

    case "dashboard":
        checkLogin();
        include("pages/dashboard.php");
    break;

    case "planning_overzicht":
        checkLogin();
        include("pages/planning_overzicht.php");
    break;

    default:
        checkLogin();
        include("pages/dashboard.php");
    break;
}
?>
