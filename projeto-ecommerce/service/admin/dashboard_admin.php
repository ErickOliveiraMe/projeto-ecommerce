<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

include '../db_connect.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("<script>console.log('Erro: Falha na conexão com o banco');</script>");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Usuário</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans serif;
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
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 1.5rem;
        }
        a {
            display: inline-block;
            margin: 0.5rem;
            padding: 0.8rem 1.5rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bem-vindo ao Dashboard</h2>
        <a href="../produto/cadastro_produto.php">Cadastrar Produto</a>
        <a href="../produto/gerenciar_entidades.php">Gerenciar Entidades</a>
        <a href="mostrar_produtos.php">Mostrar Produtos</a>
        <a href="logout.php">Sair</a>
    </div>
</body>
</html>