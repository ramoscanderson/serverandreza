<?php

$numRecv = count($this->clients) - 1;
echo sprintf('Conexao %d de %d enviou a mensagem: "' . $msg . '"' . "\n"
	, $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$from->send("Recebido");


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