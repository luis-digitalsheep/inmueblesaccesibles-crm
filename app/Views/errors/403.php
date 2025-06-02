<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 - Acceso Denegado</title>
  <link href="/assets/css/main.css" rel="stylesheet">
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-color: var(--color-bg-light);
      font-family: var(--font-family-base);
    }

    .error-container {
      text-align: center;
      background-color: #fff;
      padding: 40px 30px;
      border-radius: 8px;
      box-shadow: var(--shadow-light);
      max-width: 500px;
      width: 90%;
    }

    .error-code {
      font-size: 8rem;
      line-height: 1;
      color: #f8d7da;
      font-weight: bold;
      margin-bottom: 20px;
      text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.05);
    }

    .error-title {
      font-size: 2rem;
      color: var(--color-text-dark);
      margin-bottom: 15px;
      font-weight: 600;
    }

    .error-description {
      color: var(--color-secondary);
      margin-bottom: 30px;
      line-height: 1.5;
    }

    .error-link {
      display: inline-block;
      padding: 10px 20px;
      background-color: var(--color-primary);
      color: #fff;
      border-radius: 5px;
      text-decoration: none;
      transition: background-color 0.2s ease-in-out;
    }

    .error-link:hover {
      background-color: #3b5cb0;
      text-decoration: none;
    }
  </style>
</head>

<body>
  <div class="error-container">
    <div class="error-code">403</div>
    <h1 class="error-title">Acceso Denegado</h1>
    <p class="error-description">No tienes permiso para acceder a esta página o realizar esta acción. Por favor, contacta al administrador del sistema.</p>
    <a href="#" onclick="window.history.back(); return false;" class="error-link">&larr; Volver a la página anterior</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <p style="margin-top: 15px; font-size: 0.9em;">¿Quizás no estás logueado con la cuenta correcta? <a href="/logout">Cerrar sesión</a></p>
    <?php else: ?>
      <p style="margin-top: 15px; font-size: 0.9em;">¿Ya tienes una cuenta? <a href="/login">Inicia sesión aquí</a></p>
    <?php endif; ?>
  </div>
</body>

</html>