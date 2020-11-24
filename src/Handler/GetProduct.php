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

final class GetProduct extends BaseHandler
{
  public function __invoke(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ): ResponseInterface 
  {
    $productRepository = new ProductRepository(new Conexao);
    $productService = new ProductService($productRepository);
    $product = $productService->getById((int)$args['idproduto']);
    $response->getBody()->write(
      $this->serializer->serialize($product, 'json')
    );
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  }
}
