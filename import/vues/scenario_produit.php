<?php

// SECURITE
if(!isset($_SESSION["util"]->id)) exit;


// THELIA CLASSES & TOOLS
include_once("../classes/Produitdesc.class.php");

/**
 * Debug
 *
 * echo '<font style="color:blue">'.$ref.' n\'éxite pas</font><br>';
 * echo '<font style="color:red">'.$ref.' éxiste déjà</font><br>';
 *
 *
 *
 *
 *
 * 
*/

// -----------------------------------------------------------
// VARIABLES PREDEFINIES
// -----------------------------------------------------------

// lang par défaut
(!empty($_POST['lang']) || !empty($_POST['lang'])) ? $lang = $_POST['lang'] : $lang = '1';
// tva par défaut
(!empty($_POST['tva'])) ? $tva = $_POST['tva'] : $tva = '19.6';
// Stock par défaut '1' = valeur pour ignorer avec varaiable verif_stock
(!empty($_POST['stock'])) ? $stock = $_POST['stock'] : $stock = '1';
// ligne (le produit est en ligne  : 1 ou 0)
(!empty($_POST['ligne'])) ? $ligne = $_POST['ligne'] : $ligne = '1';
// Créer les images à l'insert d'un nouveau produit
(!empty($_POST['creer_image'])) ? $creer_image = $_POST['creer_image'] : $creer_image = TRUE;
// Forcer Rubrique Parente (Rub 1>Rub 2>)
(!empty($_POST['forcer_arbo'])) ? $forcer_arbo = $_POST['forcer_arbo'] : $forcer_arbo = '';
// Créer les rubriques si elles n'éxiste pas
(!empty($_POST['forcer_creation_rubrique'])) ? $forcer_creation_rubrique = $_POST['forcer_creation_rubrique'] : $forcer_creation_rubrique = TRUE ;
// Remplacer les caractéristiques par celles de l'import : remplacer_carac
(!empty($_POST['remplacer_carac'])) ? $remplacer_carac = $_POST['remplacer_carac'] : $remplacer_carac = TRUE;


// DEBUG :
//
//echo 'Langue =>'.$lang.'<br>';
//echo 'TVA =>'.$tva.'<br>';
//echo 'Stock =>'.$stock.'<br>';
//echo 'Ligne =>'.$ligne.'<br>';
//echo 'Crer image => '.$creer_image.'<br>';
//echo 'Forcer Arbo => '.$forcer_arbo.'<br>';
//echo 'Forcer création rubrique'.$forcer_creation_rubrique.'<br>';


// -----------------------------------------------------------

