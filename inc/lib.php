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

