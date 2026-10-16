<?php
// Inicia a sessão para acessar os dados do usuário logado
session_start();
// Verifica se o usuário está autenticado; caso contrário, redireciona para a página de login
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
// Inclui o arquivo de configuração para estabelecer a conexão com o banco de dados
include 'config.php';
// Recebe o ID do agendamento a ser editado via parâmetro GET na URL
$id = $_GET['id'] ?? 0;
// Busca os dados do agendamento
$sql = "SELECT * FROM agendamentos WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$agendamento = $res->fetch_assoc();
if (!$agendamento) {
    echo "Agendamento não encontrado.";
    exit();
}
// Processa o formulário quando enviado via POST para atualizar o agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = (int) ($_POST['cliente_id'] ?? 0);
    $horario = trim($_POST['horario'] ?? '');
    $dia = trim($_POST['dia'] ?? '');
    $servico = trim($_POST['servico'] ?? '');
    $valor = $_POST['valor'] ?? '';
    if (!$cliente_id || $horario === '' || $dia === '' || $servico === '' || $valor === '' || !is_numeric($valor) || (float) $valor < 0) {
        echo "Cliente, horário, dia, serviço e valor válido são obrigatórios.";
    } else {
        $sql = "UPDATE agendamentos SET cliente_id=?, horario=?, dia=?, servico=?, valor=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssdi", $cliente_id, $horario, $dia, $servico, $valor, $id);
        $stmt->execute();
        header("Location: criar_agendamento.php");
        exit();
    }
}
// Busca os clientes para preencher o select
$clientes = $conn->query("SELECT id, nome FROM clientes ORDER BY nome");
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">

<h2>Editar agendamento</h2>
<form method="post">
    <select name="cliente_id" required>
        <?php while ($cliente = $clientes->fetch_assoc()): ?>
            <option value="<?= $cliente['id'] ?>" <?= $cliente['id'] == $agendamento['cliente_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nome']) ?></option>
        <?php endwhile; ?>
    </select>
    <input name="horario" type="time" value="<?= htmlspecialchars($agendamento['horario']) ?>" required>
    <input name="dia" type="date" value="<?= htmlspecialchars($agendamento['dia']) ?>" required>
    <input name="servico" placeholder="Serviço" value="<?= htmlspecialchars($agendamento['servico']) ?>" required>
    <input type="number" name="valor" min="0" step="0.01" placeholder="Valor (R$)" value="<?= htmlspecialchars((string) $agendamento['valor']) ?>" required>
    <button>Salvar</button>
</form>
<a href="criar_agendamento.php">Voltar</a>
