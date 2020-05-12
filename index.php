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
$title ='';
$content='';
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

        
//***** DELETE -  */
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


    //On redirige vers la page art_admin
    header('Location: index.php');
} 

// else {
//     //header('Location: index.php');
//     echo "message d'erreur";
// }


//***** UPDATE - */
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    //Checking if the messages exists
    $id = strip_tags($_GET['edit']);
    $sql = 'SELECT * FROM `messages` WHERE `id` = :id;';
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $message = $query->fetch(PDO::FETCH_ASSOC);

    $title= $message[ 'title'];
    $content = $message['content'];
    if (!$message) {
        //header('Location: index.php');
        echo "le message que vous voulez modifier n'existe pas";
    };

    if (isset($_POST) && !empty($_POST)) {
        if (verifForm($_POST, ['titre', 'contenu', 'categories'])) {
            $title = strip_tags($_POST['titre']);
            $content = strip_tags($_POST['contenu']);
            $id = strip_tags($_GET['edit']);

            //**************** On met à jour la BDD : Articles
            //Requête SQL
            $sql = 'UPDATE `messages` SET `title` = :title, `content` = :content, `users_id` = :user_id WHERE `id`=:id;';
            $query = $db->prepare($sql);
            $query->bindValue(':title', $title, PDO::PARAM_STR);
            $query->bindValue(':content', $content, PDO::PARAM_STR);
            $query->bindvalue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':user_id', 1, PDO::PARAM_INT);
            $query->execute();


            //****************On met à jour la BDD articles_categories
            //On efface d'abord toutes les catégories associées à cet article
            //Puis on les écris à nouveau

            //Requête SQL
            $sql = 'DELETE FROM `articles_categories` WHERE `articles_id` = :id;';
            $query = $db->prepare($sql);
            $query->bindvalue(':id', $id, PDO::PARAM_INT);
            //On éxécute la requête
            $query->execute();

            //On récupère dans $_POST les catégories qui sont cochées
            $category = $_POST['categories'];
            //On ajoute les catégories
            $sql = 'INSERT INTO `messages_categories`(`messages_id`, `categories_id`) VALUES (:idarticle, :idcategorie);';
            $query = $db->prepare($sql);
            $query->bindValue(':idarticle', $id, PDO::PARAM_INT);
            $query->bindValue(':idcategorie', strip_tags($category), PDO::PARAM_INT);     
            $query->execute();
            } 
            header('Location: index.php');
    }     
}else{
    if (isset($_POST) && !empty($_POST)) {
        //verifForm => $_POST input/select checking
        if (verifForm($_POST, ['titre', 'contenu', 'categories'])) {
            //FORM resultats, variable creation and security
            $titre = strip_tags($_POST['titre']);
            $contenu = strip_tags($_POST['contenu'], '<div><p><h1><h2><img><strong>');
            $categories = strip_tags($_POST['categories']);
    
            //***** CREATE - `messages`
            $sql = 'INSERT INTO `messages` (`title`,`content`, `users_id`) VALUES (:titre, :contenu, :user_id);';
            $query = $db->prepare($sql); //Prepare method
            $query->bindValue(':titre', $titre, PDO::PARAM_STR);
            $query->bindValue(':contenu', $contenu, PDO::PARAM_STR);
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
    <title>Expression libre</title>
</head>

<body>
    <main>
        <!-- Input to  `messages` -->
        <section id="add-mess">
            <h1>Ajouter un message</h1>
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
                <select id="categories" name="categories">
                    <!-- Creating a list of categories  -->
                    <?php foreach ($categories as $categorie) : ?>
                    <div>
                        <option type="text" id="<?= $categorie['id'] ?>" value="<?= $categorie['id'] ?>"><?= $categorie['name'] ?></option>
                    </div>
                    <?php endforeach; ?>
                </select>
                <button>
                    <?php if(!$message){
                echo "Ajouter le message";
                    }else{
                echo "Modifier le message";
                    };
                    ?>
                    
                </button>
            </form>
        </section>
        <!-- SHOW ALL INPUTS OF `messages` -->
        <section id="display-mess">
        <?php foreach ($messages as $message) : ?>
            <article>
                <h2>
                    <!-- TITRE DE l'ARRTICLE AVEC UN LIEN -->
                    <a><?= $message['title'] ?></a></h2>
                    <a href="index.php?edit=<?= $message['id'] ?>">Modifier</a> 
                    <a href="index.php?delete=<?= $message['id'] ?>">Supprimer</a>
                <div>
                    <p>
                        <!-- DATE DE PUBLICATION-->
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
                <div><?= substr(strip_tags($message['content']), 0, 300) . '...' ?></div>
            </article>
        <?php endforeach; ?>

        </section>
    </main>
</body>

</html>