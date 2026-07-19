/* ============================================================
   PDC — Plan de Charge — Script principal
   ============================================================ */

(function($) {
    'use strict';

    // Variables pour stocker l'état des modales
    var PDC_CURRENT_JALONS = [];
    var PDC_CURRENT_GRADIENTS = [];
    var PDC_PENDING_JALON_ID = null;
    var PDC_PENDING_GRADIENT_INDEX = null;
    var PDC_PENDING_TAB_INDEX = 0;
    var PDC_PENDING_FOCUS_SELECTOR = null;

    // ---- Initialisation ----
    $(document).ready(function() {
        initSidebarToggle();
        initTabs();
        initDatepickers();
        initCommentEditors();
        initFrises();
        initDragDrop();
        initModales();
        initToolbar();
    });

    function initSidebarToggle() {
        var $layout = $('#pdc-page-layout');
        var $toggle = $('#btn-toggle-sidebar');
        if (!$layout.length || !$toggle.length) {
            return;
        }

        var storageKey = 'pdc.sidebar.collapsed';
        var applyState = function(collapsed) {
            $layout.toggleClass('is-sidebar-collapsed', collapsed);
            $toggle.attr('aria-expanded', collapsed ? 'false' : 'true');
        };

        var isCollapsed = false;
        try {
            isCollapsed = window.localStorage && window.localStorage.getItem(storageKey) === '1';
        } catch (e) {
            isCollapsed = false;
        }

        applyState(isCollapsed);

        $toggle.on('click', function() {
            var collapsed = !$layout.hasClass('is-sidebar-collapsed');
            applyState(collapsed);

            try {
                if (window.localStorage) {
                    window.localStorage.setItem(storageKey, collapsed ? '1' : '0');
                }
            } catch (e) {
                // Ignore storage failures.
            }
        });
    }

    function initCommentEditors() {
        if (typeof tinymce === 'undefined') {
            console.warn('TinyMCE n\'est pas chargé. Les champs de commentaire seront des textarea simples.');
            return;
        }

        tinymce.init({
            selector: '#new-projet-commentaire, #projet-commentaire, #domaine-commentaire',
            license_key: 'gpl',
            promotion: false,
            skin: true,
            content_css: false,
            menubar: false,
            branding: false,
            statusbar: false,
            height: 180,
            plugins: '',
            toolbar: 'undo redo | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | blockquote | removeformat',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
        });
    }

    function setCommentFieldValue(selector, value) {
        var content = value || '';

        if (typeof tinymce !== 'undefined') {
            var targetId = selector.replace('#', '');
            var editor = tinymce.get(targetId);
            if (editor) {
                editor.setContent(content);
                return;
            }
        }

        $(selector).val(content);
    }

    function getCommentFieldValue(selector) {
        if (typeof tinymce !== 'undefined') {
            var targetId = selector.replace('#', '');
            var editor = tinymce.get(targetId);
            if (editor) {
                return editor.getContent();
            }
        }

        return $(selector).val();
    }

    function escapeHtml(value) {
        return $('<div></div>').text(value || '').html();
    }

    function buildJalonTooltipContent(jalon, refJalon) {
        var title = jalon && jalon.libelle ? jalon.libelle : 'Jalon';
        var date = jalon && jalon.date_jalon ? convertToFrench(jalon.date_jalon) : '';
        var reportText = 'Aucun report';
        var commentaire = jalon && jalon.commentaire ? jalon.commentaire : '';

        if (refJalon) {
            reportText = 'Report depuis ' + escapeHtml(refJalon.libelle || 'Jalon précédent') + ' (' + escapeHtml(convertToFrench(refJalon.date_jalon)) + ')';
        }

        return '' +
            '<div class="pdc-jalon-tooltip">' +
                '<div class="pdc-jalon-tooltip-title">' + escapeHtml(title) + ' (' + escapeHtml(date) + ')</div>' +
                '<div class="pdc-jalon-tooltip-report">' + reportText + '</div>' +
                '<div class="pdc-jalon-tooltip-comment">' + (commentaire || '<em>Aucun commentaire</em>') + '</div>' +
            '</div>';
    }

    function applyFriseTooltips($scope) {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
            return;
        }

        $scope.find('[data-bs-toggle="tooltip"]').each(function() {
            var existing = bootstrap.Tooltip.getInstance(this);
            if (existing) {
                existing.dispose();
            }

            new bootstrap.Tooltip(this, {
                html: true,
                placement: 'top',
                trigger: 'hover focus',
                container: 'body',
                customClass: 'pdc-jalon-tooltip-popup'
            });
        });
    }

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

    function focusJalonRow(jalonId) {
        if (!jalonId || !$('#projet-tabs').length) {
            return;
        }

        $('#projet-tabs').tabs('option', 'active', 2);

        $('#jalons-list tr').removeClass('pdc-jalon-row-target');

        var $row = $('#jalons-list tr[data-jalon-id="' + jalonId + '"]').first();
        if (!$row.length) {
            return;
        }

        $row.addClass('pdc-jalon-row-target');

        var $field = $row.find('.jalon-libelle').first();
        if ($field.length) {
            $field.trigger('focus');
            if ($field[0] && typeof $field[0].setSelectionRange === 'function') {
                var fieldValue = $field.val() || '';
                $field[0].setSelectionRange(fieldValue.length, fieldValue.length);
            }
        }

        if ($row[0] && typeof $row[0].scrollIntoView === 'function') {
            $row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function focusGradientRow(gradientIndex) {
        if (gradientIndex === null || typeof gradientIndex === 'undefined' || !$('#projet-tabs').length) {
            return;
        }

        $('#projet-tabs').tabs('option', 'active', 1);

        $('#gradients-list tr').removeClass('pdc-gradient-row-target');

        var $row = $('#gradients-list tr[data-gradient-index="' + gradientIndex + '"]').first();
        if (!$row.length) {
            return;
        }

        $row.addClass('pdc-gradient-row-target');

        var $field = $row.find('.gradient-libelle').first();
        if ($field.length) {
            $field.trigger('focus');
            if ($field[0] && typeof $field[0].setSelectionRange === 'function') {
                var fieldValue = $field.val() || '';
                $field[0].setSelectionRange(fieldValue.length, fieldValue.length);
            }
        }

        if ($row[0] && typeof $row[0].scrollIntoView === 'function') {
            $row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function applyPendingProjetEditorFocus() {
        if ($('#projet-tabs').length && PDC_PENDING_TAB_INDEX !== null && typeof PDC_PENDING_TAB_INDEX !== 'undefined') {
            $('#projet-tabs').tabs('option', 'active', PDC_PENDING_TAB_INDEX);
        }

        if (PDC_PENDING_FOCUS_SELECTOR) {
            var $field = $(PDC_PENDING_FOCUS_SELECTOR).first();
            if ($field.length) {
                $field.trigger('focus');
                if ($field[0] && typeof $field[0].setSelectionRange === 'function') {
                    var fieldValue = $field.val() || '';
                    $field[0].setSelectionRange(fieldValue.length, fieldValue.length);
                }
            }
        }

        if (PDC_PENDING_GRADIENT_INDEX !== null) {
            focusGradientRow(PDC_PENDING_GRADIENT_INDEX);
            return;
        }

        focusJalonRow(PDC_PENDING_JALON_ID);
    }

    function openProjetEditor(projetId, target) {
        PDC.currentProjetId = projetId;
        PDC_PENDING_JALON_ID = target && target.jalonId ? target.jalonId : null;
        PDC_PENDING_GRADIENT_INDEX = target && typeof target.gradientIndex !== 'undefined'
            ? target.gradientIndex
            : null;
        PDC_PENDING_TAB_INDEX = target && typeof target.tabIndex !== 'undefined'
            ? target.tabIndex
            : 0;
        PDC_PENDING_FOCUS_SELECTOR = target && target.focusSelector
            ? target.focusSelector
            : null;

        $.post(PDC.appUrl + '/api.php', {
            action: 'get_projet',
            id: projetId,
        }, function(data) {
            if (data.success) {
                loadProjetInModal(data.projet, data.gradients, data.jalons);
                $('#modal-edit-projet').modal('show');
                applyPendingProjetEditorFocus();
            } else {
                alert('Erreur : ' + data.error);
            }
        }, 'json');
    }

    function findNearestGradientIndex($frise, event) {
        var gradients = $frise.data('gradients') || [];
        if (!gradients.length || !$frise.length || !$frise[0]) {
            return null;
        }

        var rect = $frise[0].getBoundingClientRect();
        if (!rect.width) {
            return 0;
        }

        var ratio = (event.clientX - rect.left) / rect.width;
        ratio = Math.max(0, Math.min(1, ratio));

        var periodeDebut = parseISODate($frise.data('periode-debut'));
        var periodeFin = parseISODate($frise.data('periode-fin'));
        var clickedTime = periodeDebut.getTime() + ((periodeFin.getTime() - periodeDebut.getTime()) * ratio);

        var nearestIndex = 0;
        var nearestDistance = Number.MAX_VALUE;

        gradients.forEach(function(gradient, index) {
            var gradientTime = parseISODate(gradient.date_gradient).getTime();
            var distance = Math.abs(gradientTime - clickedTime);
            if (distance < nearestDistance) {
                nearestDistance = distance;
                nearestIndex = index;
            }
        });

        return nearestIndex;
    }

    function renderFrise($frise) {
        $frise.empty();

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
            var isBeforePeriod = dateFin < periodeDebut;
            var isAfterPeriod = dateDebut > periodeFin;

            var $outArrow = $('<div class="pdc-frise-arrow pdc-frise-arrow-outside"></div>');
            $outArrow.css({
                left: '0%',
                width: '100%',
                background: '#d5dde6'
            });

            if (isBeforePeriod) {
                $outArrow.addClass('pdc-frise-arrow-before');
            } else if (isAfterPeriod) {
                $outArrow.addClass('pdc-frise-arrow-after');
            }

            $outArrow.attr('title', convertToFrench(dateDebut.toISOString().substr(0,10)) + ' → ' + convertToFrench(dateFin.toISOString().substr(0,10)));
            $frise.css('min-height', '60px');
            $frise.append($outArrow);
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
                var weekNum2 = ('0' + weekNum).slice(-2);
                var day2 = ('0' + currentDate.getDate()).slice(-2);
                var month2 = ('0' + (currentDate.getMonth() + 1)).slice(-2);
                $week.html('S' + weekNum2 + "/" + year.toString().substr(2,2) + (currentDate.getDay() != 1 ? "" : "<br>" + day2 + "/" + month2));
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
            if (jalon.id) {
                $jalon.attr('data-jalon-id', jalon.id);
            }

            var $triangle = $('<div class="pdc-jalon-triangle pdc-couleur-' + jalon.couleur + '"></div>');
            
            var $libelle = $('<div class="pdc-jalon-libelle"></div>');
            
            var libelleTronque = jalon.libelle.length > 15 ? jalon.libelle.substr(0, 15) + '…' : jalon.libelle;
            $libelle.text(libelleTronque);
            var $jalonTitle = jalon.libelle + "("  + convertToFrench(jalon.date_jalon) + ')';
            var refJalon = null;
            
            // Pointillé si décalé
            if (jalon.jalon_reference_id) {
                refJalon = jalons.find(function(j) { 
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
                $jalon.attr({
                    'data-bs-toggle': 'tooltip',
                    'data-bs-html': 'true',
                    'data-bs-title': buildJalonTooltipContent(jalon, refJalon)
                });
                $jalon.append($triangle).append($libelle);
                $frise.css('min-height', ($nbrPointilles * ( $hauteurDecalage + 5)) + 60 + 'px'); // S'assurer que la frise est assez haute pour les jalons
                $frise.append($jalon);
            }
        });

        applyFriseTooltips($frise);
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
            handle: '.pdc-drag-handle-projet',
            cancel: '.pdc-edit-projet, button, input, select, textarea, a',
            placeholder: 'pdc-projet-placeholder',
            tolerance: 'pointer',
            distance: 5,
            start: function(event, ui) {
                var $item = ui.item;
                $item.data('old-domaine-id', parseInt($item.parent().data('domaine-id') || 0, 10));
                $item.data('old-index', $item.index());
            },
            stop: function(event, ui) {
                var $item = ui.item;
                var projetId = ui.item.data('projet-id');
                var $newList = ui.item.closest('.pdc-projets-list');
                var newDomaineId = parseInt($newList.data('domaine-id') || 0, 10);

                var oldDomaineId = parseInt($item.data('old-domaine-id') || 0, 10);
                var oldIndex = parseInt($item.data('old-index') || 0, 10);
                var newIndex = $item.index();

                if (oldDomaineId === newDomaineId && oldIndex === newIndex) {
                    return;
                }

                // Réorganiser les ordres du domaine cible
                var ordresCible = {};
                $newList.find('.pdc-projet').each(function(idx) {
                    ordresCible[$(this).data('projet-id')] = idx;
                });

                // Si changement de domaine
                if (oldDomaineId !== newDomaineId) {
                    $.ajax({
                        url: PDC.appUrl + '/api.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                        action: 'move_projet',
                        projet_id: projetId,
                        domaine_id: newDomaineId
                        }
                    }).done(function(moveData) {
                        if (!moveData || !moveData.success) {
                            alert('Erreur de déplacement : ' + (moveData && moveData.error ? moveData.error : 'Erreur inconnue'));
                            location.reload();
                            return;
                        }

                        $.ajax({
                            url: PDC.appUrl + '/api.php',
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'reorder_projets',
                                ordres: ordresCible
                            }
                        }).done(function(reorderData) {
                            if (!reorderData || !reorderData.success) {
                                alert('Erreur de réorganisation : ' + (reorderData && reorderData.error ? reorderData.error : 'Erreur inconnue'));
                                location.reload();
                            }
                        }).fail(function() {
                            alert('Erreur de réorganisation des projets.');
                            location.reload();
                        });
                    }).fail(function() {
                        alert('Erreur de déplacement du projet.');
                        location.reload();
                    });
                    return;
                }

                // Même domaine: simple réorganisation
                $.ajax({
                    url: PDC.appUrl + '/api.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'reorder_projets',
                        ordres: ordresCible
                    }
                }).done(function(reorderData) {
                    if (!reorderData || !reorderData.success) {
                        alert('Erreur de réorganisation : ' + (reorderData && reorderData.error ? reorderData.error : 'Erreur inconnue'));
                        location.reload();
                    }
                }).fail(function() {
                    alert('Erreur de réorganisation des projets.');
                    location.reload();
                });
            }
        });

        // Domaines draggables
        $('#domaines-container').sortable({
            items: '.pdc-domaine',
            handle: '.pdc-drag-handle-domaine',
            cancel: '.pdc-edit-domaine, .pdc-add-projet, button, input, select, textarea, a',
            tolerance: 'pointer',
            distance: 5,
            start: function(event, ui) {
                var $item = ui.item;
                $item.data('old-index', $item.index());
            },
            stop: function(event, ui) {
                var $item = ui.item;
                var oldIndex = parseInt($item.data('old-index') || 0, 10);
                var newIndex = $item.index();
                if (oldIndex === newIndex) {
                    return;
                }

                var ordres = {};
                $('#domaines-container .pdc-domaine').each(function(idx) {
                    ordres[$(this).data('domaine-id')] = idx;
                });
                $.ajax({
                    url: PDC.appUrl + '/api.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'reorder_domaines',
                        ordres: ordres
                    }
                }).done(function(data) {
                    if (!data || !data.success) {
                        alert('Erreur de réorganisation : ' + (data && data.error ? data.error : 'Erreur inconnue'));
                        location.reload();
                    }
                }).fail(function() {
                    alert('Erreur de réorganisation des domaines.');
                    location.reload();
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
            var commentaire = $(this).closest('.pdc-domaine').data('domaine-commentaire') || '';
            
            PDC.currentDomaineId = domaineId;
            $('#domaine-nom').val(nom);
            setCommentFieldValue('#domaine-commentaire', commentaire);
            $('#domaine-hierarchie-id').val(String(parseInt(PDC.hierarchieId, 10) || ''));
            $('#modal-edit-domaine').modal('show');
        });

        // Éditer un domaine au double-clic sur le titre
        $(document).on('dblclick', '.pdc-domaine-titre', function(e) {
            if ($(e.target).closest('button, a, input, select, textarea').length) {
                return;
            }

            e.preventDefault();
            var $btn = $(this).find('.pdc-edit-domaine').first();
            if ($btn.length) {
                $btn.trigger('click');
            }
        });

        $('#btn-save-domaine').on('click', function() {
            var nom = $('#domaine-nom').val().trim();
            var commentaire = (getCommentFieldValue('#domaine-commentaire') || '').trim();
            var hierarchieId = parseInt($('#domaine-hierarchie-id').val(), 10);
            var currentHierarchieId = parseInt(PDC.hierarchieId, 10);
            var hasMoved = !isNaN(currentHierarchieId) && currentHierarchieId > 0 && currentHierarchieId !== hierarchieId;
            var selectedHierarchyLabel = $('#domaine-hierarchie-id option:selected').text().replace(/\s+/g, ' ').trim();
            if (!nom) {
                alert('Le nom est requis.');
                return;
            }
            if (isNaN(hierarchieId) || hierarchieId <= 0) {
                alert('Le niveau hiérarchique est requis.');
                return;
            }
            $.post(PDC.appUrl + '/api.php', {
                action: 'update_domaine',
                id: PDC.currentDomaineId,
                nom: nom,
                commentaire: commentaire,
                hierarchie_id: hierarchieId,
            }, function(data) {
                if (data.success) {
                    if (hasMoved) {
                        alert('Domaine déplacé vers le niveau : ' + selectedHierarchyLabel);
                    }
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
            var currentHierarchieId = parseInt($('#btn-add-domaine').data('hierarchie-id'), 10);
            if (isNaN(currentHierarchieId)) {
                currentHierarchieId = parseInt(PDC.hierarchieId, 10);
            }
            if (!nom) {
                alert('Le nom est requis.');
                return;
            }
            if (isNaN(currentHierarchieId) || currentHierarchieId <= 0) {
                alert('Sélectionnez un niveau de hiérarchie avant de créer un domaine.');
                return;
            }
            $.post(PDC.appUrl + '/api.php', {
                action: 'create_domaine',
                hierarchie_id: currentHierarchieId,
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
                domaine_id: PDC.currentDomaineId,
                titre: titre,
                date_debut: dateDebut,
                date_fin: dateFin,
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
            openProjetEditor(projetId, { tabIndex: 0 });
        });

        // Éditer un projet au double-clic sur le titre
        $(document).on('dblclick', '.pdc-projet-titre', function(e) {
            if ($(e.target).closest('button, a, input, select, textarea').length) {
                return;
            }

            e.preventDefault();
            var projetId = $(this).closest('.pdc-projet').data('projet-id');
            if (projetId) {
                openProjetEditor(projetId, { tabIndex: 0, focusSelector: '#projet-titre' });
            }
        });

        // Éditer un jalon au double-clic dans la frise
        $(document).on('dblclick', '.pdc-jalon', function(e) {
            if ($(e.target).closest('button, a, input, select, textarea').length) {
                return;
            }

            e.preventDefault();

            var jalonId = $(this).attr('data-jalon-id') || null;
            var projetId = $(this).closest('.pdc-frise').data('projet-id');
            if (!projetId) {
                return;
            }

            openProjetEditor(projetId, { jalonId: jalonId, tabIndex: 2 });
        });

        // Éditer un jalon au double-clic sur une ligne du tableau sous la frise
        $(document).on('dblclick', '.pdc-jalons-list tr', function(e) {
            if ($(e.target).closest('button, a, input, select, textarea').length) {
                return;
            }

            e.preventDefault();

            var jalonId = $(this).attr('data-jalon-id') || null;
            var projetId = $(this).closest('.pdc-jalons-table-container').data('projet-id');
            if (!projetId) {
                return;
            }

            openProjetEditor(projetId, { jalonId: jalonId, tabIndex: 2 });
        });

        // Éditer le gradient le plus proche au double-clic sur la frise
        $(document).on('dblclick', '.pdc-frise-arrow', function(e) {
            if ($(e.target).closest('.pdc-jalon').length) {
                return;
            }

            e.preventDefault();

            var $frise = $(this).closest('.pdc-frise');
            var projetId = $frise.data('projet-id');
            if (!projetId) {
                return;
            }

            openProjetEditor(projetId, {
                gradientIndex: findNearestGradientIndex($frise, e)
            });
        });

        // Éditer un commentaire au double-clic
        $(document).on('dblclick', '.pdc-commentaire', function(e) {
            if ($(e.target).closest('button, a, input, select, textarea').length) {
                return;
            }

            e.preventDefault();

            var $projet = $(this).closest('.pdc-projet');
            if ($projet.length) {
                var projetId = $projet.data('projet-id');
                if (projetId) {
                    openProjetEditor(projetId, { tabIndex: 0, focusSelector: '#projet-commentaire' });
                }
                return;
            }

            var $domaine = $(this).closest('.pdc-domaine');
            if ($domaine.length) {
                var $domaineBtn = $domaine.find('.pdc-edit-domaine').first();
                if ($domaineBtn.length) {
                    $domaineBtn.trigger('click');
                }
            }
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

        // Jalons - Afficher/Masquer le tableau
        $(document).on('click', '.pdc-jalons-toggle-btn', function() {
            var projetId = $(this).data('projet-id');
            var $container = $('.pdc-jalons-table-container[data-projet-id="' + projetId + '"]');
            var $btn = $(this);
            var $icon = $btn.find('i');
            
            if ($container.is(':visible')) {
                $container.slideUp(300);
                $btn.removeClass('active');
                $icon.removeClass('fa-angles-up').addClass('fa-angles-down');
            } else {
                // Remplir le tableau avec les jalons triés
                var $frise = $('.pdc-frise[data-projet-id="' + projetId + '"]');
                var jalons = $frise.data('jalons') || [];
                
                // Trier les jalons par date
                jalons = jalons.sort(function(a, b) {
                    return new Date(a.date_jalon) - new Date(b.date_jalon);
                });
                
                // Remplir le tableau
                var $tbody = $container.find('.pdc-jalons-list');
                $tbody.empty();
                jalons.forEach(function(jalon) {
                    var date = convertToFrench(jalon.date_jalon);
                    var couleur = jalon.couleur || 'vert';
                    var libelle = jalon.libelle || '';
                    var commentaire = jalon.commentaire || '';
                    var jalonRefHtml = '--';

                    var makeBadge = function(text, colorCode) {
                        var badgeText = text ? escapeHtml(text) : '(vide)';
                        var color = colorCode || 'vert';
                        var textClass = color === 'jaune' ? ' is-light' : '';
                        return '<span class="pdc-jalon-color-badge' + textClass + '" style="background-color: var(--pdc-' + color + ')">' + badgeText + '</span>';
                    };

                    if (jalon.jalon_reference_id) {
                        var refJalon = jalons.find(function(j) { return j.id == jalon.jalon_reference_id; });
                        if (refJalon) {
                            jalonRefHtml = makeBadge(refJalon.libelle || '(vide)', refJalon.couleur || 'vert');
                        } else {
                            jalonRefHtml = escapeHtml(String(jalon.jalon_reference_id));
                        }
                    }
                    
                    var $tr = $('<tr></tr>');
                    if (jalon.id) {
                        $tr.attr('data-jalon-id', jalon.id);
                    }
                    $tr.append('<td>' + date + '</td>');
                    $tr.append('<td>' + makeBadge(libelle, couleur) + '</td>');
                    $tr.append('<td>' + jalonRefHtml + '</td>');
                    $tr.append('<td>' + (commentaire ? commentaire : '') + '</td>');
                    $tbody.append($tr);
                });
                
                $container.slideDown(300);
                $btn.addClass('active');
                $icon.removeClass('fa-angles-down').addClass('fa-angles-up');
            }
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
        setCommentFieldValue('#projet-commentaire', projet.commentaire || '');

        // Stocker les listes pour les références ultérieures
        PDC_CURRENT_JALONS = jalons;
        PDC_CURRENT_GRADIENTS = gradients;

        // Gradients
        $('#gradients-list').empty();
        gradients.forEach(function(g, index) {
            addGradientRow(g, index);
        });

        // Jalons
        $('#jalons-list').empty();
        jalons.forEach(function(j) {
            addJalonRow(j);
        });

        applyPendingProjetEditorFocus();
    }

    function addGradientRow(data, gradientIndex) {
        var date = data ? convertToFrench(data.date_gradient) : '';
        var couleur = data ? data.couleur : 'vert';
        var libelle = data ? data.libelle : '';

        var $tr = $('<tr></tr>');
        if (typeof gradientIndex !== 'undefined' && gradientIndex !== null) {
            $tr.attr('data-gradient-index', gradientIndex);
        }
        $tr.append('<td><input type="text" class="form-control gradient-date" value="' + date + '" required></td>');
        $tr.append('<td><select class="form-control gradient-couleur">' +
            '<option value="vert"' + (couleur === 'vert' ? ' selected' : '') + '>Vert</option>' +
            '<option value="jaune"' + (couleur === 'jaune' ? ' selected' : '') + '>Jaune</option>' +
            '<option value="orange"' + (couleur === 'orange' ? ' selected' : '') + '>Orange</option>' +
            '<option value="rouge"' + (couleur === 'rouge' ? ' selected' : '') + '>Rouge</option>' +
            '</select></td>');
        $tr.append('<td><input type="text" class="form-control gradient-libelle" value="' + libelle + '"></td>');
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
        var commentaire = data ? (data.commentaire || '') : '';
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
        var $commentaireField = $('<textarea class="form-control jalon-commentaire" rows="2"></textarea>');
        $commentaireField.val(commentaire);
        $tr.append($('<td></td>').append($commentaireField));

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
        var commentaire = (getCommentFieldValue('#projet-commentaire') || '').trim();

        if (!titre || !dateDebut || !dateFin) {
            alert('Tous les champs sont requis.');
            return;
        }

        // Gradients
        var gradients = [];
        $('#gradients-list tr').each(function() {
            var date = convertToISO($(this).find('.gradient-date').val());
            var couleur = $(this).find('.gradient-couleur').val();
            var libelle = $(this).find('.gradient-libelle').val();
            if (date && couleur) {
                gradients.push({ date: date, couleur: couleur, libelle: libelle });
            }
        });

        // Jalons
        var jalons = [];
        $('#jalons-list tr').each(function() {
            var date = convertToISO($(this).find('.jalon-date').val());
            var couleur = $(this).find('.jalon-couleur').val();
            var libelle = $(this).find('.jalon-libelle').val();
            var commentaireJalon = $(this).find('.jalon-commentaire').val();
            var refId = $(this).find('.jalon-reference').val();
            var jalonId = $(this).attr('data-jalon-id');
            if (date) {
                // Si décalé, vérifier que la référence est valide
                var jalonObj = {
                    date: date,
                    couleur: couleur,
                    libelle: libelle,
                    commentaire: commentaireJalon,
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
            commentaire: commentaire,
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
        // Export PDF
        $('#btn-export-pdf').on('click', function() {
            if (!PDC.id || parseInt(PDC.id, 10) <= 0) {
                alert('Sélectionnez un niveau de hiérarchie à exporter.');
                return;
            }

            $('#export-include-gradients').prop('checked', true);
            $('#export-include-jalons').prop('checked', true);
            $('#modal-export-pdf-options').modal('show');
        });

        $('#btn-confirm-export-pdf').on('click', function() {
            if (!PDC.id || parseInt(PDC.id, 10) <= 0) {
                alert('Sélectionnez un niveau de hiérarchie à exporter.');
                return;
            }

            var includeGradients = $('#export-include-gradients').is(':checked');
            var includeJalons = $('#export-include-jalons').is(':checked');

            var params = 'niveau=hierarchie&id=' + PDC.id;
            params += '&date_debut=' + PDC.dateDebut + '&date_fin=' + PDC.dateFin;
            params += '&include_gradients=' + (includeGradients ? '1' : '0');
            params += '&include_jalons=' + (includeJalons ? '1' : '0');

            $('#modal-export-pdf-options').modal('hide');
            window.open(PDC.appUrl + '/export_pdf.php?' + params, '_blank');
        });
    }

})(jQuery);