<?php

declare(strict_types=1);

namespace Alfa\Banco\Exception;

use Exception;

class CheckConstraintException extends Exception
{
  public function getPersonalizedMessage(): string {
    return 'Não é possível inserir produto com valor abaixo de R$ 5,00!';
  }
}
