<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-graduation-cap"></i>
            <span>Gestion Présence</span>
        </div>
        <div class="user-info">
            <span class="user-role"><?php echo $_SESSION['user_role'] === 'admin' ? 'Administrateur' : 'Enseignant'; ?></span>
        </div>
    </div>
    <ul>
        <li><a href="accueil.php" <?php echo basename($_SERVER['PHP_SELF']) === 'accueil.php' ? 'class="active"' : ''; ?>><i class="fas fa-home"></i> Accueil</a></li>
        
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <!-- Options réservées aux administrateurs -->
        <li><a href="etudiants.php" <?php echo basename($_SERVER['PHP_SELF']) === 'etudiants.php' ? 'class="active"' : ''; ?>><i class="fas fa-user-graduate"></i> Étudiants</a></li>
        <li><a href="enseignants.php" <?php echo basename($_SERVER['PHP_SELF']) === 'enseignants.php' ? 'class="active"' : ''; ?>><i class="fas fa-chalkboard-teacher"></i> Enseignants</a></li>
        <li><a href="cours.php" <?php echo basename($_SERVER['PHP_SELF']) === 'cours.php' ? 'class="active"' : ''; ?>><i class="fas fa-book"></i> Tous les Cours</a></li>
        <?php else: ?>
        <!-- Options pour les enseignants -->
        <li><a href="mes_cours.php" <?php echo basename($_SERVER['PHP_SELF']) === 'mes_cours.php' ? 'class="active"' : ''; ?>><i class="fas fa-book"></i> Mes Cours</a></li>
        <?php endif; ?>
        
        <!-- Options communes -->
        <li><a href="presence.php" <?php echo basename($_SERVER['PHP_SELF']) === 'presence.php' ? 'class="active"' : ''; ?>><i class="fas fa-clipboard-check"></i> Marquer Présence</a></li>
        <li><a href="generer_qr.php" <?php echo basename($_SERVER['PHP_SELF']) === 'generer_qr.php' ? 'class="active"' : ''; ?>><i class="fas fa-qrcode"></i> QR Code Présence</a></li>
        <li><a href="rapports.php" <?php echo basename($_SERVER['PHP_SELF']) === 'rapports.php' ? 'class="active"' : ''; ?>><i class="fas fa-chart-bar"></i> Rapports</a></li>
        
        <li><a href="gestion_justifications.php" <?php echo basename($_SERVER['PHP_SELF']) === 'gestion_justifications.php' ? 'class="active"' : ''; ?>><i class="fas fa-file-medical-alt"></i> Gestion Justifications</a></li>
        
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <li><a href="etudiants_exclus.php" <?php echo basename($_SERVER['PHP_SELF']) === 'etudiants_exclus.php' ? 'class="active"' : ''; ?>><i class="fas fa-user-slash"></i> Étudiants Exclus</a></li>
        <?php endif; ?>
        
        <li><a href="justifier_absence.php" target="_blank"><i class="fas fa-clipboard-list"></i> Justification d'absence</a></li>
        
        <li class="sidebar-divider"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
    </ul>
</div>
