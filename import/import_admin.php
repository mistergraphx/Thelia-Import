<?php
    // SECURITE
    include_once(realpath(dirname(__FILE__)) . "/../../../fonctions/authplugins.php");
    autorisation("import");
    // -------------------------------------------
    include_once(realpath(dirname(__FILE__)) . "/Import.class.php");
    
    // Config
    // -------------------------------------------
    // dossier tmp où sera déplacé le fichier csv
    $content_dir = '../client/plugins/import/tmp/';
    //tableau d'extension correcte
    $extensions_valides = array( 'csv' , 'txt' );
    
?>
<style>
    #form_bdd h4{
        padding-bottom:5px;
    }
</style>
    <div class="row-fluid">
        <p>
            <a href="accueil.php">Accueil </a>
            <i class="icon-chevron-right"></i>
            <a href="module_liste.php">Modules</a>
            <i class="icon-chevron-right"></i>
            <a href="module.php?nom=import">Import en masse</a>
        </p>
    </div>
    <div class="row-fluid">
        <div class="span12">
             <h3>IMPORTATION</h3>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <ul class="nav nav-tabs">
                <li>
                    <a href="module.php?nom=import">Accueil</a>
                </li>
                <li>
                    <a href="module.php?nom=import&action=import">Import</a>
                </li>
                <li>
                    <a href="module.php?nom=import&action=scenario">Scénario</a>
                </li>
                <li>
                    <a href="module.php?nom=import&action=outils">Outils</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12 bigtable">
            <?php
            
            $import = new Import();
        
            // DEBUG --------------------------------------------------
            // $produit = $import->get_produit_id(trim('87115335'));
            //
            // echo "l'id du produit est : ".$produit['id']."<br>";
            
            // $message['error']="ERREUR";
            // echo  $_SERVER['PHP_SELF'];
            // ---------------------------------------------------------
            
           
            
        
            
             //Etape Un
        //if(isset($_GET['page'])){
        //        $page=$_GET['page'];
        //}
        //include("form_bdd.php");
        //
        ////Etape Deux
        //include("form_import.php");
        //
        ////Etape Trois
        //include("import_bdd.php");
    
            if($_REQUEST['action'] == ""){
                include_once("vues/accueil.php");
            }
            elseif($_REQUEST['action'] == "import"){
                include_once("vues/form_import.php");
            }
            elseif($_REQUEST['action'] == "scenario"){
                include_once("vues/form_scenario.php");
            }
            elseif($_REQUEST['action'] == "outils"){
                include_once("vues/outils.php");
            }
            if(isset($_POST['etape2'])){
                include_once("vues/import_et2.php");
            }
            
            // affichage des messages
            if(isset($import->message['info'])){
                echo '<div class="alert alert-info">'.$import->message['info'].'</div>' ;
            }
            elseif(isset($import->message['error'])){
                echo '<div class="alert alert-error">'.$import->message['error'].'</div>' ;
            }
            elseif(isset($import->message['success'])){
                echo '<div class="alert alert-success">'.$import->message['success'].'</div>' ;
            }else{
                
            }
            
            
            
            ?>
    
    </div><!-- ./span12 -->
</div><!-- ./.row-fluid -->

<div class="modal hide fade in" id="pluginsDocModalScenario">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3>Documentation du mode Scénario</h3>
    </div>
    <div class="modal-body">
       <?php
        // Reading input file in Markdown markup
        $doc = '../client/plugins/import/doc_scenario.txt';
        $in_handler = fopen($doc, "r");
        $markdown_text = fread($in_handler, filesize($doc));
        fclose($in_handler);
        echo(Markdown($markdown_text));
        ?>
    </div>
    <div class="modal-footer">
        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Fermer', 'admin'); ?></a>
    </div>
</div>

<div class="modal hide fade in" id="pluginsDocModalImport">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3>Documentation du mode Import</h3>
    </div>
    <div class="modal-body">
       <?php
        // Reading input file in Markdown markup
        $doc = '../client/plugins/import/readme.txt';
        $in_handler = fopen($doc, "r");
        $markdown_text = fread($in_handler, filesize($doc));
        fclose($in_handler);
        echo(Markdown($markdown_text));
        ?>
    </div>
    <div class="modal-footer">
        <a class="btn" data-dismiss="modal" aria-hidden="true"><?php echo trad('Fermer', 'admin'); ?></a>
    </div>
</div>
