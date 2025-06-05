<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);

if (!isset($_SESSION['id_usuario'])) {
    $message = "Você precisa estar logado para gerenciar entidades.";
    $message_type = "error";
} else {
    include '../db_connect.php';

    if (!isset($conn) || !($conn instanceof mysqli)) {
        $message = "Erro: Falha na conexão com o banco de dados.";
        $message_type = "error";
    } else {
        $message = '';
        $message_type = '';

        // Fetch categories for brand and attribute forms
        $categorias = $conn->query("SELECT id_categoria, nome_categoria FROM categorias");
        if (!$categorias) {
            $message = "Erro ao carregar categorias: " . $conn->error;
            $message_type = "error";
        }

        // Fetch products for kit form
        $produtos = $conn->query("SELECT id_produto, titulo_produto FROM produto");
        if (!$produtos) {
            $message = "Erro ao carregar produtos: " . $

            $message_type = "error";
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $errors = [];

            if (isset($_POST['tipo_entidade'])) {
                $tipo_entidade = $_POST['tipo_entidade'];

                if ($tipo_entidade === 'marca') {
                    $nome_marca = filter_input(INPUT_POST, 'nome_marca', FILTER_SANITIZE_SPECIAL_CHARS);
                    $categorias_selecionadas = isset($_POST['categorias']) ? $_POST['categorias'] : [];

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
                    } else {
                        $message = implode("<br>", $errors);
                        $message_type = "error";
                    }
                } elseif ($tipo_entidade === 'categoria') {
                    $nome_categoria = filter_input(INPUT_POST, 'nome_categoria', FILTER_SANITIZE_SPECIAL_CHARS);

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
                    } else {
                        $message = implode("<br>", $errors);
                        $message_type = "error";
                    }
                } elseif ($tipo_entidade === 'atributo') {
                    $nome_atributo = filter_input(INPUT_POST, 'nome_atributo', FILTER_SANITIZE_SPECIAL_CHARS);
                    $valores = filter_input(INPUT_POST, 'valores', FILTER_SANITIZE_SPECIAL_CHARS);
                    $categorias_selecionadas = isset($_POST['categorias']) ? $_POST['categorias'] : [];

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
                            $stmt = $conn->prepare("INSERT INTO atributos (nome_atributo) VALUES (?)");
                            $stmt->bind_param("s", $nome_atributo);
                            $stmt->execute();
                            $id_atributo = $conn->insert_id;
                            $stmt->close();

                            foreach ($categorias_selecionadas as $id_categoria) {
                                $stmt = $conn->prepare("INSERT INTO categoria_atributos (id_atributo, id_categoria) VALUES (?, ?)");
                                $stmt->bind_param("ii", $id_atributo, $id_categoria);
                                $stmt->execute();
                                $stmt->close();
                            }

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
                    } else {
                        $message = implode("<br>", $errors);
                        $message_type = "error";
                    }
                } elseif ($tipo_entidade === 'kit') {
                    $titulo_kit = filter_input(INPUT_POST, 'titulo_kit', FILTER_SANITIZE_SPECIAL_CHARS);
                    $preco_kit = filter_input(INPUT_POST, 'preco_kit', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $produtos_selecionados = isset($_POST['produtos']) ? $_POST['produtos'] : [];

                    if (empty($titulo_kit)) {
                        $errors[] = "O título do kit é obrigatório.";
                    }
                    if ($preco_kit <= 0) {
                        $errors[] = "O preço do kit deve ser maior que zero.";
                    }
                    if (empty($produtos_selecionados)) {
                        $errors[] = "Selecione pelo menos um produto para o kit.";
                    }

                    if (empty($errors)) {
                        $conn->begin_transaction();
                        try {
                            // Insert kit as a product with a special category (e.g., 'Kits')
                            $id_categoria_kit = 0; // Assume a "Kits" category exists or create one
                            $stmt = $conn->prepare("SELECT id_categoria FROM categorias WHERE nome_categoria = 'Kits'");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows == 0) {
                                $stmt = $conn->prepare("INSERT INTO categorias (nome_categoria) VALUES ('Kits')");
                                $stmt->execute();
                                $id_categoria_kit = $conn->insert_id;
                            } else {
                                $row = $result->fetch_assoc();
                                $id_categoria_kit = $row['id_categoria'];
                            }
                            $stmt->close();

                            $stmt = $conn->prepare("INSERT INTO produto (titulo_produto, preco_produto, id_categoria, id_usuario_cadastro) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("sdii", $titulo_kit, $preco_kit, $id_categoria_kit, $_SESSION['id_usuario']);
                            $stmt->execute();
                            $id_kit = $conn->insert_id;
                            $stmt->close();

                            foreach ($produtos_selecionados as $id_produto) {
                                $stmt = $conn->prepare("INSERT INTO kit_componentes (id_kit, id_produto) VALUES (?, ?)");
                                $stmt->bind_param("ii", $id_kit, $id_produto);
                                $stmt->execute();
                                $stmt->close();
                            }

                            $conn->commit();
                            $message = "Kit cadastrado com sucesso!";
                            $message_type = "success";
                        } catch (Exception $e) {
                            $conn->rollback();
                            $message = "Erro ao cadastrar kit: " . $e->getMessage();
                            $message_type = "error";
                        }
                    } else {
                        $message = implode("<br>", $errors);
                        $message_type = "error";
                    }
                }
                $conn->close();
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
    <title>Gerenciar Entidades</title>
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
            max-width: 600px;
            width: 100%;
        }
        h2 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ccc;
            margin-bottom: 1rem;
        }
        .tab {
            flex: 1;
            padding: 0.8rem;
            text-align: center;
            cursor: pointer;
            background-color: #f1f1f1;
            border-radius: 5px 5px 0 0;
            margin-right: 0.2rem;
            transition: background-color 0.3s;
        }
        .tab.active {
            background-color: #007bff;
            color: white;
        }
        .tab:hover {
            background-color: #e0e0e0;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        input[type="text"], input[type="number"], select {
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
        <h2>Gerenciar Entidades</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <div class="tabs">
            <div class="tab active" data-tab="marca">Marca</div>
            <div class="tab" data-tab="categoria">Categoria</div>
            <div class="tab" data-tab="atributo">Atributo</div>
            <div class="tab" data-tab="kit">Kit</div>
        </div>
        <div id="marca" class="tab-content active">
            <form method="POST" action="">
                <input type="hidden" name="tipo_entidade" value="marca">
                <div class="form-group">
                    <label for="nome_marca">Nome da Marca:</label>
                    <input type="text" id="nome_marca" name="nome_marca" value="<?php echo isset($_POST['nome_marca']) ? htmlspecialchars($_POST['nome_marca']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="categorias_marca">Categorias:</label>
                    <select id="categorias_marca" name="categorias[]" multiple required>
                        <?php 
                        $categorias->data_seek(0); // Reset cursor
                        while ($categoria = $categorias->fetch_assoc()): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>">
                                <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit">Cadastrar Marca</button>
            </form>
        </div>
        <div id="categoria" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="tipo_entidade" value="categoria">
                <div class="form-group">
                    <label for="nome_categoria">Nome da Categoria:</label>
                    <input type="text" id="nome_categoria" name="nome_categoria" value="<?php echo isset($_POST['nome_categoria']) ? htmlspecialchars($_POST['nome_categoria']) : ''; ?>" required>
                </div>
                <button type="submit">Cadastrar Categoria</button>
            </form>
        </div>
        <div id="atributo" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="tipo_entidade" value="atributo">
                <div class="form-group">
                    <label for="nome_atributo">Nome do Atributo:</label>
                    <input type="text" id="nome_atributo" name="nome_atributo" value="<?php echo isset($_POST['nome_atributo']) ? htmlspecialchars($_POST['nome_atributo']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="valores">Valores (separados por vírgula):</label>
                    <input type="text" id="valores" name="valores" placeholder="Ex.: 10 polegadas, 30 polegadas, 120 polegadas" value="<?php echo isset($_POST['valores']) ? htmlspecialchars($_POST['valores']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="categorias_atributo">Categorias:</label>
                    <select id="categorias_atributo" name="categorias[]" multiple required>
                        <?php 
                        $categorias->data_seek(0); // Reset cursor
                        while ($categoria = $categorias->fetch_assoc()): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>">
                                <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit">Cadastrar Atributo</button>
            </form>
        </div>
        <div id="kit" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="tipo_entidade" value="kit">
                <div class="form-group">
                    <label for="titulo_kit">Título do Kit:</label>
                    <input type="text" id="titulo_kit" name="titulo_kit" value="<?php echo isset($_POST['titulo_kit']) ? htmlspecialchars($_POST['titulo_kit']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="preco_kit">Preço do Kit (R$):</label>
                    <input type="number" id="preco_kit" name="preco_kit" step="0.01" min="0" value="<?php echo isset($_POST['preco_kit']) ? htmlspecialchars($_POST['preco_kit']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="produtos_kit">Produtos no Kit:</label>
                    <select id="produtos_kit" name="produtos[]" multiple required>
                        <?php 
                        $produtos->data_seek(0); // Reset cursor
                        while ($produto = $produtos->fetch_assoc()): ?>
                            <option value="<?php echo $produto['id_produto']; ?>">
                                <?php echo htmlspecialchars($produto['titulo_produto']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit">Cadastrar Kit</button>
            </form>
        </div>
        <a href="../dashboard_admin.php">Voltar ao Dashboard</a>
    </div>
    <script>
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));

                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>