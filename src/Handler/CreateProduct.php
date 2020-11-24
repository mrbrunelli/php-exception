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

final class CreateProduct extends BaseHandler
{
  public function __invoke(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface 
  {
    $product = $this->serializer->deserialize(
      $request->getBody()->getContents(),
      Product::class,
      'json'
    );

    $productRepository = new ProductRepository(new Conexao);
    $productService = new ProductService($productRepository);
    $productService->add($product);
    return $response->withStatus(201);
  }
}
