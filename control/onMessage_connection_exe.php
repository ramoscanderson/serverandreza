<?php

$numRecv = count($this->clients) - 1;
echo sprintf("\n" . 'Conexao %d enviou uma requisicao - ' . date('H:i:s d-m-Y') . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$messageObj = message_getProtocol($msg);

//echo $messageObj->token . "\n";

echo $messageObj->request->method . "\n";

switch($messageObj->request->method){
	
	
	case "updateScheduleByDay":
		echo "Solicitacao de select de agenda recebida - " . $messageObj->request->data->date . "\n";
		$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
		
		$inicio_servico = "07:00:00";
		$termino_servico = "18:00:00"; //FAZER CONSULTA PARA BUSCAR ESSES PADROES
		$intervalo_padrao = "01:00:00";

		$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, getJWT($messageObj->token)->id);
		$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",$agenda));
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
					$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
					global $conexoes;
					
					$inicio_servico = "07:00:00";
					$termino_servico = "18:00:00"; //FAZER CONSULTA PARA BUSCAR ESSES PADROES
					$intervalo_padrao = "01:00:00";

					foreach ($this->clients as $client) {
						$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $conexoes["$client->resourceId"]["userId"]);
						echo "Enviando agenda para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
						$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",(array)$agenda));
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
					$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
					global $conexoes;

					$inicio_servico = "07:00:00";
					$termino_servico = "18:00:00"; //FAZER CONSULTA PARA BUSCAR ESSES PADROES
					$intervalo_padrao = "01:00:00";

					foreach ($this->clients as $client) {
						$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $conexoes["$client->resourceId"]["userId"]);
						echo "Enviando agenda para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
						$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",(array)$agenda));
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
			case "0":
				echo "Erro ao gravar registro usuario\n";
				$from->send(message_setProtocol($messageObj->request->id,"607","Error - Could not insert record","1.0.5","setUser",array("isUser" => false)));
				echo "Resposta enviada\n";
				break;
			case "-1":
				echo "Erro ao enviar e-mail de confirmacao\n";
				$from->send(message_setProtocol($messageObj->request->id,"607","Error - Could not send e-mail record","1.0.5","setUser",array("isUser" => true)));
				echo "Resposta enviada\n";
				break;
			default:
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setUser",array("isUser" => true, "token" => setJWT($user))));
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


	case "confirmUser":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setSchedule",array("isSchedule" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao de validacao de usuario recebida\n";
			$valida = validaUsuario($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			switch($valida){
				case "success":
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","confirmUser",array("isConfirmed" => true)));
					echo "Resposta enviada\n";				
					break;
				case "failed":
					echo "Erro ao confirmar usuario\n";
					$from->send(message_setProtocol($messageObj->request->id,"608","Error - Could not validate record","1.0.5","confirmUser",array("isConfirmed" => false)));
					echo "Resposta enviada\n";
					break;
			}
		}
		break;


	case "generateNewKey":
		echo "Solicitacao para gerar nova chave de usuario recebida\n";
		$dados = consultaUsuarioEmail($messageObj->request->data, $messageObj->request->client); // VERIFICAR SE VAI CONTINUAR SENDO REALIZADA A BUSCA PELO EMAIL, CASO CONTINUE PODE TIRAR ESSE FUNÇÃO, MAS SE FOR PELO CPF DEVE-SE MUDAR ESSA FUNÇÃO E CONTINUAR ASSIM
		$novaChave = gerarNovaChave($messageObj->request->client, $dados["id"]);
		switch($novaChave){
			case "success":
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","generateNewKey",array("isKey" => true)));
				echo "Resposta enviada\n";				
				break;
			case "failed":
				echo "Erro ao gerar nova chave do usuario\n";
				$from->send(message_setProtocol($messageObj->request->id,"609","Error - Could not generate new key","1.0.5","generateNewKey",array("isKey" => false)));
				echo "Resposta enviada\n";
				break;
		}
		break;
		
	case "recoverPass":
		echo "Solicitacao de recuperacao de senha recebida\n";
		$novaSenha = gravarSenha($messageObj->request->data, $messageObj->request->client);
		switch($novaChave){
			case "success":
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","recoverPass",array("isRecovered" => true)));
				echo "Resposta enviada\n";				
				break;
			case "failed":
				echo "Erro ao gravar nova senha do usuario\n";
				$from->send(message_setProtocol($messageObj->request->id,"610","Error - Could not recovered password","1.0.5","recoverPass",array("isRecovered" => false)));
				echo "Resposta enviada\n";
				break;
		}
		break;
		
		
	case "jwt":
		echo "Solicitacao de TESTE recebida\n";
		//getJWT($messageObj->token);
		//envia_email("", $destinatario, $assunto, $mensagem);
		envia_email("Código de verificação", "ticion@gmail.com", "System Confirmation", "Seja bem vindo " . "Anderson Caciator Ramos" . "\n\n" . "Este é seu código de verificação: " . "1234");
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