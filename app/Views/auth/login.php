<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="icon" type="image/png" href="/assets/img/favicon.ico">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link href="/assets/css/main.css" rel="stylesheet">

  <title>Iniciar Sesión</title>

</head>

<body class="login-page">
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <img src="/assets/img/logo-dark.png" alt="Logo Inmuebles Accesibles" class="login-logo">
      </div>

      <div id="alert-message-container" class="alert-container"></div>

      <form class="login-form" id="loginForm" method="POST">
        <div class="form-group">
          <label for="inputEmail" class="form-label sr-only">Email</label> <input type="email" id="inputEmail" name="email" class="form-input" placeholder="Introduce tu email..." required>
        </div>

        <div class="form-group">
          <label for="inputPassword" class="form-label sr-only">Contraseña</label>
          <input type="password" id="inputPassword" name="password" class="form-input" placeholder="Contraseña" required>
        </div>

        <div class="form-group">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="customCheck" name="remember_me">
            <label class="form-check-label" for="customCheck">Recordarme</label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-login">Iniciar Sesión</button>

        <div class="login-footer">
          <a href="#">¿Olvidaste tu contraseña?</a>
        </div>
      </form>
    </div>
  </div>
  <script src="/assets/js/login.js"></script>
</body>

</html>