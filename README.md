# Cahier des Charges - Application PDC (Plan de Charge)

## 1. Objet du document

Ce document definit le cahier des charges fonctionnel et technique de l'application PDC (Plan de Charge), destinee a la gestion et au suivi des domaines et projets dans une structure hierarchique, avec controle fin des droits d'acces.

Il sert de reference pour :

- le developpement
- la validation metier
- la recette
- la maintenance evolutive

## 2. Contexte et objectifs

L'application doit permettre de :

- representer une organisation sous forme de niveaux hierarchiques (arborescence)
- rattacher des domaines a un niveau hierarchique
- gerer des projets dans chaque domaine
- visualiser la planification temporelle des projets
- controler les acces selon des droits par niveau hierarchique
- administrer l'arborescence, les utilisateurs et leurs droits
- partager et exporter des vues en lecture

L'application doit remplacer les anciennes notions de "entreprise", "departement" et "service" par une notion unique de "niveau hierarchique".

## 3. Perimetre fonctionnel

### 3.1 Gestion de la hierarchie

L'application doit permettre :

- la creation d'un niveau racine
- la creation d'un sous-niveau
- la modification d'un niveau
- la suppression d'un niveau (avec option recursive)
- l'activation/desactivation d'un niveau
- le deplacement d'un niveau par glisser-deposer
- la prevention des cycles (impossible de deplacer un niveau dans son propre sous-arbre)

Comportements attendus :

- un niveau inactif n'apparait pas dans la navigation standard (hors administration)
- les ordres d'affichage sont maintenus apres deplacement/reorganisation

### 3.2 Gestion des domaines

L'application doit permettre :

- la creation d'un domaine sur un niveau hierarchique
- la modification du nom d'un domaine
- la suppression d'un domaine (avec ses projets associes)
- la reorganisation des domaines par glisser-deposer
- la modification du niveau hierarchique d'un domaine depuis la fenetre d'edition

Regle specifique de la combo de niveau hierarchique en edition domaine :

- la liste doit afficher tous les niveaux hierarchiques
- seuls les niveaux pour lesquels l'utilisateur a le droit modificateur sont selectionnables
- les autres niveaux doivent etre affiches en desactive (disabled)

### 3.3 Gestion des projets

L'application doit permettre :

- la creation d'un projet dans un domaine
- la modification d'un projet
- la suppression d'un projet
- la reorganisation des projets dans un domaine
- le deplacement d'un projet vers un autre domaine

Chaque projet contient au minimum :

- titre
- date de debut
- date de fin
- gradients de statut (couleurs par date)
- jalons (date, couleur, libelle, reference optionnelle)

### 3.4 Visualisation plan de charge

L'application doit afficher :

- une navigation par niveaux hierarchiques
- la liste des domaines du niveau courant
- la liste des projets par domaine
- une frise temporelle par projet avec :
	- gradients de couleur
	- jalons
	- affichage des semaines

La periode affichee est filtrable par date de debut et date de fin.

### 3.5 Partage et export

L'application doit permettre :

- la generation d'un lien de partage en lecture seule
- l'export PDF de la vue courante

Ces fonctions sont disponibles uniquement si l'utilisateur a le droit de lecture sur le niveau hierarchique courant.

### 3.6 Administration des utilisateurs et droits

L'interface d'administration doit permettre :

- la recherche et l'ajout d'utilisateurs LDAP
- la suppression d'un utilisateur
- l'attribution d'un droit global Admin (checkbox)
- l'attribution des droits metier par niveau hierarchique (radios)

Droits metier autorises :

- aucun
- lecteur
- modificateur

Le role global Admin est reserve a l'acces a la partie administration et ne constitue pas un droit metier sur les domaines/projets.

## 4. Regles de droits et securite

### 4.1 Principes generaux

- L'UI doit masquer/desactiver les actions non autorisees.
- L'API doit toujours verifier les droits serveur, independamment de l'UI.
- Les droits s'appliquent par scope de type hierarchie:{id}.

