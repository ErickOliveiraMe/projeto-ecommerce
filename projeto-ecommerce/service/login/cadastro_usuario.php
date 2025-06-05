<?php
include '../db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    $message = "Erro: Falha na conexão com o banco de dados.";
    $message_type = "error";
} else {
    $errors = [];
    $message = '';
    $message_type = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $cpf = $_POST['cpf'];
        $category = "Usuario";

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($cpf)) {
            $errors[] = "Todos os campos são obrigatórios.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "E-mail inválido.";
        }
        if (strlen($password) < 6) {
            $errors[] = "A senha deve ter pelo menos 6 caracteres.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "As senhas não coincidem.";
        }
        if (!preg_match('/^[0-9]{11}$/', $cpf)) {
            $errors[] = "O CPF deve conter exatamente 11 dígitos numéricos.";
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id_usuario FROM cadastro_user WHERE email = ? OR CPF = ?");
            $stmt->bind_param("ss", $email, $cpf);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "E-mail ou CPF já cadastrado.";
            }
            $stmt->close();
        }

        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO cadastro_user (username, email, password, category, CPF) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $category, $cpf);

            if ($stmt->execute()) {
                $message = "Usuário cadastrado com sucesso! Redirecionando para o login...";
                $message_type = "success";
                header("refresh:2;url=login.php");
            } else {
                $message = "Erro ao cadastrar usuário: " . $conn->error;
                $message_type = "error";
            }
            $stmt->close();
            $conn->close();
        } else {
            $message = implode("<br>", $errors);
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
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
        <h2>Cadastro de Usuário</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (empty($message) || $message_type != "success"): ?>
            <form action="cadastro_usuario.php" method="POST">
                <div class="form-group">
                    <label for="username">Nome de Usuário:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Senha:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF:</label>
                    <input type="text" id="cpf" name="cpf" required placeholder="Apenas 11 dígitos numéricos">
                </div>
                <input type="submit" value="Cadastrar">
            </form>
            <a href="login.php">Já tem uma conta? Faça login</a>
        <?php else: ?>
            <a href="login.php">Ir para o Login</a>
        <?php endif; ?>
    </div>
</body>
</html>