<?php

$numRecv = count($this->clients) - 1;
echo sprintf('Conexao %d de %d enviou a mensagem: "' . $msg . '"' . "\n"
	, $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$obj = json_decode($msg);

$protocol_message = array(
"token" => "visitante",
"request" => array(
	"id" => $obj->request->id,
	"status" => "200",
	"version" => "1.0.5",
	"method" => "message",
	"data" => array(
		"message" => "Recebido"
		)
	)
);

sleep(1);
$from->send(json_encode($protocol_message));
echo "Respondido\n";
//echo json_decode($protocol_message) . "\n";
echo "\n";
//$from->send("Recebido");


//Enviar a mensagem para todos os outros usuários
/*
foreach ($this->clients as $client) {
	if ($from !== $client) {
		// The sender is not the receiver, send to each client connected
		$client->send($msg);
	}
}
*/
?>