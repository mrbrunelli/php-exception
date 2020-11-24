<?php

declare(strict_types=1);

use Alfa\Banco\Conexao;
use Alfa\Handler\CreateProduct;
use Alfa\Handler\GetAllProducts;
use Alfa\Handler\GetProduct;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as Psr7Response;
use Slim\Routing\RouteCollectorProxy;

require_once '../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load('../.env');
$conexao = new Conexao();

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

$auth = function (Request $request, RequestHandlerInterface $handler) {
  if (!isset($request->getHeaders()['Authorization'])) {
    $response = new Psr7Response();
    $response->getBody()->write(
      json_encode([
        "error" => "Token não informado"
      ])
    );
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
  }
  $auth = $request->getHeaders()['Authorization'][0];
  list($token) = sscanf($auth, 'Basic %s');
  if (!$token) {
    $response = new Psr7Response();
    $response->getBody()->write(
      json_encode([
        "error" => "Token mal informado"
      ])
    );
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
  }
  list($usuario, $senha) = explode(":", base64_decode($token));
  if (!($usuario === "usuario" && $senha === "senha")) {
    $response = new Psr7Response();
    $response->getBody()->write(
      json_encode([
        "error" => "Usuário ou senha incorretos"
      ])
    );
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
  }
  $response = $handler->handle($request);
  return $response;
};

$cacheProduto = function (Request $request, RequestHandlerInterface $handler) {
  $file = "../data/cache/produtos.json";
  $fileExists = file_exists($file);
  if ($fileExists) {
    $request = $request->withAttribute("cache", file_get_contents($file));
  }
  $response = $handler->handle($request);
  if (!$fileExists) {
    file_put_contents($file, $response->getBody());
  }
  return $response;
};

$app->get('/', function (Request $request, Response $response) {
  $response->getBody()->write("Hello World");
  return $response;
})->add($auth);

$app->get('/mensagem[/{nome}]', function (Request $request, Response $response, $args) {
  $nome = $args['nome'] ?? "Anonymous";
  $response->getBody()->write("Hello $nome");
  return $response;
});

$app->group('/produtos', function (RouteCollectorProxy $group) use ($conexao, $cacheProduto) {
  $group->post('', CreateProduct::class);
  $group->get('/{idproduto}', GetProduct::class);
  $group->get('', GetAllProducts::class);
});

$app->run();
