# Cahier des charges — Application PDC (Plan de Charge)

**Version** : 1.0  
**Langue** : Français  
**Statut** : Document de référence  

---

## 1. Présentation générale

### 1.1 Contexte et objectifs

L'application s'appelle **Plan de Charge**, nom de code **PDC**. Elle permet de visualiser et gérer des charges de travail par projet, à destination de plusieurs entreprises. Elle présente dynamiquement l'avancement de plans de charge sous forme de frises temporelles enrichies de jalons colorés.

Elle s'adresse à des utilisateurs internes d'entreprises structurées en départements et services. Elle doit être utilisable sans formation préalable.

### 1.2 Contraintes techniques

| Composant | Technologie / Contrainte |
|---|---|
| Back-end | PHP 5.6 (contrainte serveur existant) |
| Front-End | html, javascript, BootStrap, JQuery, FontAwesome
| Base de données | MySql |
| Annuaire | LDAP (interrogation directe, temps réel) |
| Langue | Français uniquement |
| Interface | Responsive (desktop, tablette, mobile) |
| Authentification | Login LDAP |

'LDAP_HOST', '192.168.1.2';
'LDAP_PORT', 389;
'LDAP_BASE_DN', 'dc=a,dc=c,dc=d,dc=fr';
'LDAP_USER_DN', 'cn=admin,' . LDAP_BASE_DN;
'LDAP_USER_DN_PASS', 'admin';

La programmation doit être à l'ancienne : 
- Pas de views
- Pas de controllers
- Pas de models
- Tu peux faire des classes d'objets
- Utilise un compte de service pour les connexions LDAP
- Valide les saisies utilisateurs
- Sanityse le SQL

---

## 2. Structure des données

### 2.1 Hiérarchie organisationnelle

```
Entreprise
└── Département
    └── Service
        └── Domaine
            └── Projet
```

- Une **entreprise** contient plusieurs départements.
- Un **département** regroupe plusieurs services.
- Un **service** contient plusieurs domaines.
- Un **domaine** regroupe plusieurs projets.
- Un **projet** est l'unité de travail avec frise temporelle et jalons.

### 2.2 Structure d'un projet

```yaml
Projet:
  titre: string
  date_debut: date
  date_fin: date
  ordre_affichage: integer  # persisté, modifiable par drag & drop
  gradients:
    - date: date
      couleur: enum[vert, jaune, orange, rouge]
  jalons:
    - date: date
      couleur: enum[vert, jaune, orange, rouge]
      libelle: string (max 15 car. affichés, complet en tooltip)
      decale: boolean
      jalon_reference_id: integer  # id du jalon précédent (si decale=true)
```

### 2.3 Règles de tri et réorganisation

- Les domaines et les projets sont réorganisables librement par glisser-déposer.
- Un projet peut être déplacé vers un autre domaine.
- L'ordre est persisté en base de données.

---

## 3. Authentification et gestion des droits

### 3.1 Authentification LDAP

- Authentification exclusivement via LDAP.
- Toutes les connexions sont journalisées : date, heure, utilisateur, adresse IP.
- Si le LDAP est indisponible : affichage d'une page d'erreur explicite. Pas de mode dégradé.

### 3.2 Rôles utilisateurs

| Rôle | Périmètre | Droits |
|---|---|---|
| **Administrateur** | Tout | Accès total à toutes les entreprises et tous les niveaux. Accès complet à la vue d'administration LDAP. Peut tout créer, modifier, supprimer. |
| **Responsable** | Sa branche LDAP et en dessous | Accès à la vue d'administration limitée à sa branche. Peut attribuer les rôles (responsable, modificateur, lecteur) aux utilisateurs de sa branche. |
| **Modificateur** | Son périmètre LDAP et en dessous | Peut créer, modifier et supprimer des domaines et des projets (titres, gradients, jalons). Ne peut pas gérer les droits. |
| **Lecteur** | Son périmètre LDAP et en dessous | Consultation uniquement. Aucune modification possible. |

### 3.3 Règles de portée des droits

- Chaque utilisateur voit uniquement les données de son niveau et des niveaux inférieurs.
- Les droits sont attribuables par branche de l'arborescence LDAP.
- Un même utilisateur peut avoir des rôles différents sur des branches différentes.
- Un responsable peut attribuer les rôles jusqu'au niveau responsable inclus, dans sa branche uniquement.

