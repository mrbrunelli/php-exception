<?php

declare(strict_types=1);

use Alfa\Banco\Conexao;
use Alfa\Banco\Exception\SintaxeErroException;
use Alfa\Banco\Exception\TabelaInexistenteException;
use Alfa\Entidade\Produto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;

require_once '../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load('../.env');
$conexao = new Conexao();

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
  $response->getBody()->write("Hello World");
  return $response;
});

$app->get('/message[/{nome}]', function (Request $request, Response $response, $args) {
  $name = $args['name'] ?? "Anonymous";
  $response->getBody()->write("Hello $name");
  return $response;
});

$app->post('/products', function (Request $request, Response $response) use ($conexao) {
  try {
    $produto = json_decode($request->getBody()->getContents());
    $stmt = $conexao->prepare("insert into produto (descricao, valor) values (?, ?)");
    $stmt->bindParam(1, $produto->descricao);
    $stmt->bindParam(2, $produto->valor);
    $stmt->execute();
    $idProduto = $conexao->lastInsertId();
    $stmtConsulta = $conexao->prepare("select * from produto where id = ?");
    $stmtConsulta->bindParam(1, $idProduto);
    $stmtConsulta->setFetchMode(PDO::FETCH_CLASS, Produto::class);
    $stmtConsulta->execute();
    $produtoConsulta = $stmtConsulta->fetch();
    $response->getBody()->write(json_encode($produtoConsulta));
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(201);
  } catch (PDOException $e) {
    $response->getBody()->write(
      json_encode([
        'error' => $e->getMessage()
      ])
    );
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(500);
  }
});

$app->get('/products', function (Request $request, Response $response) use ($conexao) {
  try {
    $stmtConsulta = $conexao->prepare("select * from produto");
    $stmtConsulta->setFetchMode(PDO::FETCH_CLASS, Produto::class);
    $stmtConsulta->execute();
    $response->getBody()->write(json_encode($stmtConsulta->fetchAll()));
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $response->getBody()->write(
      json_encode([
        'error' => $e->getMessage()
      ])
    );
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(500);
  }
});

$app->get('/products/{id}', function (Request $request, Response $response, $args) use ($conexao) {
  try {
    $stmtConsulta = $conexao->prepare("select * from produto where id = ?");
    $stmtConsulta->bindParam(1, $args['id']);
    $stmtConsulta->setFetchMode(PDO::FETCH_CLASS, Produto::class);
    $stmtConsulta->execute();
    $produto = $stmtConsulta->fetch();
    if (!$produto) {
      return $response
        ->withStatus(204);
    }
    $response->getBody()->write(json_encode($produto));
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $response->getBody()->write(
      json_encode([
        'error' => $e->getMessage()
      ])
    );
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(500);
  }
});

$app->run();
