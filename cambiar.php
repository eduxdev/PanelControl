<?php
session_start();
include('db/db.php');

// Verifica si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nueva_contraseña = mysqli_real_escape_string($conexion, $_POST['nueva_contraseña']);
    $confirmar_contraseña = mysqli_real_escape_string($conexion, $_POST['confirmar_contraseña']);

    // Verificar que los campos no estén vacíos y que las contraseñas coincidan
    if (empty($nueva_contraseña) || empty($confirmar_contraseña)) {
        $error = "Todos los campos son requeridos.";
    } elseif ($nueva_contraseña != $confirmar_contraseña) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Actualiza la contraseña en la base de datos
        $usuario = $_SESSION['usuario'];
        $consulta = "UPDATE personal SET password = '$nueva_contraseña' WHERE usuario = '$usuario'";
        $resultado = mysqli_query($conexion, $consulta);

        if ($resultado) {
            $mensaje = "Contraseña cambiada exitosamente.";
        } else {
            $error = "Hubo un problema al actualizar la contraseña. Intenta de nuevo.";
        }
    }
}
?>


<style>
    /* ESTILOS PARA LOS OJITOS DE MOSTRAR CONTRASEÑA */
    .password-container {
        position: relative;
        margin-bottom: 15px;
    }
    
    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #7f8c8d;
        z-index: 2;
    }
    
    .toggle-password:hover {
        color: #49529d;
    }
    
    input[type="password"],
    input[type="text"] {
        padding-right: 40px !important;
        width: 100%;
    }

    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f9fe;
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 250px;
        height: 100vh;
        background-color: #2c3e50;
        color: white;
        padding: 25px 15px;
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.03);
    }

    .sidebar h2 {
        font-size: 1.4em;
        margin-bottom: 30px;
        padding-left: 15px;
        position: relative;
    }

    .sidebar h2::before {
        content: "";
        position: absolute;
        left: 0;
        width: 4px;
        height: 24px;
        background: #49529d;
        border-radius: 2px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
    }

    .main-content {
    padding: 20px 40px; /* Reducir padding superior/inferior */
    justify-content: flex-start; /* Alinear contenido al inicio */
    min-height: auto; /* Eliminar altura mínima fija */
}

.main-content h1 {
    margin-bottom: 15px; /* Reducir espacio bajo el título */
}

    .main-content h1 i {
        font-size: 1.2em;
        color: #49529d;
    }

    .success {
        color: #2e7d32;
        background: #e8f5e9;
        padding: 15px 20px;
        border-radius: 8px;
        border: 2px solid #a5d6a7;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .error {
        color: #c62828;
        background: #ffebee;
        padding: 15px 20px;
        border-radius: 8px;
        border: 2px solid #ef9a9a;
        display: flex;
        align-items: center;
        gap: 12px;
    }

  
form {
    margin-top: 20px; /* Reducir margen superior del formulario */
    padding: 30px; /* Reducir padding interno */
}

    form label {
        font-size: 14px;
        color: #49529d;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    form input {
        border: 2px solid #e0e3ff;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 15px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    form input:focus {
        border-color: #49529d;
        box-shadow: 0 0 0 3px rgba(73, 82, 157, 0.2);
    }

    form button {
        background: #8184d5;
        padding: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 8px;
        margin-top: 15px;
        transition: all 0.3s ease;
    }

    form button {
    background: #8184d5;
    color: white; /* Añade esta línea */
    padding: 15px 40px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 8px;
    margin: 20px auto 0;
    display: block; /* Asegura el centrado */
    width: fit-content; /* Ajusta al contenido */
    transition: all 0.3s ease;
    }

    /* Línea decorativa bajo el título */
    .main-content h1::after {
    margin-top: 5px; /* Ajustar línea decorativa */
}


   
    .main-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 80px); /* Ajusta según el padding */
    }

    form {
        width: 100%;
        text-align: center;
    }

    form label {
        display: block;
        text-align: left; /* Mantiene alineación izquierda de labels */
        width: 100%;
        max-width: 400px;
        margin: 0 auto 5px;
    }

    .password-container {
        max-width: 400px;
        margin: 0 auto 15px;
    }

    form button {
        width: auto;
        padding: 15px 40px;
        margin: 20px auto 0;
    }
        
</style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <h1><i class="fas fa-key"></i> Cambiar Contraseña</h1>

    <?php if (isset($mensaje)) { echo '<p class="success"><i class="fas fa-check-circle"></i>'.$mensaje.'</p>'; } ?>
    <?php if (isset($error)) { echo '<p class="error"><i class="fas fa-exclamation-circle"></i>'.$error.'</p>'; } ?>

    <form action="cambiar.php" method="POST">
        <label for="nueva_contraseña">Nueva Contraseña</label>
        <div class="password-container">
            <input type="password" id="nueva_contraseña" name="nueva_contraseña" required>
            <i class="toggle-password fas fa-eye"></i>
        </div>

        <label for="confirmar_contraseña">Confirmar Contraseña</label>
        <div class="password-container">
            <input type="password" id="confirmar_contraseña" name="confirmar_contraseña" required>
            <i class="toggle-password fas fa-eye"></i>
        </div>

        <button type="submit">Cambiar Contraseña</button>
    </form>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(icon => {
    icon.addEventListener('click', function() {
        const input = this.previousElementSibling;
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
});
</script>

</body>
</html> 