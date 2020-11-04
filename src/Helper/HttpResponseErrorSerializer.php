<?php

declare(strict_types=1);

namespace Alfa\Helper;

use Psr\Http\Message\ResponseInterface as Response;

class HttpResponseErrorSerializer
{
  private $response;
  private $errorMessage;
  private $statusCode = 500;

  public function __construct(Response $response, string $errorMessage, int $statusCode)
  {
    $this->response = $response;
    $this->errorMessage = $errorMessage;
    $this->statusCode = $statusCode;
  }

  public function getSerializedResponse (): Response {
    $this->response->getBody()->write(
      json_encode([
        'error' => $this->errorMessage
      ])
    );
    return $this->response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus($this->statusCode);
  }
}