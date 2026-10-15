</main>

<footer class="footer-rinas text-white pt-4 pb-3">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <h5 class="fw-bold mb-0">Rinas</h5>
                <small class="fw-bold text-uppercase text-rinas-naranja">Cafeteria</small>
                <p class="small mt-2 mb-0">Cafe y pupusas hechas con amor</p>
            </div>
            <div class="col-6 col-md-4">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li><a href="menu.php" class="text-white text-decoration-none footer-link">Menú</a></li>
                    <li><a href="ubicacion.php" class="text-white text-decoration-none footer-link">Ubicación</a></li>
                    <li><a href="nosotros.php" class="text-white text-decoration-none footer-link">Nosotros</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li><a href="#" class="text-white text-decoration-none footer-link">Instagram</a></li>
                    <li><a href="#" class="text-white text-decoration-none footer-link">Facebook</a></li>
                    <li><a href="#" class="text-white text-decoration-none footer-link">WhatsApp</a></li>
                </ul>
            </div>
        </div>
        <div class="border-top mt-4 pt-3 footer-divider">
            <small>© 2026 Rinas Cafetería</small>
        </div>
        <div>
            <small>Realizado por Juan Pablo Henriquez</small>
        </div>
    </div>
</footer>

<script src="<?= LINK_BOOTSTRAP_JS ?>"></script>
<script>
/* Navbar: transparente sobre el video, solido y delgado al scrollear */
(function () {
    var header = document.querySelector(".header-rinas");
    if (!header) return;
    var ticking = false;
    function onScroll() {
        header.classList.toggle("scrolled", window.scrollY > 60);
        ticking = false;
    }
    window.addEventListener("scroll", function () {
        if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
    }, { passive: true });
    onScroll();
})();
</script>
</body>
</html>
