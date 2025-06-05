<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);

if (!isset($_SESSION['id_usuario'])) {
    $message = "Você precisa estar logado para cadastrar uma categoria.";
    $message_type = "error";
} else {
    include '../db_connect.php';

    if (!isset($conn) || !($conn instanceof mysqli)) {
        $message = "Erro: Falha na conexão com o banco de dados.";
        $message_type = "error";
    } else {
        $message = '';
        $message_type = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nome_categoria = filter_input(INPUT_POST, 'nome_categoria', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($nome_categoria)) {
                $errors[] = "O nome da categoria é obrigatório.";
            }

            $stmt = $conn->prepare("SELECT id_categoria FROM categorias WHERE nome_categoria = ?");
            $stmt->bind_param("s", $nome_categoria);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = "A categoria já existe.";
            }
            $stmt->close();

            if (empty($errors)) {
                $stmt = $conn->prepare("INSERT INTO categorias (nome_categoria) VALUES (?)");
                $stmt->bind_param("s", $nome_categoria);
                if ($stmt->execute()) {
                    $message = "Categoria cadastrada com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao cadastrar categoria: " . $conn->error;
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
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Categoria</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        h2 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        input[type="text"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 0.8rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 5px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        a {
            display: inline-block;
            margin-top: 1rem;
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Cadastro de Categoria</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nome_categoria">Nome da Categoria:</label>
                <input type="text" id="nome_categoria" name="nome_categoria" value="<?php echo isset($_POST['nome_categoria']) ? htmlspecialchars($_POST['nome_categoria']) : ''; ?>" required>
            </div>
            <button type="submit">Cadastrar Categoria</button>
        </form>
        <a href="../dashboard_admin.php">Voltar ao Dashboard</a>
    </div>
</body>
</html>