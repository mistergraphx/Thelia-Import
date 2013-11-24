IMPORT 2 :
==========

Basé sur le plugin Import pour Thelia 1.4, auteur Gil Fourgeau.

Avertissements :
----------------

*   Avant de travailler sur des importations, ou mises a jour il est recommandé de **faire une sauvegarde de la base de donnée**.


Utilisation :
-------------

1.  Préparation du fichier avec Excel :
    
    *Tiré du plugin originel mais non testé n'utilisant pas excel*

    Ouvrir le fichier dans excel (http://www.culturemediatic.fr/boutique/documents/import_produits_thelia.zip).
    Créer une nouvelle colonne le plus à droite possible que vous remplirez avec ce que vous voullez... 
    Enregistrez sous >> CSV.
    
    Cela permet au moment de l'enregistrement au format CSV de générer le bon nombre de point virgule
    si certaines lignes n'ont pas d'infos dans leur dernière colonne. En effet excel ne rajoute pas de ";"
    
    après la dernière valeur d'une ligne même si il reste des colonnes vides...
    
    Au moment de l'import les lignes incomplètes sont ignorées.
    Il faut donc formatter le fichier csv dans excel grâce à cette petite manip avant de l'enregistrer.


2.  Le script permet aussi d'ajouter des données dans d'autres tables de la base :
    Il suffit d'ajouter après la ligne ligne 10 de fichier form_bdd.php "`<select name="table">`":
    des balise option, il faut mettre le vrai nom du table dans l'attribut value, par exemple:
    `<option value="nom table dans base de donnée">nom de table vous l'appele<option>`
	
3.  Pour importer les données correctememt, il faut bien mettre les clé étrangères, 
    par example si le id_rubrique rentrée dans le produit correspondant à aucune rubrique, le produit va afficher nul part(même pas dans l'admin).

4.  Pour imporer les images, il faut aussi copier les images dans le dossier correspandant, 
    si il est associé avec un produit, mettre dans le répertoire client/gfx/photos/produit
    si il est associé avec un contenu, mettre dans le répertoire client/gfx/photos/contenu
    etc
    Le nom du image doit être exactement la même que celle dans la base.
    Vous pouvez utiliser le plugin uploadimage pour faire cette opération.
 
5.  Le script sert pour les clients qui veulent import grand masse de donnée eux-même sans avoir accès à la base.
    (Sinon phpmyadmin supporte mieux que ce plugin.)


---------------------------------------------------------------------------------

## Mode TABLES :

### import :

* le fichier csv doit avoir le même nombre de colonnes que les champs de la table.

* Préférer le séparateur ';' et enregistrer le fichier en entourant les cellules de textes de guillemets si les textes
contiennent des signes (') apostrophes

* la première colonne étant normalement l'id en base de donnée, cette colonne est obligatoire et doit être remplie même de valeurs fausses

* les autres champs peuvent subire des traitement ou des valeurs forcées :
par exemple rubrique sur une table produit pourra être forcée a 2 (2 étant l'id en base de donnée de la rubrique)

* Options Génériques : lors d'un import de produits, on peut créer directement une image associée
(les images doivent êtres placées dans /client/gfx/produit/ et sousfixées `REFERENCE_1.jpg`).

* Traitements :

    Ref2Id
    : (produits) permet de retrouver l'id en base de donnée à partir de la référence produit.
    
    Titre2Id
    : (Rubriques) permet de retrouver l'id en base d'une rubrique daprès son titre.
    Attention si plusieurs rubriques portent le même titre !!

#### produits :

Importation d'un fichier csv des produit :

*   Option génériques :

    *   Créer les images correspondantes : permet de créer à ce moment une image liée au produit
        En cochant cette option, on associe une image au produit.
        Limitations : le titre de l'image nest pas insséré, seul le lien est créé,
        les images doivent être placée dans le dossier /client/gfx/produits et nommées sous la forme `REFERENCE_1.jpg`

*   Le champ ID doit être rempli (y compris avec des valeurs bidons).
    Au moment de l'import cocher la case ref2id dans les traitements sur le champ id
    permet de retourner une valeur vide et donc d'ignorer les valeurs de cette colonne
    TODO : supprimer cette nécessité, l'utilisateur e-commerçant n'as pas de necessité d'avoir accès a cette info

*   Remplir les valeurs forcées si besoin :
    par exemple la langue (1, pour le français) ou les valeurs,
    comme la tva qui n'ont pas à être répété dans le fichier csv sur toutes les lignes
    Rappel : les prix et tva doivent êtres avec un séparateur décimal [00.00]

*   Rubrique d'appartenance : on peut choisir l'option de traitement titre2id,
    qui permet de retrouver l'id en base de donnée de la rubrique, ainsi le e-commerçant peut concevoir son fichier
    en remplissant le champ `rubrique` avec le titre réel de sa rubrique, ce qui est relativement plus confortable.


#### produidesc : description des produits

Importation d'un fichier csv de description des produits

*   Pour lier la description du produit aux informations techniques de prix, tva



### update :

l'update se fait sur la référence du produit et non sur l'id en base

*   Comme pour l'import, le fichier csv doit contenir le mêm nombre de champs que la table de destination
    et avoir une première colonne id, remplie avec des valeurs bidons, car ignorée par la suite

*   En cochant la case traitement ref2id on active la fonction
    qui retrouve une id en base de donnée à partir d'une référence

------------------------------------------------------------------------------

TODO :
------

* TODO : INSERT : prévoir la ré-écriture de url : actuellement le plugin ne gère pas les url ré-écrites
* TODO : INSERT : prévoir l'importation du descriptif,titre,chapo en même temps que les champs ref,prix, sur un produit
* TODO : INSERT : supprimer la necessiter du champ id
* TODO : INSERT : Rajouter un(des) test(s) sur l'éxitence d'une ref, ou la présence d'une rubrique a l'importation de produit
* TODO : INSERT : titre2id : faire un test sur le parent, pour les rubriques portant le même nom :
actuellement il prend la première rubriqu'il trouve ayant un titre
* TODO : INSERT : Ajouter la possibiliter de forcer sur une rubrique parent

------------------------------------------------------------------------------

TRAVAUX :
---------
*   import.class = update : suppression du champ id des data => on UPDATE pas un auto-increment !!
*   import.class = modification de la fonction ajouter() pour qu'elle retourne une id en cas de succès
*   ajout d'options : insert images sur l'import de produits
*   DEBUG : echapement des apostrophes contenues dans les datas inssérées avant préparation de la requète



    
    

