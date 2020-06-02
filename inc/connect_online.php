<?php
require_once('inc/brouette.php');
try {
    //Connexion à la base de données
    $db = new PDO('mysql:host='. $Aa . ';dbname=' . $Bb . ',' . $Cc . ',' . $Dd);

    //On force les échanges en UTF8
    $db->exec('SET NAMES "UTF8"');

}catch (PDOException $e) {
    //en cas de problème on émet un message d'erreur
    echo 'Erreur: ' . $e->getMessage();
    die;
}