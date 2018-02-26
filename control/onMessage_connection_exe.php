<?php

$numRecv = count($this->clients) - 1;
echo sprintf("\n" . 'Conexao %d enviou uma requisicao' /*. $msg . '"'*/ . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$messageObj = message_getProtocol($msg);
echo $messageObj->request->method . "\n";
switch($messageObj->request->method){
	case "uploadCalendarByDay":
		echo "Solicitacao de agenda recebida\n";
		//CÓDIGO ABAIXO TEMPORÁRIO
		$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","uploadScheduleByDay",array(
			array(
				"id" => 1,
				"Date" => "2018-01-01",
				"time" => "10h00 as 11h00",
				"available" => true,
				"mySchedule" => false,
				"strAvailable" => "Horario disponivel"
			),
			array(
				"id" => 2,
				"Date" => "2018-01-01",
				"time" => "11h00 as 11h30",
				"available" => false,
				"mySchedule" => false,
				"strAvailable" => "Horario indisponivel"
			),
			array(
				"id" => 3,
				"Date" => "2018-01-01",
				"time" => "10h30 as 12h00",
				"available" => false,
				"mySchedule" => true,
				"strAvailable" => "Meu horario"
			)
		)));
		echo "Resposta enviada\n";
		break;
	case bbb:

		break;
	default:
		echo "Solicitacao nao reconhecida recebida\n";
		$from->send(message_setProtocol($messageObj->request->id,"602","Error - unrecognized request","1.0.5","errorRequest",array()));
		echo "Resposta default enviada\n";
		break;
}


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