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
    // Prepara uma consulta segura para inserir um agendamento vinculado a um cliente
    $stmt = $conn->prepare(
        "INSERT INTO agendamentos (cliente_id, horario, dia, servico, valor)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "isssd",
        $_POST['cliente_id'],
        $_POST['horario'],
        $_POST['dia'],
        $_POST['servico'],
        $_POST['valor']
    );
    // Executa a inserção no banco
    $stmt->execute();
    // Após adicionar o agendamento, redireciona para a mesma página para atualizar a lista
    header("Location: criar_agendamento.php");
    exit();
}
// Recebe o termo de busca enviado via GET, ou vazio se não houver
$busca = $_GET['busca'] ?? '';
// Protege o termo de busca contra SQL Injection antes de colocar na consulta
$termo = $conn->real_escape_string($busca);
// Monta a cláusula WHERE da consulta para filtrar agendamentos pelo horario ou dia, se houver termo de busca
$where = $busca ? "WHERE a.horario LIKE '%$termo%' OR a.dia LIKE '%$termo%'" : '';
// Une agendamentos e clientes para mostrar o nome do cliente
$query = "SELECT a.*, c.nome AS cliente_nome
          FROM agendamentos a
          JOIN clientes c ON a.cliente_id = c.id
          $where
          ORDER BY a.horario";


// Executa a consulta e armazena o resultado
$agendamentos = $conn->query($query);


// Busca todos os clientes para preencher o select do formulário de cadastro
$clientes = $conn->query("SELECT id, nome FROM clientes ORDER BY nome");
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">


<h2>Cadastro de Agendamento</h2>


<!-- Formulário para pesquisa de agendamentos -->
<form method="get">
    <input name="busca" placeholder="Buscar por horário ou dia..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit">Buscar</button>
    <a href="principal.php">Voltar</a>
</form>


<!-- Tabela exibindo os agendamentos buscados/listados -->
<table border="1" cellpadding="5">
    <tr><th>Horário</th><th>Dia</th><th>Cliente</th><th>Serviço</th><th>Valor (R$)</th><th>Ações</th></tr>
    <?php while($row = $agendamentos->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['horario']) ?></td>
        <td><?= htmlspecialchars($row['dia']) ?></td>
        <td><?= htmlspecialchars($row['cliente_nome']) ?></td>
        <td><?= htmlspecialchars($row['servico'] ?? '') ?></td>
        <td><?= number_format((float) $row['valor'], 2, ',', '.') ?></td>
        <td>
            <a href="editar_agendamento.php?id=<?= $row['id'] ?>">Editar</a>
            <a href="deletar_agendamento.php?id=<?= $row['id'] ?>" onclick="return confirm('Excluir este agendamento?')">Excluir</a>
        </td>
      </tr>
    <?php endwhile; ?>
</table>


<!-- Formulário para cadastro de novo agendamento -->
<h3>Novo agendamento</h3>
<form method="post">
    <!-- Select com os clientes cadastrados: o valor enviado é o id do cliente -->
    <select name="cliente_id" required>
        <option value="">Selecione o cliente</option>
        <?php while ($c = $clientes->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
        <?php endwhile; ?>
    </select>
    
    <input name="horario" required placeholder="Horário">
    <input type="date" name="dia" required>
    <input name="servico" required placeholder="Serviço">
    <input type="number" name="valor" required min="0" step="0.01" placeholder="Valor (R$)">
    <button name="add">Cadastrar</button>
</form>
