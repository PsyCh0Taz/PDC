PROJET : Matrix

CONTEXTE : 
- Je suis un développeur / administrateur IT
- Je dois suivre mes serveurs, PC clients, applications au travers des services qui sont hébergés et des flux qui les relient.
- Je veux savoir :
    * Les équipements (0 à N équipements)
    * Les IP (0 à n IP)
    * Les services ( 0 à n services )
    * les flux : ( 0 à n flux ) :
        * Le protocole de chaque flux (exactement 1 protocole, par exemple TCP ou UDP)
        * Les ports et à quoi ils correspondent ( 0 à n ports ) : le n° et le type de flux avec un correction éventuelle ( le flux 1521 est normalement le port d'Oracle mais pourrait être utilisé par un autre service )
        * Le sens des flux
    * Une description pour chacun de ces éléments
- Je veux une application dans une page HTML autonome
- Je ne veux pas de serveur web
- Je veux une persistance en fichiers

SPECIFICATIONS VALIDEES :

Équipements :
- Un équipement possède un nom, un type, une zone, un statut, un fabricant, un modèle et une description.
- Un équipement possède zéro à plusieurs interfaces réseau.
- Un équipement possède zéro à plusieurs services.
- Le statut d'un équipement est limité aux valeurs suivantes : ONLINE, OFFLINE, PENDING, CRASHED.
- Le nom, le type, la zone et le statut sont obligatoires.
- Le fabricant, le modèle et la description sont facultatifs.
- Le statut par défaut est PENDING.
- Le statut est renseigné manuellement ; l'application n'effectue aucun test réseau automatique.
- Chaque changement de statut est conservé dans un historique daté avec l'ancienne valeur, la nouvelle valeur et le pseudo déclaré de l'utilisateur.

Réseau :
- Une adresse IP n'appartient qu'à un seul équipement.
- Une même valeur d'adresse IP peut être réutilisée dans plusieurs réseaux isolés.
- Les adresses IPv4 et IPv6 sont prises en charge.
- Les adresses IP sont rattachées aux interfaces réseau des équipements.
- Les noms DNS, les interfaces réseau et les VLAN sont gérés.

Services :
- Un service est toujours hébergé par un équipement précis.
- Un service appartient à exactement un équipement.
- Un service possède un nom, un type, une version, un statut et une description.
- Le statut d'un service est limité aux valeurs suivantes : ONLINE, OFFLINE, PENDING, CRASHED.
- Le nom, le type et le statut sont obligatoires.
- La version et la description sont facultatives.
- Le statut par défaut est PENDING.
- Le statut est renseigné manuellement ; l'application n'effectue aucun test automatique du service.
- Chaque changement de statut est conservé dans un historique daté avec l'ancienne valeur, la nouvelle valeur et le pseudo déclaré de l'utilisateur.
- Un service peut déclarer zéro à plusieurs points d'écoute parmi les interfaces, adresses IPv4, adresses IPv6 et noms DNS de son équipement.
- Un point d'écoute ne peut référencer qu'un élément réseau appartenant à l'équipement qui héberge le service.

Historique des statuts :
- À la création d'un équipement ou d'un service, une première entrée immuable « Aucun vers statut initial » est enregistrée.
- Toutes les entrées de l'historique des statuts sont immuables.
- Une duplication métier ne copie jamais l'historique de l'élément original.
- Après un import ou une fusion, le dernier statut de l'historique doit correspondre au statut courant de l'élément.
- Toute résolution de conflit qui modifie un statut génère une nouvelle entrée dans l'historique.
- Lors d'une fusion, les événements courants et importés d'un même équipement ou service sont réunis sans modifier leurs contenus.
- Cette union s'applique aussi lorsqu'une collision métier entre UUID différents remplace l'équipement ou le service courant par l'élément importé.
- Seuls les événements strictement identiques par leur date, leur ancienne valeur, leur nouvelle valeur et leur pseudo sont dédupliqués, indépendamment de leur UUID technique.
- Chaque événement conserve dans `originDocumentUUID` l'UUID du document dans lequel il a été créé.
- L'ordre de fusion des événements utilise leur date, puis `originDocumentUUID`, puis leur UUID d'événement afin de rester déterministe.
- L'événement de résolution reste toujours le dernier événement logique.
- Une date importée située dans le futur est signalée comme une anomalie d'horloge sans modifier l'événement immuable.
- Après cette union, si le dernier événement ne correspond pas au statut finalement retenu, une nouvelle entrée de résolution est ajoutée avec le pseudo de l'utilisateur courant.
- Chaque entrée d'historique enregistre uniquement une copie du pseudo de l'utilisateur courant au moment de l'action.

Flux :
- Un flux enregistré possède une ou deux extrémités de service renseignées.
- À l'état DRAFT avec un seul service, le flux est rattaché à cette unique extrémité connue sans lui attribuer artificiellement une position locale ou distante.
- Cette extrémité conserve son service, son point d'écoute et ses ports éventuels ; l'autre extrémité est représentée par « À compléter ».
- Si le sens est renseigné dans ce cas, il est exprimé du service connu vers l'extrémité à compléter, de l'extrémité à compléter vers le service connu, ou dans les deux sens.
- Les positions d'affichage « service local » et « service distant » ne sont attribuées que lorsque les deux services sont renseignés.
- Les deux services reliés peuvent appartenir au même équipement ou à deux équipements différents.
- Un service peut être relié à lui-même par un flux.
- Dans un auto-flux, les deux extrémités ne peuvent pas être entièrement identiques.
- Un auto-flux doit différer entre ses deux côtés par au moins un point d'écoute ou une expression de ports.
- Un auto-flux ayant le même service, le même point d'écoute et les mêmes ports sur ses deux côtés est considéré comme une aberration technique et est refusé.
- Un flux ne peut pas avoir pour extrémité une adresse ou un réseau externe non enregistré.
- Les équipements externes sont enregistrés dans l'application et distingués des autres équipements par leur zone.
- Le sens d'un flux peut aller du service local vers le service distant, du service distant vers le service local, ou être bidirectionnel.
- Un flux utilise exactement un protocole, y compris à l'état DRAFT.
- Pour chaque extrémité, un flux peut ne comporter aucun port renseigné ou comporter une expression mélangeant des ports individuels et des plages, par exemple « 80, 443, 8000-8010 ».
- L'absence de port signifie « non renseigné » et non « tous les ports ».
- Les ports sont définis séparément pour chacune des deux extrémités du flux.
- Les expressions de ports du service local et du service distant sont indépendantes.
- Il n'existe aucune correspondance positionnelle ou individuelle entre les ports des deux côtés.
- Chaque extrémité du flux peut sélectionner facultativement un point d'écoute déclaré par son service.
- L'absence de point d'écoute sélectionné signifie « non renseigné ».
- Les ports peuvent être différents entre les deux équipements qui hébergent les services, afin de représenter notamment une traduction de ports.
- Un flux possède une description.
- Un flux ne possède pas de nom.
- Un flux ne possède pas de statut.
- Le terme « statut » est réservé aux équipements et aux services.
- Pour un flux, le terme utilisé est exclusivement « état technique » avec les valeurs DRAFT et OK.
- Au moins un service et exactement un protocole sont obligatoires pour enregistrer un flux.
- Les deux services et le sens sont obligatoires pour obtenir l'état OK.
- Les ports et la description sont facultatifs.
- L'état technique d'un flux est calculé automatiquement : DRAFT ou OK.
- Un flux est OK lorsque les deux services, le sens et le protocole sont présents et valides.
- Un flux ayant un seul service ou aucun sens est automatiquement DRAFT.
- Un flux possédant une dépendance supprimée ou une anomalie d'intégrité non acquittée est également DRAFT.
- Si la modification d'un flux OK supprime le second service ou le sens, il redevient automatiquement DRAFT.
- La suppression du dernier service ou du protocole est refusée, car un flux DRAFT enregistré exige toujours au moins un service et exactement un protocole valides.
- Dès qu'un flux DRAFT possède de nouveau tous ses champs obligatoires valides et ne présente plus d'anomalie d'intégrité non acquittée, il devient automatiquement OK.
- Après la suppression d'un point d'écoute utilisé, le flux concerné reste DRAFT jusqu'à ce que l'utilisateur sélectionne un nouveau point d'écoute ou confirme explicitement la valeur « non renseigné ».
- Un numéro de port est un entier compris entre 1 et 65535.
- Le port 0 est réservé et n'est pas accepté.
- Dans une plage de ports, la borne de début est inférieure ou égale à la borne de fin.
- Les doublons et les chevauchements sont automatiquement supprimés ou normalisés dans une expression de ports.
- Les entrées de ports ou plages ayant le même usage réel peuvent être fusionnées lors de cette normalisation.
- Un chevauchement portant des usages réels différents est refusé et doit être corrigé par l'utilisateur.
- L'ordre de saisie des ports est ignoré.
- Une plage est équivalente à la liste exhaustive des mêmes ports ; par exemple, « 80-82 » est équivalent à « 80, 81, 82 ».
- Les expressions de ports sont normalisées en intervalles triés et fusionnés avant comparaison.
- Plusieurs flux peuvent référencer le même port d'un même service avec un protocole et un point d'écoute identiques.
- Ces références représentent la réutilisation d'une seule ouverture logique du service, et non plusieurs ouvertures du même port.
- Cette réutilisation est autorisée indépendamment de la position locale ou distante du service dans l'affichage du flux.
- L'interdiction des doublons porte uniquement sur les flux dont toute la clé technique est identique.
- Plusieurs flux peuvent relier les mêmes services s'ils diffèrent par le sens, le protocole, les points d'écoute ou les ports.
- La clé technique d'un flux contient les services, leurs points d'écoute, leurs ports normalisés, le protocole et le sens.
- La description et les usages réels sont exclus de la clé technique.
- Deux flux ayant la même clé technique sont interdits.
- L'interdiction des doublons s'applique aux états DRAFT et OK.
- Deux flux DRAFT sont considérés comme des doublons lorsque toutes leurs données techniques actuellement renseignées sont identiques après normalisation, y compris les champs absents.
- Les termes « service local » et « service distant » désignent uniquement des positions d'affichage et n'ont pas de signification métier permanente.
- Permuter les deux extrémités complètes d'un flux — service, point d'écoute, ports et usages réels — tout en inversant son sens ne crée pas un flux différent.
- Ces deux représentations sont considérées identiques lors de la détection des doublons.

Protocoles :
- Les protocoles sont sélectionnés dans une liste initialisée avec les protocoles connus.
- Cette liste peut être enrichie par l'utilisateur.
- La liste initiale est limitée aux protocoles les plus courants.
- Les protocoles initiaux sont : TCP et UDP.
- Les protocoles sans notion de port, notamment ICMP, ICMPv6, GRE, ESP et AH, ne sont pas gérés.
- Tous les protocoles présents dans le référentiel utilisent des ports.
- La création d'un protocole personnalisé sans notion de port est refusée.

Référentiel des ports :
- Pour un port connu, l'application propose automatiquement sa correspondance standard.
- Chaque couple « protocole + numéro de port » possède au maximum une correspondance standard principale.
- Une correspondance standard peut posséder zéro à plusieurs alias.
- L'utilisateur peut renseigner l'usage réel séparément pour chaque port ou plage de ports, sur chacune des deux extrémités du flux.
- La correspondance standard et l'usage réel sont conservés séparément.
- La correspondance standard est relue dynamiquement depuis le référentiel global et n'est pas copiée dans le flux.
- L'usage réel est propre au flux et n'altère jamais le référentiel global.
- Le référentiel initial est limité aux ports et services les plus courants.
- Le référentiel des ports est extensible.
- Les correspondances initiales sont : FTP données (20/TCP), FTP contrôle (21/TCP), SSH (22/TCP), Telnet (23/TCP), SMTP (25/TCP), DNS (53/TCP et 53/UDP), DHCP (67/UDP et 68/UDP), TFTP (69/UDP), HTTP (80/TCP), POP3 (110/TCP), NTP (123/UDP), IMAP (143/TCP), SNMP (161/UDP et 162/UDP), LDAP (389/TCP et 389/UDP), HTTPS (443/TCP), SMB (445/TCP), SMTPS (465/TCP), LDAPS (636/TCP), IMAPS (993/TCP), POP3S (995/TCP), Microsoft SQL Server (1433/TCP et 1434/UDP), Oracle (1521/TCP), MySQL (3306/TCP), RDP (3389/TCP et 3389/UDP), PostgreSQL (5432/TCP), VNC (5900/TCP), WinRM HTTP (5985/TCP), WinRM HTTPS (5986/TCP) et Redis (6379/TCP).

Vues :
- L'application propose des vues en tableaux pour les équipements, les services et les flux.
- Chaque élément dispose d'une fiche détaillée.
- L'application permet la recherche et le filtrage des données.
- L'application propose une cartographie graphique des équipements, des services et de leurs connexions.
- La cartographie regroupe les équipements par zone.
- Elle affiche les services à l'intérieur de leur équipement.
- Dans les formulaires et tableaux, une icône vers la droite représente « local vers distant », une icône vers la gauche « distant vers local » et une icône double un flux bidirectionnel.
- Dans la cartographie libre, la pointe de chaque flèche est orientée vers le service destinataire, quelle que soit la position géométrique des nœuds ; un flux bidirectionnel porte une pointe à chaque extrémité.
- La cartographie peut être filtrée par zone, équipement, service et protocole.
- Un filtre « statut » porte uniquement sur les statuts ONLINE, OFFLINE, PENDING et CRASHED des équipements et des services.
- Un filtre distinct « état du flux » porte uniquement sur les états DRAFT et OK.
- Elle permet le zoom, le déplacement et la réorganisation manuelle des éléments.
- Un clic sur un élément ouvre sa fiche détaillée.
- Lorsque plusieurs flux relient les mêmes services, ils sont regroupés visuellement en une seule connexion portant le nombre de flux.
- Les directions présentes sont résumées sur la connexion regroupée.
- Un clic sur une connexion regroupée ouvre le détail des flux avec leur protocole, leurs ports, leur usage réel et leur état.
- Les auto-flux sont représentés par une boucle ; plusieurs auto-flux sont regroupés avec un compteur.
- Les compteurs de flux tiennent compte des filtres actifs.
- La recherche globale couvre les noms, descriptions, adresses IP, adresses MAC, noms DNS, VLAN, versions, protocoles, ports, usages réels, fabricants, modèles, utilisateurs, statuts et contenus d'historique.
- Des filtres structurés dédiés complètent la recherche globale pour ces champs.
- La cartographie peut être exportée au format SVG.
- L'export SVG et la vue d'impression portent toujours sur l'intégralité de la cartographie.
- Une vue optimisée pour l'impression permet notamment l'enregistrement en PDF depuis le navigateur.
- La cartographie permet une navigation au clavier entre ses nœuds et ses connexions.
- Chaque nœud et chaque connexion possède un libellé exploitable par les lecteurs d'écran.
- Une vue tabulaire accessible fournit une représentation équivalente de toutes les informations présentes dans la cartographie.
- Les positions définies manuellement dans la cartographie sont sauvegardées avec les données.
- La cartographie conserve une disposition canonique pour ordinateur.
- Des dispositions adaptées peuvent être enregistrées séparément pour tablette et téléphone.
- Un déplacement réalisé sur un petit écran ne modifie pas la disposition canonique sur ordinateur.
- Un nouvel équipement ou service est placé automatiquement sans chevauchement dans sa zone, puis peut être déplacé manuellement.

Opérations :
- La suppression d'un équipement entraîne, après confirmation, la suppression de ses interfaces, de ses services et de tous les flux liés à ces services.
- La suppression d'un service entraîne, après confirmation, la suppression de tous les flux qui lui sont liés.
- Les flux liés supprimés en cascade comprennent explicitement les flux OK et DRAFT.
- Les positions et autres données cartographiques devenues orphelines sont également supprimées.
- Avant confirmation, l'application affiche le nombre d'éléments qui seront supprimés dans chaque catégorie, en distinguant notamment les flux OK des flux DRAFT.
- Toute suppression en cascade exige une confirmation explicite de l'utilisateur.
- La suppression d'une zone, d'un type, d'un VLAN, d'un protocole ou d'une autre valeur de référentiel encore utilisée est interdite.
- L'application permet de créer, modifier, supprimer et dupliquer les équipements, les services et les flux.
- L'application permet de créer, modifier, supprimer et dupliquer les interfaces réseau.
- L'application permet de créer, modifier et supprimer les VLAN, zones, types et autres valeurs de référentiels personnalisées.
- La duplication d'une interface conserve sa description et sa configuration VLAN, numérote automatiquement son nom et laisse vides ses adresses IPv4, IPv6, MAC et ses noms DNS.
- Toute duplication effectivement enregistrée d'un équipement, d'une interface, d'un service ou d'un flux crée une nouvelle entité avec un nouvel UUID et de nouvelles dates de création et de modification.
- Elle conserve uniquement les valeurs métier dont la copie est autorisée par les règles propres au type d'élément.
- Lors d'une duplication métier, les historiques de l'élément original ne sont jamais copiés et aucun événement historique antérieur n'est créé pour la copie.
- Lorsqu'une copie d'équipement ou de service est enregistrée, son propre historique commence par une nouvelle entrée « Aucun vers statut initial », datée au moment de la duplication et associée au pseudo de l'utilisateur courant.
- La suppression d'une interface exige une confirmation, supprime ses adresses IP et ses noms DNS et nettoie les points d'écoute qui les référencent.
- Avant la suppression d'une interface, d'une IP ou d'un nom DNS utilisé comme point d'écoute, l'application affiche les services et les flux affectés.
- Après confirmation, les références devenues invalides sont retirées et les éléments affectés sont signalés.
- Tout flux affecté par cette suppression passe à l'état DRAFT avec le motif « point d'écoute supprimé ».
- Le retrait direct d'un point d'écoute depuis la fiche d'un service affiche également les flux affectés avant confirmation.
- Après confirmation, la référence est retirée des flux concernés, qui passent à l'état DRAFT avec un motif explicite.
- Chaque flux reste DRAFT jusqu'à la sélection d'un nouveau point d'écoute ou à la confirmation explicite « non renseigné ».
- Avant tout changement de zone d'un équipement, de mode accès/trunk ou de liste de VLAN, l'application affiche les interfaces, adresses, services et flux qui deviendraient invalides.
- La modification est bloquée tant que les dépendances ne sont pas réaffectées ou explicitement retirées.
- Après validation, tout flux dont le point d'écoute a été invalidé passe à l'état DRAFT avec un motif explicite.
- L'application permet d'importer et d'exporter uniquement le document total contenant notamment les équipements, les services et les flux.
- La duplication d'un équipement copie ses champs, ses interfaces et ses services ; son nom est numéroté automatiquement.
- Lors de la duplication d'un équipement, les interfaces sont copiées mais leurs adresses IPv4, IPv6, leurs adresses MAC et leurs noms DNS sont laissés vides.
- Toutes les entités créées par cette duplication reçoivent de nouveaux UUID et de nouvelles dates de création et de modification.
- Les historiques de statut ne sont pas copiés.
- Les points d'écoute qui référencent directement une interface copiée sont remappés vers cette nouvelle interface.
- Les points d'écoute qui référencent une IP ou un DNS vidé sont retirés de la copie.
- Les flux copiés qui perdent ainsi un point d'écoute passent à l'état DRAFT jusqu'au choix d'un nouveau point ou à la confirmation explicite « non renseigné ».
- La duplication d'un service copie ses champs ; son nom est numéroté automatiquement.
- Lorsque les deux services d'un flux sont copiés lors de la duplication d'un équipement, le flux est recréé intégralement entre les deux services copiés et conserve son état OK si toutes ses données restent valides.
- Un auto-flux est recréé intégralement sur le service copié.
- Un modèle incomplet est créé lorsque la duplication ne dispose que d'une seule extrémité de service à rattacher.
- Ce cas se produit lorsqu'une seule extrémité d'un flux à deux services est copiée ou lorsque le flux d'origine est déjà un DRAFT à un seul service.
- Ces modèles sont enregistrés avec l'état DRAFT et peuvent rester temporairement incomplets.
- Pour chaque flux lié au service original, que celui-ci soit le service local, le service distant ou une extrémité d'un flux bidirectionnel, le flux DRAFT conserve le service dupliqué et les ports de son côté.
- Le sens du flux, le service de l'autre côté et les ports de l'autre côté sont laissés vides dans le flux DRAFT.
- Le protocole et la description d'origine sont conservés dans le flux DRAFT.
- Si plusieurs modèles deviennent techniquement identiques à l'issue d'une duplication, avec ou sans port renseigné, un seul flux DRAFT est créé pour leur clé technique.
- Ce flux DRAFT conserve pour chaque flux d'origine un instantané de son UUID, de sa description, de ses usages réels et du contexte de son autre extrémité.
- Les champs principaux du DRAFT reprennent provisoirement ceux du premier flux d'origine selon l'ordre déterministe « date de création puis UUID ».
- Une anomalie de regroupement maintient ce flux à l'état DRAFT tant que l'utilisateur n'a pas explicitement choisi ou fusionné les variantes de description et d'usages réels.
- Un DRAFT regroupé constitue un groupe temporaire de contextes d'origine.
- Son assistant de complétion permet de créer un flux distinct pour chaque contexte d'origine.
- Dès que les clés techniques résultantes diffèrent, les flux sont enregistrés séparément à l'état DRAFT ou OK selon leur complétude.
- Les contextes qui ne sont pas encore complétés restent dans le groupe DRAFT.
- Les contraintes exigeant deux services, un sens et un protocole s'appliquent à l'état OK.
- Un flux DRAFT apparaît dans la liste des flux avec l'indicateur « DRAFT ».
- Un flux DRAFT est inclus dans la cartographie avec un indicateur visuel spécifique.
- Dans la cartographie, un flux DRAFT incomplet est représenté par un trait pointillé entre le service renseigné et un nœud « À compléter ».
- Aucune flèche n'est affichée sur ce trait tant que le sens du flux n'est pas renseigné.
- Avec un seul service renseigné, le trait pointillé relie ce service au nœud « À compléter ».
- Avec deux services mais aucun sens, le trait pointillé relie les deux services sans flèche.
- Avec deux services, un sens connu et une autre anomalie, le trait pointillé porte la ou les pointes correspondant au sens.
- Toute liaison DRAFT est rouge et possède une ombre et un effet de halo, ou glow.
- Le style pointillé et l'indicateur textuel DRAFT complètent la couleur afin de préserver l'accessibilité.
- L'effet de halo reste statique lorsque la préférence de réduction des animations est active.
- Les flux DRAFT sont inclus dans les sauvegardes et les exports totaux.
- La duplication d'un flux ouvre une copie temporaire dans le formulaire sans créer immédiatement d'UUID ni enregistrer de nouvelle entité.
- Le nouveau flux n'est créé qu'après modification d'au moins un champ de sa clé technique : une extrémité de service, un point d'écoute, le sens, le protocole ou les ports.
- La modification de la description ou des usages réels seuls ne permet pas de créer le flux dupliqué, car elle ne résout pas le doublon technique.
- L'application propose uniquement des imports et des exports totaux.
- Un export de données contient toujours l'intégralité des données, quels que soient la sélection et les filtres actifs dans l'interface.
- Tout export de données contient l'intégralité du document et tout import exige un document total.

Persistance locale :
- Les données sont persistées dans un fichier JSON.
- Les fichiers de données restent lisibles en clair et ne proposent aucun chiffrement par mot de passe.
- Un export total contient les équipements, interfaces, adresses IP, noms DNS, services, historiques de statut, flux OK et DRAFT, utilisateurs, zones, VLAN, référentiels, points d'écoute et positions cartographiques.
- Il contient également les identifiants, dates et métadonnées de version nécessaires.
- Le thème, les filtres temporaires, l'utilisateur actuellement sélectionné, la copie de récupération, les permissions et la référence technique du fichier local ouvert sont exclus des exports.
- L'application propose l'import manuel d'un fichier JSON et son export par téléchargement.
- L'application propose également, lorsque le navigateur le permet, l'ouverture et la réécriture directes d'un fichier local.
- Microsoft Edge est obligatoirement pris en charge.
- Mozilla Firefox est pris en charge dans la mesure permise par ses API ; l'import et l'export JSON manuels y assurent la solution de repli.
- Les deux dernières versions stables de Microsoft Edge et Mozilla Firefox ainsi que la version Firefox ESR courante sont prises en charge.
- L'écriture directe dans un fichier est une amélioration progressive activée uniquement lorsque l'API nécessaire est détectée.
- Le fichier JSON est intégralement validé avant tout import.
- Un fichier invalide est refusé et les erreurs détectées sont affichées en détail.
- L'import commence par la lecture de l'enveloppe JSON et l'identification de sa version de schéma.
- Une version de schéma inconnue ou plus récente que celle prise en charge est refusée.
- Une ancienne version reconnue est migrée uniquement sur une copie en mémoire après accord de l'utilisateur.
- Les données migrées sont entièrement validées avant l'affichage et la résolution des conflits.
- Lorsqu'un remplacement ou une restauration exige une migration, le document résultant conserve l'UUID importé, utilise la révision importée augmentée de 1 et enregistre comme empreinte parente l'empreinte du contenu importé avant migration.
- Lorsqu'une fusion exige une migration, la migration et la fusion constituent une seule transaction atomique et la révision courante n'est augmentée que de 1.
- Après validation du fichier, l'utilisateur choisit explicitement entre fusionner les données importées et remplacer toutes les données courantes.
- Avant toute fusion ou tout remplacement, l'application demande confirmation et crée automatiquement une sauvegarde téléchargeable des données courantes.
- Si la création ou le téléchargement de cette sauvegarde est annulé ou échoue, l'import n'est pas appliqué.
- Lorsque la sauvegarde de sécurité utilise une écriture directe, sa réussite est vérifiée techniquement avant de poursuivre.
- Lorsque la sauvegarde de sécurité utilise un téléchargement manuel, l'utilisateur doit confirmer explicitement « sauvegarde effectuée » avant de poursuivre.
- Lorsqu'un fichier local a été ouvert avec un accès direct en écriture, les modifications y sont sauvegardées automatiquement.
- Les écritures automatiques sont sérialisées et les modifications rapprochées sont regroupées afin d'éviter les écritures concurrentes.
- L'interface affiche l'état « Enregistrement », « Enregistré » ou « Échec ».
- En cas d'échec, l'état non sauvegardé est conservé et une nouvelle tentative est proposée.
- L'application avertit l'utilisateur lorsqu'elle détecte une modification externe du fichier ou une autre instance susceptible d'entrer en concurrence.
- Chaque révision conserve l'empreinte de sa révision parente.
- L'application mémorise séparément l'UUID, la révision et l'empreinte du dernier état externe lu ou écrit avec succès.
- Avant chaque écriture, elle compare l'état actuel du fichier externe à cette référence persistée.
- L'empreinte de la révision parente reste utilisée pour représenter la lignée du document, mais la détection de concurrence repose sur la référence du dernier état externe connu.
- En cas de divergence, l'autosauvegarde est suspendue et aucun contenu n'est écrasé silencieusement.
- L'utilisateur peut alors recharger la version externe, confirmer explicitement son écrasement par la version courante ou enregistrer la version courante ailleurs.
- Si un import ou une restauration est lancé alors qu'un fichier local est associé, l'application demande où enregistrer le résultat avant toute écriture.
- L'utilisateur peut choisir d'écrire dans le fichier actuellement ouvert, d'enregistrer dans un nouveau fichier ou de détacher le fichier ouvert et de travailler sans sauvegarde directe.
- Aucun fichier n'est réécrit avant ce choix explicite.
- Pour une destination en écriture directe, l'état candidat est préparé en mémoire, puis le fichier choisi doit être écrit et fermé avec succès avant que cet état soit publié dans l'application.
- En cas d'échec de cette écriture, les données affichées et le fichier courant restent inchangés.
- En mode détaché, l'état candidat est publié en mémoire avec l'indicateur « non sauvegardé ».
- Lorsque l'écriture directe n'est pas disponible, un avertissement visuel reste affiché tant que les modifications n'ont pas été exportées.
- Après chaque modification, une copie de récupération est également conservée dans le stockage interne du navigateur.
- Cette copie de récupération complète la sauvegarde en fichier JSON, qui reste la sauvegarde officielle.
- La récupération utilise de préférence IndexedDB lorsque cette API est disponible.
- La récupération est une protection de meilleur effort, propre au navigateur, au profil utilisateur et potentiellement au chemin du fichier HTML.
- Elle peut être effacée par le navigateur et ne remplace jamais la sauvegarde JSON officielle.
- La copie de récupération contient l'UUID du document, sa révision, sa date et une empreinte de son contenu.
- Plusieurs copies de récupération peuvent être conservées et sont indexées par UUID de document.
- Une seule copie de récupération, la plus récente, est conservée pour chaque UUID de document.
- Plusieurs récupérations peuvent donc coexister uniquement pour des documents différents.
- Au démarrage, avant même l'ouverture d'un document, l'application affiche les récupérations disponibles avec le nom du document, leur date, leur révision et leur origine.
- L'utilisateur choisit la récupération à examiner, à ignorer ou à supprimer.
- Lorsqu'un document est déjà ouvert, une récupération n'est proposée pour celui-ci que si elle est différente et plus récente que sa dernière sauvegarde officielle.
- Une copie de récupération n'est jamais restaurée silencieusement.
- La restauration d'une récupération suit les mêmes contrôles de sécurité qu'un import.
- Elle effectue, dans cet ordre, la migration éventuelle du schéma, la validation complète, l'affichage d'un aperçu, la création d'une sauvegarde de sécurité et le choix de la destination.
- Les données restaurées ne sont publiées en mémoire ou écrites dans un fichier qu'après la réussite de ces contrôles et décisions.
- Une commande « Réinitialiser l'application » remet l'application dans son état initial après confirmation et création préalable d'un export de sécurité.
- Elle efface les données chargées, toutes les copies de récupération gérées par l'application dans le profil du navigateur, les préférences locales, l'utilisateur courant mémorisé et la référence au fichier ouvert.
- Avant la réinitialisation, l'application affiche la liste et le nombre des récupérations appartenant à d'autres documents.
- Elle propose un export total individuel de chacune de ces récupérations.
- Toute récupération supprimée sans export exige une confirmation explicite.
- La commande « Réinitialiser l'application » crée un nouveau document avec un nouvel UUID, une révision 0 et une empreinte de révision parente nulle.
- Avant son exécution, toutes les écritures automatiques en attente sont suspendues et le fichier externe courant est détaché.
- Le document vide résultant n'est jamais écrit automatiquement dans le fichier externe précédemment associé.
- La réinitialisation ne supprime pas le fichier JSON externe.
- Si l'export de sécurité est annulé ou échoue, la réinitialisation est annulée.
- Lors d'une fusion, si deux éléments ayant le même identifiant ont des contenus différents, l'application affiche le conflit.
- Pour chaque conflit, l'utilisateur peut conserver la donnée actuelle, utiliser la donnée importée ou dupliquer l'élément importé avec un nouvel identifiant.
- L'utilisateur peut appliquer son choix à tous les conflits du même type d'entité et de même nature.
- Avant l'application groupée de ce choix, l'application affiche le nombre d'éléments concernés.
- La fusion détecte également les collisions métier entre identifiants différents, notamment les IP dans un même périmètre, les VLAN dans une même zone, les clés de référentiels et les flux techniquement identiques.
- Chaque collision métier est affichée avant toute modification avec les choix conserver la donnée actuelle, utiliser la donnée importée, ignorer l'élément importé ou le dupliquer lorsque cette duplication est valide.
- Pour une collision métier entre deux UUID différents, le choix « utiliser la donnée importée » remplace l'élément courant par l'élément importé.
- Toutes les relations courantes et importées qui restent valides sont remappées vers l'UUID importé.
- Les relations incompatibles sont présentées à l'utilisateur et doivent être résolues explicitement avant l'application de la fusion.
- Aucune relation ou donnée incompatible n'est supprimée silencieusement.
- La duplication d'un élément en conflit n'est proposée que si elle respecte toutes les contraintes d'unicité après résolution.
- L'option « dupliquer l'élément importé » est un remappage d'import et non une duplication métier.
- Elle attribue les nouveaux UUID nécessaires et remappe les relations, tout en conservant les dates, les contenus d'historique et leur provenance.
- Elle ne crée aucune nouvelle entrée initiale de statut.
- Lorsqu'un élément importé reçoit un nouvel identifiant, une table de remappage réécrit toutes les relations importées vers l'identifiant retenu.
- Lorsqu'un parent importé est ignoré, ses enfants et relations sont remappés vers un parent courant équivalent si ce remappage respecte toutes les règles de validité.
- En l'absence de parent courant équivalent valide, le sous-graphe importé dépendant est ignoré et cette décision apparaît dans le résumé de fusion.
- Lorsqu'un import total est appliqué en mode fusion, l'absence d'une interface, d'un service, d'un flux ou d'un autre élément dans le document importé ne supprime jamais la donnée courante correspondante.
- Le remappage, la résolution des conflits et la validation sont entièrement préparés en mémoire.
- La fusion est appliquée en une seule opération atomique ; en cas d'échec, les données courantes restent inchangées.
- Tout échec de lecture, migration, validation, résolution, sauvegarde ou application laisse les données et le fichier courant inchangés.
- La comparaison des conflits porte sur le contenu métier normalisé et ignore les UUID techniques, les dates de création et de dernière modification ainsi que les métadonnées d'export.
- Les dates effectives des événements d'historique sont des données métier conservées dans les comparaisons.
- L'ordre des listes, notamment des noms DNS et des ports, n'influence pas cette comparaison.
- Les positions cartographiques sont comparées et résolues séparément des conflits métier.
- Le renommage automatique des noms en doublon utilise le format « Nom (2) », « Nom (3) », puis ainsi de suite.
- Lors d'une collision de nom entre deux éléments ayant des UUID différents, la donnée courante conserve son nom et les suffixes automatiques s'appliquent uniquement aux données importées.
- Lors d'un conflit portant sur le même UUID, le choix « utiliser la donnée importée » remplace également le nom courant par le nom importé.
- Les données importées sont traitées dans l'ordre du fichier et reçoivent le plus petit suffixe numérique disponible.
- Les espaces superflus en début et fin de nom sont supprimés avant comparaison, sans distinction de casse.
- Avant chaque suppression en cascade ou autre opération destructive, l'application présente précisément les éléments qui seront touchés.
- L'application conserve les données nécessaires à une opération inverse ciblée portant uniquement sur les éléments touchés.
- L'utilisateur peut annuler la dernière opération destructive uniquement jusqu'à la prochaine modification des données.
- Cette annulation restaure uniquement les éléments touchés par l'opération destructive et ne supprime aucune modification antérieure.
- L'annulation ne supprime aucun événement d'historique appartenant à un équipement ou service qui subsiste après l'opération inverse.
- Si le statut d'un élément survivant est restauré, l'annulation ajoute un événement compensatoire daté avec le pseudo de l'utilisateur courant et le motif « annulation ».
- Les historiques des entités nouvelles entièrement retirées par l'annulation disparaissent avec ces entités.
- Une réinitialisation complète ne peut pas être annulée dans l'application ; elle est récupérable uniquement à partir de l'export de sécurité créé avant l'opération.

Interface :
- L'interface est en français.
- L'utilisateur peut choisir entre un thème clair et un thème sombre.
- Au premier lancement, le thème suit automatiquement la préférence du système.
- Le choix manuel du thème est mémorisé pour les lancements suivants.
- L'interface est responsive et adaptée aux ordinateurs, tablettes et téléphones.
- Toutes les fonctions sont disponibles sur ordinateur et tablette.
- Sur téléphone, l'application permet la consultation, la recherche, le filtrage, la création, la modification et la duplication des équipements, interfaces, services et flux, le changement des statuts, la gestion des référentiels, l'import total et l'export total.
- Les suppressions simples et les suppressions en cascade sont autorisées sur téléphone avec exactement les mêmes règles d'aperçu, de confirmation, de sauvegarde et d'annulation que sur ordinateur.
- Après un import total sur téléphone, seul le remplacement complet des données est autorisé ; la fusion n'est pas proposée.
- Sur téléphone, seule la réorganisation graphique de la cartographie n'est pas disponible parmi ces opérations.
- La réorganisation avancée de la cartographie et la résolution complexe des conflits d'import sont réservées aux ordinateurs et tablettes.
- L'identité visuelle affiche « C²Factory » en blanc, avec uniquement le caractère « ² » en rouge.
- L'identité « C²Factory » est placée dans un bandeau de marque toujours sombre afin de rester visible dans les thèmes clair et sombre.
- La page HTML fonctionne entièrement hors ligne.
- Elle ne charge aucune bibliothèque, police, icône ou autre ressource depuis Internet.
- Toutes les dépendances et ressources d'interface sont intégrées dans l'unique fichier HTML.
- L'ensemble des fonctions est utilisable au clavier.
- Le focus clavier est toujours visible.
- Les contrôles possèdent des libellés explicites compatibles avec les lecteurs d'écran.
- Les thèmes clair et sombre utilisent des contrastes suffisants.
- Aucune information n'est transmise uniquement par la couleur.
- L'application respecte la préférence système de réduction des animations.
- L'objectif d'accessibilité est la conformité WCAG 2.2 niveau AA.
- Tous les noms, descriptions et autres contenus importés sont traités exclusivement comme du texte.
- Aucun contenu HTML ou script provenant des données importées n'est exécuté.
- Toutes les valeurs affichées sont échappées selon leur contexte.
- L'application reste fluide avec au moins 1 000 équipements, 5 000 interfaces, 5 000 services et 10 000 flux.
- Ces seuils sont utilisés pour les tests de recherche, de filtrage, de cartographie, d'import, d'export et de sauvegarde.
- Sur la machine de référence consignée dans le rapport de recette, le chargement initial de cette volumétrie prend moins de 5 secondes.
- Une recherche ou un filtrage affiche son résultat en moins de 300 millisecondes.
- L'ouverture d'une fiche prend moins de 500 millisecondes.
- Les interactions cartographiques ne provoquent aucun blocage visible de plus de 100 millisecondes.
- Toute opération d'import, d'export ou de sauvegarde dépassant une seconde affiche une progression et ne bloque pas durablement l'interface.

Données initiales :
- Au premier lancement, l'application ne contient aucun équipement, service ni flux.
- Les référentiels intégrés, notamment la liste des protocoles connus et la liste des ports connus, sont préchargés.

Structure et versionnement des données :
- Chaque document possède un nom obligatoire et modifiable.
- Le nom interne contenu dans le fichier JSON est toujours prioritaire sur le nom du fichier.
- Lors de la migration d'un ancien document dépourvu de nom interne, ce nom est initialisé avec le nom du fichier sans son extension.
- Un nouveau jeu de données utilise initialement le nom « Nouveau document ».
- La modification du nom du document ne renomme jamais automatiquement son fichier JSON.
- Chaque jeu de données possède un UUID de document stable et un numéro de révision incrémenté après chaque modification.
- Le numéro de révision est incrémenté une seule fois par transaction métier atomique, même lorsque cette transaction modifie plusieurs entités.
- Une création, une sauvegarde de formulaire, une suppression en cascade, une duplication métier, une fusion ou une annulation ciblée constitue une seule transaction incrémentale.
- Un remplacement total sans migration adopte la lignée importée sans incrément supplémentaire.
- Une réinitialisation et une copie indépendante créent une nouvelle lignée à la révision 0.
- L'autosauvegarde et les modifications des préférences d'interface ne créent aucune révision supplémentaire.
- Un nouveau document et un document créé par réinitialisation commencent à la révision 0 avec une empreinte de révision parente nulle.
- Un remplacement complet adopte l'UUID, la révision et l'empreinte de la révision parente du document importé.
- La première modification effectuée après ce remplacement crée la révision suivante.
- Une fusion conserve l'UUID du document courant et incrémente sa révision.
- La commande « Enregistrer sous » conserve l'UUID du document.
- La commande « Créer une copie indépendante » attribue un nouvel UUID au document et de nouveaux UUID à toutes ses entités.
- Une copie indépendante commence à la révision 0 avec une empreinte de révision parente nulle.
- Elle conserve les dates et les historiques métier des entités copiées.
- Sa première modification crée la révision 1.
- Toutes les relations entre entités ainsi que les références des positions cartographiques sont remappées vers ces nouveaux UUID.
- Les identifiants canoniques fixes et versionnés des valeurs intégrées des référentiels ne sont pas modifiés.
- Une fusion ultérieure considère donc les entités de la copie indépendante comme des entités distinctes de celles du document d'origine.
- Chaque entité possède un UUID technique unique et stable, masqué dans l'usage courant de l'interface.
- Les entités concernées sont : équipement, interface, adresse IP, nom DNS, service, point d'écoute, flux, zone, VLAN, type d'équipement, type de service, protocole, port connu, utilisateur et entrée d'historique.
- Chaque entité conserve sa date de création et sa date de dernière modification.
- Les positions cartographiques référencent l'UUID de leur élément et ne possèdent pas d'UUID propre.
- Le fichier JSON contient un numéro de version de schéma, la date d'export et la version de l'application.
- Le fichier JSON contient également une version du catalogue des référentiels intégrés.
- Le catalogue intégré dans l'application HTML constitue la référence canonique.
- Lorsqu'un fichier contient une version antérieure reconnue du catalogue, ses valeurs intégrées sont migrées vers les valeurs canoniques courantes.
- Une version de catalogue plus récente ou inconnue est refusée.
- Les valeurs personnalisées ne sont jamais modifiées par une migration du catalogue intégré.
- Si une nouvelle valeur canonique entre en collision avec une valeur personnalisée existante, la migration est suspendue et le conflit est affiché.
- L'utilisateur peut remapper les usages vers la nouvelle valeur canonique puis supprimer la valeur personnalisée, ou renommer la valeur personnalisée lorsque son type le permet.
- En l'absence de résolution explicite, la migration et l'import sont annulés sans aucune modification.
- Les protocoles, ports connus, types d'équipements et autres valeurs préchargées possèdent des identifiants canoniques fixes et versionnés.
- Lors d'une fusion, une valeur intégrée est reconnue par son identifiant canonique et n'est jamais renommée ni dupliquée.
- Les dates sont stockées en UTC au format ISO 8601 et affichées selon le format français dans le fuseau local de l'utilisateur.
- Lors de l'import d'un ancien schéma reconnu, l'application détecte sa version et propose sa migration avant import.

Référentiels métier :
- La liste des types d'équipements est préchargée et extensible.
- Les types d'équipements initiaux sont : PC, Serveur, Tablette, Switch, Routeur, Pare-Feux et Chiffreur.
- Les listes des zones et des types de services démarrent vides.
- Les listes des zones et des types de services sont modifiables et extensibles par l'utilisateur.
- Le nom d'un équipement est unique dans toute l'application.
- Le nom d'une interface réseau est unique au sein de son équipement.
- Le nom d'un service est unique au sein de son équipement, mais peut être réutilisé sur un autre équipement.
- Le nom d'une zone est unique dans le référentiel des zones.
- Le nom d'un type d'équipement ou de service est unique dans son référentiel respectif.
- Le nom d'un VLAN est unique dans sa zone et peut être réutilisé dans une autre zone.
- Toutes les vérifications d'unicité des noms ignorent les différences entre majuscules et minuscules.
- Un type d'équipement possède un nom et une description.
- Une zone possède un nom, une description, une couleur utilisée dans la cartographie et une propriété booléenne « externe ».
- Une couleur est attribuée automatiquement à chaque nouvelle zone et peut être modifiée par l'utilisateur.
- La propriété « externe » vaut « non » par défaut.
- Elle permet de filtrer et de distinguer visuellement les équipements appartenant à une zone externe.
- Dans la cartographie, une zone externe est représentée par défaut avec un contour ou un rendu en forme de nuage.
- Ce rendu est complété par un libellé textuel accessible « Externe » ; la distinction ne repose jamais uniquement sur la couleur ou la forme.
- Un type de service possède un nom et une description.
- Un protocole possède un nom et une description.
- Le nom d'un protocole est unique dans son référentiel sans tenir compte de la casse.
- Cette unicité s'applique entre les protocoles intégrés et les protocoles personnalisés.
- Une correspondance de port connu possède un numéro, un protocole, le nom principal du service standard, zéro à plusieurs alias et une description.
- Toutes les valeurs préchargées des référentiels sont entièrement immuables : nom, description, alias et autres propriétés ne peuvent être modifiés ni supprimés.
- Leur personnalisation s'effectue uniquement par l'ajout de nouvelles valeurs utilisateur.
- L'utilisateur peut ajouter de nouvelles valeurs dans chaque référentiel extensible.
- Les valeurs ajoutées par l'utilisateur sont modifiables et peuvent être supprimées lorsqu'elles ne sont pas utilisées.
- Lorsqu'un formulaire exige une zone, un type d'équipement, un type de service ou une autre valeur extensible, un bouton « Ajouter » permet de créer cette valeur sans quitter le formulaire.
- La nouvelle valeur est automatiquement sélectionnée et les données déjà saisies dans le formulaire sont conservées.

Utilisateurs :
- L'application possède un référentiel d'utilisateurs enregistré avec les données.
- Un utilisateur possède un pseudo et un libellé obligatoires.
- Le pseudo est unique dans le référentiel des utilisateurs, sans tenir compte de la casse.
- Le format « DOMAINE\utilisateur » reste recommandé pour le pseudo.
- Au premier usage d'un jeu de données, l'application demande de sélectionner un utilisateur existant ou d'en créer un.
- L'utilisateur courant est mémorisé dans les préférences du navigateur pour ce jeu de données et automatiquement réutilisé lors des ouvertures suivantes.
- Si l'utilisateur mémorisé n'existe plus dans le jeu de données ouvert, l'application demande une nouvelle sélection.
- L'utilisateur courant reste visible dans l'interface et peut être changé à tout moment.
- Si l'utilisateur courant est supprimé ou si son pseudo est modifié, l'application demande immédiatement de confirmer l'utilisateur modifié ou d'en sélectionner un autre.
- Aucune nouvelle action historisée n'est autorisée avant cette confirmation.
- Les entrées d'historique enregistrent uniquement une copie du pseudo de l'utilisateur au moment de l'action.
- Elles ne conservent aucune référence technique vers le référentiel des utilisateurs et n'enregistrent pas le libellé.
- Les historiques existants conservent toujours le pseudo enregistré au moment de chaque action, même après le renommage ou la suppression de cet utilisateur.
- Un utilisateur peut être supprimé du référentiel sans modifier les historiques existants.
- Le référentiel d'utilisateurs sert uniquement à la traçabilité déclarative.
- L'application ne gère actuellement aucun droit, rôle, mot de passe ni mécanisme d'authentification.

Interfaces réseau :
- Une interface réseau appartient à exactement un équipement.
- Un équipement peut ne posséder aucune adresse IP au total.
- Une interface réseau possède un nom, une adresse MAC, un VLAN et une description.
- Une interface réseau possède zéro à plusieurs adresses IPv4.
- Une interface réseau possède zéro à plusieurs adresses IPv6.
- Chaque adresse IPv4 peut renseigner son propre masque de réseau.
- Chaque adresse IPv6 peut renseigner son propre préfixe réseau.
- En mode accès, chaque adresse IPv4 ou IPv6 hérite automatiquement du VLAN d'accès de son interface.
- En mode trunk, chaque adresse IPv4 ou IPv6 référence un VLAN porté par son interface ou est explicitement déclarée « non taguée ».
- En mode trunk avec un VLAN natif, toute adresse déclarée « non taguée » est associée à ce VLAN natif, notamment pour le contrôle d'unicité des adresses IP.
- En mode trunk sans VLAN natif, une adresse déclarée « non taguée » appartient au périmètre « zone de l'équipement + sans VLAN ».
- Une adresse ne peut pas référencer un VLAN absent de la configuration accès ou trunk de son interface.
- Le masque IPv4 et le préfixe IPv6 sont facultatifs et ne peuvent être renseignés que lorsque l'adresse correspondante existe.
- Le masque IPv4 peut être saisi en notation décimale, par exemple « 255.255.255.0 », ou en notation CIDR, par exemple « /24 ».
- Les deux notations IPv4 sont automatiquement synchronisées et enregistrées sous une forme canonique unique de longueur de préfixe.
- Le préfixe éventuellement saisi avec l'adresse et le champ de masque séparé représentent une seule valeur logique.
- Une contradiction entre ces deux saisies est refusée.
- Seuls les masques IPv4 contigus et convertibles en longueur de préfixe sont acceptés.
- Pour IPv6, le masque est saisi et enregistré uniquement sous forme de longueur de préfixe CIDR, par exemple « /64 ».
- Une interface réseau possède zéro à plusieurs noms DNS.
- Un même nom DNS peut être utilisé sur plusieurs interfaces.
- Un nom DNS ne peut apparaître qu'une seule fois sur une même interface.
- Les noms courts et les noms de domaine pleinement qualifiés, ou FQDN, sont acceptés.
- Les noms de domaine internationalisés ne sont pas acceptés.
- Les noms DNS sont limités aux caractères ASCII autorisés par le format classique des noms d'hôte.
- La comparaison des noms DNS ignore la casse et le point final facultatif.
- Chaque nom DNS peut être associé facultativement à une ou plusieurs adresses IPv4 ou IPv6 de son interface.
- Sans association à une adresse précise, le nom DNS s'applique globalement à l'interface.
- Si une suppression d'adresse IP ou une modification du nom DNS retire sa dernière association IP, l'application demande explicitement si ce nom DNS doit être supprimé ou devenir global à l'interface.
- Cette conversion vers une portée globale n'est jamais effectuée silencieusement.
- La configuration VLAN d'une interface est facultative.
- Sans configuration VLAN, l'interface n'est rattachée à aucun VLAN.
- En mode accès, l'interface est rattachée à exactement un VLAN.
- En mode trunk, l'interface est rattachée à un ou plusieurs VLAN tagués et peut posséder un VLAN natif facultatif.
- Un VLAN natif ne peut pas figurer simultanément dans la liste des VLAN tagués de la même interface.
- Une interface ne peut référencer que des VLAN appartenant à la même zone que son équipement.
- Les adresses IPv4 et IPv6 sont validées selon leur format strict.
- Les adresses sont normalisées sous une forme canonique avant comparaison ; les différentes écritures équivalentes d'une même IPv6 représentent la même adresse.
- Le préfixe réseau n'entre pas dans l'identité de l'adresse : deux adresses identiques avec des préfixes différents sont considérées comme la même adresse dans un même périmètre.
- Les longueurs de préfixe autorisées vont de 0 à 32 en IPv4 et de 0 à 128 en IPv6.
- L'unicité d'une adresse IP est vérifiée sur le triplet « adresse normalisée + zone de l'équipement + VLAN associé ».
- Sans VLAN associé à l'adresse, le périmètre d'unicité utilisé est « zone de l'équipement + sans VLAN ».
- Une adresse IPv4 ou IPv6 peut inclure un préfixe réseau CIDR facultatif.
- L'adresse MAC est facultative et validée selon son format strict.
- L'adresse MAC est enregistrée sous la forme canonique « AA:BB:CC:DD:EE:FF ».
- Une adresse MAC déjà utilisée est refusée par défaut.
- L'utilisateur peut autoriser explicitement un doublon de MAC après affichage d'un avertissement et confirmation, notamment pour représenter une adresse virtuelle de haute disponibilité.
- Les noms DNS sont validés comme noms d'hôte et leur comparaison ignore les différences entre majuscules et minuscules.
- Le nom de l'interface est obligatoire.
- L'adresse MAC, la configuration VLAN, les adresses IP, les noms DNS et la description sont facultatifs.

VLAN :
- Un VLAN possède une zone, un identifiant numérique compris entre 1 et 4094, un nom et une description.
- La zone, l'identifiant et le nom sont obligatoires.
- Le couple « zone + identifiant VLAN » est unique dans l'application.
- Le couple « zone + nom du VLAN » est unique dans l'application.
- Un même identifiant VLAN peut être réutilisé dans plusieurs zones.
- Un VLAN peut être utilisé par zéro à plusieurs interfaces.
- Le changement de zone d'un VLAN est interdit tant qu'il est utilisé par au moins une interface.
- Un VLAN inutilisé peut changer de zone après vérification de l'absence de collision de nom et d'identifiant dans la zone de destination.
- La description est facultative.

CRITERES DE RECETTE :
- La recette couvre la création, la modification, la duplication et la suppression en cascade des équipements, interfaces, services et flux.
- Elle vérifie les transitions automatiques entre les états DRAFT et OK.
- Elle vérifie la cartographie, ses regroupements, ses dispositions responsives et son export SVG ou imprimable.
- Elle couvre l'import total, les migrations, la fusion conflictuelle, l'export total, les sauvegardes et la récupération.
- Elle est exécutée sur les versions prises en charge de Microsoft Edge, Mozilla Firefox et Firefox ESR.
- Elle couvre les formats ordinateur, tablette et téléphone selon le périmètre fonctionnel défini pour chacun.
- Elle utilise au minimum la volumétrie cible définie dans les spécifications.

QUESTIONS RESTANTES :
- Aucune question restante à l'issue de la dixième revue de cohérence.
- Toutes les propositions formulées jusqu'à la Q125 ont été validées, remplacées par les réponses consignées ou supprimées à la demande de l'utilisateur.

SUIVI D'AVANCEMENT :

Ce chapitre sert de point de reprise entre les deux postes de développement. Il doit être mis à jour à chaque lot significatif, avant le commit et le push Git.

Dernière mise à jour :
- Date : 30 juillet 2026.
- Application : prototype autonome contenu dans `index.html`.
- Version déclarée dans l'application : 0.1.0.
- Version du schéma de données : 1.
- Version du catalogue intégré : 1.
- Fichiers suivis dans ce dossier : `index.html` et `project.md`.
- Environnement de cette session : Git 2.53.0 est fourni par GitHub Desktop à l'emplacement `C:\Users\Taz\AppData\Local\GitHubDesktop\app-3.6.3\resources\app\git\cmd\git.exe`. Node.js n'est pas disponible dans le `PATH`, les tests JavaScript par Node.js n'ont donc pas pu être exécutés.

État fonctionnel actuellement implémenté :
- Document autonome sans serveur, thèmes clair et sombre et interface responsive.
- Gestion des utilisateurs déclaratifs et sélection de l'utilisateur courant.
- Création, modification, détail, duplication et suppression des équipements.
- Gestion des interfaces réseau, adresses IPv4 et IPv6, MAC, DNS et modes VLAN accès ou trunk.
- Création, modification, duplication et suppression des services et de leurs points d'écoute.
- Création, modification, duplication et suppression des flux, calcul automatique DRAFT ou OK, validation et normalisation des expressions de ports et détection de doublons techniques.
- Référentiels des zones, VLAN, types d'équipement, types de service, protocoles et ports connus.
- Historique des changements de statut des équipements et services.
- Suppressions en cascade principales et invalidation des flux lorsque leur point d'écoute disparaît.
- Import total par remplacement ou fusion simple, export JSON, sauvegarde directe lorsque l'API du navigateur est disponible, copie indépendante, récupération IndexedDB et réinitialisation.
- Cartographie SVG avec vue tabulaire accessible, impression et export SVG.

Dernier lot réalisé le 30 juillet 2026 — cartographie :
- Ajout des filtres par zone, équipement, service, protocole, statut d'équipement ou service et état de flux.
- Ajout d'une commande de réinitialisation des filtres.
- Regroupement visuel des flux qui relient les mêmes services.
- Affichage d'un compteur sur une connexion regroupée.
- Ouverture du détail des flux regroupés par clic, touche Entrée ou barre d'espace.
- Correction du sens des flèches pour les flux « distant vers local ».
- Agrégation des deux sens en connexion bidirectionnelle, y compris lorsque les extrémités sont enregistrées dans l'ordre inverse.
- Application des filtres à la vue tabulaire accessible.
- Fichier modifié : `index.html`, principalement `renderMap()`, `openFlowGroup()` et les styles de cartographie.

Complément du lot cartographique réalisé le 30 juillet 2026 :
- Représentation des auto-flux par une boucle au-dessus du service, au lieu d'une ligne de longueur nulle.
- Regroupement et compteur également appliqués aux auto-flux.
- Prise en charge des flèches directionnelles sur les boucles.
- Ouverture de la fiche détaillée d'un équipement depuis son nœud cartographique, à la souris ou au clavier.
- Ouverture de la nouvelle fiche détaillée d'un service depuis son libellé cartographique, à la souris ou au clavier.
- Ajout de la fiche détaillée d'un service dans la vue tabulaire des services.
- La fiche du service récapitule son équipement, son type, sa version, son statut, sa description, ses points d'écoute, ses flux liés et son historique de statut.
- Le détail d'une connexion regroupée permet désormais d'ouvrir directement chaque formulaire de flux.
- Amélioration du retour visuel au survol et au focus des connexions, boucles et services.

Lot de persistance réalisé le 30 juillet 2026 — récupérations locales :
- Ajout dans la vue « Données » d'un panneau listant les copies automatiques présentes dans IndexedDB.
- Tri des récupérations de la plus récemment modifiée à la plus ancienne.
- Affichage pour chaque récupération du nom du document, de sa date, de sa révision et de son UUID.
- Identification de la récupération correspondant au document courant.
- Ajout d'une restauration avec confirmation explicite.
- Avant toute restauration, téléchargement automatique d'une sauvegarde JSON du document courant.
- Validation minimale du document récupéré avant son remplacement.
- Après restauration, réinitialisation de l'utilisateur courant, du descripteur de fichier et de la référence externe afin d'éviter d'écrire dans l'ancien fichier.
- Possibilité d'annuler la restauration via le mécanisme d'annulation destructive existant.
- Ajout de la suppression ciblée d'une récupération avec confirmation, sans effacer toutes les autres copies locales.
- Ajout d'un message explicite lorsque la récupération demandée a disparu ou est invalide.

Lot de persistance poursuivi le 30 juillet 2026 — validation d'intégrité des imports :
- Remplacement de la validation minimale par une validation globale exécutée avant toute proposition de remplacement ou de fusion.
- Contrôle de l'enveloppe totale, des versions de schéma et de catalogue, des métadonnées du document et de la présence de toutes les collections attendues.
- Contrôle des identifiants obligatoires et détection des UUID dupliqués dans les entités, interfaces, éléments réseau, points d'écoute et historiques.
- Contrôle des champs métier obligatoires, statuts, dates d'historique, plages de VLAN et de ports.
- Contrôle des références entre VLAN et zone, équipement et référentiels, service et équipement, point d'écoute et élément réseau, flux et services, protocoles ou points d'écoute.
- Vérification que chaque point d'écoute appartient bien à l'équipement du service et que le dernier événement d'historique correspond au statut courant.
- Refus des versions anciennes tant qu'aucune migration explicite n'est disponible, afin de ne jamais charger silencieusement un schéma non pris en charge.
- Affichage de la liste détaillée des anomalies d'import dans une fenêtre dédiée, avec confirmation que le document courant reste inchangé.
- Ajout d'un message distinct lorsque la lecture du fichier sélectionné échoue.
- La restauration IndexedDB réutilise automatiquement la même validation complète.

Sous-lot suivant — aperçu et sauvegarde avant import :
- Après validation, le choix du mode n'applique plus immédiatement l'import.
- Un aperçu obligatoire récapitule le document, son UUID, sa révision et les volumes d'utilisateurs, zones, VLAN, équipements, services et flux.
- L'aperçu explique distinctement l'effet du remplacement total et les limites de la fusion additive actuelle.
- La sauvegarde JSON de sécurité est désormais créée avant un remplacement comme avant une fusion.
- L'utilisateur doit confirmer explicitement que le téléchargement de cette sauvegarde a réussi avant que l'état candidat soit publié.
- Une annulation à l'étape de l'aperçu ou de la confirmation laisse le document courant inchangé.
- Après un remplacement, l'éventuel descripteur de fichier précédemment ouvert est détaché afin d'éviter une écriture ultérieure dans l'ancien fichier.

Sous-lot suivant — destination atomique de l'import :
- Lorsqu'un fichier local est déjà associé, l'utilisateur choisit explicitement entre le fichier courant, un nouveau fichier et un travail détaché.
- Le remplacement ou la fusion est d'abord construit intégralement dans une copie en mémoire.
- Pour une destination en écriture directe, cette copie candidate est écrite et le flux est fermé avant toute publication dans l'interface.
- Une annulation du sélecteur de fichier ou un échec d'écriture conserve intégralement le document courant et son association de fichier.
- Une écriture réussie publie ensuite l'état candidat, conserve le nouveau descripteur et initialise la référence externe sur le contenu effectivement écrit.
- Le mode détaché publie l'état candidat comme non sauvegardé et supprime toute association avec l'ancien fichier.
- La fusion candidate conserve la lignée courante et n'incrémente la révision qu'une seule fois.

Sous-lot suivant — divergence du fichier externe :
- Avant de réécrire un fichier déjà associé, l'application relit son contenu et compare son empreinte à celle du dernier état effectivement écrit.
- Une divergence suspend l'écriture et affiche explicitement l'état « Conflit externe ».
- L'utilisateur peut recharger un document externe valide via le parcours sécurisé d'aperçu et de sauvegarde, confirmer l'écrasement par la version courante ou enregistrer la version courante dans un nouveau fichier.
- Un contenu externe invalide ne peut pas être rechargé, mais peut être écrasé explicitement ou contourné par un nouvel enregistrement.
- Le choix « nouveau fichier » n'abandonne l'ancien descripteur qu'après la réussite du nouvel enregistrement.
- L'écriture d'un état candidat d'import dans le fichier courant est également refusée si une divergence est détectée entre-temps.

Sous-lot suivant — compléments de validation métier :
- Correction du validateur pour accepter le mode VLAN réel `none` et contrôler les champs persistés `accessVlanId` et `nativeVlanId`.
- Vérification de l'appartenance de chaque VLAN à la zone de l'équipement, des exigences accès/trunk et de l'absence de VLAN natif également tagué.
- Vérification des préfixes IP, de l'appartenance des IP aux VLAN portés par l'interface et de l'unicité d'une adresse dans une même zone et un même VLAN.
- Vérification des associations DNS vers des IP existantes de la même interface.
- Vérification de la cohérence entre le type déclaré d'un point d'écoute et l'élément réseau réellement référencé.
- Détection des noms ou clés métier en double pour les utilisateurs, zones, référentiels, VLAN, équipements, interfaces, DNS et services.
- Détection des chevauchements de ports ayant des usages différents, des auto-flux aberrants et des clés techniques de flux dupliquées.

État des migrations :
- L'historique Git complet de `matrix/index.html` a été examiné.
- La version initiale du prototype utilisait déjà `schemaVersion: 1` et aucun export d'un schéma antérieur n'est défini dans le dépôt.
- Aucune migration rétroactive n'est donc ajoutée sans format source réel ; les versions antérieures restent refusées explicitement.
- La première évolution incompatible du modèle devra augmenter `SCHEMA_VERSION` et fournir dans le même lot sa migration depuis la version 1 ainsi que ses fichiers de recette.

Première tranche de fusion avancée — conflits de même UUID :
- La fusion compare désormais le contenu métier des éléments portant le même UUID au lieu de les ignorer silencieusement.
- Les UUID, dates de création et dates de modification sont exclus de cette comparaison.
- Un élément strictement équivalent n'est pas réimporté et la donnée courante est conservée.
- Chaque contenu différent doit recevoir une décision explicite « conserver la donnée courante » ou « utiliser la donnée importée ».
- Une commande permet d'appliquer la même décision à tous les conflits affichés.
- Les décisions sont appliquées uniquement à la copie importée préparée en mémoire.
- Le candidat fusionné est entièrement revalidé avant sauvegarde ou publication ; une relation devenue invalide annule toute la fusion.
- Les collisions métier entre UUID différents, le remappage d'import et l'union déterministe des historiques restent à traiter dans les tranches suivantes.

Deuxième tranche de fusion avancée — union des historiques :
- Pour un conflit de même UUID concernant un équipement ou un service, les historiques courant et importé sont désormais réunis quelle que soit la donnée principale retenue.
- Les événements strictement identiques par date, ancien statut, nouveau statut et pseudo sont dédupliqués indépendamment de leur UUID d'événement.
- Les contenus des événements conservés ne sont pas modifiés.
- Le tri utilise successivement la date effective, `originDocumentUUID` et l'UUID d'événement.
- Si le dernier événement fusionné ne correspond pas au statut finalement retenu, un nouvel événement « résolution de fusion » est ajouté avec le pseudo courant.
- Le candidat complet est ensuite soumis à la validation qui impose la cohérence entre son dernier événement et son statut courant.
- L'aperçu d'import signale les événements datés de plus d'une minute dans le futur comme anomalies d'horloge.
- Ces événements futurs sont listés avec leur élément, leur date et leur pseudo, mais leur contenu immuable n'est jamais corrigé automatiquement.

Recette Edge complémentaire :
- Une capture réelle a été produite avec Microsoft Edge en mode headless à la résolution 1 440 × 1 000.
- Le tableau de bord, la navigation, les compteurs initiaux et la fenêtre obligatoire « Nouvel utilisateur » sont rendus correctement.
- Aucun écran blanc ni défaut bloquant n'est visible sur cette capture.
- La capture temporaire de recette a été supprimée après inspection et n'est pas suivie par Git.

Lot cartographique poursuivi — zoom et panoramique :
- Ajout de commandes accessibles de zoom avant, zoom arrière et recadrage complet.
- La molette permet également de zoomer avec des bornes empêchant un grossissement ou un recul excessif.
- Le glisser-déplacer du fond de carte permet le panoramique à la souris, au stylet et au tactile via les événements de pointeur.
- Le déplacement du fond n'intercepte pas l'activation des équipements, services ou connexions.
- Le curseur indique les états disponibles et actifs du panoramique.
- Le cadrage courant est mémorisé dans les préférences locales séparément pour les formats ordinateur, tablette et téléphone.
- Le cadrage reste exclu du document métier exporté, conformément à la séparation entre préférences d'interface et données.
- Les filtres disposent désormais d'un libellé de réinitialisation distinct du bouton de recadrage.

Lot cartographique poursuivi — déplacement manuel :
- Un équipement peut être déplacé directement par glisser-déplacer sans empêcher l'ouverture de ses services.
- Les coordonnées du pointeur sont converties dans le repère SVG, y compris après zoom ou panoramique.
- La position n'est enregistrée qu'à la fin d'un déplacement dépassant le seuil de clic.
- Chaque déplacement constitue une transaction métier unique, incrémente une seule fois la révision et reste couvert par la récupération locale.
- Les positions sont enregistrées dans le document séparément pour les formats ordinateur, tablette et téléphone.
- Les flux sont recalculés sur les nouvelles coordonnées lors du rendu suivant.
- La validation d'import contrôle les coordonnées et refuse les positions rattachées à un équipement inexistant.
- La suppression d'un équipement retire ses positions dans les trois formats.
- La création d'une copie indépendante remappe les clés de position vers les nouveaux UUID d'équipement.
- La fiche d'un équipement déplacé propose de rétablir uniquement sa position automatique dans le format d'écran courant.
- Le clic synthétique suivant un glisser-déplacer est neutralisé afin de ne pas ouvrir involontairement la fiche de l'équipement.

Vérifications de ce lot :
- `git diff --check` ne signale aucune erreur de whitespace.
- Comptage statique équilibré : 969 accolades ouvrantes et fermantes, 2 135 parenthèses ouvrantes et fermantes, 314 crochets ouvrants et fermants.
- Le lancement headless Edge s'est terminé avec un code de sortie nul, mais n'a retourné aucun DOM exploitable dans cette session ; cette vérification reste donc à refaire visuellement.
- La recette avec des fichiers JSON valides et volontairement corrompus reste à effectuer dans un navigateur normal.
- Le parcours aperçu, téléchargement, annulation et confirmation doit également être inclus dans cette recette.

Vérifications du dernier lot :
- Comptage statique équilibré des accolades, parenthèses et crochets dans `index.html`.
- Présence vérifiée des nouveaux gestionnaires de filtres, de clavier et de détail.
- Microsoft Edge est installé sur le poste. Après autorisation d'exécution locale, le chargement headless de `index.html` a rendu le tableau de bord et le contenu de l'application sans erreur de syntaxe bloquante visible.
- Les interactions cartographiques et le rendu des auto-flux doivent encore faire l'objet d'une recette visuelle dans un navigateur normal avec un jeu de données approprié.
- Aucun test automatisé n'existe actuellement dans le dépôt.

Recette manuelle prioritaire à effectuer sur l'autre poste :
1. Ouvrir directement `index.html` dans Edge puis dans Firefox.
2. Créer deux équipements, un service sur chacun et plusieurs flux entre ces services.
3. Vérifier les flèches dans les trois sens : local vers distant, distant vers local et bidirectionnel.
4. Vérifier qu'une seule connexion est dessinée pour plusieurs flux entre les mêmes services et que son compteur est exact.
5. Ouvrir le groupe à la souris, avec Entrée puis avec la barre d'espace.
6. Tester chaque filtre séparément, plusieurs filtres combinés, puis la réinitialisation.
7. Vérifier que la vue tabulaire contient exactement les flux visibles après filtrage.
8. Tester l'impression et l'export SVG.
9. Contrôler la console JavaScript et l'attribut `data-js-error` de l'élément `<html>`.
10. Créer un auto-flux valide et vérifier sa boucle, son sens, son compteur et son détail.
11. Ouvrir les fiches d'un équipement et d'un service depuis la carte à la souris et au clavier.
12. Depuis une connexion regroupée, ouvrir chaque flux puis annuler le formulaire.
13. Modifier un document, attendre la copie automatique, puis vérifier sa présence dans « Données > Récupérations locales ».
14. Restaurer une récupération et vérifier qu'une sauvegarde JSON du document remplacé est téléchargée.
15. Vérifier le contenu restauré, la nouvelle sélection d'utilisateur et la possibilité d'annuler la restauration.
16. Supprimer une seule récupération et vérifier que les autres restent disponibles.

Limites et écarts connus par rapport aux spécifications :
- La fusion actuelle est additive et simple. Elle n'implémente pas encore la résolution complète des conflits métier, l'union déterministe des historiques ni toutes les règles de concurrence décrites dans les spécifications.
- Les migrations de schéma et de catalogue ne sont pas encore implémentées ; une version de schéma antérieure est donc refusée explicitement.
- La cartographie utilise une disposition automatique fixe. Le déplacement manuel, le zoom, le panoramique et la sauvegarde des positions par format d'écran restent à réaliser.
- Les auto-flux sont dessinés sous forme de boucle, mais leur géométrie fixe doit être vérifiée en présence de nombreux services proches.
- Les filtres cartographiques doivent être validés sur des jeux de données où les statuts de l'équipement et de ses services diffèrent.
- La recherche globale et tous les filtres structurés prévus ne sont pas encore complets.
- Les associations précises entre un nom DNS et une ou plusieurs IP de son interface ne disposent pas encore d'une interface complète.
- La confirmation détaillée des impacts avant toutes les modifications réseau sensibles reste partielle.
- Les objectifs de volumétrie n'ont pas encore été mesurés.
- Les récupérations IndexedDB restent propres au navigateur, au profil et à l'origine locale utilisés. Elles ne sont pas synchronisées par Git et ne remplacent jamais les fichiers JSON exportés.

Prochain lot recommandé :
- Terminer la stabilisation cartographique par la recette ci-dessus et corriger les éventuels défauts de rendu ou de navigation.
- Effectuer la recette des récupérations locales sur Edge et Firefox.
- Effectuer la recette d'import avec un export valide, puis des documents altérés couvrant chaque famille d'anomalies, et compléter les contrôles selon les défauts observés.
- Vérifier le choix de destination avec une écriture réussie, une annulation du sélecteur et un échec d'écriture simulé.
- Vérifier les trois résolutions de divergence avec une modification externe réelle du fichier associé.
- Préparer la fusion avancée sous forme de fonctions pures : analyse des conflits, décisions, remappage puis validation atomique.
- Ajouter ensuite la détection et la résolution des collisions métier entre UUID différents, avec remappage des relations.
- Traiter ensuite la fusion avancée comme un lot séparé, avec des fonctions testables hors interface.

Procédure de passage d'un poste à l'autre :
1. Mettre à jour ce suivi avec la date, le lot terminé, les vérifications et la prochaine action précise.
2. Vérifier la recette minimale dans un navigateur.
3. Examiner `git status` et ne versionner que les fichiers attendus.
4. Créer un commit décrivant le résultat fonctionnel, puis pousser la branche.
5. Sur l'autre poste, tirer la même branche et lire d'abord cette section avant toute modification.
6. Après reprise, inscrire ici le nom de la branche et le hash du commit de départ.

Informations Git relevées le 30 juillet 2026 :
- Racine du dépôt : `D:\PDC\www`.
- Dépôt distant : `origin`, URL `https://github.com/PsyCh0Taz/PDC.git`.
- Branche de travail : `main`.
- Branche amont : `origin/main`.
- Dernier commit intégré localement : `c6d2808` (`matrix: secure import publication`).
- Dernier commit connu sur `origin/main` : `c6d2808`.
- Au moment du relevé, la branche locale et la branche distante connue pointent sur le même commit.
- Modifications Matrix non validées au point de reprise : aucune, avant la présente mise à jour du suivi.
- Autres modifications présentes dans le dépôt mais hors du projet Matrix : `pdc/classes/Auth.php` et `pdc/config/config.php`. Elles appartiennent à un autre travail et ne doivent pas être incluses dans un commit Matrix sans vérification explicite.
- Git considère la racine comme appartenant au compte Windows `Taz`, tandis que Codex utilise un compte isolé. Les commandes Codex utilisent donc ponctuellement `-c safe.directory=D:/PDC/www` sans modifier la configuration Git globale.
