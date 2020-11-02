<?php

declare(strict_types=1);

use Alfa\Banco\Conexao;
use Alfa\Banco\Exception\SintaxeErroException;
use Alfa\Banco\Exception\TabelaInexistenteException;
use Symfony\Component\Dotenv\Dotenv;

require_once '../vendor/autoload.php';

try {
  $dotenv = new Dotenv();
  $dotenv->load('../.env');
  $conexao = new Conexao();
  $stmt = $conexao->prepare("select * from produtos");
  $stmt->execute();
  var_dump($stmt->fetchAll());
} catch (TabelaInexistenteException $e) {
  echo "Tabela não existe no banco de dados";
  if ($_ENV["ENV"] == "dev") {
    echo $e->getMessage();
  } else {
    file_put_contents(sprintf("../data/log/%s.log", date("Y-m-d")), $e->getMessage(), FILE_APPEND);
  }
} catch (SintaxeErroException $e) {
  echo "Query escrita incorretamente";
} catch (PDOException $e) {
  echo $e->getCode() . "\n";
  echo $e->getMessage();
  if ($_ENV["ENV"] == "dev") {
    echo $e->getMessage();
  } else {
    shell_exec("sudo chmod ../data/log 777");
    file_put_contents(sprintf("../data/log/%s.log", date("Y-m-d")), $e->getMessage(), FILE_APPEND);
  }
} catch (Throwable $e) {
  echo "Erro não esperado";
}