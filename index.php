<!-- Page qui va afficher le formulaire de connexion
Page qui va afficher le formulaire d'ajout de message
Page qui va afficher la liste des messages  -->


<?php

//********AFFICHAGE DES CATEGORIES */
//On se connecte à la base
require_once('inc/connect.php');

//Requête SQL
$sql = 'SELECT * FROM `categories` ORDER BY `name` ASC;';

//Requête non préparée
$query = $db->query($sql);

//on stocke le tableau des données dans $categories
$categories = $query->fetchALl(PDO::FETCH_ASSOC);


//********RECUPERATIONS DES INFOS DU POST */
//On vérifie si le formulaire est présent et remplis
if (isset($_POST) && !empty($_POST)) {
    //On veut vérifier le formulaire ligne par ligne
    //On charge notre lib de fonctions
    require_once('inc/lib.php');
    //on vérifie si les champs suivants sont remplis

    if (verifForm($_POST, ['titre', 'contenu', 'categories'])) {
        //On stocke le résultat dans des variables

        $titre = strip_tags($_POST['titre']);
        $contenu = strip_tags($_POST['contenu'], '<div><p><h1><h2><img><strong>');
        $categories = strip_tags($_POST['categories']);
        //On autorise certaines balises HTML dans le contenu


        //********AJOUT DES INFOS DANS LA TABLE MESSAGES */
        //Requête sql
        $sql = 'INSERT INTO `messages` (`title`,`content`, `users_id`) VALUES (:titre, :contenu, :user_id);';
        //bien respecter l'ordre des valeurs en fonction de la table

        //Requête préparée
        $query = $db->prepare($sql);

        //On injecte les valeurs dans la requête
        $query->bindValue(':titre', $titre, PDO::PARAM_STR);
        $query->bindValue(':contenu', $contenu, PDO::PARAM_STR);
        $query->bindValue('user_id', 1, PDO::PARAM_INT);
        //En attendant d'avoir des sessions on définit l'user à 1

        // On éxécute la requête
        $query->execute();

        //********AJOUT DES INFOS DANS LA TABLE MESSAGES  messages_categories*/
        //On doit mettre à jour l'id message et l'id catégorie dans cette table

        //On récupère l'id du message nouvellement crée
        //pour savoir à quel message inserrer la cat
        $idMessage = $db->lastInsertId();

        $sql = 'INSERT INTO `messages_categories`(`messages_id`, `categories_id`) VALUES (:idmessage, :idcategorie);';
        //On prépare la requête
        $query = $db->prepare($sql);
        //On injecte les valeurs
        $query->bindValue(':idmessage', $idMessage, PDO::PARAM_INT);
        $query->bindValue(':idcategorie', $categories, PDO::PARAM_INT);
        //On nettoie $categories dans la boucle
        //Pas besoin de nettoyer $idMessage car il vient d'un endroit clean

        //On éxécute la requête
        $query->execute();




        //AFFICHAGE DE TOUS LES MESSAGES
        //Requête SQL
        $sql = 'SELECT `messages`.*,
        GROUP_CONCAT(`categories`.`name`) as categorie_name
        FROM `messages`
        LEFT JOIN `messages_categories`
        ON `messages`.`id` = `messages_categories`.`messages_id`
        LEFT JOIN `categories`
        ON `messages_categories`.`categories_id` = `categories`.`id`
        GROUP BY `messages`.`id`
        ORDER BY `created_at` DESC;';

        //On lit tout ce qu'il y a dans la table message
        //group_concat permet de regrouper toutes les catégories d'un seul message
        //FROM message
        //LEFT JOIN sur message_categories
        //qd id de l'message = id de l'message dans la table message_catégorie
        //LEFT JOIN sur categories
        //qd id de la categorie = id de la categorie dans table catégorie
        //OU id de l'message = id de l'url
        //On groupe pour éviter les doublons
        //On affiche les messages par ordre de créations


        //Requête non préparée car on veut tous les messages
        $query = $db->query($sql);

        //On récupère les données de chaque message dans le tableau variable $messages
        $messages = $query->fetchAll(PDO::FETCH_ASSOC);
        //die(var_dump($messages));

        //On déconnecte la base de données
        require_once('inc/close.php');

    } else {
        //On affiche un warning si l'utilisateur n'a pas remplis le formulaire
        //les images ne seront par obligatoires
        echo "Attention il faut indiquer un titre, des catégories et un contenu";
    }
}




?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expression libre</title>
</head>

<body>
    <main>
        <section id="add-mess">
            <h1>Ajouter un message</h1>
            <form method="post" enctype="multipart/form-data">
                <!-- method="post" => pour récupérer dans la superglobale $_POST
    enctype="multipart/form-data" => pour uploader des fichiers -->

                <!-- Champs titre, contenu et catégories -->
                <div>
                    <label for="titre">Titre : </label>
                    <input type="text" id="titre" name="titre">
                </div>
                <div>
                    <label for="contenu">Contenu : </label>
                    <textarea name="contenu" id="contenu"></textarea>
                </div>
                <h2>Image</h2>
                <div>
                    <label for="image"> Image :</label>
                    <input type="file" name="image" id="image">
                </div>
                <h2>Catégories</h2>
                <!-- menu select des messages -->
                <label for="categories">Catégories</label>
                <select id="categories" name="categories">
                    <?php foreach ($categories as $categorie) : ?>
                    <div>
                        <option type="text" id="<?= $categorie['id'] ?>" value="<?= $categorie['id'] ?>"><?= $categorie['name'] ?></option>
                    </div>
                    <?php endforeach; ?>
                </select>
                <!-- Bouton Ajouter -->
                <button>Ajouter l'message</button>
            </form>
        </section>

        <section id="display-mess">
        <?php foreach ($messages as $message) : ?>
            <article>
                <h2>
                    <!-- TITRE DE l'ARRTICLE AVEC UN LIEN -->
                    <a href="message.php?id=<?= $message['id'] ?>"> <?= $message['title'] ?></a></h2>
                <div>
                    <p>
                        <!-- DATE DE PUBLICATION-->
                        Publié le <?= date('d/m/Y à H:i:s', strtotime($message['created_at'])) ?>
                        dans
                        <?php
                        //Notre GROUP_CONCAT crée un string : cat1, cat2, cat3 etc...
                        $categories = explode(',', $message['categorie_name']);
                        //Explode : On transforme ce string en tableau, chaque ligne du tableau apres les ","
                        
                        foreach ($categories as $categorie) {
                            //On fait une boucle qui affiche un lien pour chaque catégorie
                            echo '<a href="#">' . $categorie . '</a> ';
                        }
                        ?>
                    </p>
                </div>
                <!-- ON AFFICHE UNIQUEMENT UN EXTRAIT DE CHAQUE message-->
                <div><?= substr(strip_tags($message['content']), 0, 300) . '...' ?></div>

            </article>
        <?php endforeach; ?>

        </section>
    </main>
</body>

</html>