<?php

$numRecv = count($this->clients) - 1;
echo sprintf("\n" . 'Conexao %d enviou uma requisicao' /*. $msg . '"'*/ . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$messageObj = message_getProtocol($msg);
echo $messageObj->request->method . "\n";
switch($messageObj->request->method){
	case "uploadCalendarByDay":
		echo "Solicitacao de agenda recebida\n";
		$agenda = carregarAgenda("2018-02-27", "1");//PEGAR INFORMAÇÕES DO REQUEST RECEBIDO -- VER COMO PEGAR INFORMAÇÕES DO USUÁRIO
		$from->send(message_setProtocol($messageObj->request->id,"200","Success - DATA PADRAO DEFINIDA COMO 27-02-18","1.0.5","uploadScheduleByDay",$agenda));
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