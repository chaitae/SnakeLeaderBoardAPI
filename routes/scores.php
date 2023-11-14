<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Selective\BasePath\BasePathMiddleware;
    use Psr\Http\Message\ResponseInterface;
    use Slim\Exception\HttpNotFoundException;
    use Slim\Factory\AppFactory;
    use Selective\BasePath\BasePathDetector;

$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->setBasePath("/leaderboard/public");

$app->addErrorMiddleware(true, true, true);
//Get all scores
$app->get('/scores', function(Request $request, Response $response){
    $sql = "SELECT * FROM snakegame";
    try{
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $scores = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        $response->getBody()->write(json_encode($scores));
        return $response;
    }catch(PDOException $e){
        echo '{"error": {"text" :'. $e->getMessage() . '}';
    }
});
//single score
$app->get('/scores/{username}', function(Request $request, Response $response){
    $username = $request->getAttribute('username');
    $sql = "SELECT * FROM snakegame WHERE username = ?";
    try{
        $db = new db();
        $db = $db->connect();
        $stmt = $db -> prepare($sql);
        $stmt->bindParam(1,$username);
        $stmt-> execute();
        $scores = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        $response->getBody()->write(json_encode($scores));
        return $response;
    }catch(PDOException $e){
        echo '{"error": {"text" :'. $e->getMessage() . '}';
    }
});
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("this is when it's empty");
    return $response;
});
$app->post('/scores/add', function(Request $request, Response $response){
    $username = $request->getParam('username');
    $score = $request->getParam('score');
    $sql = "INSERT INTO snakegame (username, score) VALUES (:username, :score)";
    
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':score', $score);
        $stmt->execute();
        
        $db = null;

        // No need to fetch results after an INSERT

        $response->getBody()->write('{"notice": {"text": "Score Added"}}');
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);  // 201 Created status for a successful creation
    } catch (PDOException $e) {
        $response->getBody()->write('{"error": {"text": "'. $e->getMessage() . '"}}');
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);  // 500 Internal Server Error for database-related errors
    }
});
