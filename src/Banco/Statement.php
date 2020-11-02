<?php

declare(strict_types=1);

namespace Alfa\Banco;

use Alfa\Banco\Exception\SintaxeErroException;
use Alfa\Banco\Exception\TabelaInexistenteException;
use PDO;
use PDOException;
use PDOStatement;

class Statement extends PDOStatement
{
  public function execute($input_parameters = null)
  {
    try {
      parent::execute($input_parameters);
    } catch (PDOException $e) {
      switch ($e->getCode()) {
        case '42S02':
          throw new TabelaInexistenteException($e->getMessage());
          break;
        case '42000':
          throw new SintaxeErroException($e->getMessage());
          break;
        default:
          throw $e;
          break;
      }
    }
  }
}
