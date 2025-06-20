document.addEventListener('DOMContentLoaded', function() {
    initAlertes();
    initBoutonsSuppression();
    initDateParDefaut();
    initValidationFormulaires();
    initBoutonMenu();
});

function initAlertes() {
    var alertes = document.querySelectorAll('.alert');
    if (alertes.length > 0) {
        setTimeout(function() {
            for (var i = 0; i < alertes.length; i++) {
                if (alertes[i].parentNode) {
                    alertes[i].parentNode.removeChild(alertes[i]);
                }
            }
        }, 5000);
    }
}

function initBoutonsSuppression() {
    var boutonsSuppression = document.querySelectorAll('.btn-delete');
    for (var i = 0; i < boutonsSuppression.length; i++) {
        boutonsSuppression[i].addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        });
    }
}

function initDateParDefaut() {
    var champsDate = document.querySelectorAll('input[type="date"]');
    var aujourdhui = new Date();
    var jour = aujourdhui.getDate().toString().padStart(2, '0');
    var mois = (aujourdhui.getMonth() + 1).toString().padStart(2, '0');
    var annee = aujourdhui.getFullYear();
    var formatDate = annee + '-' + mois + '-' + jour;
    
    for (var i = 0; i < champsDate.length; i++) {
        if (!champsDate[i].value) {
            champsDate[i].value = formatDate;
        }
    }
}

function initValidationFormulaires() {
    var formulaires = document.querySelectorAll('.needs-validation');
    for (var i = 0; i < formulaires.length; i++) {
        formulaires[i].addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
}

function initBoutonMenu() {
    var boutonMenu = document.getElementById('sidebarToggle');
    if (boutonMenu) {
        boutonMenu.addEventListener('click', function() {
            var menuLateral = document.querySelector('.sidebar');
            if (menuLateral.style.display === 'none' || !menuLateral.style.display) {
                menuLateral.style.display = 'block';
            } else {
                menuLateral.style.display = 'none';
            }
        });
    }
}

function updateStudentListForCourse(coursId) {
    if (!coursId) return;
    
    var conteneur = document.getElementById('studentAttendanceList');
    if (!conteneur) return;
    
    conteneur.innerHTML = '<div class="alert alert-info">Chargement des étudiants...</div>';
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_students.php?cours_id=' + coursId, true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var donnees = JSON.parse(xhr.responseText);
                afficherListeEtudiants(donnees, conteneur);
            } catch (e) {
                conteneur.innerHTML = '<div class="alert alert-danger">Erreur lors de l\'analyse des données</div>';
            }
        } else {
            conteneur.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des étudiants</div>';
        }
    };
    
    xhr.onerror = function() {
        conteneur.innerHTML = '<div class="alert alert-danger">Erreur de connexion</div>';
    };
    
    xhr.send();
}

function afficherListeEtudiants(etudiants, conteneur) {
    if (!etudiants || etudiants.length === 0) {
        conteneur.innerHTML = '<div class="alert alert-info">Aucun étudiant inscrit à ce cours.</div>';
        return;
    }
    
    var html = '<div class="list-group">';
    
    for (var i = 0; i < etudiants.length; i++) {
        var etudiant = etudiants[i];
        html += '<div class="list-group-item">';
        html += '<div class="form-check form-check-inline">';
        html += '<input class="form-check-input" type="radio" name="presence[' + etudiant.id + ']" id="present_' + etudiant.id + '" value="1" checked>';
        html += '<label class="form-check-label" for="present_' + etudiant.id + '">Présent</label>';
        html += '</div>';
        html += '<div class="form-check form-check-inline">';
        html += '<input class="form-check-input" type="radio" name="presence[' + etudiant.id + ']" id="absent_' + etudiant.id + '" value="0">';
        html += '<label class="form-check-label" for="absent_' + etudiant.id + '">Absent</label>';
        html += '</div>';
        html += '<span style="margin-left: 15px;">' + etudiant.nom + ' ' + etudiant.prenom + ' (' + etudiant.matricule + ')</span>';
        html += '</div>';
    }
    
    html += '</div>';
    conteneur.innerHTML = html;
}

function filterReports() {
    var formulaire = document.getElementById('reportsFilterForm');
    if (formulaire) {
        formulaire.submit();
    }
}

function marquerTous(status) {
    var radioButtons = document.querySelectorAll('input[type="radio"]');
    
    for (var i = 0; i < radioButtons.length; i++) {
        var radio = radioButtons[i];
        if ((status === 1 && radio.value === '1') || 
            (status === 0 && radio.value === '0')) {
            radio.checked = true;
        }
    }
    
    // Animation pour indiquer la modification
    var container = document.querySelector('.card-body');
    if (container) {
        container.classList.add('highlight-action');
        setTimeout(function() {
            container.classList.remove('highlight-action');
        }, 500);
    }
    
    // Afficher un message de feedback
    var statusText = status === 1 ? 'présents' : 'absents';
    var message = document.createElement('div');
    message.className = 'alert alert-info text-center';
    message.innerHTML = '<i class="fas fa-info-circle"></i> Tous les étudiants ont été marqués comme ' + statusText;
    message.style.position = 'sticky';
    message.style.top = '10px';
    message.style.zIndex = '1000';
    
    var existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.parentNode.removeChild(existingAlert);
    }
    
    container.insertBefore(message, container.firstChild);
    
    setTimeout(function() {
        message.classList.add('fade-out');
        setTimeout(function() {
            if (message.parentNode) {
                message.parentNode.removeChild(message);
            }
        }, 500);
    }, 3000);
}