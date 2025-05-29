<?php
session_start();
include('db/db.php');

// Verifica si los datos del formulario se enviaron
if (isset($_POST['usuario']) && isset($_POST['password'])) {
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $USUARIO = validate($_POST['usuario']);
    $PASSWORD = validate($_POST['password']);

    // Verifica que los campos no estén vacíos
    if (empty($USUARIO)) {
        header("Location: index.php?error=El usuario es requerido");
        exit();
    } elseif (empty($PASSWORD)) {
        header("Location: index.php?error=La contraseña es requerida");
        exit();
    } else {
        // Verifica si el usuario existe
        $consulta_usuario = "SELECT * FROM personal WHERE usuario = '$USUARIO'";
        $resultado_usuario = mysqli_query($conexion, $consulta_usuario);

        if (!$resultado_usuario) {
            die("Error en la consulta: " . mysqli_error($conexion));
        }

        if (mysqli_num_rows($resultado_usuario) === 0) {
            // El usuario no existe
            header("Location: index.php?error=usuario");
            exit();
        } else {
            // El usuario existe, verifica la contraseña
            $row = mysqli_fetch_assoc($resultado_usuario);
            if ($row['password'] !== $PASSWORD) { // Cambia esto si usas md5 o hashing
                // Contraseña incorrecta
                header("Location: index.php?error=password");
                exit();
            } else {
                // Credenciales correctas
                $_SESSION['usuario'] = $row['usuario'];
                $_SESSION['correo'] = $row['correo'];
                $_SESSION['ID'] = $row['ID'];
                header("Location: home.php");
                exit();
            }
        }

        mysqli_free_result($resultado_usuario);
        mysqli_close($conexion);
    }
} else {
    // Si no se enviaron los datos del formulario, redirige al index
    header("Location: index.php?error=Por favor, ingrese usuario y contraseña");
    exit();
}
?>
