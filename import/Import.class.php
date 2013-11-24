<?php

include_once(realpath(dirname(__FILE__)) . "/../../../classes/PluginsClassiques.class.php");

class Import extends PluginsClassiques{
    
        public static $message = array();
	
	function Import(){        
	    $this->PluginsClassiques();
        }
        
        /**
         * ajouter
         * 
         * Ajouter une nouvelle ligne dans la table
         * 
         * @param $table,$colonnes,$valeurs,$ligne
         *
         * @return un array si succès , string le message en cas d'erreur
        */
	function ajouter($table, $colones, $valeurs, $ligne){
            $requete = "INSERT INTO $table $colones VALUES $valeurs ";
            // DEBUG
            echo '<font style="color:red">REQUETE ajouter() => '.$requete.'</font><br>';
            echo '<pre>'.$requete.'</pre><br>';
            $resultat = mysql_query($requete, $this->link);
            $id = mysql_insert_id();
            if($resultat){
                return array('id'=>$id,'ligne'=>$ligne);
            }else{
                return 'ligne '.$ligne.' du fichier '.$name_file.' -> Erreur SQL '.mysql_errno().' : '.mysql_error() .'<br>';	
            }
	}
	
	/**
         * update
         *
         * met a jour une ligne dans la table
         *
         * @param : $table=table de destination
         * @param : $data=(string)les valeurs a mettre a jour sous forme sql (`id`='valeur'),
         *          UPDATE `produit` SET `stock`='1',`ligne`='1',`tva`='19.6' WHERE `id` = '1287'
         * @param : $champ = champ servant a la clause WHERE : exemple : id, ref
         * @param : $valeur = valeur du champ $champ
        */
	function update($table,$data,$champ,$valeur,$ligne){
            $pattern = "#(`id`=')[0-9]*(', )#i";
            $replacement = '';
            $data=preg_replace($pattern, $replacement, $data);
            
            $requete = "UPDATE $table SET $data WHERE $champ = $valeur";
            // DEBUG :
            echo '<font style="color:red">REQUETE update() => '.$requete.'</font><br>';
            $resultat = mysql_query($requete, $this->link);
            $id = mysql_insert_id();
            if ($resultat) {
                return array('id'=>$id,'ligne'=>$ligne);
            } else {
                return 'ligne '.$ligne.' du fichier '.$name_file.' -> Erreur SQL '.mysql_errno().' : '.mysql_error().'<br>';	
            }
	}	
	
        /**
         * verif_exist
         *
         * fonction qui verifie l'existence d'une clé dans la table
         * 
         * @param : $table
         * @param : $champ
         * @param : $valeur
         *
         * @return : id ou FALSE
        */
	function verif_exist($table, $champ, $valeur){
            $requete = "SELECT * FROM `$table` WHERE $champ = '$valeur'";
            //echo $requete;
            $resultat = mysql_query($requete, $this->link);
            if($resultat) {
                $exist = mysql_fetch_assoc($resultat);
            }
            // retourner $id ou FALSE si erreur
            return ($resultat) ? $exist['id']: FALSE;
	}
        
        /*
         * function listeTables
         *
         * Fonction qui liste toutes les tables
         * retourne un array des tables THELIA
         *  
         */
        function listeTables() {
            $sql = "SHOW TABLES";
	    $resultat = mysql_query($sql);
            if($resultat) {
                //$tables=mysql_fetch_array($resultat);
                while ($row = mysql_fetch_array($resultat, MYSQL_NUM)) {
                    $tables[] = $row[0];
                }
                mysql_free_result($resultat);
                return $tables;
            }            
        }
	
	//fonction qui recupere tout les champs d'une table
	function get_table_field($table){
	    return mysql_query("SHOW FIELDS FROM ".$table, $this->link);		
	}
        
        
        /**
         * get_produit_id
         * Fonction qui retrouve l'id d'un produit d'après sa référence
         *
         * Utilisation :
         * $produit = $import->get_produit_id(trim('87115563'));
         * echo "l'id du produit est : ".$produit['id']."<br>";
         * 
        */
        function get_produit_id($ref){
            $sql = "SELECT id FROM produit WHERE ref = '".$ref."'";
	    $resultat = mysql_query($sql);
            if($resultat) {
                $id= mysql_fetch_assoc($resultat);
            }
            mysql_freeresult($resultat);
            // retourner $id ou FALSE si erreur
            return ($resultat) ? $id : FALSE;
        }
        
