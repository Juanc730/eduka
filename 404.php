<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Códigos Yape — Instituto Eduka</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<div style="display:flex; justify-content:center; width:100%;">
    <div class="container-404">
        <div class="card-404">
            <div class="icon-404">🔍</div>
            <h1>404</h1>
            <h2>Página no encontrada</h2>
            <p>La página que buscas no existe o fue movida.</p>
            <div style="text-align: center; width: 100%;">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="/eduka/frontend/pages/dashboard.html" class="btn-primary">Volver al inicio</a>
                <?php else: ?>
                    <a href="/eduka/frontend/pages/login.html" class="btn-primary">Ir al login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>