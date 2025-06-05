<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);

if (!isset($_SESSION['id_usuario'])) {
    $message = "Você precisa estar logado para cadastrar um atributo.";
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
            $nome_atributo = filter_input(INPUT_POST, 'nome_atributo', FILTER_SANITIZE_SPECIAL_CHARS);
            $valores = filter_input(INPUT_POST, 'valores', FILTER_SANITIZE_SPECIAL_CHARS);
            $categorias_selecionadas = isset($_POST['categorias']) ? $_POST['categorias'] : [];

            $errors = [];

            if (empty($nome_atributo)) {
                $errors[] = "O nome do atributo é obrigatório.";
            }
            if (empty($valores)) {
                $errors[] = "Pelo menos um valor é obrigatório.";
            }
            if (empty($categorias_selecionadas)) {
                $errors[] = "Selecione pelo menos uma categoria.";
            }

            $stmt = $conn->prepare("SELECT id_atributo FROM atributos WHERE nome_atributo = ?");
            $stmt->bind_param("s", $nome_atributo);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = "O atributo já existe.";
            }
            $stmt->close();

            if (empty($errors)) {
                $conn->begin_transaction();
                try {
                    // Insert attribute
                    $stmt = $conn->prepare("INSERT INTO atributos (nome_atributo) VALUES (?)");
                    $stmt->bind_param("s", $nome_atributo);
                    $stmt->execute();
                    $id_atributo = $conn->insert_id;
                    $stmt->close();

                    // Insert category associations
                    foreach ($categorias_selecionadas as $id_categoria) {
                        $stmt = $conn->prepare("INSERT INTO categoria_atributos (id_atributo, id_categoria) VALUES (?, ?)");
                        $stmt->bind_param("ii", $id_atributo, $id_categoria);
                        $stmt->execute();
                        $stmt->close();
                    }

                    // Insert attribute values
                    $valores_array = array_filter(array_map('trim', explode(',', $valores)));
                    foreach ($valores_array as $valor) {
                        $stmt = $conn->prepare("INSERT INTO valores_atributos (id_atributo, valor) VALUES (?, ?)");
                        $stmt->bind_param("is", $id_atributo, $valor);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $conn->commit();
                    $message = "Atributo cadastrado com sucesso!";
                    $message_type = "success";
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "Erro ao cadastrar atributo: " . $e->getMessage();
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
    <title>Cadastro de Atributo</title>
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
        input[type="text"], select, textarea {
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
        <h2>Cadastro de Atributo</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nome_atributo">Nome do Atributo:</label>
                <input type="text" id="nome_atributo" name="nome_atributo" value="<?php echo isset($_POST['nome_atributo']) ? htmlspecialchars($_POST['nome_atributo']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="valores">Valores (separados por vírgula):</label>
                <input type="text" id="valores" name="valores" placeholder="Ex.: 10 polegadas, 30 polegadas, 120 polegadas" value="<?php echo isset($_POST['valores']) ? htmlspecialchars($_POST['valores']) : ''; ?>" required>
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
            <button type="submit">Cadastrar Atributo</button>
        </form>
        <a href="../dashboard_admin.php">Voltar ao Dashboard</a>
    </div>
</body>
</html>