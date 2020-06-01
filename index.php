<!-- Toute information relative à ce projet (commentaires du code) trouvable dans le projet blog -->

<?php
$title = '';
$content = '';
$message = null;
$alertConnectRequired = '';
$alertAdminRequired = '';
$connected = '';
$alertConnexion = '';
$erreurs = [];
$selected = '';

//SESSION
session_start();
//DB OPEN
require_once('inc/connect.php');
//functions library loading
require_once('inc/lib.php');

//********************************************************************************************************************************************************* */
//* $_SESSION UTILISATEUR
//*********** */

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
                $erreurs[] = "Email et/ou mot de passe invalide4";
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
                    $erreurs[] = "Email et/ou mot de passe invalide2";
                }
            }
        } else {
            $erreurs[] = "Email et/ou mot de passe invalide : veuillez saisir un mot de passe ET un identifiant";
        }
    }
}
//********************************************************************* USER'S DISCONNECTION */
if (isset($_POST['disconnect'])) {
    unset($_SESSION['user']);

    // On efface l'éventuel cookie 'remember'
    setcookie('remember', '', 1);

    header('Location: index.php');
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
            $erreurs[] = "Vous ne pouvez pas effectuer cette action car vous n'êtes pas administrateur";
        } else {
            //****READ - Checking if the messages exists
            $id = strip_tags($_GET['delete']);
            $sql = 'SELECT * FROM `messages` WHERE `id` = :id;';
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $message = $query->fetch(PDO::FETCH_ASSOC);
            if (!$message) {
                $erreurs[] = "le message que vous voulez supprimer n'existe pas";
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
            header('Location: index.php');
        }
    }
}

//************************************************************************************************************************************************************************************************** */

