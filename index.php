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


<!-- 
$_SESSION : User with admin rights :
    => Etoilenoire@gmail.com
    => mdp : 123456 -->



<?php
$title = '';
$content = '';
$message = null;
$alertConnectRequired = '';
$alertAdminRequired = '';
$connected = '';


//SESSION
session_start();
//DB OPEN
require_once('inc/connect.php');
//functions library loading
require_once('inc/lib.php');

//********************************************************************************************************************************************************* */
//* $_SESSION UTILISATEUR
//*********** */

//A SUPPRIMER À LA FIN 

//Checking user's roles
if (verifForm($_SESSION, ['user'])) {
    //if there is a user connected, we check his role
    $roles = json_decode($_SESSION['user']['roles']);
    if (!in_array('ROLE_ADMIN', $roles)) {
        //$notAdmin = "Vous ne pouvez pas effectuer cette action car vous n'êtes pas administrateur";
    } else {
        $isAdmin = "Vous êtes connectés en tant qu'administrateur";
    }
} else {
    $noUser = "Il n'y a pas d'utilisateurs connectés";
}




//********************************************************************* USER'S CONNECTION */
if (isset($_POST['connect'])) {
    if (isset($_POST) && !empty($_POST)) {
        if (verifForm($_POST, ['mail', 'motdepasse'])) {
            $mail = strip_tags($_POST['mail']);
            $pass = $_POST['motdepasse'];
            require_once("inc/connect.php");
            $sql = 'SELECT * FROM `users` WHERE `email` = :email;';
            $query = $db->prepare($sql);
            $query->bindValue(':email', $mail, PDO::PARAM_STR);
            $query->execute();
            $user = $query->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                echo 'Email et/ou mot de passe invalide';
                die();
            } else {
                if (password_verify($pass, $user['password'])) {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['name'],
                        'roles' => $user['roles']
                    ];
                    if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                        $token = md5(uniqid());
                        setcookie('remember', $token, [
                            'expires' => strtotime('12 months'),
                            'sameSite' => 'strict'
                        ]);
                        $sql = 'UPDATE `users` SET `remember_token` = :token WHERE `id` = :id;';
                        $query = $db->prepare($sql);
                        $query->bindValue(':token', $token, PDO::PARAM_STR);
                        $query->bindValue('id', $user['id'], PDO::PARAM_INT);
                        $query->execute();

                        header('Location: index.php');
                    }
                } else {
                    echo "Email et/ou mot de passe invalide";
                }
            }
        } else {
            echo "Veuillez renseigner votre mot passe ET votre email";
        }
    }
}


//************************************************************************************************************************************************************************************************** */
//***** READ - `categories` */
$sql = 'SELECT * FROM `categories` ORDER BY `name` ASC;';
$query = $db->query($sql); //Query method
$categories = $query->fetchALl(PDO::FETCH_ASSOC);


//***** READ - `messages` && `messages_categories : JOIN */
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

//************************************************************************************************************************************************************************************************** */

//***CONDITION : DELETE CLICKED? */
//******************************* */
//** DELETING ONE MESSAGE */ */
//****************************** */
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    if (verifForm($_SESSION, ['user'])) {
        //if there is a user connected, we check his role
        $roles = json_decode($_SESSION['user']['roles']);

        if (!in_array('ROLE_ADMIN', $roles)) {
            $alertAdminRequired = "Vous ne pouvez pas effectuer cette action car vous n'êtes pas administrateur";
        } else {

            //****READ - Checking if the messages exists
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

            //***** DELETE - from `messages_categories`*/
            $sql = 'DELETE FROM `messages_categories` WHERE `messages_id` = :id;';
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();

            //***** DELETE - from `messages_categories`*/
            $sql = 'DELETE FROM `messages` WHERE `id` = :id;';
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            require_once('inc/close.php');

            //DELETING OLD IMAGES
            if ($message['featured_image'] != null) {
                $debutNom = pathinfo($message['featured_image'], PATHINFO_FILENAME);
                $fichiers = scandir(__DIR__ . '/uploads/');
                foreach ($fichiers as $fichier) {
                    if (strpos($fichier, $debutNom) === 0) {
                        unlink(__DIR__ . '/uploads/' . $fichier);
                    }
                }
            }
            //On redirige vers la page art_admin
            header('Location: index.php');
        }
    }
}



// else {
//     //header('Location: index.php');
//     echo "message d'erreur";
// }

//************************************************************************************************************************************************************************************************** */

