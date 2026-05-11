<?php
/**
 * Point d’entrée : imports CSV (promotions, étudiants).
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

require_auth();
require_admin();

include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fas fa-file-csv"></i> Imports CSV</h1>
    <p class="text-muted">Collecte de données en masse — encodage UTF-8, séparateur <code>;</code> ou <code>,</code>.</p>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Promotions (niveau · filière)</h2>
                    <p class="small">Créer des groupes : code, nom, niveau LMD, filière.</p>
                    <a href="import_promotions_csv.php" class="btn btn-primary">Importer des promotions</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Étudiants</h2>
                    <p class="small">Inscription : nom, prénom, email, code promotion ; optionnel matricule et cours.</p>
                    <a href="import_etudiants_csv.php" class="btn btn-primary">Importer des étudiants</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
