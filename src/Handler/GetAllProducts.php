<?php

declare(strict_types=1);

namespace Alfa\Handler;

use Alfa\Banco\Conexao;
use Alfa\Entity\Product;
use Alfa\Repository\ProductRepository;
use Alfa\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;

final class GetAllProducts extends BaseHandler
{
  public function __invoke(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface 
  {
    $productRepository = new ProductRepository(new Conexao);
    $productService = new ProductService($productRepository);
    $product = $productService->getAll();
    $response->getBody()->write(
      $this->serializer->serialize($product, 'json')
    );
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }
}
