<?php

$numRecv = count($this->clients) - 1;
echo sprintf("\n" . 'Conexao %d enviou uma requisicao - ' . date('H:i:s d-m-Y') . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$messageObj = message_getProtocol($msg);

//echo $messageObj->token . "\n";

echo $messageObj->request->method . "\n";

switch($messageObj->request->method){
	
	
	case "updateScheduleByDay":
		echo "Solicitacao de select de agenda recebida - " . $messageObj->request->data->date . "\n";
		if(!validar_estrutura_data($messageObj->request->data, array("date"))){
			$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("updateScheduleByDay" => false)));
			echo "Dados necessários nao recebidos\n";
			echo "Resposta enviada\n";
			break;
		}
		$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
		
		$inicio_servico = "07:00:00";
		$termino_servico = "18:00:00"; //FAZER CONSULTA PARA BUSCAR ESSES PADROES
		$intervalo_padrao = "01:00:00";

		$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, getJWT($messageObj->token)->id);
		$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",$agenda));
		//print_r($agenda);
		echo "Resposta enviada\n";
		break;
		
		
	case "setSchedule":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setSchedule",array("isSchedule" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao de insert de agenda recebida\n";
			if(!validar_estrutura_data($messageObj->request->data, array("date","hour"))){
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("isSchedule" => false)));
				echo "Dados necessários nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			$agenda = inserirAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			switch($agenda){
				case "success":
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setSchedule",array("isSchedule" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando demais dispositivos\n";
					$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
					global $conexoes;
					print_r($conexoes);
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
			if(!validar_estrutura_data($messageObj->request->data, array("id","reasonForCancellation"))){// FALTA O ARRAY reasonForCancellation
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("isScheduleCanceled" => false)));
				echo "Dados necessários nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
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


	case "reasonForCancellation":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelSchedule",array("isScheduleCanceled" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao de motivo de cancel de agenda recebida\n";
			$motivos = carregarMotivosCancelamento($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","reasonForCancellation",$motivos));
			//print_r($agenda);
			echo "Resposta enviada\n";
		}
		break;


	case "setUser":
		echo "Solicitacao de insert de usuario recebida\n";
		if(!validar_estrutura_data($messageObj->request->data, array("name","cpf","email","phone","password"))){
			$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("isUser" => false)));
			echo "Dados necessários nao recebidos\n";
			echo "Resposta enviada\n";
			break;
		}
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
			if(!validar_estrutura_data($messageObj->request->data, array("user","password"))){
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("isSignIn" => false)));
				echo "Dados necessários nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			$user = consultaUsuario($messageObj->request->data, $messageObj->request->client);
		}else{
			if(!validar_estrutura_data($messageObj->request->data, array("id"))){
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("isSignIn" => false)));
				echo "Dados necessários nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			$user = consultaUsuario(getJWT($messageObj->token), $messageObj->request->client);
		}
		if(is_array($user)){
			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","signIn",array("isSignIn" => true, "user" => (array)$user, "token" => setJWT($user['id']))));
			global $conexoes;
			$conexoes["{$from->resourceId}"] = array("userId"=>$user['id'], "userEmail"=>$user['email'], "userName"=>$user['name']);
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
			if(!validar_estrutura_data($messageObj->request->data, array("cod"))){
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("isConfirmed" => false)));
				echo "Dados necessários nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
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
		if(!validar_estrutura_data($messageObj->request->data, array("cpf"))){
			$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("isConfirmed" => false)));
			echo "Dados necessários nao recebidos\n";
			echo "Resposta enviada\n";
			break;
		}
		$dados = consultaUsuarioCpf($messageObj->request->data, $messageObj->request->client); // VERIFICAR SE VAI CONTINUAR SENDO REALIZADA A BUSCA PELO EMAIL, CASO CONTINUE PODE TIRAR ESSE FUNÇÃO, MAS SE FOR PELO CPF DEVE-SE MUDAR ESSA FUNÇÃO E CONTINUAR ASSIM
		echo "DADOSID: " . $dados["id"] . "\n";
		$novaChave = gerarNovaChave($messageObj->request->client, $dados["id"]);
		switch($novaChave[0]){
			case "success":
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","generateNewKey",array("isKey" => true, "email" => $novaChave[1])));
				global $conexoes;
				$conexoes["{$from->resourceId}"]["cpf"] = $messageObj->request->data->cpf;
				$conexoes["{$from->resourceId}"]["newUserId"] = $dados["id"];
				print_r($conexoes["{$from->resourceId}"]);
				echo "Resposta enviada\n";				
				break;
			case "failed":
				echo "Erro ao gerar nova chave do usuario\n";
				$from->send(message_setProtocol($messageObj->request->id,"609","Error - Could not generate new key","1.0.5","generateNewKey",array("isKey" => false)));
				echo "Resposta enviada\n";
				break;
		}
		break;
		
	case "recoverPassword":
		echo "Solicitacao de recuperacao de senha recebida\n";
		if(!validar_estrutura_data($messageObj->request->data, array("password","cod"))){
			$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("isConfirmed" => false)));
			echo "Dados necessários nao recebidos\n";
			echo "Resposta enviada\n";
			break;
		}
		global $conexoes;
		$novaSenha = gravarSenha($messageObj->request->data, $messageObj->request->client, $conexoes["{$from->resourceId}"]["cpf"]);
		switch($novaSenha){
			case "success":
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","recoverPassword",array("isRecovered" => true, "token" => setJWT($conexoes["{$from->resourceId}"]["newUserId"]))));
				echo "Resposta enviada\n";				
				break;
			case "failed":
				echo "Erro ao gravar nova senha do usuario\n";
				$from->send(message_setProtocol($messageObj->request->id,"610","Error - Could not recovered password","1.0.5","recoverPassword",array("isRecovered" => false)));
				echo "Resposta enviada\n";
				break;
		}
		break;


	case "updateFoodPlan":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateFoodPlan",array("isFoodPlan" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao de update de plano alimentar recebida\n";
			$plano_alimentar = carregarPlanoAlimentar($messageObj->request->client, getJWT($messageObj->token)->id);
			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
			//print_r($plano_alimentar);
			echo "Resposta enviada\n";
		}
		break;
		
		
	case "jwt":
		echo "Solicitacao de TESTE recebida\n";
		
		$plano_alimentar = carregarPlanoAlimentar($messageObj->request->client, getJWT($messageObj->token)->id);
		print_r($plano_alimentar);
		
		/*
		if(!validar_estrutura_data($messageObj->request->data, array("id","date","outro"))){
			$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("isRecovered" => false)));
			break;
		}
		echo "teste\n";
		*/
		
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