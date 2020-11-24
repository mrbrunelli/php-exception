<?php

declare(strict_types=1);

namespace Alfa\Repository;

use Alfa\Entity\Product;
use Alfa\Repository\ProductRepositoryInterface;
use PDO;

class ProductRepository implements ProductRepositoryInterface
{
  protected PDO $connection;

  public function __construct(PDO $connection)
  {
    $this->connection = $connection;
  }

  public function add(Product $product): void
  {
    $stmt = $this->connection->prepare("insert into produto (descricao, valor) values (:descricao, :valor)");
    $stmt->bindValue(':descricao', $product->descricao);
    $stmt->bindValue(':valor', $product->valor);
    $stmt->execute();
  }

  public function getAll(): array
  {
    $stmtConsulta = $this->connection->prepare("select * from produto");
    $stmtConsulta->setFetchMode(PDO::FETCH_CLASS, Product::class);
    $stmtConsulta->execute();
    return $stmtConsulta->fetchAll();
  }

  public function getById(int $id): Product
  {
    $stmtConsulta = $this->connection->prepare("select * from produto where id = :id");
    $stmtConsulta->setFetchMode(PDO::FETCH_CLASS, Product::class);
    $stmtConsulta->bindValue(':id', $id);
    $stmtConsulta->execute();
    return $stmtConsulta->fetch();
  }
}
