/* =============================================================================
   TIR118 - JavaScript personnalisé
   ============================================================================= */
$(document).ready(function () {

    // Fermeture automatique des alertes flash après 5 s
    setTimeout(function () {
        $('.alert.alert-success, .alert.alert-info').fadeOut('slow');
    }, 5000);

    // Confirmation de suppression via data-confirm
    $(document).on('submit', 'form[data-confirm]', function (e) {
        var msg = $(this).data('confirm') || 'Êtes-vous sûr ?';
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });

    // Activation des tooltips Bootstrap
    $('[data-toggle="tooltip"]').tooltip();

    // Activation des popovers Bootstrap
    $('[data-toggle="popover"]').popover();

    // Navigation calendrier vers ancre mois (calendar.php)
    var anchor = window.location.hash;
    if (anchor && anchor.match(/^#\d{4}-\d{2}$/)) {
        var parts = anchor.substr(1).split('-');
        var year  = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10) - 1;
        // FullCalendar gotoDate si initialisé
        if ($('#calendar').data('fullCalendar')) {
            var d = new Date(year, month, 1);
            $('#calendar').fullCalendar('gotoDate', d);
        }
    }

});