if(file_exists("$content_dir"."$name_file")) {
    // Mode
    //$mode = $_POST['option'];
    //
    // la 1ere ligne pour générer la liste des champs
    $fp=fopen("$content_dir"."$name_file", 'r');
    //recupere la première ligne en csv
    $tabcsv = fgetcsv($fp, 0, $del);	
    fclose ($fp);
    
    // les lignes a insserer : array $listInsert
    $fp = fopen("$content_dir"."$name_file", 'r');
    $cpt=0;
    while ($listInsert[$cpt] = fgetcsv($fp, 0, $del)) { 
        $cpt ++;
    }
    fclose ($fp);
    
    // Tableau de correspondance
    
        
    // Extraire le shema de destination de l'entète du csv
    // ---------------------------------------------------
    // liste les tables et correspondances des champs du csv
    // et les place dans le array shema
    // Spéparateur ':'
    $i=0;
    while ($i < count($tabcsv)) {
        $res=split(':',$tabcsv[$i]);
        $shema[$i]=array(strtolower($res[0])=>$res[1]);
        $i++;
    }
    
    
    
        // DEBUG : Afficher le shema
        //for($i=0;$i<count($shema);$i++){
        //     foreach($shema[$i] as $table => $valeur){
        //         echo $table.'==>'.$valeur.'<br>';
        //     }
        // } 
        // 
        var_dump($shema);
        
        // compteur qui va donner le numero à la ligne des valeurs, pour ne pas avoir de trou dans l'index du tableau
        // $l = ligne
        // $c = colonne
	$numligneval = 1;
	for ($l=1; $l<=($cpt-1) ; $l++) {
            // DEBUG :                
            echo '<h5>référence Produit : '.$listInsert[$l][0].'</h5><br>';
            
            for($c=0;$c < count($listInsert[$l]);$c++){
                foreach($shema[$c] as $table => $valeur){
                    
                    
                    // TRAITEMENTS
                    // -----------------------------------------------------------
                    
                    // Formatter les prix
                    if($valeur=='prix'){
                        $listInsert[$l][$c] = strtr($listInsert[$l][$c],',','.');
                    }
                    
                    
                    // INSERTION
                    // -----------------------------------------------------------
                    
                    // on est sur la première colone (ref)
                    // on commence par créer ou récupérer l'id produit
                    if($c==0){
                        $ref = $listInsert[$l][0];
                        // TEST si la ref existe
                        if(!$import->verif_exist($table,'ref',$ref)){
                            $insert = $import->ajouter($table,'(`ref`,`stock`,`ligne`,`tva`)',"('".$ref."','".$stock."','".$ligne."','".$tva."')",$l);
                            $idProduit = $insert['id'];
                            $mode = "insert"; // 
                        }else{
                            $produit = $import->get_produit_id(trim($ref));
                            $idProduit = $produit['id'];
                            $update = $import->update("`produit`","`stock`='".$stock."',`ligne`='".$ligne."',`tva`='".$tva."'","`id`","'".$idProduit."'",$l);
                            $mode = "update"; // 
                        }
                        // Creer les images associées
                        if((!$import->verif_exist('image','produit',$idProduit)) AND ($creer_image==TRUE)){
                            $images_champs = "(`produit`,`fichier`)";
                            $image_file = $ref."_1.jpg";
                            $image_valeurs ="('".$idProduit."','".$image_file."')";
                            $image = $import->ajouter('image',$images_champs,$image_valeurs,$l);
                            if(is_array($image)) {
                                $import->message['success'] .= 'L\'image'.$image_file.' du produit '.$ref.' -> à été insérer dans la table image -> id en base :'.$image['id'].'<br/>';
                            }else{
                                $import->message['error'] .= $image;
                            }
                        }
                        // Vider les caractéristiques si le mode est UPDATE 
                        if($mode=='update' || $remplacer_carac==TRUE){
                            $import->vider_carac($idProduit);
                        }
                    }
                    // On Parcour les autres colonnes
                    else{
                        // en cas de valeur vide dans un champ du csv Sauter la cellule
                        if(!empty($listInsert[$l][$c]) OR $listInsert[$l][$c] !='') {
                            
                            // k des infos produit (PRODUIT)
                            if($table=='produit'){
                                    // K du champ rubrique
                                if($valeur=='rubrique'){
                                    // creer arbo
                                    // test la présence des rubrique et créer l'arborescence si besoin
                                    // et si forcer_creation_rubrique est TRUE
                                    $arbo = $import->creer_arbo('>',$forcer_arbo.$listInsert[$l][$c],$forcer_creation_rubrique);
                                    // on déplace le produit dans sa rubrique
                                    // Récupération de l'id
                                    $RubProd = $import->rubrique_titre2id($listInsert[$l][$c]);
                                    $import->update("`".$table."`","`".$valeur."`='".$RubProd['id']."'",'`id`',"'".$idProduit."'",$l);
                                    // Si c'est le premier import On lui affecte un rang pour générer un classement de départ
                                    if($mode=="insert"){
                                        $rang = $import->produit_range($RubProd['id']);
                                        $import->update("`produit`","`classement`='".$rang."'","`id`","'".$idProduit."'",$l);
                                    }                                    
                                }else{
                                    // K des autres champs de la table produit
                                    // on update le champ ($valeur) de l'entête de csv avec la valeur de colonne de la ligne du csv
                                    $import->update("`".$table."`","`".$valeur."`='".$listInsert[$l][$c]."'",'`id`',"'".$idProduit."'",$l);
                                }
                            }
                            // K description produit (PRODUITDESC)
                            if($table=='produitdesc'){
                                // Réecrire les url
                                if($valeur=='titre')
                                    $titreProd = $listInsert[$l][$c];
                            
                                // Convertion caractères spéciaux pour sql
                                $listInsert[$l][$c] = addslashes($listInsert[$l][$c]);
                                if(!$import->verif_exist($table,'produit',$idProduit)){
                                    $cible = '(`produit`,`lang`,`'.$valeur.'`)';
                                    $data = "('".$idProduit."','".$lang."','".$listInsert[$l][$c]."')";
                                    $insert = $import->ajouter($table,$cible,$data,$l);
                                }else{
                                    $import->update("`".$table."`","`".$valeur."`='".$listInsert[$l][$c]."'",'`produit`',"'".$idProduit."'",$l);
                                }
                            }
                            // K des caracteristiques/caracdisp
                            if($table=='caracteristique'){
                                $caracteristique = $valeur;
                                // Convertion caractères spéciaux pour sql
                                $listInsert[$l][$c] = addslashes($listInsert[$l][$c]);
                                echo '<strong>Caracteristique</strong><br>';
                                echo 'APRES TRAITEMENT ==> '.$listInsert[$l][$c].'<br>';
                                $caracdispList = explode(',',trim($listInsert[$l][$c]));
                                $import->associer_produit($caracteristique,$caracdispList,$idProduit,$RubProd['id']);
                                
                                
                                
                                // DEBUG : echo '<i>Caracteristique :'.$caracteristique.'> Caracdisp :'.$caracdispList.'</i><br>';
                                
                            }
                        } // ./ if(!empty)
                        else {
                            echo '<font style="color:red"><b>Le produit : '.$ref.'</b>Pour le champ '.$valeur.' est vide : '.$listInsert[$l][$c].' )</font><br>';
                        }
                    }
                    //echo '<font style="color:red;">c\'est la première colonne => table '.$table.' | Champ :'.$valeur.'| Données : '.$listInsert[$l][0].' </font>';
                    //$insert = $import->ajouter($table, $colones, $valeurs[$u], $u+1);
                    
                    // Générer la réécriture de l'url du produit
                    if($mode=='insert'){
                       $url = new Produitdesc();
                        $url->titre = $titreProd;
                        $url->lang = $lang;
                        $url->produit = $idProduit;
                        $url->reecrire(); 
                    }
                    
                    
                    // echo 'L\'URL du produit : '.$url.'<br>';
                    // DEBUG
                    echo 'num ligne csv : '.$l.' | num col csv : '.$c.' | table :'.$table.' | Champ :'.$valeur.' | Insertion :'.$listInsert[$l][$c].'<br>';
                }
            }  
        }


}

?>