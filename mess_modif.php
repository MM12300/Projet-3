<?php
require_once('inc/connect.php');
$sql = 'SELECT * FROM `categories` ORDER BY `name` ASC;';
$query = $db->query($sql);
$categories = $query->fetchALl(PDO::FETCH_ASSOC);

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = strip_tags($_GET['id']);
    $sql = 'SELECT * FROM `messages` WHERE `id` = :id;';
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $message = $query->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        echo "Le message n'existe pas";
    }

    //******** SI L'ARTICLE EXISTE ON VA CHERCHER SA CATEGORIE DANS LA TABLE article.categorie

    $sql = 'SELECT * FROM `messages_categories` WHERE `messages_id` = :id;';
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $categoriesArticle = $query->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "erreur GET is not set "
}

//**********ON VÉRIFIE SI ON A DES MODIFICATIONS DANS LE FORMULAIRE */
    require_once('inc/lib.php');
    if (verifForm($_POST, ['titre_modif', 'contenu_modif'])) {
        //On récupère les nouveaux éléments et on les nettoie
        $newTitre = strip_tags($_POST['titre']);
        $newContenu = strip_tags($_POST['contenu']);

        //***********On vérifie si on a une IMAGE */
        //on récupère le nom de l'image dans notre BDD
        $nom = $article['featured_image'];
        //--------------------- IMAGE
        if (isset($_FILES) && !empty($_FILES)) {
            //On vérifie si $_FILES existe et est rempli
            if (isset($_FILES['image']) && !empty($_FILES['image']) && $_FILES['image']['error'] != 4) {
                //On vérifie si l'image est là 
                //'images'est le nom du champs dans le form
                //erreur 4 = pas de fichier, on vérifie si y'en a un, !=4
                
                // On récupère les données dans une variable $image
                $image = $_FILES['image'];

                //On vérifie si erreur pendant transfert, error !=0
                if ($image['error'] != 0) {
                    //si il y a une erreur : on arrête
                    echo "Une erreur s'\est produite lors du chargement de votre fichier";
                    die;
                }
                //Le transfert s'est bien déroulé

                //On limite l'Upload aux types MIME suivants
                $types = ['image/png', 'image/jpeg'];
                
                //On vérifie si le type du fichier est absent de la liste
                //on vérifie si le type apparait dans le tableau $image à la ligne type
                if (!in_array($image['type'], $types)) {
                    //si le type ne corresponds pas : on arrête
                    $_SESSION['error'] = "le type de fichier doit être un jpeg ou png";
                    header('Location:art_ajout.php');
                    die;
                }

                //On limite la taille à Mo maximum
                if ($image['size'] > 1048576) {
                    echo "Le fichier est trop volumineux";
                    die;
                    //si la taille est trop importante : on arrête
                }

                //Générer un nom pour le fichier -> nom + extension
                //On récupère l'extension de notre fichier 
                $extension = pathinfo($image['name'], PATHINFO_EXTENSION);

                //On génère un nom 'aléatoire'
                //md5+uniq pour créer le nom et ensuite on concatène pour faire une string
                // ne pas oublier le . de nom.extension
                $nom = md5(uniqid()) . '.' . $extension;
                //On déplace l'image dans notre dossier uploads
                //On génère le nom complet : nom + chemin complet vers la destination
                $nomComplet = __DIR__  . '/uploads/' . $nom;

                //On copie le fichier
                if (!move_uploaded_file($image['tmp_name'], $nomComplet)) {
                    //'tmp_name' : origine du fichier (dossier temp de htdoc de xampp)
                    //nom complet : chemin du dossier/uploads/nom_du_fichier
                    echo "le fichier n'a pas été copié";
                    die;
                } else {
                    echo "Le fichier a été uploadé";
                }

                //-------CREATION d'UNE IMAGE VERSIOM BASE RÉS : 75% taille initiale
                //fonction resizeImage dans lib.php
               resizeImage($nom, 75);
               resizeImage($nom, 25);
            
                //-------CREATION d'UNE IMAGE VERSION CARRÉ
                //fonction thumb dans lib.php
                thumb(300, $nom);
            }
        }

        //**ON EFFACE LES ANCIENNES IMAGES !!!!! */
        if ($article['featured_image'] != null) {
            //On gère la suppresion des anciennes images
            //On récupère la première partie du nom des images
            $debutNom = pathinfo($article['featured_image'], PATHINFO_FILENAME);

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

        //**************** On met à jour la BDD : Articles
        //Requête SQL
        $sql = 'UPDATE `articles` SET `title` = :title, `content` = :content, `featured_image` = :image WHERE `id`=:id;';
        //On prépare la requête
        $query = $db->prepare($sql);
        //On injecte les valeurs
        $query->bindValue(':title', $newTitre, PDO::PARAM_STR);
        $query->bindValue(':content', $newContenu, PDO::PARAM_STR);
        $query->bindvalue(':image', $nom, PDO::PARAM_STR);
        $query->bindvalue(':id', $id, PDO::PARAM_STR);
        //$id vient de $_GET
        //$newTitre et $newContenu viennent du $_POST

        //On éxécute la requête
        $query->execute();

        //****************On met à jour la BDD articles_categories
        //On efface d'abord toutes les catégories associées à cet article
        //Puis on les écris à nouveau

        //Requête SQL
        $sql = 'DELETE FROM `articles_categories` WHERE `articles_id` = :id;';
        //On prépare la requête
        $query = $db->prepare($sql);
        //On injecte les valeurs
        $query->bindvalue(':id', $id, PDO::PARAM_STR);
        //On éxécute la requête
        $query->execute();

        //On récupère dans $_POST les catégories qui sont cochées
        $categories = $_POST['categories'];
        //On ajoute les catégories
        foreach ($categories as $categorie) {
            //Requête SQL
            $sql = 'INSERT INTO `articles_categories`(`articles_id`, `categories_id`) VALUES (:idarticle, :idcategorie);';
            //On prépare la requête
            $query = $db->prepare($sql);
            //On injecte les valeurs
            $query->bindValue(':idarticle', $id, PDO::PARAM_INT);
            $query->bindValue(':idcategorie', strip_tags($categorie), PDO::PARAM_INT);
            //On éxécute la requête
            $query->execute();
        }
                 

        //On redirige vers une autre page (liste des articles par exemple)
        header('location: art_admin.php');
    }
}

//On se déconnecte
require_once('inc/close.php');
?>





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
        <button>Modifier l'article</button>
    </form>

</body>

</html>