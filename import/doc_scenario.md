MODE SCENARIO :
===============

Par scénario on entend, qu'il est possible de construire un import dont le fichier csv
n'est pas calqué sur la structure et limité a une table THELIA.

Exemple de fichier csv scenario :

|   PRODUIT:ref   |   PRODUITDESC:titre |   PRODUIT:prix    |   PRODUIT:rubrique            |   CARACTERISTIQUE:Thèmes  | 
| --------------- | ------------------- | ----------------- | ----------------------------- | ------------------------- |
| PP-8756         | Titre du produit1   | 12,5              | Rubrique1>Rubrique2>Rubrique3 | Brésil,Carnaval,Pailettes |

## Mise en garde

*   **Il est fortement conseillé des faire une sauvegarde de la base de donnée avant chaque imports**
*   **Il est conseillé de ne pas faire d'import sur une site en production et donc de travailler en local**
*   **Il est conseillé de faire plusieurs fichiers, par exemples par rubriques principales, pour ne pas provoquer une érreur de time out**


## Préparation du fichiers csv

Pour des raisons de compatibilité du format csv il est conseillé d'utiliser LibreOffice ou OpenOffice (Logiciels Libres et gratuits),
la suite Microsoft Office posant des problème d'encodages et ne proposant pas toute les fonctions d'export necessaires.

L'esport csv se fera de préférence avec ces options :

*   Encodage : UTF-8
*   Séparateur de colonne : ' ; ' (Point virgule)
*   Protection des champs texte : ' " ' (Guillemets)



## Définir la table de destination et le champ

La table de destination est spécifiée dans l'entête de colonne en capitale
(purement esthétique et pour être cohérent avec le langage de boucle de THELIA),
puis le champ séparé par ':'

par exemple PRODUIT:ref ou PRODUITDESC:titre (sans espaces)

**la première colonne doit toujours être la référence produit.**

## Traitements complémentaires :

En fonction du type de champ ou de la table, des traitements complémentaires sont appliqués
    
*   prix : on formatte automatiquement les prix au format international en remplaçant les virgules par un point.

*   Les url propres sont crées automatiquement au moment de l'ajout d'un nouveau produit.

*   Si l'option est activé, les images sont créées et liées au produit en base de donné.
    Il faut ensuite les déposer dans le dossier /client/images/produits/ du site
    en les nomant reference_1.jpg (1 étant l'image 1 associé a la référence produit).
    La taille conseillé pour les images est de 600x800px max.
    
    **Le script ne gère qu'une seule image produit**
    



*   La rubrique dont dépend le produit : est définie par son titre,
    le script retrouve l'identifiant de la rubrique par le titre, les titres doivent donc êtres strictement identiques,
    une option du script permet de forcer la création automatique des rubriques si elles n'éxistent pas.
    
    Définire une arborescence de rubriques :
    On définie l'arborescence depuis la racine du catalogue en utilisant la syntaxe :
    
    `Rubrique de niveau 1>Rubrique de niveau 2>Rubrique de Niveau 3`
    
    On peut forcer une partie de l'arborescence en définissant la variable `forcer_arbo` :
    
        $forcer_arbo = "Je me déguise>Déguisements enfants>";
    
    **Les rubriques se classe par ordre d'importation**,
    on génère un rang en fonction du nombre de rubrique contenues dans le parent.
    On peut ensuite modifier le classement dans l'administration.
    
    
*   Association des caractéristiques aux produits :
    Dans l'entête de colonne on signale par CARACTERISTIQUE:titre
    et dans les colonnes on insère les caracdisp en utilisant la virgule comme séparateur.
    
    On doit donc créer une colonne par caracteristique, pour ensuite lister les valeurs de caracdisp.
    Dans le cas de plusieures valeurs de caracdisp (types de caractéristiques) on les sépares par une virgule :
    on ne peut donc pas avoir un type de caracteristique ayant dans son intitulé une virgule.
    
    *   A partir du titre on extrait de la table caracdispdesc la caracdisp (id)
    *   A partir de l'id de caracdisp on extrait de la table caracdisp sa caracteristique (si besoin)
    *   On associe alors dans la table caracval le produit a sa caracteristique et caracdisp
    

## Forcer certains champs

On peut définir dans les variables du script, en éditant ou dupliquant le fichier /vues/scenario_produit.php
    
    // lang par défaut 
    $lang = '1';
    // tva par défaut
    $tva = '19.6';
    // ligne (le produit est en ligne : 1 ou 0)
    $ligne = '1';
    // Créer les images à l'insert d'un nouveau produit
    $creer_image = TRUE ;
    // Forcer Rubrique Parente (Rub 1>Rub 2>)
    $forcer_arbo = "Je me déguise>Déguisements enfants>";
    // Création des rubriques si elles n'éxistent pas
    $forcer_creation_rubrique = TRUE;
    
    
## INFOS :

## Pas implémenté :
  
*   Ignorer certains champs du csv qui peuvent être utile au commerçant mais pas dans l'import vers THELIA

*   Mise en forme au format Markdown, sur les champ description :
    *   Prévoire une directive de configuration

### TRAVAUX :

DONE :  gestion d'un critère de tri sur les caracdisp : si il n'y a pas de tri lors de l'insertion,
        ça fait planter le tri dans les listes et provoque des problèmes de boucle dans le template ou l'admin

### Vider les Tables THELIA :

images produits

```
TRUNCATE TABLE `produit`;
TRUNCATE TABLE `produitdesc`;
TRUNCATE TABLE `rubrique`;
TRUNCATE TABLE `rubriquedesc`;
TRUNCATE TABLE `caracteristique`;
TRUNCATE TABLE `caracteristiquedesc`;
TRUNCATE TABLE `caracdisp`;
TRUNCATE TABLE `caracdispdesc`;
TRUNCATE TABLE `caracval`;
TRUNCATE TABLE `rubcaracteristique`;
TRUNCATE TABLE `reecriture`;
DELETE FROM `image` WHERE `produit` NOT LIKE '0';
```


