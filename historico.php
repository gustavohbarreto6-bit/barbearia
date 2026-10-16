<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
if (time() - $_SESSION['ultimo_acesso'] > 30 * 60) {
    session_destroy();
    header('Location: login.php?expirou=1');
    exit();
}
$_SESSION['ultimo_acesso'] = time();
include 'config.php';

$sql = "SELECT a.id, a.dia, a.horario, a.servico, a.valor, c.nome AS cliente_nome,
               CAST(AES_DECRYPT(c.cpf, '$chave') AS CHAR) AS cliente_cpf,
               c.telefone, c.email
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        ORDER BY a.dia, a.horario";
$result = $conn->query($sql);
?>
<link rel="stylesheet" href="css/style.css">
<h2>Histórico de agendamentos e serviços</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Dia</th>
        <th>Horário</th>
        <th>Serviço</th>
        <th>Valor (R$)</th>
        <th>Cliente</th>
        <th>CPF</th>
        <th>Telefone</th>
        <th>E-mail</th>
        <th>Ações</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['dia']))) ?></td>
        <td><?= htmlspecialchars($row['horario']) ?></td>
        <td><?= htmlspecialchars($row['servico'] ?? '') ?></td>
        <td><?= number_format((float) $row['valor'], 2, ',', '.') ?></td>
        <td><?= htmlspecialchars($row['cliente_nome']) ?></td>
        <td><?= htmlspecialchars($row['cliente_cpf'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['telefone'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
        <td><a href="criar_serviço_pagamento.php?agendamento_id=<?= (int) $row['id'] ?>">Editar serviço e valor</a></td>
    </tr>
    <?php endwhile; ?>
</table>
<a href="principal.php">Voltar</a>
