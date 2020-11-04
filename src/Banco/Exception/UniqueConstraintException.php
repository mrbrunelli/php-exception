<?php

declare(strict_types=1);

namespace Alfa\Banco\Exception;

use Exception;

class UniqueConstraintException extends Exception
{
  public function getPersonalizedMessage(): string {
    return 'Já existe um produto com essa descrição cadastrada!';
  }
}