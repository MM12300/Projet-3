<?php
/**
* Fonction universelle de vérification de formulaire
*
* @param array $variablesuperglobale : Variable $_GET ou $_POST
* @param array $champs : Tableaux des champs à vérifier
* @return boolean : vrai ou faux
*/
function verifForm($variablesuperglobale, $champs){
   //Boucler sur champs
   foreach($champs as $champ){
       //Vérifier si le champs existe && si le champs n'est pas vide
       if(isset($variablesuperglobale[$champ]) && !empty($variablesuperglobale[$champ])){
           $reponse = true;
       }else{
           return false;
       }    
   }
    //Envoyer la réponse : vrai ou faux
    return $reponse;   
}


/**
* Fonction qui croppe notre image uploadé en carré d'une taille spécifique
*
* @param variable $taille = taille du carré
* @param integer $nomImage = nom du fichier image
*/
function thumb($taille, $nomImage){
    //On sépare le nom et son extension avec pathinfo();
    $debutNom = pathinfo($nomImage, PATHINFO_FILENAME);
    $extension = pathinfo($nomImage, PATHINFO_EXTENSION);

    //Créer le nom complet de l'image
    $nomComplet = __DIR__ . '/../uploads/' . $nomImage;

    //On récupère les informations de l'image
    $infosImage = getimagesize($nomComplet);

    //On définit les dimensions de l'image finale
    $largeurFinale = $taille;
    $hauteurFinale = $taille;

//On crée l'image de destination vide "en mémoire"
$imageDest = imagecreatetruecolor($largeurFinale, $hauteurFinale);

//On charge l'image source "en mémoire"
switch($infosImage['mime']){
    case 'image/jpeg':
        $imageSrc = imagecreatefromjpeg($nomComplet);
        break;
    case 'image/png':
        $imageSrc = imagecreatefrompng($nomComplet);
        break;
    case 'image/gif':
        $imageSrc = imagecreatefromgif($nomComplet);
        break;
}

//On calcule le décalage 

//Si Image carrée
$decalageY = 0;
$decalageX= 0;

//Si largeur > hauteur = paysage
if($infosImage[0] > $infosImage[1]){
    //On calcule decalageX = (largeurImage - largeurCarré) / 2
    $decalageX = ($infosImage[0] - $infosImage[1])/2;
    $tailleCarreSource = $infosImage[1];
}

//Si largeur < hauteur = portrait
if($infosImage[0] <= $infosImage[1]){
    //On calcule decalageY = (hauteurImage - hauteurCarré) / 2
    $decalageY = ($infosImage[1] - $infosImage[0])/2;
    $tailleCarreSource = $infosImage[0];
}

//Copier l'image source dans l'image finale
imagecopyresampled(
    $imageDest, //Image dans laquelle on copie image origine
    $imageSrc, //Image origine
    0, //Décalage horizontal dans l'image destination
    0, //Décalage vertical dans l'image destination
    $decalageX, //Décalage horizontal dans l'image source
    $decalageY, //Décalage vertical dans l'image source
    $largeurFinale, //largeur de la zone cible dans image destination
    $hauteurFinale, //hauteur de la zone cible dans image destination
    $tailleCarreSource, //largeur de la zone cible dans image source
    $tailleCarreSource  //hauteur de la zone cible dans image source
);

//On enregistre l'image de destination
//On définit le chemin d'enregistrement et le nom du fichier destination : nom-300x300.ext
$nomDest = __DIR__ . '/../uploads/' .  $debutNom . '-' . $taille . 'x' . $taille . '.' . $extension;

//Enregistrement en fonction du type d'image
switch($infosImage['mime']){
    case 'image/jpeg':
        imagejpeg($imageDest, $nomDest);
        break;
    case 'image/png':
        imagepng($imageDest, $nomDest);
        break;
    case 'image/gif':
        imagegif($imageDest, $nomDest);
        break;
}

//On efface les images en mémoire
imagedestroy($imageDest); //imageDest = image vide de la taille de l'image désirée = canva
imagedestroy($imageSrc); //imageSrc = copie de l'image source

}




/**
* Fonction de resize d'image (même ratio / taille différente)
*
* @param variable $nomImage
* @param integer $pourcentage
*/

function resizeImage($nomImage, $pourcentage){
    //On sépare le nom et son extension
    $debutNom = pathinfo($nomImage, PATHINFO_FILENAME);
    $extension = pathinfo($nomImage, PATHINFO_EXTENSION);
    //On génère le nom complet : nom + chemin complet vers la destination
    $nomComplet = __DIR__ . '/../uploads/' . $nomImage;
    //On récupère les informations de l'image
    $infosImage = getimagesize($nomComplet);

    //Définition des dimensions de l'image "finale"
    $largeurFinale = $infosImage[0] * ($pourcentage/100); //[0]=> largeur
    $hauteurFinale = $infosImage[1] * ($pourcentage/100); //[1] = hauteur

    //On crée l'image de destination vide "en mémoire"
    $imageDest = imagecreatetruecolor($largeurFinale, $hauteurFinale);

    //On charge l'image source "en mémoire"
    switch ($infosImage['mime']) {
        case 'image/jpeg':
            $imageSrc = imagecreatefromjpeg($nomComplet);
            break;
        case 'image/png':
            $imageSrc = imagecreatefrompng($nomComplet);
            break;
        case 'image/gif':
            $imageSrc = imagecreatefromgif($nomComplet);
            break;
    }

    //Copier l'image source dans l'image
    imagecopyresampled(
        $imageDest, //Image dans laquelle on copie image origine
        $imageSrc, //Image origine
        0, //Décalage horizontal dans l'image destination
        0, //Décalage vertical dans l'image destination
        0, //Décalage horizontal dans l'image source
        0, //Décalage vertical dans l'image source
        $largeurFinale, //largeur de la zone cible dans image destination
        $hauteurFinale, //hauteur de la zone cible dans image destination
        $infosImage[0], //largeur de la zone cible dans image source
        $infosImage[1]  //hauteur de la zone cible dans image source
    );

    //On enregistre l'image de destination
    //On définit le chemin d'enregistrement et le nom du fichier destination
    $nomDest = __DIR__ . '/../uploads/' .  $debutNom . '-' . $pourcentage . 'pourcent' . '.' . $extension;

    //Enregistrement en fonction du type d'image
    switch ($infosImage['mime']) {
        case 'image/jpeg':
            imagejpeg($imageDest, $nomDest);
            break;
        case 'image/png':
            imagepng($imageDest, $nomDest);
            break;
        case 'image/gif':
            imagegif($imageDest, $nomDest);
            break;
    }

    //On efface les images en mémoire
    imagedestroy($imageDest); //imageDest = image vide de la taille de l'image désirée = canva
    imagedestroy($imageSrc); //imageSrc = copie de l'image source
}


?>