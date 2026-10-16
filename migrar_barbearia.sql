CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    cpf VARBINARY(255) NOT NULL,
    telefone VARCHAR(30) NULL,
    email VARCHAR(254) NULL,
    PRIMARY KEY (id),
    INDEX idx_clientes_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS agendamentos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cliente_id INT UNSIGNED NOT NULL,
    dia DATE NOT NULL,
    horario TIME NOT NULL,
    servico VARCHAR(150) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    INDEX idx_agendamentos_cliente (cliente_id),
    INDEX idx_agendamentos_dia_horario (dia, horario),
    CONSTRAINT fk_agendamentos_clientes
        FOREIGN KEY (cliente_id) REFERENCES clientes (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
