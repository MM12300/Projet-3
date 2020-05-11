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
//DB OPEN
require_once('inc/connect.php');


//***** READ - `categories` */
$sql = 'SELECT * FROM `categories` ORDER BY `name` ASC;';
$query = $db->query($sql); //Query method
$categories = $query->fetchALl(PDO::FETCH_ASSOC);



//***** READ - 
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

        


//$_POST checking
if (isset($_POST) && !empty($_POST)) {
    //functions library loading
    require_once('inc/lib.php');
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
                <button>Ajouter l'message</button>
            </form>
        </section>
        <!-- SHOW ALL INPUTS OF `messages` -->
        <section id="display-mess">
        <?php foreach ($messages as $message) : ?>
            <article>
                <h2>
                    <!-- TITRE DE l'ARRTICLE AVEC UN LIEN -->
                    <a><?= $message['title'] ?></a></h2>
                    <a href="mess_modif.php?id=<?= $message['id'] ?>">Modifier</a> 
                    <a href="mess_suppr.php?id=<?= $message['id'] ?>">Supprimer</a>
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