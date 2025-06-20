/**
 * Script principal pour le système de gestion de présence
 * Version simplifiée pour l'explication
 */

// Attendre que la page soit chargée
$(document).ready(function() {
    // Masquer les alertes automatiquement après 3 secondes
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);
    
    // Confirmation avant suppression
    $('.btn-delete').click(function() {
        return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');
    });
    
    // Marquer tous les étudiants présents
    $('#select-all-present').click(function(e) {
        e.preventDefault();
        $('input[value="present"]').prop('checked', true);
    });
    
    // Marquer tous les étudiants absents
    $('#select-all-absent').click(function(e) {
        e.preventDefault();
        $('input[value="absent"]').prop('checked', true);
    });
    
    // Soumettre le formulaire quand on change de cours ou de date
    $('#cours-select, #date-presence').change(function() {
        if ($('#cours-select').val() && $('#date-presence').val()) {
            $('#filter-presence-form').submit();
        }
    });
    
    // Validation simple des formulaires
    $('.needs-validation').submit(function(event) {
        if (this.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Effet visuel sur les cartes du dashboard
    $('.dashboard-card').hover(
        function() {
            $(this).css('transform', 'scale(1.05)');
        },
        function() {
            $(this).css('transform', 'scale(1)');
        }
    );
});

/**
 * Fonction pour exporter un tableau en PDF
 * @param {string} tableId - ID du tableau à exporter
 * @param {string} filename - Nom du fichier PDF
 */
function exportTableToPDF(tableId, filename) {
    // Fonction simplifiée d'exemple
    alert('Le tableau ' + tableId + ' serait exporté sous le nom ' + filename);
}
