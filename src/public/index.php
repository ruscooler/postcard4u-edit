<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
spl_autoload_register(function ($classname) {
    require ("../classes/" . $classname . ".php");
});

require "../classes/ServiceAuth.php";
require "../constants.php";

$config['displayErrorDetails'] = true;
$config['db']['host']   = DB_HOST;
$config['db']['user']   = DB_USER;
$config['db']['pass']   = DB_PASS;
$config['db']['dbname'] = DB_NAME;


$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['view'] = new \Slim\Views\PhpRenderer("../templates/");

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};



$app->get('/', function (Request $request, Response $response) use ($app) {
    $mapper = new TicketMapper($this->db);
    $this->logger->addInfo("Links list");
    $tickets = $mapper->getTickets();
    $response = $this->view->render($response, "links.phtml", ["tickets" => $tickets, "router" => $this->router]);
    return $response;
});

$app->get('/postcard', function (Request $request, Response $response) use ($app) {
    $mapper = new TicketMapper($this->db);
    $allGetVars = $request->getQueryParams();
    $productId = $allGetVars['productId'];
    $ticket = $mapper->getTicketById($productId);
    $response = $this->view->render($response, "poscard_view.php", ["ticket" => $ticket, "router" => $this->router]);
    return $response;
});

$app->get('/postcard-edit', function (Request $request, Response $response) use ($app) {
    $mapper = new TicketMapper($this->db);
    $allGetVars = $request->getQueryParams();
    $productId = $allGetVars['productId'];
    $ticket = $mapper->getTicketById($productId);
    $response = $this->view->render($response, "poscard_edit.php", ["ticket" => $ticket, "router" => $this->router]);
    return $response;
});

$app->post('/savecard', function (Request $request, Response $response) {
    $mapper = new TicketMapper($this->db);
    $data = $request->getParsedBody();
    $ticket_data = [];
    $ticket_data['product_id'] = $data['postcard_id'];
    $ticket_data['product_name_ru'] = $data['product_name_ru'];
    $ticket_data['category_name_ru'] = $data['category_name_ru'];
    $ticket_data['product_description_ru'] = $data['product_description_ru'];
    $ticket_data['product_idealfor_ru'] = $data['product_idealfor_ru'];
    $ticket_data['status'] = $data['status'];
    $ticket = new TicketEntity($ticket_data);
    $ticket_mapper = new TicketMapper($this->db);
    $ticket_mapper->save($ticket);

    $response = $response->withRedirect("postcard?productId=" . $ticket_data['product_id'] );
    return $response;

});

$app->post('/savecard_line', function (Request $request, Response $response) {
    $mapper = new TicketMapper($this->db);
    $data = $request->getParsedBody();
    $ticket_data = [];
    $ticket_data['product_id'] = $data['postcard_id'];
    $ticket_data['product_frame'] = $data['NewPostcardFrame'];
    $ticket_data['product_text_input'] = $data['NewPostcardTextInput'];
    $ticket = new TicketEntity($ticket_data);
    $ticket_mapper = new TicketMapper($this->db);
    $ticket_mapper->saveLine($ticket);

    $response = $response->withRedirect("postcard?productId=" . $ticket_data['product_id'] );
    return $response;

});

$app->post('/saveMap', function (Request $request, Response $response) {
    $mapper = new TicketMapper($this->db);
    $data = $request->getParsedBody();

    define('UPLOAD_DIR', 'uploads/');
    $savedMap = $data['savedMap'];
    $productId = $data['productId'];
    $savedMap = str_replace('data:image/png;base64,', '', $savedMap);
    $savedMap = str_replace(' ', '+', $savedMap);
    $data = base64_decode($savedMap);
    $unique = substr(uniqid(rand(), true), 4, 4);
    $file = UPLOAD_DIR . $productId . '-'. $unique . '.jpeg';
    $success = file_put_contents($file, $data);
    print $success ? $file : 'Unable to save the file.';

    return $response;

});

$app->get('/proxy', function (Request $request, Response $response) {
    $allGetVars = $request->getQueryParams();
    header('Access-Control-Max-Age:' . 5 * 60 * 1000);
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Request-Method: *');
    header('Access-Control-Allow-Methods: OPTIONS, GET');
    header('Access-Control-Allow-Headers *');
    header("Content-Type: application/javascript");
    $url = $allGetVars['url'];
    $callback = $allGetVars['callback'];
    $file_details = get_url_details($url, 1, $callback);

    if (!in_array($file_details["mime_type"], array("image/jpg", "image/png",  "image/jpeg")))
    {
        $response = "error:Application error";
    } else {
        $re_encoded_image = sprintf(
            'data:%s;base64,%s', $file_details["mime_type"], base64_encode($file_details["data"])
        );
        $response = "{$callback}(" . json_encode($re_encoded_image) . ")";
    }

    return $response;

});

function get_url_details($url, $attempt = 1, $callback = "")
{
    $pathinfo = pathinfo($url);
    $max_attempts = 10;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 0);
    //curl_setopt($ch, CURLOPT_PROXY, 'username:password@host:port');
    $data = curl_exec($ch);
    $error = curl_error($ch);

    $mime_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    if (!in_array($mime_type, array("image/jpg", "image/png", "image/jpeg")) && $max_attempts != $attempt)
    {
        return get_url_details($url, $attempt++, $callback);
    }

    return array(
        "pathinfo" => $pathinfo,
        "error" => $error,
        "data" => $data,
        "mime_type" => $mime_type
    );
}


$app->run();
