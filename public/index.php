<?php

declare(strict_types=1);

use Alfa\Banco\Conexao;
use Alfa\Banco\Exception\SintaxeErroException;
use Alfa\Banco\Exception\TabelaInexistenteException;
use Alfa\Banco\Exception\CheckConstraintException;
use Alfa\Banco\Exception\UniqueConstraintException;
use Alfa\Entidade\Produto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;
use Alfa\Helper\HttpResponseErrorSerializer;
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

/*$app->add(function (Request $request, RequestHandlerInterface $handler) {
  $response = $handler->handle($request);
  $content = (string) $response->getBody();
  $responseCustom = new Psr7Response();
  $responseCustom->getBody()->write("Antes ". $content);
  return $responseCustom;
});*/

/*$app->add(function (Request $request, RequestHandlerInterface $handler) {
  $inicio = microtime(true);
  $response = $handler->handle($request);
  $fim = microtime(true);
  file_put_contents(
    "../data/log/access_log",
    sprintf(
      "%s [%s] %s %ss\n",
      date("d/m/Y H:i:s"),
      $request->getMethod(),
      $request->getUri(),
      round($fim-$inicio, 2)
    ),
      FILE_APPEND
  );
  return $response;
});*/

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
  $group->post('', function (Request $request, Response $response) use ($conexao) {
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
    } catch (CheckConstraintException $e) {
      return (new HttpResponseErrorSerializer($response, $e->getPersonalizedMessage(), 400))
        ->getSerializedResponse();
    } catch (UniqueConstraintException $e) {
      return (new HttpResponseErrorSerializer($response, $e->getPersonalizedMessage(), 400))
        ->getSerializedResponse();
    } catch (Exception $e) {
      return (new HttpResponseErrorSerializer($response, $e->getMessage(), 500))
        ->getSerializedResponse();
    }
  });
  
  $group->get('', function (Request $request, Response $response) use ($conexao) {
    try {
      if ($request->getAttribute("cache")) {
        $response->getBody()->write($request->getAttribute("cache"));
        return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
      }
      $stmtConsulta = $conexao->prepare("select * from produto");
      $stmtConsulta->setFetchMode(PDO::FETCH_CLASS, Produto::class);
      $stmtConsulta->execute();
      $response->getBody()->write(json_encode($stmtConsulta->fetchAll()));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      return (new HttpResponseErrorSerializer($response, $e->getMessage(), 500))
        ->getSerializedResponse();
    }
  })->add($cacheProduto);
  
  $group->get('/{id}', function (Request $request, Response $response, $args) use ($conexao) {
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
      return (new HttpResponseErrorSerializer($response, $e->getMessage(), 500))
        ->getSerializedResponse();
    }
  });
})->add($auth);

$app->run();
