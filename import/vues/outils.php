<?php

// SECURITE
if(!isset($_SESSION["util"]->id)) exit;



?>

<div class="row-fluid">
    <div class="span6">
        <!--
            Remplacer CARACTERISTIQUE
        -->
        <h3>Remplacer des valeurs de caractéristique</h3>
        <p>
        Utile pour remplacer sur tous les produits, une valeur de caracdisp qui aurait été ajouté par erreur lors de l'import via le mode scénario.<br>
        Agit sur la table CARACVAL. <b>Attention : les deux valeurs doivent dépendre de la même caractéristique</b>
        </p>
        <form action="" name="form_caracdisp" id="form_caracdisp" method="post" enctype="multipart/form-data">
            <input type="hidden" name="carac_switch" value="1">
            <div class="row-fluid">
                <div class="span6">
                    <label for="caracdisp_depart">Caracdisp a remplacer</label>
                    <span class="help-block">Spécifiez la valeur de caracdisp a remplacer (ID)</span>
                    <input type="text" name="caracdisp_depart" id="caracdisp_depart">
                </div>
                <div class="span6">
                    <label for="caracdisp_remplacement">Caracdisp de remplacement</label>
                    <span class="help-block">Spécifiez la valeur de caracdisp de remplacement (ID)</span>
                    <input type="text" name="caracdisp_remplacement" id="caracdisp_remplacement">
                </div>
                <div class="span12">
                    <label for="caracdisp_delete">Supprimer la caracdisp après traitement
                    <input type="checkbox" name="caracdisp_delete">
                    </label>            
                </div>
                <div class="span12">
                    <input type="submit" class="btn btn-primary">
                </div>
            </div>  
        </form>
    </div>
    <div class="span6">
        <!--
        Vider CARACVAL
        -->
        <h3>Vider les valeurs de caractéristiques d'un produit</h3>
        <form action="" name="form_vider_carac" id="form_vider_carac" method="post" enctype="multipart/form-data">
            <input type="hidden" name="vider_carac" value="1">
            <label for="produit">Produit à désassocier</label>
            <span class="help-block">Spécifiez le produit que vous souhaitez désassocier (ID)</span>
            <input type="text" name="produit" id="produit">
            <input type="submit" class="btn btn-primary">
        </form>
    </div>
</div>

<div class="row-fluid">
    <div class="span6">
        <!--
        Réinitialisation d'un classement
        -->
        <h3>Réinitialiser le classement des produits d'une rubrique</h3>
        <form action="" name="form_classer_prod" id="form_classer_prod" method="post" enctype="multipart/form-data">
            <input type="hidden" name="classer_prod" value="1">
            <div class="row-fluid">
                <div class="span3">
                    <label for="caracdisp_depart">Rubrique contenant les produits</label>
                    <span class="help-block">Spécifiez la rubrique qui contient les produits (ID)</span>
                    <input type="text" name="rubrique" id="rubrique">
                </div>
                <div class="span12">
                    <input type="submit" class="btn btn-primary">
                </div>
            </div>  
        </form>
    </div>
    <div class="span6">
        <!--
        // TODO : associer un contenu a tous les produits
        -->
        
        
    </div>
</div>





<?php

 



//var_dump($liste);





?>


<?php
// -----------------------------------------------------------
// TRAITEMENTS
// -----------------------------------------------------------

if($_POST['carac_switch']=='1'){
    var_dump($_POST);

    if(!empty($_POST['caracdisp_depart']) AND !empty($_POST['caracdisp_remplacement'])){
        $cible = $_POST['caracdisp_depart'];
        $replace = $_POST['caracdisp_remplacement'];
        
        $sql = "UPDATE `caracval` SET `caracdisp`='".$replace."' WHERE `caracdisp`='".$cible."'";
        
        echo $sql;
        $resultat = mysql_query($sql);
        
        
        if(!$resultat) {
            $import->message['error']= 'Erreur SQL '.mysql_errno().' : '.mysql_error().'<br>';
        }else{
            $resultat = mysql_affected_rows() ;
            $import->message['success'] = $resultat." Lignes ont étés modifiées !";
            // TODO : outils : carac_switch : option supprimer la caracdisp après le traitement
            if(isset($_POST['caracdisp_delete'])){
                $del_desc = "DELETE `caracdispdesc`,`caracdisp` FROM `caracdispdesc`,`caracdisp` WHERE caracdispdesc.caracdisp='".$cible."' AND caracdisp.id='".$cible."'";
                $del = mysql_query($del_desc);
                (!empty($del)) ? $import->message['success']= "La caracdisp ".$cible."à bien été supprimée" : $import->message['error'] = 'Erreur SQL '.mysql_errno().' : '.mysql_error().'<br>';
            }      
        }
    }else{
        $import->message['error'] = "Renseignez les champs de départ et d'arrivée";
    }
    
}

// -----------------------------------------------------------
// Réinitialisation d'un classement
// -----------------------------------------------------------


if($_POST['classer_prod']=='1'){
    // on sélectionne tout les produits de la rubrique 3
    
    $sql = "SELECT * FROM `produit` WHERE `rubrique`='".$_POST['rubrique']."'";
    $result = mysql_query($sql);
    
    $liste = mysql_num_rows($result);
    
    $i = 1;
    while ($data = mysql_fetch_array($result)) {
            $import->update("`produit`","`classement`='".$i."'","`id`","'".$data['id']."'",'');
            // on affiche les résultats
            echo 'I ='.$i.'ID : '.$data['id'].' ==> Classement : '.$data['classement'].'<br />';
          $i++;
    } 
}


// -----------------------------------------------------------
// Vider les caracval d'un produit
// -----------------------------------------------------------

if($_POST['vider_carac']=='1'){
    if(!empty($_POST['produit'])) {
        $vider = $import->vider_carac($_POST['produit']);
        return $vider;
    }else{
        return $import->message['error'] = 'Spécifiez un produit !';
        }
}









?>