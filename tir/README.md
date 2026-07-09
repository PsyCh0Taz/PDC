# TIR - Application de gestion des séances de tir

## Présentation

TIR est une application web légère conçue pour gérer les inscriptions à des séances de tir militaire. Elle fonctionne avec PHP 5.4, PDO et est compatible avec MySQL et SQLite.

## Prérequis

- PHP 5.4 avec PDO
- MySQL ou SQLite
- LDAP accessible pour l'authentification
- Serveur web compatible Apache ou équivalent
- Activation des extensions `pdo_mysql` ou `pdo_sqlite`
- Programmation old-school, pas de models, pas de controllers pas de views

## Utilisation

### Authentification

- Les utilisateurs se connectent via LDAP.
- Aucune gestion de mot de passe local n'est utilisée.
- Les informations utilisateur sont synchronisées dans la table `users`.

### Rôles

- **Tireur** : consulte les séances, s'inscrit, se désinscrit, consulte ses inscriptions et l'historique.
- **Administrateur** : gère les séances, publie/dépublie, gère les listes d'attente, pointe les présences, valide les tirs et No Safe, gère les référentiels d'armes et notifications.


Pour un administrateur, TIR118 permet :
- De gérer un catalogue d'armes, par exemple : "PA MAC50","BERETTA 92FS","Fusil de chasse juxtaposé". On doit pouvoir ajouter, modifier, supprimer une arme. Pour chaque arme, on retrouve un libellé et une image;
- De gérer des catégories d'armes, par exemple : "Armes de poing", "Fusils d'assaut", "Fusils de chasse". On doit pouvoir ajouter, modifier, supprimer une liste et ajouter, supprimer une arme du catalogue des armes dans ces listes. On retrouvera un titre, une image ;
- De gérer un catalogue de raison du tir , par exemple : "Renouvellement","Inscription". On doit pouvoir ajouter, modifier, supprimer une raison. Pour chaque raison, on retrouve un libellé ;

- De gérer des catégories de tir qui serviront de modèle (par exemple "Tir Armes de poing", "Tir fusils d'assaut"). On doit pouvoir ajouter, modifier, supprimer une catégorie. Pour chacune de ces catégories, on pourra associer une catégorie d'arme, on retrouvera un titre, une icone, une date de raison et une ou plusieurs raisons, une couleur

- De gérer des tirs : L'administrateur doit pour ajouter, modifier, supprimer un tir. Chaque tir est basé sur un modèle de catégorie de tir et associé à une date/heure de début, date/heure de fin, un nombre de place prévue et une liste d'inscrits.   

- L'administrateur devra pouvoir ajouter, supprimer un tireur sur ce créneau, à chaque modification, il sera proposé d'envoyer un mail de confirmation. 

- TIR118 permet de gérer sa page de garde avec un ou plusieurs articles modifiables à volonté avec une interface "Rich Text Box".

Pour un utilisateur : 
- TIR118 affiche sur sa page de garde un caroussel du ou des articles préparés par l'administrateur.
- TIR118 permet de consulter un calendrier présenté par mois sur lequel on retrouve tous les tirs planifiés. On doit pouvoir naviguer simplement dans ce calendrier et se rendre à une date précise. Un filtre doit permettre de filtrer par catégorie. En bas de l'écran, un rappel cliquable doit donner la liste de toutes les catégories avec la prochaine date pour laquelle une place est disponible, un clic amène sur le mois concerné.
- Il permet de réserver une place dans une séance de tir prévu dans un calendrier en cliquant sur le créneau souhaité. Une fiche s'ouvre, elle permet de renseigner  les données de la catégorie de tir correspondante : la raison et sa date, l'arme choisie dans la catégorie d'arme du tir, son mail. Un mail de confirmation est envoyé pour confirmer l'inscription avec un lien de désinscription basé sur un hash;
- Il permet de se désinscrire d'un créneau. Soit en rentrant le hash d'inscription dans un écran, soit via le lien reçu. Un mail est envoyé pour confirmer la désinscription. 



### Accès admin

L'utilisateur est considéré comme administrateur si :

- son OU LDAP est "ou=ccoa,ou=ec2sa,ou=ba118,ou=users,dc=a,dc=c,dc=d,dc=fr"

## Remarques

- Le projet respecte PHP 5.4 strict et n'utilise pas de frameworks modernes.
- Les protections CSRF et XSS sont intégrées dans les formulaires et vues.

