<!-- Toute information relative à ce projet (commentaires du code) trouvable dans le projet blog -->

<!-- PHP : 
- READ : collect categorie information to display in the form/select 
- FORM : checking/security
    - CREATE : new input in `messages`  `messages_categories`
    - CREATE : new input in `messages_categories`
    - READ : display inputs from `messages`

HTML :
- #add-mess : FORM for user
- #display-mess : Show all from `messages` with `messages_categories` -->



<?php
$title = '';
$content = '';
$message = null;
//DB OPEN
require_once('inc/connect.php');
//functions library loading
require_once('inc/lib.php');


//***** READ - `categories` */
$sql = 'SELECT * FROM `categories` ORDER BY `name` ASC;';
$query = $db->query($sql); //Query method
$categories = $query->fetchALl(PDO::FETCH_ASSOC);


//***** READ - */
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
$query = $db->query($sql);
$messages = $query->fetchAll(PDO::FETCH_ASSOC);





//***** DELETE - DELETING * FROM MESSAGE WHERE ID */
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    //Checking if the messages exists
    $id = strip_tags($_GET['delete']);
    $sql = 'SELECT * FROM `messages` WHERE `id` = :id;';
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $message = $query->fetch(PDO::FETCH_ASSOC);
    if (!$message) {
        //header('Location: index.php');
        echo "le message que vous voulez supprimer n'existe pas";
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

    //DELETING OLD IMAGES
    if ($message['featured_image'] != null) {
        $debutNom = pathinfo($message['featured_image'], PATHINFO_FILENAME);

        //On récupère la liste des fichiers dans le dossier "uploads" dans un tableau
        $fichiers = scandir(__DIR__ . '/uploads/');

        //On boucle sur les fichier scar c'est un tableau
        foreach ($fichiers as $fichier) {
            //Si le nom du fichier commence par la même chose que celle du fichier précédemment uploadé ($debutnom), alors on le supprime.
            //strpos renvoit 0 si les deux STR comparés ont le mm début
            if (strpos($fichier, $debutNom) === 0) {
                // attention pas ==, car on compare en valeur et en type, et si !===0, c'est égal à false, donc aussi valeur 0
                unlink(__DIR__ . '/uploads/' . $fichier);
            }
        }
    }

    //On redirige vers la page art_admin
    header('Location: index.php');
}

// else {
//     //header('Location: index.php');
//     echo "message d'erreur";
// }