---

## 4. Vue Plan de charge

### 4.1 Barre de navigation (breadcrumb)

La navigation est de type breadcrumb. Les niveaux sont : Entreprise > Département > Service > Domaine.

| Clic sur | Effet |
|---|---|
| Entreprise | Affiche le regroupement par département |
| Département | Affiche le regroupement par service |
| Service | Affiche les domaines et projets du service |
| Domaine | Affiche uniquement les projets du domaine |

### 4.2 Sélecteur de période

- Datepicker avec saisie libre d'une **date de début** et d'une **date de fin**.
- Période par défaut : **quadrimestre courant** (blocs de 4 mois calendaires depuis le 1er janvier : jan-avr, mai-août, sep-déc).

### 4.3 Affichage des domaines

- Chaque domaine affiche son titre + une icône **crayon** pour éditer le titre.
- Les domaines sont réorganisables par glisser-déposer (le domaine emmène tous ses projets).
- Un bouton **« + »** en bas à droite permet de créer un nouveau domaine (modificateur et au-dessus).

### 4.4 Affichage des projets

- Chaque projet affiche sa frise temporelle et ses jalons.
- Une icône **crayon** ouvre la modale d'édition (modificateur et au-dessus).
- Les projets sont réorganisables par glisser-déposer, y compris vers un autre domaine auquel l'utilisateur a accès, les modifications sont sauvegardées automatiquement.

### 4.5 Frise temporelle

- La frise est représentée par une **flèche horizontale**.
- Elle commence à `max(date_debut_projet, date_debut_periode)`.
- Elle se termine à `min(date_fin_projet, date_fin_periode)`.
- Si le projet continue au-delà de la période, la flèche s'arrête au bord droit.
- Couleur par défaut (aucun gradient défini) : **vert**.
- Les transitions entre points de changement de gradient sont **progressives** sur toute la distance entre deux points.
- Couleurs disponibles : vert, jaune, orange, rouge.

### 4.6 Jalons

- Représentés par un **triangle** de couleur (vert, jaune, orange, rouge) positionné sur la frise.
- Le **libellé** est affiché sous le triangle, **tronqué à 15 caractères**.
- Le libellé complet est accessible au survol via un **tooltip**.
- Si `decale = true` : un **pointillé** relie l'ancienne date à la nouvelle date (de l'ancienne vers la nouvelle).
- Si plusieurs décalages successifs existent pour un même libellé : chaque décalage est relié au précédent par un pointillé (chaîné).
- Si aucun jalon précédent n'existe : le pointillé part du **bord gauche de la période affichée**.

---

## 5. Vue Administration

### 5.1 Accès

- Accessible aux rôles **Administrateur** et **Responsable**.
- Le responsable voit uniquement sa branche de l'arborescence LDAP.

### 5.2 Gestion des services

- Affiche un **treeview** de la structure LDAP (entreprises, départements, services, utilisateurs).
- Une case à cocher par service permet de l'**activer ou désactiver** dans l'application.

### 5.3 Gestion des droits utilisateurs

- Pour chaque utilisateur dans le treeview : 3 cases à cocher (Responsable, Modificateur, Lecteur).
- L'administrateur peut attribuer tous les rôles sur toute la structure.
- Le responsable peut attribuer les rôles dans sa branche uniquement.

### 5.4 Paramètres généraux (pour les exports PDF)

- **Image** (logo) affichée dans les en-têtes PDF.
- **Titre** affiché dans les en-têtes PDF.

### 5.5 Historique et traçabilité

- **Journal des modifications** : toutes les actions (auteur, date, heure, nature de la modification).
  - Accessible à l'administrateur (tout) et au responsable (sa branche uniquement).
  - Consultatif uniquement. Pas de rollback.
- **Journal des connexions** : date, heure, utilisateur, adresse IP.
  - Toutes les connexions sont journalisées, y compris via lien de partage.

---

## 6. Modale d'édition d'un projet

Accessible via l'icône crayon d'un projet. Rôle minimum requis : **Modificateur**.

### 6.1 Onglet Informations générales

- Titre du projet
- Date de début
- Date de fin

### 6.2 Onglet Gradients

Tableau avec une ligne par point de changement :

| Colonne | Type | Description |
|---|---|---|
| date | date | Date d'application du changement de couleur trié ASC, un seul gradient par date |
| couleur | enum | vert / jaune / orange / rouge |

