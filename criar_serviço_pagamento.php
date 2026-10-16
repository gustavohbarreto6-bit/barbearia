<?php
// Inicia a sessão para acessar dados do usuário logado e verificar autenticação
session_start();
// Se o usuário não estiver autenticado, redireciona para a página de login e encerra o script
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
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
// Inclui o arquivo de configuração que contém a conexão com o banco de dados
include 'config.php';
// Inicializa variável para armazenar mensagens de alerta que poderão ser exibidas para o usuário
$alerta = '';
// Registra o serviço e o valor no agendamento selecionado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agendamento_id = (int) ($_POST['agendamento_id'] ?? 0);
    $servico = trim($_POST['servico'] ?? '');
    $valor = $_POST['valor'] ?? '';
    if (!$agendamento_id || $servico === '' || $valor === '' || !is_numeric($valor) || (float) $valor < 0) {
        $alerta = 'Selecione um agendamento, informe o serviço e um valor válido.';
    } else {
        $stmt = $conn->prepare("UPDATE agendamentos SET servico = ?, valor = ? WHERE id = ?");
        $stmt->bind_param("sdi", $servico, $valor, $agendamento_id);
        $stmt->execute();
        header('Location: criar_serviço_pagamento.php');
        exit;
    }
}
$agendamentos = $conn->query(
    "SELECT a.id, a.dia, a.horario, a.servico, a.valor, c.nome AS cliente_nome
     FROM agendamentos a
     JOIN clientes c ON a.cliente_id = c.id
     ORDER BY a.dia, a.horario"
);
$agendamentoSelecionadoId = (int) ($_POST['agendamento_id'] ?? $_GET['agendamento_id'] ?? 0);
$servicoSelecionado = trim($_POST['servico'] ?? '');
$valorSelecionado = $_POST['valor'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $agendamentoSelecionadoId) {
    $stmt = $conn->prepare("SELECT servico, valor FROM agendamentos WHERE id = ?");
    $stmt->bind_param('i', $agendamentoSelecionadoId);
    $stmt->execute();
    $selecionado = $stmt->get_result()->fetch_assoc();
    if ($selecionado) {
        $servicoSelecionado = $selecionado['servico'];
        $valorSelecionado = $selecionado['valor'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Serviços e pagamento</title>
    <!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h2>Serviços e pagamento</h2>
<!--Exibe mensagem de alerta em destaque, caso tenha sido definida -->
<?php if ($alerta): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 1em;">
        <?= htmlspecialchars($alerta) ?>
    </div>
<?php endif; ?>
<form action="" method="post">
    <label for="agendamento_id">Agendamento:</label>
    <select id="agendamento_id" name="agendamento_id" required>
        <option value="">Selecione um agendamento</option>
        <?php while ($row = $agendamentos->fetch_assoc()): ?>
            <option value="<?= (int) $row['id'] ?>" <?= (int) $row['id'] === $agendamentoSelecionadoId ? 'selected' : '' ?>><?= htmlspecialchars($row['cliente_nome'] . ' - ' . $row['dia'] . ' ' . $row['horario']) ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label for="servico">Serviço:</label>
    <input type="text" id="servico" name="servico" value="<?= htmlspecialchars($servicoSelecionado) ?>" required>
    <br><br>

    <label for="valor">Valor do serviço (R$):</label>
    <input type="number" step="0.01" min="0" id="valor" name="valor" value="<?= htmlspecialchars((string) $valorSelecionado) ?>" required>
    <br><br>

    <button type="submit">Salvar serviço</button>
</form>

<br>
<a href="principal.php">Voltar</a>

</body>
</html>
