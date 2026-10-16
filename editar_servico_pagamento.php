<?php
$id = (int) ($_GET['id'] ?? 0);
header('Location: criar_serviço_pagamento.php?agendamento_id=' . $id);
exit();