//***** UPDATE - */
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    //****READ - Checking if the messages exists
    $id = strip_tags($_GET['edit']);
    $sql = 'SELECT * FROM `messages` WHERE `id` = :id;';
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $message = $query->fetch(PDO::FETCH_ASSOC);

    $title = $message['title'];
    $content = $message['content'];
    if (!$message) {
        //header('Location: index.php');
        echo "le message que vous voulez modifier n'existe pas";
    };

    //*****READ - If message exists we want to know its category
    $sql = 'SELECT * FROM `messages_categories` WHERE `messages_id` = :id;';
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $message_cat = $query->fetch(PDO::FETCH_ASSOC);

    //die(var_dump($message_cat['messages_id']));

    $selected = "";
    if ($message_cat['messages_id'] == $message['id']) {
        $selected = 'selected';
    }


    if (isset($_POST) && !empty($_POST)) {
        if (verifForm($_POST, ['titre', 'contenu', 'categories'])) {
            $title = strip_tags($_POST['titre']);
            $content = strip_tags($_POST['contenu']);
            $id = strip_tags($_GET['edit']);


            //DELETING OLD IMAGES
            if ($message['featured_image'] != null) {
                $debutNom = pathinfo($message['featured_image'], PATHINFO_FILENAME);
                //On récupère la liste des fichiers dans le dossier "uploads" dans un tableau
                $fichiers = scandir(__DIR__ . '/uploads/');

                //On boucle sur les fichier scar c'est un tableau
                foreach ($fichiers as $fichier) {
                    //Si le nom du fichier commence par la même chose que celle du fichier précédemment uploadé ($debutnom), alors on le supprime.
                    //strpos renvoit 0 si les deux STR comparés ont le mm début
                    if (strpos($fichier, $debutNom) === 0) {
                        // attention pas ==, car on compare en valeur et en type, et si !===0, c'est égal à false, donc aussi valeur 0
                        unlink(__DIR__ . '/uploads/' . $fichier);
                    }
                }
            }
            //-------- jusque la ca marche

            //IMAGES HANDELING - JPEG AND PNG ONLY
            if (isset($_FILES) && !empty($_FILES)) {
                if (isset($_FILES['image']) && !empty($_FILES['image']) && $_FILES['image']['error'] != 4) {
                    $image = $_FILES['image'];
                    if ($image['error'] != 0) {
                        echo "Une erreur s'\est produite lors du chargement de votre fichier";
                        die;
                    }
                    $types = ['image/png', 'image/jpeg'];
                    if (!in_array($image['type'], $types)) {
                        $_SESSION['error'] = "le type de fichier doit être un jpeg ou png";
                        header('Location:art_ajout.php');
                        die;
                    }
                    if ($image['size'] > 1048576) {
                        echo "Le fichier est trop volumineux";
                        die;
                    }
                    $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
                    $image_name = md5(uniqid()) . '.' . $extension;
                    $nomImageComplet = __DIR__  . '/uploads/' . $image_name;
                    if (!move_uploaded_file($image['tmp_name'], $nomImageComplet)) {
                        echo "le fichier n'a pas été copié";
                        die;
                    } else {
                        echo "Le fichier a été uploadé";
                    }
                    resizeImage($image_name, 75);
                    resizeImage($image_name, 25);
                    thumb(300, $image_name);
                }
            }




            //**************** On met à jour la BDD : Messages
            //Requête SQL
            $sql = 'UPDATE `messages` SET `title` = :title, `featured_image` = :image, `content` = :content, `users_id` = :user_id WHERE `id`=:id;';
            $query = $db->prepare($sql);
            $query->bindValue(':title', $title, PDO::PARAM_STR);
            $query->bindValue(':content', $content, PDO::PARAM_STR);
            $query->bindvalue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':user_id', 1, PDO::PARAM_INT);
            $query->bindValue(':image', $image_name, PDO::PARAM_STR);
            $query->execute();


            //****************On met à jour la BDD articles_categories
            //On efface d'abord toutes les catégories associées à cet message
            //Puis on les écris à nouveau

            //Requête SQL
            $sql = 'DELETE FROM `messages_categories` WHERE `messages_id` = :id;';
            $query = $db->prepare($sql);
            $query->bindvalue(':id', $id, PDO::PARAM_INT);
            //On éxécute la requête
            $query->execute();

            //On récupère dans $_POST les catégories qui sont cochées
            $category = $_POST['categories'];
            //On ajoute les catégories
            $sql = 'INSERT INTO `messages_categories`(`messages_id`, `categories_id`) VALUES (:idmessage, :idcategorie);';
            $query = $db->prepare($sql);
            $query->bindValue(':idmessage', $id, PDO::PARAM_INT);
            $query->bindValue(':idcategorie', strip_tags($category), PDO::PARAM_INT);
            $query->execute();
        }



        header('Location: index.php');
    }



    //***** CREATE - `messages`
} else {
    if (isset($_POST) && !empty($_POST)) {
        //verifForm => $_POST input/select checking
        if (verifForm($_POST, ['titre', 'contenu', 'categories'])) {
            //FORM resultats, variable creation and security
            $titre = strip_tags($_POST['titre']);
            $contenu = strip_tags($_POST['contenu'], '<div><p><h1><h2><img><strong>');
            $categories = strip_tags($_POST['categories']);

            //IMAGES HANDELING - JPEG AND PNG ONLY
            if (isset($_FILES) && !empty($_FILES)) {
                if (isset($_FILES['image']) && !empty($_FILES['image']) && $_FILES['image']['error'] != 4) {
                    $image = $_FILES['image'];
                    if ($image['error'] != 0) {
                        echo "Une erreur s'\est produite lors du chargement de votre fichier";
                        die;
                    }
                    $types = ['image/png', 'image/jpeg'];
                    if (!in_array($image['type'], $types)) {
                        $_SESSION['error'] = "le type de fichier doit être un jpeg ou png";
                        header('Location:art_ajout.php');
                        die;
                    }
                    if ($image['size'] > 1048576) {
                        echo "Le fichier est trop volumineux";
                        die;
                    }
                    $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
                    $image_name = md5(uniqid()) . '.' . $extension;
                    $nomImageComplet = __DIR__  . '/uploads/' . $image_name;
                    if (!move_uploaded_file($image['tmp_name'], $nomImageComplet)) {
                        echo "le fichier n'a pas été copié";
                        die;
                    } else {
                        echo "Le fichier a été uploadé";
                    }
                    resizeImage($image_name, 75);
                    resizeImage($image_name, 25);
                    thumb(300, $image_name);
                }
            }


            $sql = 'INSERT INTO `messages` (`title`,`content`, `featured_image`, `users_id`) VALUES (:titre, :contenu, :image, :user_id);';
            $query = $db->prepare($sql); //Prepare method
            $query->bindValue(':titre', $titre, PDO::PARAM_STR);
            $query->bindValue(':contenu', $contenu, PDO::PARAM_STR);
            $query->bindValue(':image', $image_name, PDO::PARAM_STR);
            $query->bindValue('user_id', 1, PDO::PARAM_INT); //USER DEFINED TO ONE BECAUSE NO $_SESSION AT THE MOMENT
            $query->execute();

            //collecting the message_id for the next step
            $idMessage = $db->lastInsertId();


            //***** CREATE - `messages_categories`
            $sql = 'INSERT INTO `messages_categories`(`messages_id`, `categories_id`) VALUES (:idmessage, :idcategorie);';
            $query = $db->prepare($sql); //Prepare method
            $query->bindValue(':idmessage', $idMessage, PDO::PARAM_INT);
            $query->bindValue(':idcategorie', $categories, PDO::PARAM_INT);
            $query->execute();


            header('Location: index.php');
        } else {
            //On affiche un warning si l'utilisateur n'a pas remplis le formulaire
            //les images ne seront par obligatoires
            echo "Attention il faut indiquer un titre, des catégories et un contenu";
        }
    }
}

