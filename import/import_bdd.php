<?php
if(!isset($_SESSION["util"]->id)) exit;
//Importation
if (isset($_POST['listeok'])){
	echo "<strong>Traitement du fichier CSV</strong><br />";
	$del = $_POST['del'];//déliminateur
	$content_dir = $_POST['content_dir'];//répertoire tmp
	$name_file = $_POST['name_file'];//nom du fichier
	$table = $_POST['table'];//table à importer
	$fields = $_POST['fields'];//nb de champs dans la table
	$nb_colones_csv = $_POST['nb_colones_csv'];// nb de champs dans le fichier csv

	//on lis les lignes a insserer
	$fp = fopen("$content_dir"."$name_file", 'r');
	$cpt=0;
	while ($listInsert[$cpt] = fgetcsv($fp, 0, $del)) { 
		$cpt ++;
	}
	fclose ($fp);
        

        
        //génération de la requette
        
	//Champ de la table
	$colones = "(" ;
	for ($g=0; $g<$fields; $g++) {		
            $colones .=  '`'.$_POST["colone$g"].'`';
            //pour ne pas ajouter de virgule apres la derniere colone on teste
            if ($g < ($fields-1)){
                $colones .= ", ";
            }
	}
	$colones .= " ) ";
        
	echo $colones.'<br>';
	echo $nb_colones_csv.' champs dans le fichier '.$name_file.'<br />'; 
	echo $fields.' champs dans la table '.$table.'<br />'; 

	$valeurs = array();//Les valeurs de l'insertion de requête
	$valeurs_update = array();//Les valeurs de l'update de requête
	$array_id = array();//Les indexes des données à insérer
	
	//compteur qui va donner le numero à la ligne des valeurs, pour ne pas avoir de trou dans l'index du tableau
	$numligneval = 1;
	for ($u=1; $u<=($cpt-1) ; $u++) {
                // DEBUG -------------------------------------------------------
                // $listInsert[$u] = explode($del,$listInsert[$u][0]);
                // 
		// on verrifie que le parsage de la ligne soit bien fait en regardant que tabcsv contient bien le même nombre
                // de champs pour cette ligne que le nombre de champs de la 1ere ligne du fichier csv
                // ET que le premier champ (id) n'est pas vide
                //
                // var_dump($listInsert[$u]); tableau des valeurs Avant traitement
                // -------------------------------------------------------------
		if (count($listInsert[$u]) == $nb_colones_csv AND $listInsert[$u][0]!="") {

                    $valeurs[$numligneval] = "(" ;
                    //on remplis les valeurs en fonction du nb de colones
                    for ($m=0; $m<$fields ; $m++) {
                            $tempcol = 'colonecsv'.$m;
                            //on stocke la valeur sans les espaces avant et après
                            if ($_POST[$tempcol]==0){
                                $array_id[$numligneval] = $listInsert[$u][$_POST[$tempcol]];
                            }
                            
                            // convertion des data a inssérer notament pour échaper
                            // les apostrophes dans les titres qui pourraient planter la requète sql
                            $data = addslashes($listInsert[$u][$_POST[$tempcol]]);
                            
                            
                            // Traitements sur les champs 
                            $tempforce = 'force'.$m;
                            $tempcrypte = 'crypte'.$m;
                            $tempignore = 'ignore'.$m;
                            $tempRef2id = 'ref2id'.$m;
                            $tempTitre2id = 'titre2id'.$m;
                            
                            // DEBUG ---------------------------------------
                            //	echo '$tempforce = '.$tempforce;
                            //	echo ' = '.$_POST[$tempforce];
                            //	echo '<br>';
                            // ---------------------------------------------
                            
                            // Traitements
                            if ($_POST[$tempforce] != ""){
                                    //si on a demandé un cryptage de ce champ ..le @ car si on a pas coché la case ça affiche une erreur
                                    if ( @$_POST[$tempcrypte] == 1){
                                        @$valeurs[$numligneval] .= '\''.md5(trim($_POST[$tempforce])).'\'';
                                        @$valeurs_update[$numligneval] .='`'.$_POST["colone$m"].'`=\''.md5(trim($_POST[$tempforce])).'\'';
                                    }
                                    //si on a demandé un traitement : Ref2id
                                    elseif ($_POST[$tempRef2id]!=""){
                                        // en utilisant get_produit_id on retrouve l'id du produit par rapport à sa référence
                                        $produit = $import->get_produit_id(trim($data));
                                        @$valeurs[$numligneval] .= '\''.$produit['id'].'\'';
                                        //si c'est une update de produitdesc on retrouve l'id du produit conçerné a partir de la référence
                                        @$valeurs_update[$numligneval] .='`'.$_POST["colone$m"].'`=\''.$produit['id'].'\'';
                                    }elseif($_POST[$tempTitre2id]!=""){
                                        $rubrique = $import->rubrique_titre2id($data);
                                        
                                        @$valeurs[$numligneval] .= '\''.$rubrique['id'].'\'';
                                        @$valeurs_update[$numligneval] .='`rubrique`=\''.$rubrique['id'].'\'';
                                    } else {
                                        @$valeurs[$numligneval] .= '\''.trim($_POST[$tempforce]).'\'';
                                        @$valeurs_update[$numligneval] .= '`'.$_POST["colone$m"].'`=\''.trim($_POST[$tempforce]).'\'';
                                    }
                            }else{
                                    if (@$_POST[$tempcrypte] == 1){
                                        // on evite d'afficher un erreur d'index indefini sur la table si on a pas choisi de correspondance de champ avec @
                                        @$valeurs[$numligneval] .= '\''.md5(trim($data)).'\'';
                                        @$valeurs_update[$numligneval] .= '`'.$_POST["colone$m"].'`=\''.md5(trim($data)).'\'';
                                    }elseif($_POST[$tempRef2id]!=""){
                                        // en utilisant get_produit_id on retrouve l'id du produit par rapport à sa référence
                                        // si la case traitement a été coché sur le champ ref
                                        $produit = $import->get_produit_id(trim($data));
                                        // insert
                                        @$valeurs[$numligneval] .= '\''.$produit['id'].'\'';
                                        // Update : TODO if -> si c'est une update de produitdesc on retrouve l'id du produit conçerné a partir de la référence
                                        @$valeurs_update[$numligneval] .='`'.$_POST["colone$m"].'`=\''.$produit['id'].'\'';
                                    }
                                    // Retrouve une id rubrique via sontitre
                                    elseif($_POST[$tempTitre2id]!=""){
                                        $rubrique = $import->rubrique_titre2id($data);
                                        @$valeurs[$numligneval] .= '\''.$rubrique['id'].'\'';
                                        @$valeurs_update[$numligneval] .='`rubrique`=\''.$rubrique['id'].'\'';
                                    }
                                    else {
                                        // on evite d'afficher un erreur d'index indefini sur la table
                                        // si on a pas choisi de correspondance de champ avec @
                                        @$valeurs[$numligneval] .= '\''.trim($data).'\'';
                                        @$valeurs_update[$numligneval] .= '`'.$_POST["colone$m"].'`=\''.trim($data).'\'';
                                    }
                            }
                            //pour ne pas ajouter de virgule apres la derniere colone
                            if ($m < ($fields-1)){
                                $valeurs[$numligneval] .= ", ";
                                $valeurs_update[$numligneval] .= ", ";
                            }				
                    }
                    $valeurs[$numligneval] .= " ) ";
                    $numligneval++;
                }
                // si l'id est vide
		else if ($listInsert[$u][0]==""){
                    echo '<font color="red">Ligne '.($numligneval+1).' du fichier '.$name_file.' -> Erreur : id non rentrée</font> - Ligne ignorée. <br />';
                    $valeurs[$numligneval] = "";
                    $numligneval++;
		}
		else{
                    echo '<font color="red">Ligne '.($numligneval+1).' du fichier '.$name_file.' -> Erreur : '.count($listInsert[$u]).' champs au lieu de '.$nb_colones_csv.' </font> - Ligne ignorée. <br />';
                    //on rempli quand même le tableau des valeurs mais cette ligne sera ignorée lors des insert ... ?
                    // ça permet de garder le compteur des lignes à jour au niveau du journal des erreurs sql
                    $valeurs[$numligneval] = "";
                    $numligneval++;
		}		
	}
        
        // ------------------------------------------------------------------
	// MARK : Insertion dans la base
        // ------------------------------------------------------------------
	
	$import->message['success'] .='La ligne 1 du fichier CSV correspond à la définition des champs. <br />';
        
	for ($u=1; $u<$numligneval; $u++) {		
		if ($valeurs[$u]!= ""){
                    //MARK : mode update
                    if ($_POST["option"]==1){
                        
                        if($table=='produitdesc'){
                            $champ = '`'.$_POST["colone1"].'`';
                            $produit = $import->get_produit_id($listInsert[$u][1]);
                            $valeur = "'".$produit['id']."'";
                            
                            //DEBUG : 
                            //echo $_POST["colone1"].'=>'.$produit['id'].'<br>';
                            //echo 'VALEURS =>'.$valeurs_update[$u].'<br>';
                            //echo '--------------------------------------------------- <br>';
                            
                            $update = $import->update($table,$valeurs_update[$u],$champ,$valeur, $u+1);
                                
                                if(is_array($update)){
                                     $import->message['success'] .= 'La ligne '.$u.' du fichier '.$name_file.' -> à été mise à jour dans la table ' . $table . ' -> id en base :'.$update['id'].'<br/>';
                                }else{
                                    $import->message['error'] .= $update;
                                }
                        }elseif($table=='produit'){
                            $champ = '`'.$_POST["colone0"].'`'; // champ cible  
                            $produit = $import->get_produit_id($listInsert[$u][1]);
                            $valeur = "'".$produit['id']."'";
                            
                            //DEBUG : 
                            //echo $_POST["colone1"].'=>'.$produit['id'].'<br>';
                            //echo 'VALEURS =>'.$valeurs_update[$u].'<br>';
                            //echo '--------------------------------------------------- <br>';
                            
                            $update = $import->update($table,$valeurs_update[$u],$champ,$valeur, $u+1);
                                if(is_array($update)){
                                     $import->message['success'] .= 'La ligne '.$u.' du fichier '.$name_file.' -> à été mise à jour dans la table ' . $table . ' -> id en base :'.$update['id'].'<br/>';
                                }else{
                                    $import->message['error'] .= $update;
                                }
                        }else{
                            $erreur_sql .= $import->update($table, $colones, $valeurs_update[$u], $_POST["colone0"], $array_id[$u], $u+1);
                        }
                    //MARK : mode Insertion    
                    }else{
                        // MARK : Insert : Produit
                        if($table=='produit'){
                            //DEBUG : echo $valeurs[$u].'<br>';
                            $insert = $import->ajouter($table, $colones, $valeurs[$u], $u+1);
                            if(is_array($insert)){
                                $import->message['success'] .= 'La ligne '.$u.' du fichier '.$name_file.' -> insérer dans la table ' . $table . ' -> id en base :'.$insert['id'].'<br/>';
                                // création des images correspondantes en bdd si l'option est cochée
                                if($_POST['creerimages']== 1){
                                    $images_champs = "(`produit`,`fichier`)";
                                    $image_file = $listInsert[$u][1]."_1.jpg";
                                    $image_valeurs ="('".$insert['id']."','".$image_file."')";
                                    $image = $import->ajouter('image',$images_champs,$image_valeurs,$u+1);
                                    if(is_array($image)) {
                                        $import->message['success'] .= 'La ligne '.$u.' du fichier '.$name_file.' -> insérer dans la table ' . $table . ' -> id en base :'.$insert['id'].'<br/>';
                                    }else{
                                        $import->message['error'] .= $image;
                                    }
                                }
                                // TODO : Ajouter la génération des url Propres !
                            }else{
                                $import->message['error'] .= $insert;
                            }
                        }else{
                            $insert = $import->ajouter($table, $colones, $valeurs[$u], $u+1);
                            if(is_array($insert)) {
                                $import->message['success'] .= 'La ligne '.$u.' du fichier '.$name_file.' -> insérer dans la table ' . $table . ' -> id en base :'.$insert['id'].'<br/>';
                            }else{
                                $import->message['error'] .= $insert;
                            }
                        }
                    }
		}
	}
	
	//suppression du fichier csv dans tmp
	//unlink("$content_dir"."$name_file");
}
?>