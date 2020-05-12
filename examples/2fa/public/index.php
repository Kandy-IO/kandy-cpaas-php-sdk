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
	return $response->withStatus(302)->withHeader('Location', '/login');
});

$app->get('/login', function (Request $request, Response $response, $args) {
	$session = new \SlimSession\Helper;
	if ($session->logged_in) {
		return $response->withStatus(302)->withHeader('Location', '/dashboard');
	}
	$renderer = new PhpRenderer('templates/');

	return $renderer->render($response, 'login.php');
});

$app->post('/login', function (Request $request, Response $response, $args) {
	$session = new \SlimSession\Helper;
	if ($session->logged_in) {
		return $response->withStatus(302)->withHeader('Location', '/dashboard');
	}

	$_input = $request->getParsedBody();
	$user_email = getenv('EMAIL');
	$user_password = getenv('PASSWORD');

	$email = $_input['email'];
	$password = $_input['password'];

	if (!isset($email) || !isset($password)) {
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'login.php', ['alert' => 'Invalid Credentials. Please try again.']);

	} elseif ($email != $user_email || $password != $user_password) {
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'login.php', ['alert' => 'Invalid Credentials. Please try again.']);
	}

	$session->cred_verified = true;
	return $response->withStatus(302)->withHeader('Location', '/verify');
});

$app->get('/verify', function (Request $request, Response $response, $args) {
	$session = new \SlimSession\Helper;

	if (!$session->cred_verified) {
		return $response->withStatus(302)->withHeader('Location', '/login');
	}

	$renderer = new PhpRenderer('templates/');
	return $renderer->render($response, 'verify.php');
});

$app->post('/verify', function (Request $request, Response $response, $args) use($client) {
	$session = new \SlimSession\Helper;
	$_input = $request->getParsedBody();
	$code_id = $session->codeid;
	$code = $_input['code'];

	$params = [
    'code_id'=>$code_id,
    'verification_code'=>$code
	];
	$res = $client->twofactor->verify_code($params);

	if ($res['verified']) {
		$session->logged_in = true;
		return $response->withStatus(302)->withHeader('Location', '/dashboard');
  } else {
		$renderer = new PhpRenderer('templates/');
		return $renderer->render($response, 'verify.php', ['alert' => $res['message']]);
  }
});

$app->post('/sendcode', function (Request $request, Response $response, $args) use($client) {
	$session = new \SlimSession\Helper;
	$_input = $request->getParsedBody();
	$tfa_method = $_input['tfa'];

	if ($tfa_method == 'email' && null == getenv('DESTINATION_EMAIL')) {
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'verify.php', ['alert' => 'please enter a destination email in your .env file.']);

	} elseif ($tfa_method == 'sms' && null == getenv('DESTINATION_NUMBER')) {
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'verify.php', ['alert' => 'please enter a destination number in your .env file.']);
	}

	if ($tfa_method== 'email') {
		$params = [
			'message'=>'Your verification code {code}',
			'destination_address'=>getenv('DESTINATION_EMAIL'),
			'method'=>'email',
			'subject'=>'Twofactor verification'
		];
		$code_id = $client->twofactor->send_code($params);
		$session->codeid = $code_id['code_id'];
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'verify.php', ['success_msg' =>'verification code sent successfully.']);
	} elseif ($tfa_method== 'sms') {
		$params = [
			'message'=>'Your verification code {code}',
			'destination_address'=>getenv('DESTINATION_NUMBER'),
		];
		$code_id = $client->twofactor->send_code($params);
		$session->codeid = $code_id['code_id'];
		$renderer = new PhpRenderer('templates/');

		return $renderer->render($response, 'verify.php', ['success_msg' =>'verification code sent successfully.']);
	}
});

$app->get('/dashboard', function (Request $request, Response $response, $args) {
	$session = new \SlimSession\Helper;

	if (!$session->logged_in) {
		return $response->withStatus(302)->withHeader('Location', '/login');
	}

	$renderer = new PhpRenderer('templates/');
	return $renderer->render($response, 'dashboard.php');
});

$app->post('/logout', function (Request $request, Response $response, $args) {
	$session = new \SlimSession\Helper;
	$session->logged_in = false;

	return $response->withStatus(302)->withHeader('Location', '/login');
});

$app->run();