### 6.3 Onglet Jalons

Tableau avec une ligne par jalon :

| Colonne | Type | Description |
|---|---|---|
| date | date | Date du jalon |
| couleur | enum | vert / jaune / orange / rouge |
| libelle | string | Texte libre (affiché tronqué à 15 car. dans la vue) |
| decale | boolean | Case à cocher |
| jalon_reference | string\|null | Visible si `decale=true`. Liste déroulante des libellés des jalons existants du même projet. |

---

## 7. Export PDF

### 7.1 Déclenchement

- Depuis la vue plan de charge, bouton dédié.
- La période exportée est celle **affichée à l'écran** au moment de l'export.

### 7.2 Structure du PDF

- Le PDF correspond au **niveau du breadcrumb courant**.
- **Saut de page** entre chaque entreprise.
- **Saut de page** entre chaque département.
- **Saut de page** entre chaque service.
- Chaque section débute par un **en-tête** : logo + titre (configurés en administration) + date de génération + nom de la structure.

### 7.3 Rendu visuel

- Reproduction fidèle de l'affichage écran : frises colorées, triangles de jalons, pointillés de décalage.
- Les libellés de jalons sont affichés en **texte complet** (centré, multilignes si nécessaire). Pas de troncature dans le PDF.

---

## 8. Partage en lecture seule

- Un bouton génère un **lien URL** correspondant à la vue courante (niveau du breadcrumb + période affichée).
- Le lien donne accès à l'**état courant** des données (non figé).
- L'accès via le lien requiert une **authentification LDAP**.
- L'accès via le lien est en **lecture seule**, quel que soit le rôle de l'utilisateur connecté.
- Toutes les connexions via un lien de partage sont journalisées.
- Les liens n'ont **pas de durée d'expiration**.

---

## 9. Exigences non fonctionnelles

### 9.1 Ergonomie

- Interface professionnelle, épurée et intuitive.
- Utilisable sans formation préalable.
- Responsive : desktop, tablette, mobile.
- Tooltips sur les éléments tronqués.

### 9.2 Volumétrie cible

| Niveau | Volume estimé |
|---|---|
| Entreprises | ~30 |
| Départements par entreprise | ~4 |
| Services par département | ~5 |
| Domaines par service | ~5 |
| Projets par domaine | ~5 |

### 9.3 Sécurité

- Authentification obligatoire pour tout accès, y compris via lien de partage.
- Journalisation de toutes les connexions et de toutes les modifications.
- Cloisonnement strict des données selon le périmètre de l'utilisateur.

### 9.4 Disponibilité

- LDAP indisponible → page d'erreur explicite, pas de mode dégradé.
- Les données métier (MySQL) sont indépendantes du LDAP.

---

## 10. Récapitulatif des règles métier

| ID | Règle | Détail |
|---|---|---|
| R01 | Couleur par défaut de la frise | Vert si aucun gradient défini |
| R02 | Période par défaut | Quadrimestre courant (4 mois calendaires depuis le 1er janvier) |
| R03 | Frise hors période | Tronquée aux bords, symbolisée par une flèche |
| R04 | Libellé de jalon — vue | Tronqué à 15 caractères, complet en tooltip |
| R05 | Libellé de jalon — PDF | Complet, centré, multilignes si nécessaire |
| R06 | Transition de gradient | Progressive sur toute la distance entre deux points de changement |
| R07 | Pointillé de décalage | De l'ancienne date vers la nouvelle, chaîné si plusieurs décalages |
| R08 | Absence de jalon précédent | Le pointillé part du bord gauche de la période affichée |
| R09 | Sélection du jalon de référence | Via liste déroulante des libellés existants du même projet |
| R10 | Glisser-déposer | Persisté en base de données |
| R11 | Changement de domaine d'un projet | Autorisé par glisser-déposer |
| R12 | Lien de partage | État courant, authentification requise, lecture seule, sans expiration |
| R13 | Export PDF | Période affichée, sauts de page par entreprise / département / service |
| R14 | Historique | Consultatif uniquement, pas de rollback |
| R15 | LDAP indisponible | Page d'erreur explicite, pas de mode dégradé |
| R16 | Droits par branche | Un utilisateur peut avoir des rôles différents sur des branches différentes |
| R17 | Responsable — attribution des droits | Limité à sa branche, jusqu'au rôle responsable inclus |