        /**
         * rubrique_titre2id
         * retrouve l'id d'une rubrique par son titre
         *
         * Utilisation :
         * $rubrique = $import->rubrique_titre2id($data);
         * echo $data.' => '.$rubrique['id'].'<br>';
         *
         * @param = $titre : (string) Titre de la rubrique
         * 
        */        
        function rubrique_titre2id($titre) {
            $sql = "SELECT * FROM rubrique AS rub, rubriquedesc AS rubdesc WHERE rubdesc.rubrique = rub.id AND rubdesc.titre ='".$titre."'";
	    $resultat = mysql_query($sql);
            if($resultat) {
                $match=mysql_fetch_assoc($resultat);
            }
            mysql_freeresult($resultat);
            // retourner id,parent ou FALSE si erreur
            return ($resultat) ? array('id'=>$match['id'],'parent'=>$match['parent']) : FALSE;
        }
        
    
        /*
         * function creer_arbo
         * 
         * Vérifier l'éxistence de l'arbo et la créer si besoin et si l'option Forcer=TRUE
         * 
         * @param $delimiter,$arbo,$forcer
         */
        function creer_arbo($delimiter,$arbo,$forcer) {
            $structure=explode($delimiter,$arbo);
            //on parcour le array $structure
            for($i=0;$i<count($structure);$i++){
                // on teste l'éxitence de la rubrique
                $rubrique[$i] = $this->rubrique_titre2id($structure[$i]);
                if($rubrique[$i]['id']){
                    $rubrique[$i]['titre'] = $structure[$i];
                    // DEBUG : echo "la rubrique ".$rubrique[$i]['titre']." existe, ID = ".$rubrique[$i]['id'].", Parent = ".$rubrique[$i]['parent']."<br>";
                    //return array(
                    //    'id'=> $rubrique[$i]['id'],
                    //    'titre'=>  $rubrique[$i]['titre']           
                    //    );
                }else{
                    // DEBUG : echo "Creer la rubrique ".$structure[$i]."Parent :".$rubrique[$i-1]['id']."<br>";
                    // si forcer=TRUE
                    if($forcer==TRUE){
                        // si c'est le premier niveau d'arbo
                        if($i==0){
                            $rang = $this->rubrique_range($i);
                            $creerRub = $this->ajouter('rubrique','(`parent`,`ligne`,`classement`)',"('0','1','".$rang."')",'');
                        }else{
                            // Si c'est pas le niveau 1
                            $parent = $this->rubrique_titre2id($structure[$i-1]);
                            $rang = $this->rubrique_range($parent['id']);
                            $creerRub = $this->ajouter('rubrique','(`parent`,`ligne`,`classement`)',"('".$parent['id']."','1','".$rang."')",'');
                        }
                        // Création de la Description (titre)
                        $descRub = $this->ajouter('rubriquedesc','(`rubrique`,`lang`,`titre`)',"('".$creerRub['id']."','1','".$structure[$i]."')",'');
                        // DEBUG echo 'La rubrique à été créé : ID => '.$creerRub['id'];
                        //return array(
                        //    'id'=> $creerRub['id'],
                        //    'titre'=> $structure[$i]          
                        //    );
                    }
                    
                }
                // Si on est au bout de l'arbo
                //if(count($structure)==$i){
                //    
                //}
                //var_dump($this->rubrique_titre2id($structure[$i]));
            }
            //return $structure;
        }
        
        
        
        /*
         * function carac_titre2id
         *
         * Retourne l'id de caracteristique depuis son titre
         * 
         * @param $titre
         */
        
        function carac_titre2id($titre) {
            $sql = "SELECT * FROM caracteristique AS carac, caracteristiquedesc AS caracdesc WHERE caracdesc.caracteristique = carac.id AND caracdesc.titre ='".$titre."'";
	    $resultat = mysql_query($sql);
            if($resultat) {
                $match=mysql_fetch_assoc($resultat);
            }
            //mysql_freeresult($resultat);
            // retourner id,parent ou FALSE si erreur
            return ($resultat) ? array('id'=>$match['id']) : FALSE;
        }
        
        /*
         * function vider_carac
         * @param $idProduit
         */
        
