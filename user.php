<?php
//****AJOUTER UN UTILISATEUR */


//************************************************ */
//PENSER A BIEN AJOUTER UNE COLONNE NAME DANS LA TABLE USER 
//**************************************************** */

if (isset($_POST) && !empty($_POST)) {
    //On vérifie si tous les champs du formulaire soient remplis
    //pour cela on importe la lib avec verifForm
    require_once('inc/lib.php');

    if (verifForm($_POST, ['nom','mail', 'motdepasse'])) {
        //désactiver les balises HTML pour protéger faille XSS
        $nom = strip_tags($_POST['nom']);
        $email = strip_tags($_POST['mail']);
        $password = password_hash($_POST['motdepasse'], PASSWORD_BCRYPT);
        // ON FAIT UN CHIFFREMENT DU MOT DE PASSE
        //Seul l'utilisateur le connait, il n'est pas stocké en base de données

        //On se connecte à la base
        require_once('inc/connect.php');

        //Requête SQL
        $sql = 'INSERT INTO `users` (`email`, `password`, `name`) VALUES (:mail, :motdepasse, :nom);';
        //On prépare la requête
        $query = $db->prepare($sql);
        //On injecte les valeurs dans la requête
        $query->bindValue(':nom', $nom, PDO::PARAM_STR);
        $query->bindValue(':mail', $email, PDO::PARAM_STR);
        $query->bindValue(':motdepasse', $password, PDO::PARAM_STR);
        //On éxécute la requête
        $query->execute();

        //On se déconnecte de la base 
        require_once('inc/close.php');
        //On se redirige vers la liste des catégories
        header("Location: index.php");
    } else {
        echo "Attention il faut entrer un mail ET un mot de passe";
    }
}
?>



<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>

<body>
    <h1>Inscription</h1>
    <main>
        <form method="post">
            <div>
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom">
            </div>
            <div>
                <label for="mail">E-Mail</label>
                <input type="email" id="mail" name="mail">
            </div>
            <div>
                <label for="pass">Mot de passe</label>
                <input type="password" id="motdepasse" name="motdepasse">
            </div>

            <button>S'inscrire</button>
    </main>

</body>

</html>