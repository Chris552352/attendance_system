/**
 * Configuration des graphiques pour le système de gestion de présence
 * Utilise Chart.js
 */

// Couleurs pour les graphiques
const chartColors = {
    present: 'rgba(40, 167, 69, 0.7)',
    absent: 'rgba(220, 53, 69, 0.7)',
    borderPresent: 'rgba(40, 167, 69, 1)',
    borderAbsent: 'rgba(220, 53, 69, 1)',
    months: [
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(255, 205, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)',
        'rgba(201, 203, 207, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(255, 205, 86, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(153, 102, 255, 0.7)'
    ]
};

// Options globales pour Chart.js
Chart.defaults.font.family = "'Segoe UI', 'Helvetica Neue', 'Arial', sans-serif";
Chart.defaults.font.size = 14;
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
Chart.defaults.plugins.tooltip.titleFont.size = 14;
Chart.defaults.plugins.tooltip.bodyFont.size = 13;
Chart.defaults.plugins.tooltip.displayColors = true;
Chart.defaults.plugins.tooltip.boxPadding = 5;

// Initialiser les graphiques une fois le DOM chargé
document.addEventListener('DOMContentLoaded', function() {
    // Graphique circulaire de présence (Dashboard)
    const presenceChartEl = document.getElementById('presenceChart');
    if (presenceChartEl) {
        // Récupérer les données depuis l'attribut data-* (injecté par PHP)
        const totalPresent = parseInt(presenceChartEl.getAttribute('data-present') || 0);
        const totalAbsent = parseInt(presenceChartEl.getAttribute('data-absent') || 0);
        
        new Chart(presenceChartEl, {
            type: 'doughnut',
            data: {
                labels: ['Présences', 'Absences'],
                datasets: [{
                    data: [totalPresent, totalAbsent],
                    backgroundColor: [chartColors.present, chartColors.absent],
                    borderColor: [chartColors.borderPresent, chartColors.borderAbsent],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            boxWidth: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = totalPresent + totalAbsent;
                                const percentage = total ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Graphique de tendance mensuelle (Dashboard)
    const monthlyChartEl = document.getElementById('monthlyChart');
    if (monthlyChartEl) {
        // Données mensuelles depuis l'attribut data-*
        // Format attendu: [{mois: "1", present: 45, absent: 5}, ...]
        const monthlyData = JSON.parse(monthlyChartEl.getAttribute('data-monthly') || '[]');
        
        // Préparer les données pour Chart.js
        const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        const labels = monthlyData.map(item => months[parseInt(item.mois) - 1]);
        const presentData = monthlyData.map(item => parseInt(item.present));
        const absentData = monthlyData.map(item => parseInt(item.absent));
        
        new Chart(monthlyChartEl, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Présences',
                        data: presentData,
                        backgroundColor: chartColors.present,
                        borderColor: chartColors.borderPresent,
                        borderWidth: 1
                    },
                    {
                        label: 'Absences',
                        data: absentData,
                        backgroundColor: chartColors.absent,
                        borderColor: chartColors.borderAbsent,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }
    
    // Graphique de présence par étudiant (Rapports)
    const studentChartEl = document.getElementById('studentChart');
    if (studentChartEl) {
        // Format attendu: [{nom: "Nom Étudiant 1", present: 15, absent: 3}, ...]
        const studentData = JSON.parse(studentChartEl.getAttribute('data-students') || '[]');
        
        const labels = studentData.map(item => item.nom);
        const presentData = studentData.map(item => parseInt(item.present));
        const absentData = studentData.map(item => parseInt(item.absent));
        
        new Chart(studentChartEl, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Présences',
                        data: presentData,
                        backgroundColor: chartColors.present,
                        borderColor: chartColors.borderPresent,
                        borderWidth: 1
                    },
                    {
                        label: 'Absences',
                        data: absentData,
                        backgroundColor: chartColors.absent,
                        borderColor: chartColors.borderAbsent,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        stacked: false,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    y: {
                        stacked: false,
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }
    
    // Graphique de présence par cours (Rapports)
    const courseChartEl = document.getElementById('courseChart');
    if (courseChartEl) {
        // Format attendu: [{nom: "Nom Cours 1", present: 75, absent: 25}, ...]
        const courseData = JSON.parse(courseChartEl.getAttribute('data-courses') || '[]');
        
        const labels = courseData.map(item => item.nom);
        const presentData = courseData.map(item => parseInt(item.present));
        const absentData = courseData.map(item => parseInt(item.absent));
        
        new Chart(courseChartEl, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: presentData,
                    backgroundColor: courseData.map((_, index) => chartColors.months[index % chartColors.months.length]),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = presentData.reduce((a, b) => a + b, 0);
                                const percentage = total ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
