<?php

$numRecv = count($this->clients) - 1;
echo sprintf("\n" . 'Conexao %d enviou uma requisicao' /*. $msg . '"'*/ . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$messageObj = message_getProtocol($msg);
echo $messageObj->request->method . "\n";

switch($messageObj->request->method){
	
	
	case "updateScheduleByDay":
		echo "Solicitacao de select de agenda recebida\n";
		$agenda = carregarAgenda($messageObj->request->data, "1");//PEGAR INFORMAÇÕES DO REQUEST RECEBIDO -- VER COMO PEGAR INFORMAÇÕES DO USUÁRIO
		$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","uploadScheduleByDay",$agenda));
		echo "Resposta enviada\n";
		break;
		
		
	case "setSchedule":
		echo "Solicitacao de insert de agenda recebida\n";
		$agenda = inserirAgenda($messageObj->request->data, "1");//PEGAR INFORMAÇÕES DO REQUEST RECEBIDO -- VER COMO PEGAR INFORMAÇÕES DO USUÁRIO
		switch($agenda){
			case "success":
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setSchedule",array("isSchedule" => true)));
				echo "Resposta enviada\n";
				echo "Atualizando demais dispositivos\n";
				$agenda = carregarAgenda($messageObj->request->data, "1");//PEGAR INFORMAÇÕES DO REQUEST RECEBIDO -- VER COMO PEGAR INFORMAÇÕES DO USUÁRIO
				foreach ($this->clients as $client) {
					$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","uploadScheduleByDay",$agenda));
				}
				echo "Dispositivos atualizados\n";
				break;
			case "failed":
				echo "Erro ao gravar registro na agenda\n";
				$from->send(message_setProtocol($messageObj->request->id,"603","Error - Could not insert record","1.0.5","setSchedule",array("isSchedule" => false)));
				echo "Resposta enviada\n";
				break;
		}
		break;
	
	
	case "cancelSchedule":
		echo "Solicitacao de cancel de agenda recebida\n";
		$agenda = cancelarAgenda($messageObj->request->data, "1");//PEGAR INFORMAÇÕES DO REQUEST RECEBIDO -- VER COMO PEGAR INFORMAÇÕES DO USUÁRIO
		switch($agenda){
			case "success":
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelSchedule",array("isScheduleCanceled" => true)));
				echo "Resposta enviada\n";
				echo "Atualizando demais dispositivos\n";
				$agenda = carregarAgenda($messageObj->request->data, "1");//PEGAR INFORMAÇÕES DO REQUEST RECEBIDO -- VER COMO PEGAR INFORMAÇÕES DO USUÁRIO
				foreach ($this->clients as $client) {
					$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","uploadScheduleByDay",$agenda));
				}
				echo "Dispositivos atualizados\n";
				break;
			case "failed":
				echo "Erro ao gravar registro na agenda\n";
				$from->send(message_setProtocol($messageObj->request->id,"604","Error - Could not canceled record","1.0.5","cancelSchedule",array("isScheduleCanceled" => false)));
				echo "Resposta enviada\n";
				break;
		}
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