<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>500 - Error Interno del Servidor</title>
  <link href="/assets/css/main.css" rel="stylesheet">
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-color: var(--color-bg-light); /* */
      font-family: var(--font-family-base); /* Necesitaría ser definido en main.css o aquí */
    }

    .error-container {
      text-align: center;
      background-color: #fff;
      padding: 40px 30px;
      border-radius: 8px;
      box-shadow: var(--shadow-light); /* */
      max-width: 500px;
      width: 90%;
    }

    .error-code {
      font-size: 8rem;
      line-height: 1;
      /* Un color más asociado a error/peligro, similar al de Bootstrap .text-danger o tu .btn-danger */
      color: #dc3545; /* Color usado en .btn-danger */
      font-weight: bold;
      margin-bottom: 20px;
      text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.05);
    }

    .error-title {
      font-size: 2rem;
      color: var(--color-text-dark); /* */
      margin-bottom: 15px;
      font-weight: 600;
    }

    .error-description {
      color: var(--color-secondary); /* */
      margin-bottom: 30px;
      line-height: 1.5;
    }

    .error-link {
      display: inline-block;
      padding: 10px 20px;
      background-color: var(--color-primary); /* */
      color: #fff;
      border-radius: 5px;
      text-decoration: none;
      transition: background-color 0.2s ease-in-out;
    }

    .error-link:hover {
      background-color: #3b5cb0; /* Color hover del .btn-primary */
      text-decoration: none;
    }

    .error-actions {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 15px;
    }
  </style>
</head>

<body>
  <div class="error-container">
    <div class="error-code">500</div>
    <h1 class="error-title">Error Interno del Servidor</h1>
    <p class="error-description">
      Lo sentimos, algo salió mal de nuestro lado y no pudimos completar tu solicitud.
      Por favor, contacte a servicio técnico.
    </p>
    <div class="error-actions">
        <a href="/" class="error-link">Ir a la página de inicio</a>
        <a href="#" onclick="window.history.back(); return false;" class="error-link">&larr; Volver atrás</a>
    </div>
    <?php if (defined('APP_DEBUG') && APP_DEBUG && isset($errorMessage)): ?>
        <div style="text-align: left; margin-top: 20px; padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9; max-height: 200px; overflow-y: auto;">
            <h5 style="margin-top:0; color: #333;">Detalles del Error (Modo Debug):</h5>
            <pre style="white-space: pre-wrap; word-wrap: break-word; font-size: 0.85em; color: #555;"><?php echo htmlspecialchars($errorMessage); ?></pre>
        </div>
    <?php endif; ?>
  </div>
</body>
</html>