<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sidebar</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="sidebar" id="sidebar">
    <!-- Botón minimalista para colapsar el sidebar -->
    <button id="sidebarToggle" class="toggle-btn">
      <i class="fas fa-angle-double-left"></i>
    </button>
    
    <div class="sidebar-content">
      <h2><i class="fas fa-store"></i> <span class="text">Plaza Shopping Center</span></h2>
      <ul class="main-menu">
        <li><a href="home.php"><i class="fas fa-home"></i> <span class="text">Inicio</span></a></li>
        <li><a href="regulares.php"><i class="fas fa-file-contract"></i> <span class="text">Regulares</span></a></li>
        <li><a href="irregulares.php"><i class="fas fa-file-contract"></i> <span class="text">Irregulares</span></a></li>
        <li><a href="comunicados.php"><i class="fas fa-message"></i> <span class="text">Aviso</span></a></li>
        <li><a href="mensajes.php"><i class="fas fa-message"></i> <span class="text">Enviar Mensaje</span></a></li>
        <li><a href="cambiar.php"><i class="fas fa-key"></i> <span class="text">Cambiar Contraseña</span></a></li>
      </ul>
    </div>
    
    <!-- Menú inferior para logout, fijado al fondo -->
    <div class="lower-menu">
      <ul>
        <li><a href="cerrar_sesion.php" class="logout-link"><i class="fas fa-unlock"></i> <span class="text">Cerrar Sesión</span></a></li>
      </ul>
    </div>
  </div>
  
  <script>
    // Toggle minimalista para colapsar el sidebar
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('collapsed');
      // Cambiar icono del botón
      const icon = this.querySelector('i');
      if (document.getElementById('sidebar').classList.contains('collapsed')) {
        icon.classList.remove('fa-angle-double-left');
        icon.classList.add('fa-angle-double-right');
      } else {
        icon.classList.remove('fa-angle-double-right');
        icon.classList.add('fa-angle-double-left');
      }
    });
  </script>
</body>
</html>
