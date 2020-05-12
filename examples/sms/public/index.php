<?php

require_once('../vendor/autoload.php');

use CpaasSdk\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

$app = AppFactory::create();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$app = \Slim\Factory\AppFactory::create();
$app->add(new \Slim\Middleware\Session([
  'name' => 'dummy_session',
  'autorefresh' => true,
  'lifetime' => '1 hour'
]));

$container = new \DI\Container;
$client = new Client([
	'client_id' => getenv('CLIENT_ID'),
	'client_secret' => getenv('CLIENT_SECRET'),
	'base_url' => getenv('BASE_URL')
]);

// Register globally to app
$container->set('session', function () {
  return new \SlimSession\Helper;
});
\Slim\Factory\AppFactory::setContainer($container);


$app->get('/', function (Request $request, Response $response, $args) {
	$renderer = new PhpRenderer('templates/');

	return $renderer->render($response, 'index.php');
});

$app->post('/', function (Request $request, Response $response, $args) use($client) {
	$_input = $request->getParsedBody();
	$message = $_input['message'];
	$number = $_input['number'];

	$params = [
		'type'=>'sms',
		'message'=>$message,
		'destination_address'=>$number,
		'sender_address'=>getenv('SENDER_NUMBER')
	];
	$msg_response = $client->conversation->create_message($params);

	if (array_key_exists('exception_id', $msg_response)) {
    $message = $msg_response['message'];
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'index.php', ['alert'=> true, 'message' => $message]);

	} elseif (array_key_exists('delivery_info', $msg_response)) {
		$message = 'Message sent successfully.';
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'index.php', ['success'=> true, 'message' => $message]);
  }
});

$app->post('/subscribe', function (Request $request, Response $response, $args) use($client) {
	$_input = $request->getParsedBody();
	$webhook_url = $_input['webhook'].'/webhook';
	$params = [
		'type'=>'sms',
		'webhook_url'=>$webhook_url,
		'destination_address'=>getenv('SENDER_NUMBER')
	];
	$subscribe_response = $client->conversation->subscribe($params);

	if (array_key_exists('subscription_id', $subscribe_response)) {
    $message = 'Created subscription';
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'index.php', ['success'=> true, 'message' => $message]);

  } elseif (array_key_exists('delivery_info', $msg_response)) {
    $message = $subscribe_response['message'];
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'index.php', ['alert'=> true, 'message' => $message]);
  }
});

$app->post('/webhook', function (Request $request, Response $response, $args) use($client) {
	$input = $request->getBody();
	$json_input = json_decode($input, true);

	$parsed_response = $client->notification->parse($json_input);
	$fp = fopen('notification.txt', 'a');
	$data = json_encode($parsed_response).PHP_EOL;
	fwrite($fp, $data);
	fclose($fp);

	return $response->withStatus(201);
});

$app->get('/notifications', function (Request $request, Response $response, $args) {
	$notification_list = array();

	$response = $response->withHeader('Content-Type','application/json');

	if (file_exists('notification.txt')) {
		$handle = fopen("notification.txt", "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				array_push($notification_list, $line);
			}
		}
	}

	$response->getBody()->write(json_encode($notification_list));

	return $response;
});

$app->run();