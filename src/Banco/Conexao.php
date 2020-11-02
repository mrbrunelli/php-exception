<?php

declare(strict_types=1);

namespace Alfa\Banco;

use PDO;

class Conexao extends PDO
{
  protected $banco;
  protected $usuario;
  protected $senha;
  protected $host;

  public function __construct()
  {
    $this->banco = $_ENV["BANCO_NOME"];
    $this->usuario = $_ENV["BANCO_USUARIO"];
    $this->senha = $_ENV["BANCO_SENHA"];
    $this->host = $_ENV["BANCO_HOST"];
    $options = array(
      PDO::ATTR_STATEMENT_CLASS => array(Statement::class),
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    );
    parent::__construct("mysql:host={$this->host};dbname={$this->banco}", $this->usuario, $this->senha, $options);
  }
}
