CREATE TABLE IF NOT EXISTS produto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(30) NOT NULL UNIQUE,
  valor FLOAT(10,2) NOT NULL,
  data_inclusao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CHECK (valor >= 5)
);

INSERT INTO produto (descricao, valor) values ('CELULAR', 1200);
INSERT INTO produto (descricao, valor) values ('TV', 1750);
INSERT INTO produto (descricao, valor) values ('CANECA', 5);
INSERT INTO produto (descricao, valor) values ('CHALEIRA', 12);