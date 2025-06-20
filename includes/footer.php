            </main>
        </div>
    </div>

    <!-- Pied de page ultra-moderne -->
    <footer class="footer-modern-ultimate student-fade-in">
        <div class="footer-modern-ultimate-bg"></div>
        <div class="footer-modern-glass"></div>
        <div class="footer-modern-separator"></div>
        <div class="footer-content-ultimate">
            <div class="footer-flag-ultimate">
                <span class="flag-star">★</span>
            </div>
            <div class="footer-title-ultimate"><i class="fas fa-graduation-cap"></i> Système de Gestion de Présence</div>
            <nav class="footer-links-ultimate">
                <a href="etudiants.php" title="Étudiants"><i class="fas fa-user-graduate"></i></a>
                <a href="cours.php" title="Cours"><i class="fas fa-book"></i></a>
                <a href="presence.php" title="Présence"><i class="fas fa-clipboard-check"></i></a>
                <a href="rapports.php" title="Rapports"><i class="fas fa-chart-bar"></i></a>
            </nav>
            <div class="footer-social-ultimate">
                <a href="#" title="LinkedIn" class="footer-social-btn"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" title="GitHub" class="footer-social-btn"><i class="fab fa-github"></i></a>
                <a href="#" title="Mail" class="footer-social-btn"><i class="fas fa-envelope"></i></a>
            </div>
            <div class="footer-credits-ultimate">
                <span class="footer-copyright">&copy; <span id="footer-year"></span> Manga Chris</span>
                <span class="footer-powered">| Powered by <i class="fas fa-bolt"></i> Cascade</span>
            </div>
        </div>
    </footer>
    <script>document.getElementById('footer-year').textContent = new Date().getFullYear();</script>
    <style>
    .footer-modern-ultimate {
        position: relative;
        margin-top: 6px;
        padding: 2px 0 1px 0;
        border-radius: 0.5rem 0.5rem 0 0;
        overflow: hidden;
        background: transparent;
        z-index: 10;
        animation: fadeInUpFooter 1.2s cubic-bezier(.42,0,.58,1);
        box-shadow: 0 -8px 48px 0 rgba(171,71,188,0.23);
    }
    .footer-modern-ultimate-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
        background: linear-gradient(120deg, #ab47bc55 0%, #6a1b9a44 100%),
            radial-gradient(circle at 80% 30%, #ab47bc44 0%, transparent 65%),
            radial-gradient(circle at 20% 80%, #6a1b9a33 0%, transparent 60%);
        background-size: 200% 200%;
        animation: footerBgMoveUltimate 18s ease-in-out infinite;
        filter: blur(0.5px);
    }
    @keyframes footerBgMoveUltimate {
        0% { background-position: 0% 50%, 80% 30%, 20% 80%; }
        50% { background-position: 100% 50%, 70% 40%, 30% 70%; }
        100% { background-position: 0% 50%, 80% 30%, 20% 80%; }
    }
    .footer-modern-glass {
        position: absolute;
        inset: 0;
        z-index: 1;
        background: rgba(255,255,255,0.38);
        box-shadow: 0 -6px 32px 0 rgba(171,71,188,0.10);
        backdrop-filter: blur(16px) saturate(1.2);
        border-top: 2.5px solid #ab47bc33;
        border-radius: 2.2rem 2.2rem 0 0;
        pointer-events: none;
    }
    .footer-modern-separator {
        position: absolute;
        left: 50%;
        top: 0;
        transform: translateX(-50%);
        width: 80%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #ab47bc99 40%, #6a1b9a99 60%, transparent);
        border-radius: 4px;
        z-index: 2;
        opacity: 0.7;
        box-shadow: 0 0 16px #ab47bc55;
        animation: separatorGlow 2.5s infinite alternate;
    }
    @keyframes separatorGlow {
        0% { opacity: 0.6; }
        100% { opacity: 1; box-shadow: 0 0 32px #ab47bc99; }
    }
    .footer-content-ultimate {
        position: relative;
        z-index: 3;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 20px;
        margin-bottom: 0;
    }
    .footer-flag-ultimate {
        font-size: 0.8rem;
        margin-bottom: 6px;
        color: #ab47bc;
        filter: drop-shadow(0 2px 12px #ab47bc44);
        animation: starPulseUltimate 2.7s infinite;
        text-shadow: 0 2px 16px #ab47bc44;
    }
    @keyframes starPulseUltimate {
        0%,100% { transform: scale(1); color: #ab47bc; }
        60% { transform: scale(1.22); color: #6a1b9a; }
    }
    .footer-title-ultimate {
        font-size: 0.75rem;
        font-weight: 700;
        color: #6a1b9a;
        margin-bottom: 13px;
        letter-spacing: 0.9px;
        text-shadow: 0 2px 12px #ab47bc33;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        background: linear-gradient(90deg,#ab47bc88 10%,#6a1b9a22 90%);
        border-radius: 1.2rem;
        padding: 7px 22px;
        box-shadow: 0 2px 16px #ab47bc11;
        backdrop-filter: blur(2px);
    }
    .footer-links-ultimate {
        display: flex;
        gap: 0.3rem;
        margin: 2px 0 2px 0;
    }
    .footer-links-ultimate a {
        color: #fff;
        font-size: 0.72rem;
        background: linear-gradient(135deg,#ab47bc 40%,#6a1b9a 100%);
        padding: 2px 3px;
        border-radius: 50%;
        box-shadow: 0 4px 18px #ab47bc33, 0 1.5px 0 #fff3 inset;
        transition: background 0.18s, color 0.18s, transform 0.18s, box-shadow 0.18s;
        position: relative;
        outline: none;
        border: none;
        filter: drop-shadow(0 2px 12px #ab47bc44);
    }
    .footer-links-ultimate a:hover, .footer-links-ultimate a:focus {
        background: linear-gradient(135deg,#fff 10%,#ab47bc 90%);
        color: #ab47bc;
        transform: scale(1.18) rotate(-8deg);
        box-shadow: 0 8px 32px #ab47bc77;
        z-index: 9;
    }
    .footer-social-ultimate {
        display: flex;
        gap: 0.2rem;
        margin: 1px 0 0 0;
    }
    .footer-social-btn {
        color: #ab47bc;
        background: #fff;
        font-size: 0.65rem;
        border-radius: 50%;
        padding: 1px 2px;
        box-shadow: 0 2px 10px #ab47bc22;
        transition: background 0.18s, color 0.18s, transform 0.18s;
        outline: none;
        border: none;
    }
    .footer-social-btn:hover, .footer-social-btn:focus {
        background: linear-gradient(135deg,#ab47bc 40%,#fff 100%);
        color: #fff;
        transform: scale(1.14) rotate(7deg);
        box-shadow: 0 6px 24px #ab47bc55;
    }
    .footer-credits-ultimate {
        margin-top: 2px;
        font-size: 0.72em;
        color: #888;
        letter-spacing: 0.6px;
        opacity: 0.92;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        background: rgba(255,255,255,0.15);
        border-radius: 7px;
        padding: 3px 12px;
        box-shadow: 0 1.5px 8px #ab47bc11;
        animation: creditsPulse 5s infinite alternate;
    }
    @keyframes creditsPulse {
        0% { opacity: 0.92; }
        100% { opacity: 1; box-shadow: 0 1.5px 16px #ab47bc44; }
    }
    .footer-copyright {
        font-weight: 600;
        color: #6a1b9a;
        letter-spacing: 0.7px;
        animation: copyrightWave 2.8s infinite alternate;
    }
    @keyframes copyrightWave {
        0% { letter-spacing: 0.7px; }
        60% { letter-spacing: 2.2px; }
        100% { letter-spacing: 0.7px; }
    }
    .footer-powered {
        color: #ab47bc;
        font-weight: 500;
        letter-spacing: 0.3px;
        font-size: 0.97em;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    @media (max-width: 768px) {
        .footer-modern-ultimate { padding: 19px 0 8px 0; border-radius: 1.2rem 1.2rem 0 0; }
        .footer-title-ultimate { font-size: 1.02rem; padding: 6px 8px; }
        .footer-links-ultimate { gap: 0.95rem; }
        .footer-links-ultimate a { font-size: 1.08rem; padding: 7px 9px; }
        .footer-social-ultimate { gap: 0.6rem; }
        .footer-credits-ultimate { font-size: 0.95em; padding: 2px 6px; }
    }
    @keyframes fadeInUpFooter {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }
    </style>

    <script src="assets/js/script.js"></script>
</body>
</html>
