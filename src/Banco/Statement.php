<?php

declare(strict_types=1);

namespace Alfa\Banco;

use Alfa\Banco\Exception\CheckConstraintException;
use Alfa\Banco\Exception\SintaxeErroException;
use Alfa\Banco\Exception\TabelaInexistenteException;
use Alfa\Banco\Exception\UniqueConstraintException;
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
      switch ($e->errorInfo[1]) {
        case '1146':
          throw new TabelaInexistenteException($e->getMessage());
          break;
        case '1064':
          throw new SintaxeErroException($e->getMessage());
          break;
        case '1062':
          throw new UniqueConstraintException($e->getMessage());
          break;
        case '3819':
          throw new CheckConstraintException($e->getMessage());
          break;
        default:
          throw $e;
          break;
      }
    }
  }
}
