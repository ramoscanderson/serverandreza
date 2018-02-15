<?php
require 'Vendor/autoload.php';
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require ('model/connection_mdl.php');

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new Connection()
		)
	),
	8000
);

$server->run();

?>