//***CONDITION : UPDATE CLICKED? */
//**************************** */
//** UPDATING ONE MESSAGE */ */
//**************************** */
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    if (verifForm($_SESSION, ['user'])) {
        if (in_array('ROLE_ADMIN', $roles)) {


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
            //setting selected empty
            $selected = "";
            if ($message_cat['messages_id'] == $message['id']) {
                $selected = 'selected';
            }

            //***CONDITION : $_POST */
            if (isset($_POST['message'])) {
                if (isset($_POST) && !empty($_POST)) {
                    if (verifForm($_POST, ['titre', 'contenu', 'categories'])) {
                        $title = strip_tags($_POST['titre']);
                        $content = strip_tags($_POST['contenu']);
                        $id = strip_tags($_GET['edit']);


                        //***CONDITION : $_FILES */ IMAGES HANDELING - JPEG AND PNG ONLY
                        if (isset($_FILES) && !empty($_FILES)) {
                            if (isset($_FILES['image']) && !empty($_FILES['image']) && $_FILES['image']['error'] != 4) {
                                //DELETING OLD IMAGES
                                if ($message['featured_image'] != null) {
                                    $debutNom = pathinfo($message['featured_image'], PATHINFO_FILENAME);
                                    $fichiers = scandir(__DIR__ . '/uploads/');
                                    foreach ($fichiers as $fichier) {
                                        if (strpos($fichier, $debutNom) === 0) {
                                            unlink(__DIR__ . '/uploads/' . $fichier);
                                        }
                                    }
                                }
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
                                //*****UPDATE : `messages` */ IF NEW FEATURED IMAGE
                                $sql = 'UPDATE `messages` SET `title` = :title, `featured_image` = :image, `content` = :content, `users_id` = :user_id WHERE `id`=:id;';
                                $query = $db->prepare($sql);
                                $query->bindValue(':title', $title, PDO::PARAM_STR);
                                $query->bindValue(':content', $content, PDO::PARAM_STR);
                                $query->bindvalue(':id', $id, PDO::PARAM_INT);
                                $query->bindValue(':user_id', 1, PDO::PARAM_INT);
                                $query->bindValue(':image', $image_name, PDO::PARAM_STR);
                                $query->execute();
                            }
                            //*****UPDATE : `messages` */ IF NO NEW FEATURED IMAGE
                            $sql = 'UPDATE `messages` SET `title` = :title,`content` = :content, `users_id` = :user_id WHERE `id`=:id;';
                            $query = $db->prepare($sql);
                            $query->bindValue(':title', $title, PDO::PARAM_STR);
                            $query->bindValue(':content', $content, PDO::PARAM_STR);
                            $query->bindvalue(':id', $id, PDO::PARAM_INT);
                            $query->bindValue(':user_id', 1, PDO::PARAM_INT);
                            $query->execute();
                        }


                        ////*****DELETE : `messages_categories` (to replace by new entries) */
                        $sql = 'DELETE FROM `messages_categories` WHERE `messages_id` = :id;';
                        $query = $db->prepare($sql);
                        $query->bindvalue(':id', $id, PDO::PARAM_INT);
                        $query->execute();
                        $category = $_POST['categories'];

                        ////*****CREATE : `messages_categories` */
                        $sql = 'INSERT INTO `messages_categories`(`messages_id`, `categories_id`) VALUES (:idmessage, :idcategorie);';
                        $query = $db->prepare($sql);
                        $query->bindValue(':idmessage', $id, PDO::PARAM_INT);
                        $query->bindValue(':idcategorie', strip_tags($category), PDO::PARAM_INT);
                        $query->execute();
                    }
                    header('Location: index.php');
                }
            }
        } else {
            $alertAdminRequired = "Vous ne pouvez pas effectuer cette action car vous n'êtes pas administrateur";
        }
    }
    //************************************************************************************************************************************************************************************************** */
} else {
    //***CONDITION : $_POST['message'] */
    //**************************** */
    //** CREATING ONE MESSAGE */ */
    //**************************** */
    if (isset($_POST['message'])) {
        if (verifForm($_SESSION, ['user'])) {
            if (isset($_POST) && !empty($_POST)) {
                if (verifForm($_POST, ['titre', 'contenu', 'categories'])) {
                    $titre = strip_tags($_POST['titre']);
                    $contenu = strip_tags($_POST['contenu'], '<div><p><h1><h2><img><strong>');
                    $categories = strip_tags($_POST['categories']);

                    //***CONDITION : $_FILES */ IMAGES HANDELING - JPEG AND PNG ONLY
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

                    ////*****CREATE : `messages_categories` */
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
        } else {
            $alertConnectRequired = "Vous devez vous connecter pour écrire un message";
        }
    }
}

//if (verifForm($_SESSION, ['user'])){
//var_dump($_SESSION);
//************************************************************************************************************************************************************************************************** */
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
        <div>
            <?php if (verifForm($_SESSION, ['user'])) : ?>
                <p><?= $_SESSION['user']['name'] ?> est connecté</p>
            <?php else : ?>
                <?= $alertConnectRequired ?>
            <?php endif ?>
            <p><?= $alertAdminRequired ?></p>
        </div>
        <?php
        if (verifForm($_SESSION, ['user'])) {
            $roles = json_decode($_SESSION['user']['roles']);
            if (isset($_GET['edit']) && !empty($_GET['edit'])) {
                if (!in_array('ROLE_ADMIN', $roles)) {
                    //echo "<div> Vous ne pouvez pas effectuer cette action car vous n'êtes pas administrateur </div>";
                } else {
                    echo "<div> Vous êtes connectés en tant qu'administrateur </div>";
                }
            }
        } 
        ?>




    </header>
    <main>
        <section class="row" id="connect">
            <h2>Formulaire de connexion</h2>
            <form method="post">
                <div>
                    <label for="mail">Email :</label>
                    <input type="email" id="mail" name="mail">
                </div>
                <div>
                    <label for="pass">Mot de passe</label>
                    <input type="password" id="motdepasse" name="motdepasse">
                </div>
                <div>
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Rester connecté(e)</label>
                </div>
                <button name="connect">Me connecter</button>
            </form>
            <a href="oubli_pass.php">Mot de passe oublié ? Cliquez ici</a>

        </section>
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
                    <div class="input-group">
                        <!-- <div class="input-group-prepend">
                            <label for="titre">Titre : </label>
                        </div> -->
                        <input class="form-control" type="text" id="titre" name="titre" placeholder="<?php if (!$message) {
                                                                                                            echo "Titre de votre message";
                                                                                                        } else {
                                                                                                            echo "";
                                                                                                        };
                                                                                                        ?>" value="<?= $title ?>">
                    </div>
                    <div class="input-group">
                        <!-- <div class="input-group-prepend">
                            <label for="contenu">Contenu : </label>
                        </div> -->
                        <textarea class="form-control" name="contenu" id="contenu" placeholder="<?php if (!$message) {
                                                                                                    echo "Contenu de votre message";
                                                                                                } else {
                                                                                                    echo "";
                                                                                                };
                                                                                                ?>"><?= $content ?></textarea>
                    </div>
                    <h2>Image</h2>
                    <div>
                        <label for="image"> Image :</label>
                        <input type="file" name="image" id="image">
                    </div>

                    <h2>Catégories</h2>
                    <div class="d-flex flex-row justify-content-between">
                        <!-- SELECT MENU FROM `categories`-->
                        <div>
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
                        </div>
                        <button name="message" class="btn btn-primary">
                            <!-- change the button if post a new message or update an old one -->
                            <?php if (!$message) {
                                echo "Ajouter le message";
                            } else {
                                echo "Modifier le message";
                            };
                            ?>

                        </button>
                    </div>
                </form>
            </div>
        </section>


        <!-- SHOW ALL INPUTS OF `messages` -->
        <section id="display-mess">
            <h2>Vos messages</h2>
            <?php foreach ($messages as $message) : ?>
                <section class="row">
                    <!-- BUTTONS & TITLE -->
                    <div class="align-self-center col-12 d-flex flex-row justify-content-between">
                        <h2><a><?= $message['title'] ?> </a></h2>
                        <div class="align-self-center">
                            <?php if (verifForm($_SESSION, ['user'])) : ?>
                                <a class="btn btn-warning align-self-center" href="index.php?edit=<?= $message['id'] ?>">Modifier</a>
                                <a class="btn btn-danger align-self-center" href="index.php?delete=<?= $message['id'] ?>">Supprimer</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <p>
                            <!-- DATE & CATEGORIES -->
                            Publié le <?= date('d/m/Y à H:i:s', strtotime($message['created_at'])) ?>
                            par
                            <?php
                            $categories = explode(',', $message['categorie_name']);
                            //Explode : transform string into an array after each ','                        
                            foreach ($categories as $categorie) {
                                echo '<a href="#">' . $categorie . '</a> ';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="col-12">


                        <?php
                        // On vérifie si l'article a un image
                        if ($message['featured_image'] != null) :
                            // On a une image, on la traite et on l'affiche
                            // On sépare le nom et l'extension
                            $nom_image = pathinfo($message['featured_image'], PATHINFO_FILENAME);
                            $extension = pathinfo($message['featured_image'], PATHINFO_EXTENSION);

                            // On crée le nom de l'image à afficher
                            $image = $nom_image . '-300x300.' . $extension;

                            // On affiche l'image
                        ?>
                            <img src="uploads/<?= $image ?>" alt="<?= $message['title'] ?>" <?php
                                                                                        endif; ?>>
                            <?= substr(strip_tags($message['content']), 0, 300) . '...' ?>
                    </div>
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