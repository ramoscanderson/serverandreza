<?php

$numRecv = count($this->clients) - 1;
echo sprintf("\n" . 'Conexao %d enviou uma requisicao - ' . date('H:i:s d-m-Y') . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$messageObj = message_getProtocol($msg);

//echo $messageObj->token . "\n";

echo $messageObj->request->method . "\n";

switch($messageObj->request->method){
	
	
	case "updateScheduleByDay":
		echo "Solicitacao de select de agenda recebida - " . $messageObj->request->data->date . "\n";
		$agenda = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
		$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",$agenda[0]));
		echo "Resposta enviada\n";
		break;
		
		
	case "setSchedule":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setSchedule",array("isSchedule" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao de insert de agenda recebida\n";
			$agenda = inserirAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			switch($agenda){
				case "success":
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setSchedule",array("isSchedule" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando demais dispositivos\n";
					$agenda = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
					foreach ($this->clients as $client) {
						//echo "ID CONEXAO: $from->resourceId\n";
						global $conexoes;
						$usuario_atual = $conexoes["{$client->resourceId}"]["userId"];
						//echo "{$client->resourceId} - userloop: " . $usuario_atual . "\n";
						$nova_agenda;
						$cont = 0;
						foreach ($agenda[0] as $horario){
							if($usuario_atual == $agenda[1][$cont]){
								$agenda[0][$cont]["strAvailable"] = "Meu horário";
								$agenda[0][$cont]["available"] = false;
								$agenda[0][$cont]["mySchedule"] = true;
							}else{
								if($agenda[0][$cont]["strAvailable"] != "Horário disponível"){
									$agenda[0][$cont]["strAvailable"] = "Indisponível";
									$agenda[0][$cont]["available"] = false;
									$agenda[0][$cont]["mySchedule"] = false;
								}
							}
							$cont++;
						}
						//print_r($agenda[0]);
						$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",$agenda[0]));
					}
					echo "Dispositivos atualizados\n";
					break;
				case "failed":
					echo "Erro ao gravar registro na agenda\n";
					$from->send(message_setProtocol($messageObj->request->id,"603","Error - Could not insert record","1.0.5","setSchedule",array("isSchedule" => false)));
					echo "Resposta enviada\n";
					break;
			}
		}
		break;
	
	
	case "cancelSchedule":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelSchedule",array("isScheduleCanceled" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao de cancel de agenda recebida\n";
			$agenda = cancelarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			switch($agenda){
				case "success":
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelSchedule",array("isScheduleCanceled" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando demais dispositivos\n";
					$agenda = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
					foreach ($this->clients as $client) {
						//echo "ID CONEXAO: $from->resourceId\n";
						global $conexoes;
						$usuario_atual = $conexoes["{$from->resourceId}"]["userId"];
						$nova_agenda;
						$cont = 0;
						foreach ($agenda[0] as $horario){
							if($usuario_atual == $agenda[1][$cont]){
								$agenda[0][$cont]["strAvailable"] = "Meu horário";
								$agenda[0][$cont]["available"] = false;
								$agenda[0][$cont]["mySchedule"] = true;
							}else{
								if($agenda[0][$cont]["strAvailable"] != "Horário disponível"){
									$agenda[0][$cont]["strAvailable"] = "Indisponível";
									$agenda[0][$cont]["available"] = false;
									$agenda[0][$cont]["mySchedule"] = false;
								}
							}
							$cont++;
						}
						//print_r($agenda[0]);
						$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",$agenda[0]));
					}
					echo "Dispositivos atualizados\n";
					break;
				case "failed":
					echo "Erro ao gravar registro na agenda\n";
					$from->send(message_setProtocol($messageObj->request->id,"604","Error - Could not canceled record","1.0.5","cancelSchedule",array("isScheduleCanceled" => false)));
					echo "Resposta enviada\n";
					break;
			}
		}
		break;


	case "setUser":
		echo "Solicitacao de insert de usuario recebida\n";
		$user = cadastraUsuario($messageObj->request->data, $messageObj->request->client);
		switch($user){
			case "success":
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setUser",array("isUser" => true)));
				echo "Resposta enviada\n";				
				break;
			case "failed":
				echo "Erro ao gravar registro usuario\n";
				$from->send(message_setProtocol($messageObj->request->id,"607","Error - Could not insert record","1.0.5","setUser",array("isUser" => false)));
				echo "Resposta enviada\n";
				break;
		}
		break;


	case "signIn":
		echo "Solicitacao de login recebida\n";
		$user;
		if(isVisitante($messageObj->token)){
			$user = consultaUsuario($messageObj->request->data, $messageObj->request->client);
		}else{
			$user = consultaUsuario(getJWT($messageObj->token), $messageObj->request->client);
		}
		if(is_array($user)){
			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","signIn",array("isSignIn" => true, "user" => (array)$user, "token" => setJWT($user['id']))));
			global $conexoes;
			$conexoes["{$from->resourceId}"] = array("userId"=>$user['id']);
			echo "Resposta enviada\n";
		}else{
			echo "Nenhum usuario encontrado com os dados enviados\n";
			$from->send(message_setProtocol($messageObj->request->id,"606","Error - Login or password invalid","1.0.5","signIn",array("isSignIn" => false)));
			echo "Resposta enviada\n";
		}
		break;
		
		
	case "jwt":
		echo "Solicitacao de TESTE recebida\n";
		getJWT($messageObj->token);
		
		break;
		
		
	default:
		echo "Solicitacao nao reconhecida recebida\n";
		$from->send(message_setProtocol($messageObj->request->id,"602","Error - Unrecognized request","1.0.5","errorRequest",array()));
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