        function vider_carac($idProduit) {
            $sql= "DELETE FROM caracval WHERE produit='".$idProduit."'";
            echo '<pre>'.$sql.'</pre><br>';
            $resultat = mysql_query($sql);
            var_dump($resultat);
            if(!empty($resultat)) {
                return $import->message['success']= "Nombre d'enregistrements supprimés : ".mysql_affected_rows();
            }else{
                return $import->message['error'] = 'Erreur SQL '.mysql_errno().' : '.mysql_error().'<br>';
            }
        }
        
        /*
         * function carac_rub
         * @param $carac,$idrub
         */
        
        function carac_rub($carac,$idrub) {
            // tester si la caracteristique est associé a la rubrique
            $sqlTest = "SELECT rubrique FROM `rubcaracteristique` WHERE (rubrique = '".$idrub."' AND caracteristique='".$carac."')";
            $testexist = mysql_query($sqlTest);
            if(mysql_num_rows($testexist) > 0){
                echo "<font style='color:blue;'>la caracteristique ID =".$carac."est associé a la rubrique ID = ".$idrub."</font><br>";
            }else{ // Sinon asocier
                echo "<font style='color:red;'>la caracteristique ID =".$carac."n'est  pas associé a la rubrique ID = ".$idrub."</font><br>";
                $this->ajouter('rubcaracteristique','(`rubrique`,`caracteristique`)',"('".$idrub."','".$carac."')",'');
            }
            mysql_freeresult($testexist);
        }
        
        /*
         * function associer_produit
         * associe un produit a une caracdisp
         * @param $caracdispliste : une liste de caracdisp séparées par des virgules
         * @param $caractitre
         * @param $idproduit
         */
        
