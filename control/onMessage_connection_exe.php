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
			array(
			
				"Date" => "2018-01-01",
				"time" => "10h00 às 11h00",
				"available" => true,
				"mySchedule" => false,
				"strAvailable" => "Horário disponível"
			),
			array(
			
				"Date" => "2018-01-01",
				"time" => "11h00 às 11h30",
				"available" => false,
				"mySchedule" => false,
				"strAvailable" => "Horário indisponível"
			),
			array(
			
				"Date" => "2018-01-01",
				"time" => "10h30 às 12h00",
				"available" => false,
				"mySchedule" => true,
				"strAvailable" => "Meu horário"
			)
		)
	)
);

sleep(1);
$from->send(json_encode($protocol_message));
echo "Respondido\n";
//echo json_decode($protocol_message) . "\n";
echo "\n";
//$from->send("Recebido");


//Enviar a mensagem para todos os outros usuᲩos
/*
foreach ($this->clients as $client) {
	if ($from !== $client) {
		// The sender is not the receiver, send to each client connected
		$client->send($msg);
	}
}
*/
?>