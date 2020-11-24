<?php

declare(strict_types=1);

namespace Alfa\Repository;

use Alfa\Entity\Product;

interface ProductRepositoryInterface
{
  public function add(Product $product): void;
  public function getAll(): array;
  public function getById(int $id): Product;
}