        function associer_produit($caracTitre,$caracdispListe,$idproduit,$idrubrique) {
            // Convert pour req SQL
            //$caracTitre = addslashes($caracTitre);
            // Id de caracteristique
            $carac = $this->carac_titre2id($caracTitre);
            
            var_dump($carac);
            // si elle existe
            if($carac['id']){
                echo '<font style="color:green">La CARACTERISTIQUE'.$caracTitre." EXISTE : ID = ".$carac['id'].' : Rubrique = '.$idrubrique.'</font><br>';
                // on vérifie que la caracteristique est associée à la rubrique qui contient le produit
                $this->carac_rub($carac['id'],$idrubrique);    
            }
            // Sinon on crée la caracteristique
            else{
                echo '<font style="color:red">créer la caracteristique : '.$caracTitre.'</font><br>';
                $carac = $this->ajouter('caracteristique','(`affiche`,`classement`)',"('1','".$this->carac_range()."')",'');
                $caracDesc = $this->ajouter('caracteristiquedesc',"(`caracteristique`,`lang`,`titre`)","('".$carac['id']."','1','".$caracTitre."')",'');
                // on associe la rub
                $this->carac_rub($carac['id'],$idrubrique);
            }
            
            // DEBUG : $this->caracdisp_range($carac['id']);
            //TODO : si c'est une update, le script ajoute les caracdisp aulieu de les remplacer
            // On parcours la liste de caracdisp
            for($i=0 ; $i < count($caracdispListe) ; $i++){
                
                echo "<font style='color:purple'>".$i."  --->  ".stripcslashes($caracdispListe[$i]).'</font><br>';
                    
                
                    // On retrouve l'id à partir du titre ET de la caracteristique
                    $sql = "SELECT * FROM caracdisp AS disp, caracdispdesc AS dispdesc WHERE disp.caracteristique='".$carac['id']."' AND dispdesc.titre ='".$caracdispListe[$i]."'";
                    $resultCaracdisp = mysql_query($sql);
                    $caracdisp = mysql_fetch_assoc($resultCaracdisp);
                    //mysql_freeresult($resultCaracdisp);
                    echo 'RESULTAT caracdisp existe :<br>';
                    var_dump($caracdisp);
                    // Si la caracdisp existe
                    if($caracdisp){
                        // On associe alors dans la table caracval le produit a sa caracteristique et caracdisp
                        // si l'association n'est pas déja faite
                        $sqlTest = "SELECT produit FROM `caracval` WHERE (produit = '".$idproduit."' AND caracteristique='".$caracdisp['caracteristique']."' AND caracdisp = '".$caracdisp['id']."')";
                        echo '<pre>'.$sqlTest.'</pre><br>';
                        $testexist = mysql_query($sqlTest);
                        echo 'NOMBRE DE RESULTATS DU TEST D\'ASSOCIATION<br>';
                        var_dump(mysql_num_rows($testexist));
                        if(mysql_num_rows($testexist)==0){
                            echo 'Association du produit '.$idProduit.'a la caracteristique = '.$caracdisp['caracteristique'].' avec la caracdisp ='.$caracdisp['id'].'<br>';
                            // On associe le produit
                            //$sql3 ="INSERT INTO `caracval` (`id`,`produit`,`caracteristique`,`caracdisp`,`valeur`) VALUES ('','".$idproduit."','".$caracdisp['caracteristique']."','".$caracdisp['id']."','')";
                            //$resultCaracval = mysql_query($sql3);
                            $asso = $this->ajouter('caracval','(`id`,`produit`,`caracteristique`,`caracdisp`,`valeur`)',"('','".$idproduit."','".$caracdisp['caracteristique']."','".$caracdisp['id']."','')",'');
                            var_dump($resultCaracval);
                            //mysql_freeresult($resultCaracval);
                        }else{
                            
                        }
                    }else{ // Cree la caracdisp
                        $caracdisp = $this->ajouter('caracdisp','(`caracteristique`)',"('".$carac['id']."')",'');
                        $caracDispDesc = $this->ajouter('caracdispdesc',"(`caracdisp`,`lang`,`titre`,`classement`)","('".$caracdisp['id']."','1','".$caracdispListe[$i]."','".$this->caracdisp_range($carac['id'])."')",'');
                        // On associe le produit
                        //$sql3 ="INSERT INTO `caracval` (`id`,`produit`,`caracteristique`,`caracdisp`,`valeur`) VALUES ('','".$idproduit."','".$caracdisp['caracteristique']."','".$caracdisp['id']."','')";
                        //$resultCaracval = mysql_query($sql3);
                        $asso = $this->ajouter('caracval','(`id`,`produit`,`caracteristique`,`caracdisp`,`valeur`)',"('','".$idproduit."','".$carac['id']."','".$caracdisp['id']."','')",'');

                        //mysql_freeresult($resultCaracval);
                        //$id = mysql_insert_id();
                        //if($resultCaracval){
                        //    return array('id'=>$id,'Caracteristique'=>$carac['id'],'caracdisp'=>$caracdisp['id']);
                        //}else{
                        //    return 'Erreur SQL '.mysql_errno().' : '.mysql_error() .'<br>';	
                        //}
                    }
                
            }
            
        }
        
        
        /*
         * function carac_range
         * @param 
         */
        
        function carac_range() {
            // récupérer le nombre de résultats pour la caracteristique
            $sql = "SELECT * FROM `caracteristique`" ;
            $result = mysql_query($sql);
            $max_range = mysql_numrows($result)+1;
            
            return $max_range;
        }
        
        /*
         * function caracdisp_range
         *
         * Permet de générer un classement suivant le nombre caracdisp déjà présentes
         * 
         * @param $caracteristique
         * 
         */
        
        function caracdisp_range($caracteristique) {
            // récupérer le nombre de résultats pour la caracteristique
            $sql = "SELECT * FROM caracdisp WHERE caracteristique='".$caracteristique."'" ;
            $result = mysql_query($sql);
            $max_range = mysql_numrows($result)+1;
            
            return $max_range;
        }
        
        /*
         * function rubrique_range
         * @param $id_parent,$rubrique
         */
        
        function rubrique_range($id_parent) {
            // recupérer le nombre de résultats pour le parent passé en paramètre, sinon 0 (racine)
            $sql = "SELECT * FROM rubrique WHERE parent='".$id_parent."'" ;
            $result = mysql_query($sql);
            $max_range = mysql_numrows($result)+1;
            
            return $max_range;
            
        }
        
        /*
         * function produit_range
         * @param $id_rubrique
         */
        
        function produit_range($id_rubrique) {
            // recupérer le nombre de résultats pour la rubrique
            $sql = "SELECT * FROM produit WHERE rubrique='".$id_rubrique."'" ;
            $result = mysql_query($sql);
            $max_range = mysql_numrows($result)+1;
            
            return $max_range;
        }
        
        

}

?>