<?php

if(!isset($_SESSION["util"]->id)) exit;


    
    // Délimiteur
    $del = $_POST['del'];
    
    // Si le formulaire a été validéet qu'on passe a l'étape 2 
    if(isset($_POST['etape2'])){
        // On vérifie qu'un fichier à bien été sélectionné
        if((empty($_FILES['fichiercsv']['tmp_name']))){
            return $import->message['error'] = "Vous n'avez pas choisi de fichier à uploader.";
            exit();
        }
        $tmp_file = $_FILES['fichiercsv']['tmp_name']; 
        
        if(!is_uploaded_file($tmp_file)) {
            return $import->message['error'].="Le fichier est introuvable.";
            exit();
        }   
        
        // DEBUG
        //info de fichier à récupérer (debug)
        //$tmp_file = $_FILES['fichiercsv']['name'];
        //echo 'nom du fichier :'.$tmp_file.'<br />';
        //echo  'type du fichier : '.$type_file.'<br />';

        //extention de fichier à récupérer
        $extension_upload = substr(strrchr($_FILES['fichiercsv']['name'], '.'), 1);
    
        // on vérifie si l'extension du fichier uploadé est valide
        if (!in_array($extension_upload,$extensions_valides)){
            exit($import->message['error'].="extension incorrecte. Vous ne pouvez que uploader des fichiers <strong>".$extensions_valides."</strong>.");
        }else {
            // on copie le fichier dans le dossier tmp
            $name_file = $_FILES['fichiercsv']['name'];	
            if(!move_uploaded_file($tmp_file, $content_dir . $name_file)){
                    exit($import->message['error'].="Impossible de copier le fichier dans $content_dir");
            }	
            $import->message['success'] .= "Le fichier $name_file a bien été uploadé<br>";
        }
        
    }
    
    // ---------------------------------------------------------------------
    // Mode table : Mise a jour d'une structure identique à une table thelia
    // ---------------------------------------------------------------------
    if (isset($_POST['table']) && $_POST['table'] !="defaut" && isset($_POST['etape2'])) {
    ?>
        <form action="" name="form_import" id="form_import" method="post">
        <?php
        
        $result = $import->get_table_field($_POST['table']);
      
        
        /* Debug ---------------------------------------------------------
         * Afficher la structure de la table 
        <h4>Information sur la structure de la table</h4>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Type</th>
                    <th>Null</th>
                    <th>Key</th>
                    <th>Default</th>
                    <th>Extra</th>  
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysql_fetch_row($result)) {
                        ?>
                        <tr style="background-color:#fff">
                        <td><?php echo $row[0] ?>&nbsp;</td>
                        <td><?php echo $row[1] ?>&nbsp;</td>
                        <td><?php echo $row[2] ?>&nbsp;</td>
                        <td><?php echo $row[3] ?>&nbsp;</td>
                        <td><?php echo $row[4] ?>&nbsp;</td>
                        <td><?php echo $row[5] ?>&nbsp;</td>
                        </tr>
                        <?php
                }
                ?>
            </tbody>
        </table>
        */
        
            //traitement après upload du fichier
            if(file_exists("$content_dir"."$name_file")) {	
                //on lis la 1ere ligne pour vérifier et générer la liste des champs
                $fp=fopen("$content_dir"."$name_file", 'r');
                //recupere la première ligne en csv
                $tabcsv = fgetcsv($fp, 0, $del);	
                fclose ($fp);
                ?>
                    
                    
                    <style type="text/css">
                    table.no-padding td,
                    table.no-padding th {padding:0;}
                    th>span{
                        display:inline-block;
                    }
                    table.import td {
                        padding:10px 0;
                    }
                    table.import tr {
                        border-bottom:1px solid black;
                    }
                    span.champs{width:30%;}
                    td.champs{width:7%;}
                    .traitements{width:25%;}
                    .forcer_val{width:5%;}
                    /*.md5{width:15%;}*/
                    .destination{width:30%;}
                    </style>
                    
                    
                    <?php
                    // -----------------------------------------------------------
                    // OPTIONS GÉNÉRIQUES
                    // -----------------------------------------------------------
                    ?>
                    <table class="table table-striped">
                        <thead>
                            <th><h4>Options génériques</h4></th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <?php
                                    // option d'import des produits
                                    if($_POST['table']=='produit') { ?> 
                                    <label for="creerimages" class="checkbox in-line">
                                        <input type="checkbox" value ="1" name="creerimages">
                                        Créer les images associées aux produits
                                    </label>
                                    <?php
                                    }
                                    // Option d'import des descriptions
                                    elseif($_POST['table']=='produitdesc') { ?> 
                                    <label for="creerurl" class="checkbox in-line">
                                        <input type="checkbox" value ="1" name="creerurl">
                                        Créer les url propres associées aux descriptions des produits
                                    </label>
                                    <?php
                                    }else {
                                        echo "<div class=\"alert alert-info\">Pas d'options pour ce type d'imports</div>";
                                    } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    
                            
                            
                    <table class="table no-padding">
                        <thead>
                            <tr>
                                <th>
                                    <span class="champs">Champ de la table</span>
                                    <span class="traitements">Traitements</span>
                                </th>
                                <th>
                                    <span class="destination">Champ du fichier CSV</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                <table class="import">
                                <?php 			
                                //génération des listes de gauche avec les champs de la bdd
                                $fields = mysql_num_rows($result);
                                if ($fields != 0) {
                                    $j = 0;
                                    while ($j < $fields){
                                        ?>
                                        <tr>
                                            <td class="champs" style="border:none;">
                                                <select name="colone<?php echo $j ; ?>"> 
                                                <option value="" <?php if (!isset($_POST['colone0'])){ echo'selected'; } ?> >Choisissez une colonne</option>
                                                    <?php
                                                    // on affiche les champs de la table choisie
                                                    // titre des colonnes
                                                    $i = 0;
                                                    while ($i < $fields) {
                                                            echo "<option ";
                                                            if ($i == $j){ echo'selected '; }
                                                            echo "value=\"" . mysql_result($result, $i) . "\">" . mysql_result($result, $i) . "</option>";  
                                                            $i++;
                                                    }// fin while i
                                                    ?>  
                                                </select>
                                            </td>
                                            <!-- Traitemnts -->
                                            <td class="traitements" style="border:none;">
                                                <!-- Ignorer ce champ -->
                                                <label for="ignore" class="checkbox inline">
                                                    <input type="checkbox" value ="1" name="ignore<?php echo $j; ?>">
                                                    Ignorer
                                                </label>
                                                <!-- Retrouver une id via une référence produit -->
                                                <label class="checkbox inline" for="ref2id">
                                                    <input type="checkbox" value ="1" name="ref2id<?php echo $j; ?>">
                                                    Ref2id
                                                </label>
                                                <!-- Retrouver une id via un titre de rubrique -->
                                                <label class="checkbox inline" for="titre2id">
                                                    <input type="checkbox" value ="1" name="titre2id<?php echo $j; ?>">
                                                    Titre2id
                                                </label>
                                                <label for="crypte" class="checkbox inline">
                                                    <input type="checkbox" value ="1" name="crypte<?php echo $j; ?>">
                                                    Md5
                                                </label>
                                            </td>
                                            <td class="forcer_val" style="border:none;">
                                                <input type="text" size="10" name="force<?php echo $j; ?>" placeholder="Forcer la valeur">
                                            </td>
                                        </tr>
                                        <?php
                                        $j++;
                                    } // fin while j
                                ?>    
                                </table>
                                </td>	
                                <td>
                                    <table class="import">
                                    <?php			
                                            // génération des listes de destination
                                            // ---------------------------			
                                            $j = 0;
                                            while ($j < $fields){
                                                    ?>
                                                    <tr>
                                                        <td style="border:none;">					
                                                            <select name="colonecsv<?php echo $j ; ?>"> 
                                                            <option value="" >Aucune correspondance</option>
                                                            <?php
            
                                                            // on affiche les champs de la table choisie
                                                            // titre des colonnes
                                                            $i = 0;
                                                            while ($i < count($tabcsv)) {
                                                                    echo "<option ";
                                                                    if ($i == $j){ echo 'selected '; }
                                                                    echo "value=\"" . $i . "\">" . $tabcsv[$i] . "</option>";  
                                                                    $i++;
                                                            }
                                                            ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <?php 
                                                    $j++;
                                            } //-------------------------------				
                                    }
                            }// fin if_file_exist
                            else {
                                $import->message['error'] .='erreur. Fichier csv mal uploadé.';
                            }?>
                                    </table>
                                    </td>
                            </tr>
                            <tr>
                                <td>
                                    <!--informations pour faire l'importation-->
                                    <input type="hidden" name="content_dir" value="<?php echo $content_dir ; ?>">		
                                    <input type="hidden" name="name_file" value="<?php echo $name_file ; ?>">
                                    <input type="hidden" name="table" value="<?php echo $_POST['table'] ; ?>">
                                    <input type="hidden" name="option" value="<?php echo $_POST['option'] ; ?>">
                                    <input type="hidden" name="fields" value="<?php echo $fields ; ?>">
                                    <input type="hidden" name="del" value="<?php echo $_POST['del'] ; ?>">				
                                    <input type="hidden" name="nb_colones_csv" value="<?php echo count($tabcsv) ; ?>">				
                                    <input type="hidden" name="listeok" value="1">
                                    <input class="btn btn-large btn-primary" type="submit"  value ="Etape III > Importer dans la bdd">	
                                </td>
                            </tr>
                            
                            </tbody>
                        </table>
                    </form>
    <?php 
    } //fin du if isset mode Table
    //Mode scenario : EN DEV
    elseif (isset($_POST['scenario']) && $_POST['scenario']!="defaut" && isset($_POST['etape2'])){
        $import->message['success'].= "Mode SCENARIO : ".$_POST['scenario']."<br>";
        include_once(realpath(dirname(__FILE__)) . "/scenario_produit.php"); 
    }else{
        $import->message['error'].= "Vous n'avez pas choisi de Mode : table ou scénario de donnée<br>";
    }

?>
