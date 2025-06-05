<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);

if (!isset($_SESSION['id_usuario'])) {
    $message = "Você precisa estar logado para cadastrar uma marca.";
    $message_type = "error";
} else {
    include '../db_connect.php';

    if (!isset($conn) || !($conn instanceof mysqli)) {
        $message = "Erro: Falha na conexão com o banco de dados.";
        $message_type = "error";
    } else {
        $message = '';
        $message_type = '';

        $categorias = $conn->query("SELECT id_categoria, nome_categoria FROM categorias");
        if (!$categorias) {
            $message = "Erro ao carregar categorias: " . $conn->error;
            $message_type = "error";
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nome_marca = filter_input(INPUT_POST, 'nome_marca', FILTER_SANITIZE_SPECIAL_CHARS);
            $categorias_selecionadas = isset($_POST['categorias']) ? $_POST['categorias'] : [];

            $errors = [];

            if (empty($nome_marca)) {
                $errors[] = "O nome da marca é obrigatório.";
            }

            if (empty($categorias_selecionadas)) {
                $errors[] = "Selecione pelo menos uma categoria.";
            }

            $stmt = $conn->prepare("SELECT id_marca FROM marcas WHERE nome_marca = ?");
            $stmt->bind_param("s", $nome_marca);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = "A marca já existe.";
            }
            $stmt->close();

            if (empty($errors)) {
                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare("INSERT INTO marcas (nome_marca) VALUES (?)");
                    $stmt->bind_param("s", $nome_marca);
                    $stmt->execute();
                    $id_marca = $conn->insert_id;
                    $stmt->close();

                    foreach ($categorias_selecionadas as $id_categoria) {
                        $stmt = $conn->prepare("INSERT INTO categoria_marcas (id_marca, id_categoria) VALUES (?, ?)");
                        $stmt->bind_param("ii", $id_marca, $id_categoria);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $conn->commit();
                    $message = "Marca cadastrada com sucesso!";
                    $message_type = "success";
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "Erro ao cadastrar marca: " . $e->getMessage();
                    $message_type = "error";
                }
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
    <title>Cadastro de Marca</title>
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
        input[type="text"], select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        select[multiple] {
            height: 150px;
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
        <h2>Cadastro de Marca</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nome_marca">Nome da Marca:</label>
                <input type="text" id="nome_marca" name="nome_marca" value="<?php echo isset($_POST['nome_marca']) ? htmlspecialchars($_POST['nome_marca']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="categorias">Categorias:</label>
                <select id="categorias" name="categorias[]" multiple required>
                    <?php while ($categoria = $categorias->fetch_assoc()): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>">
                            <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit">Cadastrar Marca</button>
        </form>
        <a href="../dashboard_admin.php">Voltar ao Dashboard</a>
    </div>
</body>
</html>