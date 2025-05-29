<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" 
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Tu hoja de estilos personalizada -->
  <link rel="stylesheet" href="css/styles_inicio_sesion.css" />
  <title>Inicio</title>
  <style>
    /* Estilos para los mensajes de error */
    .error-message {
      color: red;
      font-size: 14px;
      margin-top: 5px;
      display: none; /* Oculto por defecto */
    }
  </style>
</head>
<body>
  <div class="container" id="container">
    <div class="form-container sign-in">
      <form id="loginForm" action="iniciar_sesion.php" method="POST">
        <h1>INICIAR SESIÓN</h1>
        <hr>

        

        <i class="fa-solid fa-user"></i>
        <label for="usuario">Usuario</label>
        <input type="text" name="usuario" id="usuario" placeholder="Nombre de usuario" required>

        


        <i class="fa-solid fa-unlock"></i>
        <label for="password">Contraseña</label>
        <input type="password" name="password" id="password" placeholder="Contraseña" required>
        
        <!-- Mensaje de error para contraseña incorrecta -->
        <?php if (isset($_GET['error']) && $_GET['error'] == 'password'): ?>
          <p id="errorPassword" class="error-message" style="display: block;">Contraseña incorrecta</p>
        <?php endif; ?>

        <!-- Mensaje de error para usuario incorrecto -->
        <?php if (isset($_GET['error']) && $_GET['error'] == 'usuario'): ?>
          <p id="errorUsuario" class="error-message" style="display: block;">Usuario incorrecto</p>
        <?php endif; ?>

        <hr>
        <button type="submit">Iniciar Sesión</button>
      </form>
    </div>
    <div class="toggle-container">
      <div class="toggle">
        <div class="toggle-panel toggle-right">
          <h1>Bienvenido</h1>
          <p>Arrendatario</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Script para limpiar los campos en caso de error -->
  <script>
    // Verifica si hay un error en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
      // Limpia los campos del formulario
      document.getElementById('usuario').value = '';
      document.getElementById('password').value = '';

      // Oculta el mensaje de error después de 2.5 segundos
      setTimeout(() => {
        if (urlParams.get('error') === 'usuario') {
          document.getElementById('errorUsuario').style.display = 'none';
        } else if (urlParams.get('error') === 'password') {
          document.getElementById('errorPassword').style.display = 'none';
        }
      }, 3000); // 2500 milisegundos = 2.5 segundos
    }
  </script>
</body>
</html>