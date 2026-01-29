<?php


require_once 'kringloop_centrum_duurzaam/config/database.php';
session_start();



if ($_SERVER['REQUEST_METHOD'] === 'POST') {




$ritten = $database->Query("SELECT id, in_uit, indeling, wanneer FROM ritten");
}