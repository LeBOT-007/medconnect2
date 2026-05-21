<style>
    /* ── Footer ── */
    footer {
        background: #1e293b;
        color: #94a3b8;
        padding: 1.5rem 2rem;
        margin-top: auto;
        font-size: .85rem;
    }

    .footer-inner {
        max-width: 960px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .footer-logo {
        font-size: 1.1rem;
        font-weight: 800;
        color: #fff;
    }
    .footer-logo span { color: #93c5fd; }

    .footer-links {
        display: flex;
        gap: 1.2rem;
        list-style: none;
        flex-wrap: wrap;
    }
    .footer-links a {
        color: #94a3b8;
        text-decoration: none;
        transition: color .2s;
        font-weight: 600;
    }
    .footer-links a:hover { color: #fff; }

    .footer-copy {
        color: #64748b;
        font-size: .8rem;
        text-align: right;
    }

    @media (max-width: 640px) {
        .footer-inner { flex-direction: column; text-align: center; }
        .footer-copy  { text-align: center; }
        .footer-links { justify-content: center; }
    }
</style>

<footer>
    <div class="footer-inner">
        <div class="footer-logo">Med<span>Connect</span></div>

        <ul class="footer-links">
            <li><a href="#">À propos</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Confidentialité</a></li>
            <li><a href="#">CGU</a></li>
        </ul>

        <div class="footer-copy">
            &copy; <?= date('Y') ?> MedConnect — Tous droits réservés
        </div>
    </div>
</footer>

</body>
</html>