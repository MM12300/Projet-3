<?php
//*********************** */
//Fichier pour se connecter à une BDD (ici bdd_blog)
//*********************** */

//$db === Base de Données (BDD)
//hôte : local
//nom de la base de données : bdd (exemple)
//login : root - mot de passe : '' (vide)


try {
    //Connexion à la base de données
    $db = new PDO('mysql:host=localhost;dbname=projet-3', 'root', '');

    //On force les échanges en UTF8
    $db->exec('SET NAMES "UTF8"');

}catch (PDOException $e) {
    //en cas de problème on émet un message d'erreur
    echo 'Erreur: ' . $e->getMessage();
    die;
}