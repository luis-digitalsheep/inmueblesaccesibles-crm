<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
  <title>Iniciar Sesión</title>
  <link href="/assets/css/login.css" rel="stylesheet">
</head>

<body>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <img src="/assets/img/logo-dark.png" alt="Logo" class="login-logo">
      </div>

      <div id="alert-message-container" class="alert-container"></div>

      <form class="login-form" id="loginForm" action="/login" method="POST">
        <div class="form-group">
          <input type="email" id="inputEmail" name="email" placeholder="Introduce tu email..." required>
        </div>
        <div class="form-group">
          <input type="password" id="inputPassword" name="password" placeholder="Contraseña" required>
        </div>
        <div class="form-group checkbox-group">
          <input type="checkbox" id="customCheck" name="remember_me">
          <label for="customCheck">Recordarme</label>
        </div>
        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        <div class="form-footer">
          <a href="#">¿Olvidaste tu contraseña?</a>
        </div>
      </form>
    </div>
  </div>
  <script src="/assets/js/login.js"></script>
</body>

</html>