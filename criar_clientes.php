<?php


// Inicia a sessão para verificar se o usuário está autenticado
session_start();


// Se não houver usuário logado, redireciona para a página de login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}


// Verifica o tempo de expiração da sessão: se ficou mais de 30 minutos sem usar o sistema,
// encerra a sessão e volta para o login avisando que expirou
if (time() - $_SESSION['ultimo_acesso'] > 30 * 60) {
    session_destroy();
    header('Location: login.php?expirou=1');
    exit();
}


// Ainda está no prazo: renova o horário do último acesso
$_SESSION['ultimo_acesso'] = time();


// Inclui o arquivo de configuração para conexão com o banco de dados
include 'config.php';


// Verifica se o formulário de cadastro foi enviado via POST e o botão 'add' foi clicado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    // Prepara uma consulta segura para inserir um novo cliente, evitando SQL Injection
    // O CPF é dado sensível (LGPD): AES_ENCRYPT grava o CPF criptografado usando a $chave do config.php
    $stmt = $conn->prepare("INSERT INTO clientes (nome, cpf, telefone, email) VALUES (?, AES_ENCRYPT(?, '$chave'), ?, ?)");
    // Liga os parâmetros enviados no formulário aos marcadores da consulta preparada
    $stmt->bind_param("ssss", $_POST['nome'], $_POST['cpf'], $_POST['telefone'], $_POST['email']);
    // Executa a inserção no banco
    $stmt->execute();
    // Após adicionar o cliente, redireciona para a mesma página para atualizar a lista
    header("Location: criar_clientes.php");
    exit();
}


// Recebe o termo de busca enviado via GET, ou vazio se não houver
$busca = $_GET['busca'] ?? '';


// Protege o termo de busca contra SQL Injection antes de colocar na consulta
$termo = $conn->real_escape_string($busca);


// Monta a cláusula WHERE da consulta para filtrar clientes pelo nome, se houver termo de busca
$where = $busca ? "WHERE nome LIKE '%$termo%'" : '';


// Consulta SQL para buscar os clientes filtrados (ou todos, se não buscou) ordenados por nome
// AES_DECRYPT descriptografa o CPF para mostrar na tela (CAST ... AS CHAR transforma em texto)
$query = "SELECT id, nome, CAST(AES_DECRYPT(cpf, '$chave') AS CHAR) AS cpf, telefone, email
          FROM clientes $where ORDER BY nome";


// Executa a consulta e armazena o resultado
$clientes = $conn->query($query);
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">


<h2>Cadastro de Cliente</h2>


<!-- Formulário para pesquisa de clientes -->
<form method="get">
    <input name="busca" placeholder="Buscar cliente..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit">Buscar</button>
    <a href="principal.php">Voltar</a>
</form>


<!-- Tabela exibindo os clientes buscados/listados -->
<table border="1" cellpadding="5">
    <tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>E-mail</th><th>Ações</th></tr>
    <?php while($row = $clientes->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['nome']) ?></td>
        <td><?= htmlspecialchars($row['cpf'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['telefone'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
        <td>
            <!-- Link para editar o cliente, passando o ID via GET -->
            <a href="editar_clientes.php?id=<?= $row['id'] ?>">Editar</a>
            <a href="deletar_cliente.php?id=<?= $row['id'] ?>" onclick="return confirm('Excluir este cliente?')">Excluir</a>
        </td>
      </tr>
    <?php endwhile; ?>
</table>


<!-- Formulário para cadastro de novo cliente -->
<h3>Novo cliente</h3>
<form method="post">
    <input name="nome" required placeholder="Nome">
    <!-- CPF só com os 11 números -->
    <input name="cpf" required placeholder="CPF (só números)" maxlength="11" pattern="[0-9]{11}">
    <input name="telefone" placeholder="Telefone">
    <input type="email" name="email" placeholder="E-mail">
    <button name="add">Cadastrar</button>
</form>
