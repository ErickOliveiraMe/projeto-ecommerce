<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);
include '../db_connect.php';

$errors = [];
$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "E-mail e senha são obrigatórios.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id_usuario, username, password, category FROM cadastro_user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['category'] = $user['category'];
                $message = "Login realizado com sucesso! Bem-vindo, " . htmlspecialchars($user['username']) . ".";
                $message_type = "success";
                header("refresh:2;url=../usuario/dashboard_user.php");
            } else {
                $message = "Senha incorreta.";
                $message_type = "error";
            }
        } else {
            $message = "E-mail não encontrado.";
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = implode("<br>", $errors);
        $message_type = "error";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login de Usuário</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            overflow: auto;
        }
        .container {
            background: #1a1a1a;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
            max-width: 450px;
            width: 100%;
            position: relative;
            border: 2px solid #ffffff;
            text-align: center;
        }
        h2 {
            font-family: 'Cinzel', serif;
            text-align: center;
            color: #ffffff;
            margin-bottom: 2rem;
            font-weight: 700;
            font-size: 2rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid #ffffff;
            padding-bottom: 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        label {
            display: block;
            font-weight: 500;
            color: #ffffff;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        input {
            width: 100%;
            padding: 0.9rem;
            border: 2px solid #ffffff;
            border-radius: 5px;
            font-size: 1rem;
            background: #2c2c2c;
            color: #ffffff;
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #e0e0e0;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        input[type="submit"] {
            background: linear-gradient(45deg, #ffffff, #e0e0e0);
            color: #1a1a1a;
            border: none;
            padding: 1rem;
            cursor: pointer;
            font-weight: 500;
            border-radius: 5px;
            transition: transform 0.3s, box-shadow 0.3s;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Cinzel', serif;
            text-transform: uppercase;
            font-size: 1.1rem;
        }
        input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.4);
        }
        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        .success {
            background: rgba(0, 255, 127, 0.2);
            color: #00ff7f;
            border: 1px solid #00ff7f;
        }
        .error {
            background: rgba(255, 69, 0, 0.2);
            color: #ff4500;
            border: 1px solid #ff4500;
        }
        a {
            display: block;
            text-align: center;
            color: #ffffff;
            text-decoration: none;
            margin-top: 1.5rem;
            font-weight: 500;
            transition: color 0.3s;
            font-family: 'Roboto', sans-serif; /* Changed from Cinzel to Roboto */
        }
        a:hover {
            color: #e0e0e0;
            text-decoration: underline;
        }
        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login de Usuário</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <input type="submit" value="Login">
        </form>
        <a href="cadastro_usuario.php">Não tem uma conta? Cadastre-se</a>
    </div>
</body>
</html>