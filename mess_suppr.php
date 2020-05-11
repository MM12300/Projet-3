<?php
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    //Checking if the messages exists
    $id = strip_tags($_GET['id']);
    require_once("inc/connect.php");
    $sql = 'SELECT * FROM `messages` WHERE `id` = :id;';
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $message = $query->fetch(PDO::FETCH_ASSOC);
    if (!$message) {
        header('Location: index.php');
    }

    //Removing in categories from the linked table `messages_categories`
    $sql = 'DELETE FROM `messages_categories` WHERE `messages_id` = :id;';
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();

    //Removing input id from `messages`
    $sql = 'DELETE FROM `messages` WHERE `id` = :id;';
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    require_once('inc/close.php');


    //On redirige vers la page art_admin
    header('Location: index.php');
} else {
    //header('Location: index.php');
    echo "message d'erreur";
}
