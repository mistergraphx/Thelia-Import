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
                <td>Scénario</td>
                <td>
                    <select name="scenario">
                        <option value="defaut" <?php if($_POST['scenario'] == 'defaut'){echo "selected";} ?> >Choisissez un scénario</option>
                        <option value="produit" <?php if($_POST['scenario'] == 'produit'){echo "selected";} ?> >Produits</option>
                    </select>
                </td>
                <td>
                    <span class="help-block">
                        Explication : le scénario vous permet d'importer des données depuis un seul fichier csv, vers plusieures tables Thélia.
                        Par exemple, le scénario produit, vous permet d'importer les données des produits vers les tables : produit, produitdesc,reecriture
                    </span>
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
                <td>Options</td>
                <td>
                    <label for="forcer_creation_rubrique">Forcer la création de rubriques</label>
                    <select name="forcer_creation_rubrique" id="forcer_creation_rubrique">
                        <option value="">Choisissez</option>
                        <option value="TRUE">OUI</option>
                        <option value="FALSE">NON</option>
                    </select>
                    
                    
                    <label for="forcer_arbo">Forcer l'arborescence</label>
                    <input type="text" name="forcer_arbo">
                    
                    <label for="lang">Langue par défaut</label>
                    <select name="lang" id="lang">
                        <option value="">Choisissez</option>
                        <option value="1">Français</option>
                    </select>
                    
                    <h4>Remplacer caractéristiques</h4>
                    <span class="help-block">
                    Remplacer les valeurs de caractéristiques par celles de l'import.
                    (Par défaut OUI)
                    </span>

                    <label class="radio inline">
                        <input type="radio" name="remplacer_carac" id="remplacer_carac" value="TRUE">Oui
                    </label>
                    <label class="radio inline">
                        <input type="radio" name="remplacer_carac" id="remplacer_carac" value="FALSE">Non
                    </label>
                    
                </td>
                <td>
                    <h4>Stock</h4>
                    <span class="help-block">Spécifiez une valeur de stock par défaut</span>
                    <input type="text" name="stock" placeholder=" par défaut 1">
                    
                    <h4>Mettre en ligne</h4>
                    <span class="help-block">
                    Forcer la mise ne ligne des produits (par défaut oui).
                    </span>

                    <label class="radio inline">
                        <input type="radio" name="ligne" id="ligne" value="1">Oui
                    </label>
                    <label class="radio inline">
                        <input type="radio" name="ligne" id="ligne" value="0">Non
                    </label>
                    
                    
                    <h4>TVA</h4>
                    <input type="text" name="tva" placeholder="par défaut 19.6">
                        
                    <h4>Créer les images associées</h4>
                        <label class="radio inline">
                            <input type="radio" name="creer_image" value="TRUE">Oui
                        </label>
                        <label class="radio inline">
                            <input type="radio" name="creer_image" value="FALSE">Non
                        </label>
                    
                </td>
            </tr>
            <tr><td colspan="3"><input type="hidden" name="etape2" value="1"></td></tr>
            <tr><td colspan="3"><input type="submit" class="btn btn-large btn-primary" value="Lancer l'importation"></td></tr>
        </tbody>
    </table>
</form>