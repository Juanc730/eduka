<nav class="navbar">
    <div class="navbar-brand">Instituto Eduka</div>
    <div class="navbar-links">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <span>Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <a href="/eduka/modules/auth/logout.php" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?')">Cerrar sesión</a>
        <?php else: ?>
            <a href="/eduka/login.php">Iniciar sesión</a>
        <?php endif; ?>
    </div>
</nav>