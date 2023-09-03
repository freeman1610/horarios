<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function login()
    {
        require_once '../conexion.php';

        // Obtener la conexión
        $conexion = new Conexion();
        $conn = $conexion->getConnection();

        $username = $_POST['username'];
        $password = $_POST['password'];
        $tipoUsuario = $_POST['selectUsua'];

        if ($tipoUsuario == 'admin') {
            $query = "SELECT id, user_type, user_name, status FROM usuario WHERE user_name='$username' AND password='$password'";
        }
        if ($tipoUsuario == 'coord') {
            $query = "SELECT id,
                            pnf_id,
                            (SELECT name FROM pnf WHERE id = usuario.pnf_id) AS pnf_name,
                            user_type,
                            sede_id,
                            user_name,
                            status
                            FROM usuario 
                            WHERE user_name='$username' AND password='$password'";
        }
        if ($tipoUsuario == 'prof') {
            $query = "SELECT
                        usuario.id AS user_id,
                        profesor.id AS profe_id,
                        (SELECT cedula FROM profesor WHERE id= usuario.profesor_id) AS user_name,
                        (SELECT name FROM profesor WHERE id= usuario.profesor_id) AS name,
                        (SELECT last_name FROM profesor WHERE id= usuario.profesor_id) AS last_name,
                        status,
                        user_type
                    FROM
                        usuario
                    INNER JOIN profesor ON profesor.id = usuario.profesor_id
                    WHERE
                        profesor.cedula=$username AND usuario.password='$password'";
        }
        $stmt = $conn->prepare($query);

        $stmt->execute();

        $rowCount = $stmt->rowCount();

        if ($rowCount == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['status'] == 0) {
                return "Usuario Deshabilitado";
            }
            if ($tipoUsuario == 'admin') {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_type'] = $row['user_type'];
                $_SESSION['user_name'] = $row['user_name'];
            }
            if ($tipoUsuario == 'coord') {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_type'] = $row['user_type'];
                $_SESSION['pnf_id'] = $row['pnf_id'];
                $_SESSION['pnf_name'] = $row['pnf_name'];
                $_SESSION['sede_id'] = $row['sede_id'];
                $_SESSION['user_name'] = $row['user_name'];
            }
            if ($tipoUsuario == 'prof') {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['profe_id'] = $row['profe_id'];
                $_SESSION['user_type'] = $row['user_type'];
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['name_prof'] = $row['name'] . ' ' . $row['last_name'];
            }



            // Redirigir a la página correspondiente según el tipo de usuario
            switch ($row['user_type']) {
                case 'admin':
                    header("Location: ../admin");
                    break;
                case 'coord':
                    header("Location: ../coordinador");
                    break;
                case 'prof':
                    header("Location:../profesor");
                    break;
            }
        } else {
            return "Nombre de usuario o contraseña incorrectos";
        }
    }
    $error = login();
}

if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'admin':
            header("Location: ../admin");
            break;
        case 'coord':
            header("Location: ../coordinador");
            break;
        case 'prof':
            header("Location:../profesor");
            break;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Iniciar sesión</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="shortcut icon" href="../img/iconiut.png" type="image/x-icon">
</head>

<body>
    <div class="container">
        <div class="row mt-4">
            <div class="col-12">
                <img class="img-fluid" src="../img/banneriut.png" alt="UPTAI">
            </div>
        </div>
        <div class="row d-flex justify-content-center mt-4">
            <div class="col-md-4 col-12">
                <h2 class="h2">Iniciar sesión</h2>
                <form method="post" action="">
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="selectUsua">Tipo de Usuario</label>
                        <select required class="form-select" name="selectUsua" id="selectUsua">
                            <option value="">Seleccione</option>
                            <option value="admin">Administrador</option>
                            <option value="coord">Coordinador</option>
                            <option value="prof">Profesor</option>
                        </select>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" autocomplete="off" placeholder="" required name="username">
                        <label for="username">Nombre de usuario</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" placeholder="" required name="password">
                        <label for="password">Contraseña</label>
                    </div>
                    <input class="btn btn-primary" type="submit" value="Entrar">
                    <a href="../" class="btn btn-secondary">Regresar</a>

                </form>
                <?php
                if (isset($error)) {
                    echo '<p style="color: red;">' . $error . '</p>';
                }
                ?>
            </div>
        </div>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</html>