?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <!-- STYLE.CSS -->
    <link rel="stylesheet" href="style.css">


    <title>Expression libre</title>
</head>

<body class="container">
    <header class="row">
        
    </header>
    <main>
        <!-- Input to  `messages` -->
        <section class="row" id="add-mess">
            <div class="col-12">
                <h2><?php if (!$message) {
                            echo "Ajouter un message";
                        } else {
                            echo "Modifier votre message";
                        };
                        ?></h2>
                <form method="post" enctype="multipart/form-data">
                    <div>
                        <label for="titre">Titre : </label>
                        <input type="text" id="titre" name="titre" value="<?= $title ?>">
                    </div>
                    <div>
                        <label for="contenu">Contenu : </label>
                        <textarea name="contenu" id="contenu"><?= $content ?></textarea>
                    </div>
                    <h2>Image</h2>
                    <div>
                        <label for="image"> Image :</label>
                        <input type="file" name="image" id="image">
                    </div>
                    <h2>Catégories</h2>
                    <!-- SELECT MENU FROM `categories`-->
                    <label for="categories">Catégories</label>
                    <!-- Need to know which category has the message when updating it -->
                    <?php

                    ?>

                    <select id="categories" name="categories" <?= $selected ?>>
                        <!-- Creating a list of categories  -->
                        <?php foreach ($categories as $categorie) : ?>
                        <div>
                            <option type="text" id="<?= $categorie['id'] ?>" value="<?= $categorie['id'] ?>"><?= $categorie['name'] ?></option>
                        </div>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary">
                        <!-- change the button if post a new message or update an old one -->
                        <?php if (!$message) {
                            echo "Ajouter le message";
                        } else {
                            echo "Modifier le message";
                        };
                        ?>

                    </button>
                </form>
            </div>
        </section>
        <!-- SHOW ALL INPUTS OF `messages` -->
        <section id="display-mess">
            <h2>Vos messages</h2>
            <?php foreach ($messages as $message) : ?>
            <section class="row">
                <!-- BUTTONS & TITLE -->
                <div class="col-12 d-flex flex-row">
                <h2><a><?= $message['title'] ?></a></h2>            
                <a href="index.php?edit=<?= $message['id'] ?>">Modifier</a>
                <a href="index.php?delete=<?= $message['id'] ?>">Supprimer</a>
                </div>
                <div class="col-12">
                    <p>
                        <!-- DATE & CATEGORIES -->
                        Publié le <?= date('d/m/Y à H:i:s', strtotime($message['created_at'])) ?>
                        dans
                        <?php
                            $categories = explode(',', $message['categorie_name']);
                            //Explode : transform string into an array after each ','                        
                            foreach ($categories as $categorie) {
                                echo '<a href="#">' . $categorie . '</a> ';
                            }
                            ?>
                    </p>
                </div>
                <div class="col-12"><?= substr(strip_tags($message['content']), 0, 300) . '...' ?></div>
                <?php
                    // On vérifie si l'article a un image
                    if ($message['featured_image'] != null) :
                        // On a une image, on la traite et on l'affiche
                        // On sépare le nom et l'extension
                        $nom_image = pathinfo($message['featured_image'], PATHINFO_FILENAME);
                        $extension = pathinfo($message['featured_image'], PATHINFO_EXTENSION);

                        // On crée le nom de l'image à afficher
                        $image = $nom_image . '-75pourcent.' . $extension;

                        // On affiche l'image
                        ?>
                <img src="uploads/<?= $image ?>" alt="<?= $message['title'] ?>">

                <?php
                    endif;
                    ?>
            </section>
            <?php endforeach; ?>


            

        </section>
    </main>
    <!-- BOOTSTRAP    -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>

</html>