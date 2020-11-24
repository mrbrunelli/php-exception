<?php

declare(strict_types=1);

namespace Alfa\Service;

use Alfa\Entity\Product;
use Alfa\Repository\ProductRepositoryInterface;

final class ProductService
{
  protected $productRepository;

  public function __construct(ProductRepositoryInterface $productRepository)
  {
    $this->productRepository = $productRepository;
  }

  public function add(Product $product)
  {
    $this->productRepository->add($product);
  }

  public function getById(int $id): Product
  {
    return $this->productRepository->getById($id);
  }

  public function getAll(): array
  {
    return $this->productRepository->getAll();
  }
}