//***CONDITION : UPDATE CLICKED? */
//**************************** */
//** UPDATING ONE MESSAGE */ */
//**************************** */
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    if (verifForm($_SESSION, ['user'])) {
        $roles = json_decode($_SESSION['user']['roles']);
        if (in_array('ROLE_ADMIN', $roles)) {
            //****READ - Checking if the messages exists
            $id = strip_tags($_GET['edit']);
            $sql = 'SELECT * FROM `messages` WHERE `id` = :id;';
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $message = $query->fetch(PDO::FETCH_ASSOC);

            if (!$message) {
                //header('Location: index.php');
                $erreurs[] =  "le message que vous voulez modifier n'existe pas";
            } else {

                $title = $message['title'];
                $content = $message['content'];

                //*****READ - If message exists we want to know its category
                $sql = 'SELECT * FROM `messages_categories` WHERE `messages_id` = :id;';
                $query = $db->prepare($sql);
                $query->bindValue(':id', $id, PDO::PARAM_INT);
                $query->execute();
                $message_cat = $query->fetch(PDO::FETCH_ASSOC);

                //die(var_dump($message_cat));


                if ($message_cat['messages_id'] == $message['id']) {
                    $selected = 'selected';
                    //die(var_dump($message_cat));
                }


                //***CONDITION : $_POST */
                if (isset($_POST['message'])) {
                    if (isset($_POST) && !empty($_POST)) {
                        if (verifForm($_POST, ['titre', 'contenu', 'categories'])) {
                            $titre = strip_tags($_POST['titre']);
                            $contenu = strip_tags($_POST['contenu']);
                            $id = strip_tags($_GET['edit']);

                            //***CONDITION : content should be 30chars minimum
                            if ((strlen($contenu) < 30) || (strlen($contenu) > 100)) {
                                $erreurs[] = "Le contenu du message doit contenir entre 30 et 100 caractères";
                            }

                            //***CONDITION : content should be 30chars minimum
                            if ((strlen($titre) < 3) || (strlen($titre) > 30)) {
                                $erreurs[] = "Le titre doit contenir entre 3 et 30 caractères";
                            }

                            if($_POST['categories'] = "5") {
                                $erreurs[] = "Veuillez indiquer une catégorie à votre message";
                            }

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
                                        $erreurs[] = "Une erreur s'\est produite lors du chargement de votre fichier";
                                    }
                                    $types = ['image/png', 'image/jpeg'];
                                    if (!in_array($image['type'], $types)) {
                                        $erreurs[] = "le type de fichier doit être un jpeg ou png";
                                    }
                                    if ($image['size'] > 1048576) {
                                        $erreurs[] = "Le fichier est trop volumineux";
                                    }
                                    $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
                                    $image_name = md5(uniqid()) . '.' . $extension;
                                    $nomImageComplet = __DIR__  . '/uploads/' . $image_name;
                                    if (!move_uploaded_file($image['tmp_name'], $nomImageComplet)) {
                                        $erreurs[] = "le fichier n'a pas été copié";
                                    } else {
                                        $erreurs[] = "Le fichier a été uploadé";
                                    }
                                    thumb(100, $image_name);
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
                            }

                            if ($erreurs == null) {
                                //*****UPDATE : `messages` */ IF NO NEW FEATURED IMAGE
                                $sql = 'UPDATE `messages` SET `title` = :title,`content` = :content, `users_id` = :user_id WHERE `id`=:id;';
                                $query = $db->prepare($sql);
                                $query->bindValue(':title', $titre, PDO::PARAM_STR);
                                $query->bindValue(':content', $contenu, PDO::PARAM_STR);
                                $query->bindvalue(':id', $id, PDO::PARAM_INT);
                                $query->bindValue(':user_id', 1, PDO::PARAM_INT);
                                $query->execute();

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

                                header('Location: index.php');
                            }
                        }
                    }
                }
            }
        } else {
            $erreurs[] = "Vous ne pouvez pas effectuer cette action car vous n'êtes pas administrateur";
        }
    }
    //************************************************************************************************************************************************************************************************** */
} else {
    //***CONDITION : $_POST['message'] */
    //**************************** */
    //** CREATING ONE MESSAGE */ */
    //**************************** */
    if (isset($_POST['message'])) {
        //die(var_dump('entrée dans le form'));
        if (verifForm($_SESSION, ['user'])) {
            if (isset($_POST) && !empty($_POST) && $_POST['categories'] != "5") {
                if (verifForm($_POST, ['titre', 'contenu', 'categories'])) {
                    $titre = strip_tags($_POST['titre']);
                    $contenu = strip_tags($_POST['contenu'], '<div><p><h1><h2><img><strong>');
                    $categorie = strip_tags($_POST['categories']);

                    //***CONDITION : content should be 30chars minimum
                    if ((strlen($contenu) < 30) || (strlen($contenu) > 100)) {
                        $erreurs[] = "Le contenu du message doit contenir entre 30 et 100 caractères";
                    }

                    //***CONDITION : content should be 30chars minimum
                    if ((strlen($titre) < 3) || (strlen($titre) > 30)) {
                        $erreurs[] = "Le titre doit contenir entre 3 et 30 caractères";
                    }

                    //***CONDITION : $_FILES */ IMAGES HANDELING - JPEG AND PNG ONLY
                    if (isset($_FILES) && !empty($_FILES)) {
                        if (isset($_FILES['image']) && !empty($_FILES['image']) && $_FILES['image']['error'] != 4) {
                            $image = $_FILES['image'];
                            if ($image['error'] != 0) {
                                $erreurs[] = "Une erreur s'\est produite lors du chargement de votre fichier";
                            }
                            $types = ['image/png', 'image/jpeg'];
                            if (!in_array($image['type'], $types)) {
                                $erreurs[] = "le type de fichier doit être un jpeg ou png";
                            }
                            if ($image['size'] > 1048576) {
                                $erreurs[] = "Le fichier est trop volumineux";
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
                            thumb(300, $image_name);
                        } else {
                            $erreurs[] = "Vous devez ajouter une image pour accompagner votre message";
                        }
                    }


                    if ($erreurs == null) {
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
                        $query->bindValue(':idcategorie', $categorie, PDO::PARAM_INT);
                        $query->execute();

                        header('Location: index.php');
                    };
                } else {
                    $erreurs[] = "Attention il faut indiquer un titre, des catégories et un contenu pour écrire un message2";
                }
            } else {
                $erreurs[] = "Attention il faut indiquer un titre, des catégories et un contenu pour écrire un message1";
            }
        } else {
            $erreurs[] = "Vous devez vous connecter pour ajouter un message";
        }
    }
}

//************************************************************************************************************************************************************************************************** */
?>

<!--******************************************
 *******************  HTML ***************************
***********************************************-->
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <!-- STYLE.CSS -->
    <link rel="stylesheet" href="style.css">
    <title>C.R.U.D. One-Page</title>
</head>


<!-- ************** BODY **************** -->

<body class="container">
    <!--* *************************************** TITLE -->
    <div class="d-flex flex-row justify-content-space-between">
        <h1>Le C.R.U.D. sur une page</h1>
    </div>
    <header class="row">
        <!-- -----------------WHO IS CONNECTED -------------------------->
        <?php
        if (isset($_SESSION)) : ?>
        <?php
            if (verifForm($_SESSION, ['user'])) : ?>
        <?php
                $roles = json_decode($_SESSION['user']['roles']);
                if (in_array('ROLE_ADMIN', $roles) || in_array('ROLE_USER', $roles)) : ?>
        <div class="alerts">
            <p><?= $_SESSION['user']['name'] ?> est connecté</p>
        </div>
        <?php endif ?>
        <?php else : ?>
        <div class="alerts">
            <p>Aucun utilisateur connecté</p>
        </div>
        <?php endif ?>
        <?php endif ?>
        <!--* *************************************** INTRODUCTION ************************************ -->
        <div id="intro">
            <h2>Introduction à la gestion de base de données MySQL et au langage PHP (procédural uniquement).</h2>
            <p>Pour illuster le <a href="https://en.wikipedia.org/wiki/Create,_read,_update_and_delete">C.R.U.D.</a>voici une page qui s'apparente à une livre d'or ou à une section de commentaire comme on peut en retrouver sur beaucoup de sites. L'ensemble des fonctionnalités du CRUD sont présentes sur une seule page. Pas de gestion de données en AJAX et principalement du PHP-procédural (sauf pour le PDO). Mise en page classique avec <a href="https://getbootstrap.com">Bootstrap</a>.</p>
            <h3>Mode d'emploi : </h3>
            <ul>
                <li>
                    À savoir avant de commencer :
                    <ul>
                        <li>
                            Pour faciler l'utilisation de cette page, il n'y a pas d'inscription. En conséquence j'ai crée deux comptes : "utilisateur" et "administrateur", qui ont des droits différents que vous pouvez trouver dans le tableau ci-dessous.
                        </li>
                        <li>
                            Ajoutez une image au format carré pour accompagner votre message.
                        </li>
                    </ul>
                </li>
                <li>
                    Pour écrire un message : connectez vous avec "utilisateur" ou "administrateur". Donnez un titre, un contenu, une catégorie et une image (carré de préférence) à votre message avant de l'envoyer.
                </li>
                <li>
                    Pour effacer ou modifier un message : connectez vous avec "administrateur" ("utilisateur n'a pas les droits nécessaires").
                </li>
            </ul>
            <!-- <p>Mode d'emploi : Utilisez les identifiants présents dans le tableau ci-dessous pour tester les fonctionnalités d'ajout, de modification et de supression de message.</p> -->
            <!-- <p>Ici j'ai voulu faciliter l'utilisation de cette page en vous évitant de devoir créer un compte. Bien entendu sur un site en production, on évitera de donner des identifiants. </p> -->
            <div class="d-flex justify-content-center">
                <table class="table tableau">
                    <thead>
                        <tr>
                            <th scope="col">Type d'utilisateur</th>
                            <th scope="col">E-mail</th>
                            <th scope="col">Mot de passe</th>
                            <th scope="col">Fonctionnalité</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Non-connecté</td>
                            <td>-</td>
                            <td>-</td>
                            <td>Ne peut pas poster de message</td>
                        </tr>
                        <tr>
                            <td>Utilisateur classique</td>
                            <td>user@gmail.com</td>
                            <td>654321</td>
                            <td>Peut ajouter un message (affiche les boutons modifier/supprimer)</td>
                        </tr>
                        <tr>
                            <td>Administrateur</td>
                            <td>admin@gmail.com</td>
                            <td>123456</td>
                            <td>Peut ajouter/modifier/supprimer un message</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </header>
    <!-- MAIN = CONNECT FORM + NEW MESSAGE FORM -->
    <!-- CONNECT FORM********************************************************* -->
    <main class="row">
        <section class="col-12 d-flex flex-column" id="connect">
            <h2>Formulaire de connexion</h2>
            <form class="d-flex flex-row justify-content-between" method="post">
                <div class="input-group input_connect">
                    <input class="form-control" type="email" id="mail" name="mail" placeholder="E-mail">
                </div>
                <div class="input-group input_connect">
                    <input class="form-control" type="password" id="motdepasse" name="motdepasse" placeholder="Mot de passe">
                </div>
                <div class="align-self-center">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Rester connecté(e)</label>
                </div>
                <!-- CONNECT AND DISCONNECT BUTTONS -->
                <?php
                if (isset($_SESSION)) : ?>
                <?php
                    if (verifForm($_SESSION, ['user'])) : ?>
                <?php
                        $roles = json_decode($_SESSION['user']['roles']);
                        if (in_array('ROLE_ADMIN', $roles) || in_array('ROLE_USER', $roles)) : ?>
                <button class="btn btn-primary" name="disconnect">Me déconnecter</button>
                <?php endif ?>
                <?php endif ?>
                <button class="btn btn-primary" name="connect">Me connecter</button>
                <?php endif ?>
            </form>
        </section>
        <!-- NEW MESSAGE FORM ***************************************    Input to  `messages` -->
        <section class="col-12" id="add-mess">
            <div>
                <h2><?php if (!$message) {
                        echo "Ajouter un message";
                    } else {
                        echo "Modifier votre message";
                    };
                    ?></h2>
                <!-- ----------------- ERRORS -->
                <?php if (!empty($erreurs)) : ?>
                <div class="alerts">
                    <ul>Attention :
                        <?php foreach ($erreurs as $erreur) : ?>
                        <li><?= $erreur ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="input-group input_msg">
                        <input class="form-control" type="text" id="titre" name="titre" placeholder="<?php if (!$message) {
                                                                                                            echo "Titre de votre message";
                                                                                                        } else {
                                                                                                            echo "";
                                                                                                        };
                                                                                                        ?>" value="<?= $title ?>">
                    </div>
                    <div class="input-group input_msg">
                        <textarea class="form-control" name="contenu" id="contenu" placeholder="<?php if (!$message) {
                                                                                                    echo "Contenu de votre message";
                                                                                                } else {
                                                                                                    echo "";
                                                                                                };
                                                                                                ?>"><?= $content ?></textarea>
                    </div>
                    <!-- IMAGE CATEGORIE ET BOUTON -->
                    <div class="d-flex flex-row justify-content-between">
                        <!-- IMAGE -->
                        <div class="align-self-center">
                            <label for="image"> Image :</label>
                            <input type="file" name="image" id="image">
                        </div>
                        <!-- CATEGORIE  -->
                        <div class="align-self-center">
                            <label for="categories">Catégories</label>
                            <select id="categories" name="categories">
                                <option type="text" value="5">Choisir une catégorie</option>
                                <!-- Creating a list of categories  -->
                                <?php foreach ($categories as $categorie) : ?>
                                <option type="text" id="<?= $categorie['id'] ?>" value="<?= $categorie['id'] ?>"  <?php if (isset($_GET['edit']) && !empty($_GET['edit'])) : ?><?php if ($message_cat['categories_id'] == $categorie['id']) : ?> <?= $selected ?><?php endif ?> <?php endif ?>><?= $categorie['name'] ?></option>
                                <?php endforeach; ?>                            </select>
                        </div>
                        <!-- BOUTONS -->
                        <div>
                            <button name="message" class="btn btn-success">
                                <!-- change the button if post a new message or update an old one -->
                                <?php if (!$message) {
                                    echo "Ajouter le message";
                                } else {
                                    echo "Modifier le message";
                                };
                                ?>
                            </button>
                        </div>
                    </div>
            </div>
            </form>
            </div>
        </section>
        <!-- SHOW ALL INPUTS OF `messages` -->
        <section id="display-mess">
            <h2 id="vosmessages">Vos messages</h2>
            <?php foreach ($messages as $message) : ?>
            <section class="col-12 sectionmsg">
                <!-- BUTTONS & TITLE -->

                <div class="d-flex flex-row">
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
                    <div class="col-4 d-flex justify-content-center"><img src="uploads/<?= $image ?>" alt="<?= $message['title'] ?>" <?php
                                                                                                                                            endif; ?>>
                    </div>
                    <div class="col-8 text-wrap">
                        <div class="align-self-center d-flex flex-row justify-content-between msg-title">
                            <h2><a><?= $message['title'] ?> </a></h2>
                            <div class="align-self-center">
                                <?php if (verifForm($_SESSION, ['user'])) : ?>
                                <a class="btn btn-warning align-self-center" href="index.php?edit=<?= $message['id'] ?>">Modifier</a>
                                <a class="btn btn-danger align-self-center" href="index.php?delete=<?= $message['id'] ?>">Supprimer</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <p class="date">
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
                        <div class="msg-content">
                            <?= $message['content'] ?>
                        </div>
                    </div>
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