### 4.2 Matrice des droits metier

- aucun : aucun acces metier
- lecteur : consultation domaines/projets, partage, export
- modificateur : lecteur + creation/modification/suppression/reorganisation/deplacement

### 4.3 Regles specifiques

- Un utilisateur sans droit lecteur sur un niveau ne peut pas voir ses domaines/projets.
- Un utilisateur lecteur ne voit pas les boutons de creation/modification/suppression.
- Un utilisateur modificateur peut manipuler les domaines/projets du niveau autorise.
- Pour deplacer un domaine vers un autre niveau, il faut etre modificateur sur le niveau source et sur le niveau cible.
- Les routes/API d'administration sont accessibles uniquement au role global Admin.

## 5. API et controle serveur

L'API (api.php) doit exposer les actions necessaires au fonctionnement UI et appliquer les controles de droits associes.

Exemples d'actions couvertes :

- create_domaine
- update_domaine
- delete_domaine
- reorder_domaines
- create_projet
- get_projet
- update_projet
- delete_projet
- reorder_projets
- move_projet
- create_hierarchie_level
- update_hierarchie_level
- delete_hierarchie_level
- move_hierarchie_level
- create_share_link
- set_user_scope_role
- set_user_global_admin

Chaque action doit renvoyer une reponse JSON standard :

- success: true|false
- message d'erreur explicite en cas d'echec

## 6. Contraintes techniques

### 6.1 Stack

- Backend : PHP (procedural + classes metier)
- Base de donnees : MySQL via PDO
- Frontend : HTML, CSS, JavaScript
- Librairies : Bootstrap, jQuery, jQuery UI, Font Awesome

### 6.2 Exigences de qualite

- Validation stricte des entrees cote serveur
- Journalisation des operations sensibles (CRUD, droits)
- Integrite des transactions sur les operations deplacement/reorganisation
- Prevention des regressions de droits (UI et API coherentes)

## 7. Exigences UX/UI

- Interface lisible et coherente entre ecrans
- Drag & drop fiable pour niveaux, domaines et projets
- Messages de confirmation/erreur explicites
- Surbrillance legere des niveaux lors du survol des actions (administration)
- Radios de droits sur une seule ligne dans la matrice des droits

## 8. Donnees et tracabilite

L'application doit conserver :

- les donnees de hierarchie
- les domaines et projets
- les gradients et jalons
- les roles utilisateurs
- les journaux de connexions et modifications

Des fonctionnalites de purge de journaux peuvent etre proposees en administration.

## 9. Tests et recette

### 9.1 Tests fonctionnels minimum

- creation/modification/suppression niveau hierarchique
- activation/desactivation niveau
- deplacement niveau + prevention cycle
- creation/modification/suppression/reorganisation domaine
- changement de niveau d'un domaine
- creation/modification/suppression/reorganisation/deplacement projet
- controle des droits lecteur/modificateur
- controle des droits admin sur ecrans admin/API
- generation lien de partage
- export PDF

### 9.2 Tests de non-regression securite

- verification que les actions interdites en UI sont aussi refusees par API
- verification des droits source+cible sur deplacement domaine/projet
- verification qu'un admin global sans droit metier ne modifie pas les donnees metier

## 10. Criteres d'acceptation

Le produit est considere conforme si :

- les fonctionnalites du perimetre sont disponibles et stables
- les regles de droits sont respectees sur tous les ecrans et toutes les API
- les deplacements/reorganisations preservent la coherence des donnees
- les partages/exports respectent les droits de lecture
- les erreurs sont explicites et ne laissent pas de donnees incoherentes

## 11. Evolutions possibles

- recherche et filtres avances sur projets/domaines
- indicateurs de charge/capacite
- audit detaille des changements par entite
- notifications des jalons critiques
- API externe documentee pour integrations