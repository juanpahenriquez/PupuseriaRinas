<div class="admin-login-wrap">
    <div class="admin-login-card">
        <div class="text-center mb-4">
            <img src="../assets/img/logo.jpg" alt="Pupusería Rinas" class="mx-auto d-block" width="64" height="64" style="width:64px;height:64px;object-fit:contain;border-radius:50%;background:#FFF6D3;">
            <h1 class="h4 fw-bold mt-3 mb-1" style="letter-spacing:-0.02em">Pupusería Rinas</h1>
            <p class="small text-muted mb-0">Panel administrativo</p>
        </div>
        <form method="post" autocomplete="off">
            <?= csrf_input() ?>
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase label-admin" for="loginEmail">Correo</label>
                <input id="loginEmail" class="form-control" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="admin@rinas.com" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase label-admin" for="loginPass">Contraseña</label>
                <input id="loginPass" class="form-control" type="password" name="password" placeholder="••••••" required>
            </div>
            <button type="submit" class="btn w-100 btn-admin btn-admin--black">Entrar</button>
        </form>
        <p class="small text-center text-muted mt-3 mb-0">Acceso restringido al personal de Rinas</p>
    </div>
</div>
<script>
(function () {
    var errorMessage = <?= !empty($error) ? json_encode($error, JSON_UNESCAPED_UNICODE) : 'null' ?>;
    var loginOk = <?= !empty($loginOk) ? 'true' : 'false' ?>;

    if (loginOk) {
        Swal.fire({
            position: "center",
            icon: "success",
            title: "¡Sesión iniciada!",
            text: "Bienvenido a Rinas",
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            willClose: function () {
                window.location.href = "index.php";
            }
        });
        return;
    }

    if (errorMessage) {
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: errorMessage
        });
    }
})();
</script>
