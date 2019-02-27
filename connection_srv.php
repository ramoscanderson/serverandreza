<?php
require 'Vendor/autoload.php';
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require ('model/connection_mdl.php');

global $connection;
$connection = new Connection();

$loop = \React\EventLoop\Factory::create(); // EventLoop
$socket = new \React\Socket\Server('0.0.0.0:8000', $loop);
$server = new \Ratchet\Server\IoServer(
	new \Ratchet\Http\HttpServer(
		new \Ratchet\WebSocket\WsServer(
			$connection // INSTANCIAR ESSE OBJETO FORA E REFERENCIAR AQUI, PARA DEPOIS PODER ACESSAR O ATRIBUTO client E PODER FAZER O FOREACH IGUAL NO ONMESSAGE
		)
	), 
	$socket, 
	$loop
);




/*
$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new Connection()
		)
	),
	$socket, 
	$loop
);
*/

$loop->addPeriodicTimer(60, function () {
	$memory = memory_get_usage() / 1024;
	$formatted = number_format($memory, 3).'K';
	echo date('H:i:s d-m-Y') . " Current memory usage: {$formatted}\n";	
	
	require('control/loop_confirmacaoAgenda_exe.php');
	
	/*
	global $connection;
	global $conexoes;
	foreach ($connection->getClient() as $client) {
		echo "Enviando mensagem teste para [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
		$client->send(message_setProtocol("000","500","Test","1.0.5","testAutoMessage10Seconds",array("message"=>"Teste de envio autom�tico")));
		echo "Resposta enviada\n";
	}
	*/
	
	echo "\n";
	
});


$server->run();

?>