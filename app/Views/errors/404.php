<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 - Página No Encontrada</title>
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
      color: #e3e6f0;
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
    <div class="error-code">404</div>
    <h1 class="error-title">Página No Encontrada</h1>
    <p class="error-description">Lo sentimos, la página que estás buscando no existe o se ha movido.</p>
    <a href="#" onclick="window.history.back(); return false;" class="error-link">&larr; Volver a la página anterior</a>
  </div>
</body>

</html>