/* ============================================================
   PDC — Plan de Charge — Script principal
   ============================================================ */

(function($) {
    'use strict';

    // Variables pour stocker l'état des modales
    var PDC_CURRENT_JALONS = [];
    var PDC_CURRENT_GRADIENTS = [];

    // ---- Initialisation ----
    $(document).ready(function() {
        initTabs();
        initDatepickers();
        initFrises();
        initDragDrop();
        initModales();
        initToolbar();
    });

    // ---- Datepickers jQuery UI ----
    function initDatepickers() {
        $.datepicker.setDefaults({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
            firstDay: 1,
            changeMonth: true,
            changeYear: true,
            prevText: "Précédent",
            nextText: "Suivant",
            yearRange: '-10:+10'
        });

        // Convertir les valeurs existantes au format français
        $('.pdc-datepicker, .gradient-date, .jalon-date').each(function() {
            var val = $(this).val();
            if (val && val.match(/^\d{4}-\d{2}-\d{2}$/)) {
                $(this).val(convertToFrench(val));
            }
        });

        $('.pdc-datepicker, .gradient-date, .jalon-date').datepicker();

        // Intercepter la soumission du formulaire de période
        $('.pdc-periode-form').on('submit', function(e) {
            var $dateDebut = $(this).find('input[name="date_debut"]');
            var $dateFin = $(this).find('input[name="date_fin"]');
            
            $dateDebut.val(convertToISO($dateDebut.val()));
            $dateFin.val(convertToISO($dateFin.val()));
        });

        $('.pdc-periode-form').on('change', function(e) {
            $("#periode_form").submit();
        });
    }

    // Convertir dd/mm/yy vers yyyy-mm-dd
    function convertToISO(dateStr) {
        if (!dateStr) return '';
        if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) return dateStr; // Déjà au format ISO
        var parts = dateStr.split('/');
        if (parts.length !== 3) return dateStr;
        var day = parts[0];
        var month = parts[1];
        var year = parts[2];
        if (year.length === 2) {
            year = (year < 50) ? '20' + year : '19' + year;
        }
        return year + '-' + month + '-' + day;
    }

    // Convertir yyyy-mm-dd vers dd/mm/yy
    function convertToFrench(dateStr) {
        if (!dateStr) return '';
        if (!dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) return dateStr; // Pas au format ISO
        var parts = dateStr.split('-');
        var year = "20" + parts[0].substring(2); // Prendre les 2 derniers chiffres
        var month = parts[1];
        var day = parts[2];
        return day + '/' + month + '/' + year;
    }

    // Parser une date ISO "YYYY-MM-DD" en objet Date (temps local, pas UTC)
    function parseISODate(dateStr) {
        if (!dateStr) return new Date();
        var parts = dateStr.split('-');
        if (parts.length !== 3) return new Date(dateStr);
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10) - 1; // Month est 0-indexed
        var day = parseInt(parts[2], 10);
        return new Date(year, month, day);
    }

    // ---- Onglets jQuery UI ----
    function initTabs() {
        if ($('#projet-tabs').length) {
            $('#projet-tabs').tabs();
        }
    }

    // ---- Rendu des frises temporelles ----
    function initFrises() {
        $('.pdc-frise').each(function() {
            renderFrise($(this));
        });
    }

    function renderFrise($frise) {
        var dateDebut = parseISODate($frise.data('date-debut'));
        var dateFin = parseISODate($frise.data('date-fin'));
        var periodeDebut = parseISODate($frise.data('periode-debut'));
        var periodeFin = parseISODate($frise.data('periode-fin'));
        var gradients = $frise.data('gradients') || [];
        var jalons = $frise.data('jalons') || [];

        // Calcul des dates visibles
        var visibleDebut = dateDebut > periodeDebut ? dateDebut : periodeDebut;
        var visibleFin = dateFin < periodeFin ? dateFin : periodeFin;

        if (visibleDebut > periodeFin || visibleFin < periodeDebut) {
            $frise.html('<p class="text-muted"><em>Hors période</em></p>');
            return;
        }

        var totalDays = daysBetween(periodeDebut, periodeFin);
        var startOffset = daysBetween(periodeDebut, visibleDebut) / totalDays * 100;
        var width = daysBetween(visibleDebut, visibleFin) / totalDays * 100;

        // Ajouter les semaines au-dessus
        var $weeksContainer = $('<div class="pdc-weeks"></div>');
        var currentDate = new Date(periodeDebut);
        var seenWeeks = {};

        while (currentDate <= periodeFin) {
            var weekNum = getWeekNumber(currentDate);
            var year = currentDate.getFullYear();
            var weekKey = year + '-W' + weekNum;

            if (!seenWeeks[weekKey]) {
                seenWeeks[weekKey] = true;
                var weekOffset = daysBetween(periodeDebut, currentDate) / totalDays * 100;
                
                var $week = $('<div class="pdc-week-label"></div>');
                $week.css('left', weekOffset + '%');
                $week.html('S' + weekNum + "/" + year.toString().substr(2,2) + (currentDate.getDay() != 1 ? "" : "<br>" + currentDate.getDate() + "/" + (currentDate.getMonth() + 1)));
                $week.attr('title', 'Semaine ' + weekNum + ' - ' + year);
                $weeksContainer.append($week);
            }

            // Passer au jour suivant
            currentDate.setDate(currentDate.getDate() + 1);
        }
        $frise.append($weeksContainer);

        // Créer la flèche avec gradient
        var $arrowAfter = $('<div class="pdc-frise-arrow"></div>');
        $arrowAfter.css({
            left: startOffset + '%',
            width: width + '%',
        });

        // Appliquer le gradient de couleur
        var gradient = buildGradient(gradients, dateDebut, dateFin, visibleDebut, visibleFin);
        $arrowAfter.css('background', gradient);
        $arrowAfter.attr('title', convertToFrench(dateDebut.toISOString().substr(0,10)) + ' → ' + convertToFrench(dateFin.toISOString().substr(0,10)));

        // Ajouter la flèche seulement si la frise continue après la période affichée
        if (dateFin > periodeFin)  {
            $arrowAfter.addClass('pdc-frise-arrow-after');
        }

        $frise.prepend($arrowAfter);

        // Créer la flèche avec gradient
        var $arrowBefore = $('<div class="pdc-frise-arrow"></div>');
        $arrowBefore.css({
            left: startOffset + '%',
            width: width + '%',
        });

        // Appliquer le gradient de couleur
        var gradient = buildGradient(gradients, dateDebut, dateFin, visibleDebut, visibleFin);
        $arrowBefore.css('background', gradient);
        $arrowBefore.attr('title', convertToFrench(dateDebut.toISOString().substr(0,10)) + ' → ' + convertToFrench(dateFin.toISOString().substr(0,10)));

        // Ajouter la flèche seulement si la frise débute avant la période affichée
        if ( dateDebut < periodeDebut )  {
            $arrowBefore.addClass('pdc-frise-arrow-before');
        }

        $frise.prepend($arrowBefore);
        var $nbrPointilles = 1;
        var $hauteurDecalage = 7;
        // Jalons
        jalons.forEach(function(jalon) {
            var jalonDate = parseISODate(jalon.date_jalon);
            var isJalonVisible = jalonDate >= periodeDebut && jalonDate <= periodeFin;
            var jalonOffset = ( daysBetween(periodeDebut, jalonDate) / totalDays * 100 );

            var $jalon = $('<div class="pdc-jalon"></div>');
            if (isJalonVisible) {
                $jalon.css('left', jalonOffset + '%');
            }

            var $triangle = $('<div class="pdc-jalon-triangle pdc-couleur-' + jalon.couleur + '"></div>');
            
            var $libelle = $('<div class="pdc-jalon-libelle"></div>');
            
            var libelleTronque = jalon.libelle.length > 15 ? jalon.libelle.substr(0, 15) + '…' : jalon.libelle;
            $libelle.text(libelleTronque).attr('title', jalon.libelle);
            var $jalonTitle = jalon.libelle + "("  + convertToFrench(jalon.date_jalon) + ')';
            
            // Pointillé si décalé
            if (jalon.jalon_reference_id) {
                var refJalon = jalons.find(function(j) { 
                    return parseInt(j.id, 10) === parseInt(jalon.jalon_reference_id, 10); 
                });
                if (refJalon) {
                    $nbrPointilles += 1; // Incrémenter le décalage pour le prochain jalon
                    var refDate = parseISODate(refJalon.date_jalon);
                    var refVisible = refDate >= periodeDebut && refDate <= periodeFin;
                    var jalonVisible = jalonDate >= periodeDebut && jalonDate <= periodeFin;

                    var refOffsetVisuel = daysBetween(periodeDebut, refDate < periodeDebut ? periodeDebut : (refDate > periodeFin ? periodeFin : refDate)) / totalDays * 100;
                    var jalonOffsetVisuel = ( daysBetween(periodeDebut, jalonDate < periodeDebut ? periodeDebut : (jalonDate > periodeFin ? periodeFin : jalonDate)) / totalDays * 100 );

                    var drawLine = !(refDate < periodeDebut && jalonDate < periodeDebut) && !(refDate > periodeFin && jalonDate > periodeFin);
                    $jalonTitle = refJalon.libelle + "(" + convertToFrench(refJalon.date_jalon) + ")" + ' → ' + $jalonTitle;

                    if (drawLine) {
                        var $pointille = $('<div class="pdc-jalon-pointille"></div>');
                        var startOffset, endOffset;

                        if (refVisible && jalonVisible) {
                            startOffset = refOffsetVisuel;
                            endOffset = jalonOffsetVisuel;
                        } else if (!refVisible && refDate < periodeDebut) {
                            startOffset = 0;
                            endOffset = jalonOffsetVisuel;
                            $pointille.css({
                                "border-left": "none",
                            });
                        } else if (!jalonVisible && jalonDate > periodeFin) {
                            startOffset = refOffsetVisuel;
                            endOffset = 100;
                            $pointille.css({
                                "border-right": "none",
                            });                            
                        } else {
                            startOffset = Math.min(refOffsetVisuel, jalonOffsetVisuel);
                            endOffset = Math.max(refOffsetVisuel, jalonOffsetVisuel);
                        }

                        $pointille.css({
                            left: startOffset + '%',
                            width: Math.max(0, endOffset - startOffset) + '%',
                            height: ($nbrPointilles * $hauteurDecalage) + 30 + 'px', // Décalage pour éviter les chevauchements
                            "border-color": "var(--pdc-" + refJalon.couleur + ")",
                        });
                        $pointille.attr('title', $jalonTitle);
                        $frise.append($pointille);
                    }

                    
                }
            }

            if (isJalonVisible) {
                $triangle.attr('title', $jalonTitle);
                $jalon.append($triangle).append($libelle);
                $frise.css('min-height', ($nbrPointilles * ( $hauteurDecalage + 5)) + 60 + 'px'); // S'assurer que la frise est assez haute pour les jalons
                $frise.append($jalon);
            }
        });
    }

    function buildGradient(gradients, dateDebut, dateFin, visibleDebut, visibleFin) {
        if (!gradients || gradients.length === 0) {
            return 'linear-gradient(90deg, #27ae60 0%, #27ae60 100%)';
        }

        // Trier par date
        gradients.sort(function(a, b) {
            return parseISODate(a.date_gradient) - parseISODate(b.date_gradient);
        });

        var couleurs = {
            'vert': '#27ae60',
            'jaune': '#f1c40f',
            'orange': '#e67e22',
            'rouge': '#e74c3c',
        };

        var stops = [];
        var totalDays = daysBetween(visibleDebut, visibleFin);

        gradients.forEach(function(g) {
            var gDate = parseISODate(g.date_gradient);
            if (gDate >= visibleDebut && gDate <= visibleFin) {
                var offset = daysBetween(visibleDebut, gDate) / totalDays * 100;
                stops.push({
                    offset: offset,
                    color: couleurs[g.couleur] || couleurs['vert']
                });
            }
        });

        if (stops.length === 0) {
            // Chercher la dernière couleur avant visibleDebut
            var lastColor = 'vert';
            gradients.forEach(function(g) {
                var gDate = parseISODate(g.date_gradient);
                if (gDate < visibleDebut) {
                    lastColor = g.couleur;
                }
            });
            return 'linear-gradient(90deg, ' + couleurs[lastColor] + ' 0%, ' + couleurs[lastColor] + ' 100%)';
        }

        // Construire le gradient CSS
        var gradientStr = 'linear-gradient(90deg';
        
        // Couleur de départ (avant le premier stop)
        var startColor = 'vert';
        if (stops[0].offset > 0) {
            for (var i = gradients.length - 1; i >= 0; i--) {
                var gDate = parseISODate(gradients[i].date_gradient);
                if (gDate < visibleDebut) {
                    startColor = gradients[i].couleur;
                    break;
                }
            }
            gradientStr += ', ' + couleurs[startColor] + ' 0%';
        }

        stops.forEach(function(stop) {
            gradientStr += ', ' + stop.color + ' ' + stop.offset + '%';
        });

        // Couleur de fin
        if (stops[stops.length - 1].offset < 100) {
            gradientStr += ', ' + stops[stops.length - 1].color + ' 100%';
        }

        gradientStr += ')';
        return gradientStr;
    }

    function daysBetween(date1, date2) {
        var diff = date2 - date1;
        return diff / (1000 * 60 * 60 * 24);
    }

    function getWeekNumber(date) {
        var d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        var dayNum = d.getUTCDay() || 7;
        d.setUTCDate(d.getUTCDate() + 4 - dayNum);
        var yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
    }

    // ---- Drag & Drop ----
    function initDragDrop() {
        if (typeof PDC !== 'undefined' && PDC.readOnly) return;

        // Projets draggables entre domaines
        $('.pdc-projets-list').sortable({
            connectWith: '.pdc-projets-list',
            handle: '.pdc-projet-header',
            placeholder: 'pdc-projet-placeholder',
            update: function(event, ui) {
                var projetId = ui.item.data('projet-id');
                var newDomaineId = ui.item.closest('.pdc-projets-list').data('domaine-id');
                
                // Réorganiser les ordres
                var ordres = {};
                ui.item.closest('.pdc-projets-list').find('.pdc-projet').each(function(idx) {
                    ordres[$(this).data('projet-id')] = idx;
                });

                // Si changement de domaine
                var oldDomaineId = ui.sender ? ui.sender.data('domaine-id') : newDomaineId;
                if (oldDomaineId != newDomaineId) {
                    $.post(PDC.appUrl + '/api.php', {
                        action: 'move_projet',
                        projet_id: projetId,
                        domaine_id: newDomaineId,
                    });
                }

                // Enregistrer les ordres
                $.post(PDC.appUrl + '/api.php', {
                    action: 'reorder_projets',
                    ordres: ordres,
                });
            }
        });

        // Domaines draggables
        $('#domaines-container').sortable({
            items: '.pdc-domaine',
            handle: '.pdc-domaine-header',
            update: function(event, ui) {
                var ordres = {};
                $('#domaines-container .pdc-domaine').each(function(idx) {
                    ordres[$(this).data('domaine-id')] = idx;
                });
                $.post(PDC.appUrl + '/api.php', {
                    action: 'reorder_domaines',
                    ordres: ordres,
                });
            }
        });
    }

    // ---- Modales ----
    function initModales() {
        if (typeof PDC !== 'undefined' && PDC.readOnly) return;

        // Éditer un domaine
        $(document).on('click', '.pdc-edit-domaine', function(e) {
            e.preventDefault();
            var domaineId = $(this).data('domaine-id');
            var nom = $(this).closest('.pdc-domaine-header').find('.pdc-domaine-titre').text().trim();
            
            PDC.currentDomaineId = domaineId;
            $('#domaine-nom').val(nom);
            $('#modal-edit-domaine').modal('show');
        });

        $('#btn-save-domaine').on('click', function() {
            var nom = $('#domaine-nom').val().trim();
            if (!nom) {
                alert('Le nom est requis.');
                return;
            }
            $.post(PDC.appUrl + '/api.php', {
                action: 'update_domaine',
                id: PDC.currentDomaineId,
                nom: nom,
            }, function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + data.error);
                }
            }, 'json');
        });

        $('#btn-delete-domaine').on('click', function() {
            if (!confirm('Supprimer ce domaine et tous ses projets ?')) return;
            $.post(PDC.appUrl + '/api.php', {
                action: 'delete_domaine',
                id: PDC.currentDomaineId,
            }, function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + data.error);
                }
            }, 'json');
        });

        // Ajouter un domaine
        $('#btn-add-domaine').on('click', function() {
            $('#new-domaine-nom').val('');
            $('#modal-add-domaine').modal('show');
        });

        $('#btn-create-domaine').on('click', function() {
            var nom = $('#new-domaine-nom').val().trim();
            if (!nom) {
                alert('Le nom est requis.');
                return;
            }
            $.post(PDC.appUrl + '/api.php', {
                action: 'create_domaine',
                service_id: PDC.serviceId,
                nom: nom,
            }, function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + data.error);
                }
            }, 'json');
        });

        // Ajouter un projet
        $('.pdc-add-projet').on('click', function() {
            var domaineId = $(this).data('domaine-id');
            PDC.currentDomaineId = domaineId;            
            $('#new-projet-titre').val('');
            $('#modal-add-projet').modal('show');
        });

        $('#btn-create-projet').on('click', function() {
            var titre= $('#new-projet-titre').val().trim();
            var domaineId = $('#new-projet-domaine').val().trim();
            var dateDebut = convertToISO($('#new-projet-date-debut').val());
            var dateFin = convertToISO($('#new-projet-date-fin').val());

            if (!titre) {
                alert('Le titre est requis.');
                return;
            }
            $.post(PDC.appUrl + '/api.php', {
                action: 'create_projet',
                domaine_id: PDC.serviceId,
                nom: nom,
            }, function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + data.error);
                }
            }, 'json');
        });

        // Éditer un projet
        $(document).on('click', '.pdc-edit-projet', function(e) {
            e.preventDefault();
            var projetId = $(this).data('projet-id');
            PDC.currentProjetId = projetId;
            
            $.post(PDC.appUrl + '/api.php', {
                action: 'get_projet',
                id: projetId,
            }, function(data) {
                if (data.success) {
                    loadProjetInModal(data.projet, data.gradients, data.jalons);
                    $('#modal-edit-projet').modal('show');
                } else {
                    alert('Erreur : ' + data.error);
                }
            }, 'json');
        });

        $('#btn-save-projet').on('click', function() {
            saveProjet();
        });

        $('#btn-delete-projet').on('click', function() {
            if (!confirm('Supprimer ce projet ?')) return;
            $.post(PDC.appUrl + '/api.php', {
                action: 'delete_projet',
                id: PDC.currentProjetId,
            }, function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + data.error);
                }
            }, 'json');
        });

        // Gradients
        $('#btn-add-gradient').on('click', function() {
            addGradientRow();
        });

        $(document).on('click', '.btn-remove-gradient', function() {
            $(this).closest('tr').remove();
        });

        // Jalons
        $('#btn-add-jalon').on('click', function() {
            addJalonRow();
        });

        $(document).on('click', '.btn-remove-jalon', function() {
            $(this).closest('tr').remove();
        });

        // Mise à jour des listes de références quand on change un libellé
        $(document).on('change', '.jalon-libelle', function() {
            updateJalonReferencesAll();
        });

    }

    function updateJalonReferencesAll() {
        // Récupérer les jalons actuels depuis le formulaire
        var currentJalons = [];
        $('#jalons-list tr').each(function(idx) {
            currentJalons.push({
                id: $(this).attr('data-jalon-id') || '_idx_' + idx,
                libelle: $(this).find('.jalon-libelle').val() || '(sans libellé)',
                row: $(this)
            });
        });

        // Mettre à jour tous les selects de références
        $('#jalons-list tr').each(function(idx) {
            var $row = $(this);
            var currentId = $row.attr('data-jalon-id') || '_idx_' + idx;
            var $refSelect = $row.find('.jalon-reference');
            var currentRefValue = $refSelect.val();

            // Reconstruire le select
            $refSelect.empty();
            $refSelect.append('<option value="">-- Aucune référence --</option>');

            currentJalons.forEach(function(j) {
                // Pas soi-même
                if (j.id !== currentId) {
                    $refSelect.append(
                        '<option value="' + j.id + '"' + (currentRefValue == j.id ? ' selected' : '') + '>' + j.libelle + '</option>'
                    );
                }
            });
        });
    }

    function loadProjetInModal(projet, gradients, jalons) {
        $('#projet-titre').val(projet.titre);
        $('#projet-date-debut').val(convertToFrench(projet.date_debut));
        $('#projet-date-fin').val(convertToFrench(projet.date_fin));

        // Stocker les listes pour les références ultérieures
        PDC_CURRENT_JALONS = jalons;
        PDC_CURRENT_GRADIENTS = gradients;

        // Gradients
        $('#gradients-list').empty();
        gradients.forEach(function(g) {
            addGradientRow(g);
        });

        // Jalons
        $('#jalons-list').empty();
        jalons.forEach(function(j) {
            addJalonRow(j);
        });
    }

    function addGradientRow(data) {
        var date = data ? convertToFrench(data.date_gradient) : '';
        var couleur = data ? data.couleur : 'vert';

        var $tr = $('<tr></tr>');
        $tr.append('<td><input type="text" class="form-control gradient-date" value="' + date + '" required></td>');
        $tr.append('<td><select class="form-control gradient-couleur">' +
            '<option value="vert"' + (couleur === 'vert' ? ' selected' : '') + '>Vert</option>' +
            '<option value="jaune"' + (couleur === 'jaune' ? ' selected' : '') + '>Jaune</option>' +
            '<option value="orange"' + (couleur === 'orange' ? ' selected' : '') + '>Orange</option>' +
            '<option value="rouge"' + (couleur === 'rouge' ? ' selected' : '') + '>Rouge</option>' +
            '</select></td>');
        $tr.append('<td><button type="button" class="btn btn-sm btn-danger btn-remove-gradient"><i class="fa-solid fa-trash-can"></i></button></td>');
        $('#gradients-list').append($tr);

        // Initialiser le datepicker pour le nouveau champ
        $tr.find('.gradient-date').datepicker();
    }

    function addJalonRow(data) {
        var id = data ? data.id : null;
        var date = data ? convertToFrench(data.date_jalon) : '';
        var couleur = data ? data.couleur : 'vert';
        var libelle = data ? data.libelle : '';
        var refId = data ? data.jalon_reference_id : null;

        var $tr = $('<tr></tr>');
        // Stocker l'ID du jalon dans un attribut data pour la sauvegarde
        $tr.attr('data-jalon-id', id || '');
        $tr.append('<td><input type="text" class="form-control jalon-date" value="' + date + '" required></td>');
        $tr.append('<td><select class="form-control jalon-couleur">' +
            '<option value="vert"' + (couleur === 'vert' ? ' selected' : '') + '>Vert</option>' +
            '<option value="jaune"' + (couleur === 'jaune' ? ' selected' : '') + '>Jaune</option>' +
            '<option value="orange"' + (couleur === 'orange' ? ' selected' : '') + '>Orange</option>' +
            '<option value="rouge"' + (couleur === 'rouge' ? ' selected' : '') + '>Rouge</option>' +
            '</select></td>');
        $tr.append('<td><input type="text" class="form-control jalon-libelle" value="' + libelle + '"></td>');

        var $refSelect = $('<select class="form-control jalon-reference"></select>');
        $refSelect.append('<option value="">-- Aucune référence --</option>');
        if (PDC_CURRENT_JALONS && PDC_CURRENT_JALONS.length > 0) {
            PDC_CURRENT_JALONS.forEach(function(j) {
                if (data && j.id == data.id) return; // Pas soi-même
                $refSelect.append('<option value="' + j.id + '"' + (refId == j.id ? ' selected' : '') + '>' + j.libelle + '</option>');
            });
        }
        var $refTd = $('<td></td>').append($refSelect);
        $tr.append($refTd);

        $tr.append('<td><button type="button" class="btn btn-sm btn-danger btn-remove-jalon"><i class="fa-solid fa-trash-can"></i></button></td>');
        $('#jalons-list').append($tr);

        // Initialiser le datepicker pour le nouveau champ
        $tr.find('.jalon-date').datepicker();
    }

    function saveProjet() {
        var titre = $('#projet-titre').val().trim();
        var dateDebut = convertToISO($('#projet-date-debut').val());
        var dateFin = convertToISO($('#projet-date-fin').val());

        if (!titre || !dateDebut || !dateFin) {
            alert('Tous les champs sont requis.');
            return;
        }

        // Gradients
        var gradients = [];
        $('#gradients-list tr').each(function() {
            var date = convertToISO($(this).find('.gradient-date').val());
            var couleur = $(this).find('.gradient-couleur').val();
            if (date && couleur) {
                gradients.push({ date: date, couleur: couleur });
            }
        });

        // Jalons
        var jalons = [];
        $('#jalons-list tr').each(function() {
            var date = convertToISO($(this).find('.jalon-date').val());
            var couleur = $(this).find('.jalon-couleur').val();
            var libelle = $(this).find('.jalon-libelle').val();
            var refId = $(this).find('.jalon-reference').val();
            var jalonId = $(this).attr('data-jalon-id');
            if (date) {
                // Si décalé, vérifier que la référence est valide
                var jalonObj = {
                    date: date,
                    couleur: couleur,
                    libelle: libelle,
                    jalon_reference_id: (refId && refId !== '') ? refId : null,
                };
                // Envoyer l'ID si le jalon existe en BD (n'est pas nouveau)
                if (jalonId) {
                    jalonObj.id = jalonId;
                }
                jalons.push(jalonObj);
            }
        });

        $.post(PDC.appUrl + '/api.php', {
            action: 'update_projet',
            id: PDC.currentProjetId,
            titre: titre,
            date_debut: dateDebut,
            date_fin: dateFin,
            gradients: JSON.stringify(gradients),
            jalons: JSON.stringify(jalons),
        }, function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur : ' + data.error);
            }
        }, 'json');
    }

    // ---- Barre d'outils ----
    function initToolbar() {
        // Générer un lien de partage
        $('#btn-share').on('click', function() {
            var params = 'niveau=' + PDC.niveau;
            if (PDC.id) params += '&id=' + PDC.id;
            params += '&date_debut=' + PDC.dateDebut + '&date_fin=' + PDC.dateFin;

            $.post(PDC.appUrl + '/api.php', {
                action: 'create_share_link',
                url_params: params,
            }, function(data) {
                if (data.success) {
                    $('#share-url').val(data.url);
                    $('#modal-share').modal('show');
                } else {
                    alert('Erreur : ' + data.error);
                }
            }, 'json');
        });

        $('#btn-copy-share').on('click', function() {
            var $input = $('#share-url');
            $input.select();
            document.execCommand('copy');
            $(this).find('i').removeClass('fa-clipboard').addClass('fa-check');
            setTimeout(function() {
                $('#btn-copy-share i').removeClass('fa-check').addClass('fa-clipboard');
            }, 2000);
        });

        // Export PDF
        $('#btn-export-pdf').on('click', function() {
            var params = 'niveau=' + PDC.niveau;
            if (PDC.id) params += '&id=' + PDC.id;
            params += '&date_debut=' + PDC.dateDebut + '&date_fin=' + PDC.dateFin;
            window.open(PDC.appUrl + '/export_pdf.php?' + params, '_blank');
        });
    }

})(jQuery);