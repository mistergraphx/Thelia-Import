<?php
if(!isset($_SESSION["util"]->id)) exit;
?>

<form action="" name="form_bdd" id="form_bdd" method="post" enctype="multipart/form-data">
<?php 
    if (!isset($_POST['table'])){ $_POST['table'] = 'defaut';}
    if (!isset($_POST['scenario'])){ $_POST['scenario'] = 'defaut';}
    if (!isset($_POST['del'])) { $_POST['del'] = ';';}
    if (!isset($_POST['option'])) { $_POST['option'] = 1;}	
?>
    <table class="table table-striped">
        <tbody>
            <tr>
                <td>Table à importer : </td>
                <td>
                    <select name="table"> 
                        <option value="defaut" <?php if ($_POST['table'] == 'defaut') {echo "selected";} ?> >Choisissez une table</option>
                        <option value="produit" <?php if ($_POST['table'] == 'produit') {echo "selected";} ?> >PRODUIT</option>
                        <option value="produitdesc" <?php if ($_POST['table'] == 'produitdesc') {echo "selected";} ?> >DESCRIPTIF PRODUIT</option>
                        <option value="rubrique" <?php if ($_POST['table'] == 'rubrique') {echo "selected";} ?> >RUBRIQUE</option>
                        <option value="rubriquedesc" <?if ($_POST['table'] == 'rubriquedesc') {echo "selected";} ?> >DESCRIPTIF RUBRIQUE</option>
                        <option value="image" <?php if ($_POST['table'] == 'image') {echo "selected";} ?> >IMAGE</option>   
                    </select>
                </td>
                <td>
                    <p>
                        Explication : importez un <b>fichier csv de structure identique à la table thelia</b> que vous sélectionez.
                        Vous pouvez extraire ces données avec le module EXPORT CSV ou PhpMyAdmin,
                        puis les modifier avec votre tableur préféré.
                    </p>
                </td>
            </tr>
            <tr>
                <td>Choix du fichier :</td>
                <td>
                        <input type="file" name="fichiercsv" size="16">
                </td>
                <td>
                    <span class="help-block">
                        Explication : Type de fichiers acceptés <?php var_dump($extensions_valides); ?>
                    </span>
                </td>
            </tr>
            <tr>
                    <td>Délimiteur : </td>
                    <td>
                        <select name="del">
                            <option value=";" <?php echo ($_POST['del'] == ';' ? ' selected>; Point virgule' : '>; Point virgule'); 
                            ?></option>
                            <option value="," <?php echo ($_POST['del'] == ',' ? ' selected>, Virgule' : '>, Virgule'); 
                            ?></option>
                            <option value=":" <?php echo ($_POST['del'] == ':' ? ' selected>: Deux points' : '>: Deux points'); 
                            ?></option>
                            <option value="-" <?php echo ($_POST['del'] == '-' ? ' selected>- Tiret' : '>- Tiret'); 
                            ?></option>
                            <option value="/" <?php echo ($_POST['del'] == '/' ? ' selected>/ Slash' : '>/ Slash'); 
                            ?></option>
                            <option value="|" <?php echo ($_POST['del'] == '|' ? ' selected>| Barre' : '>| Barre'); 
                            ?></option>
                            <option value="#" <?php echo ($_POST['del'] == '#' ? ' selected># Dièse' : '># Dièse'); 
                            ?></option>
                        </select>
                    </td>
                    <td>
                        <p>
                            Explication :
                        </p>
                    </td>
            </tr>
            <tr>
                    <td>Mode : </td>
                    <td>
                        <label for="option" class="radio">
                            <input type="radio" class="" name="option" value="1" <?php if ($_POST['option'] == 1) {echo "checked";} ?>>
                            Insertion + Update 
                        </label>
                        <label for="">
                            <input type="radio" name="option" value="0" <?php if ($_POST['option'] == 0) {echo "checked";} ?>>
                            Insertion seule
                        </label>
                    </td>
                    <td>
                        <p>
                            Explication :
                        </p>
                    </td>
            </tr>
            <tr><td colspan="3"><input type="hidden" name="etape2" value="1"></td></tr>
            <tr><td colspan="3"><input type="submit" class="btn btn-large btn-primary" value="Etape II"></td></tr>
        </tbody>
    </table>
</form>