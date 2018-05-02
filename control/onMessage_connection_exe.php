<?php

$numRecv = count($this->clients) - 1;
echo sprintf("\n" . 'Conexao %d enviou uma requisicao - ' . date('H:i:s d-m-Y') . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$messageObj = message_getProtocol($msg);

//echo $messageObj->token . "\n";

echo $messageObj->request->method . "\n";

switch($messageObj->request->method){
	
	
	case "updateMySchedules":
		echo "Solicitacao de select de agenda individual recebida\n";
		
		$agendamentos = carregarAgendaUsuario($messageObj->request->client, getJWT($messageObj->token)->id);

		$inicio_servico = "07:00:00";
		$termino_servico = "18:00:00"; //FAZER CONSULTA PARA BUSCAR ESSES PADROES
		$intervalo_padrao = "01:00:00";

		$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, getJWT($messageObj->token)->id, $messageObj->request->client);
		$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateMySchedules",$agenda[1]));
		print_r($agenda[1]);
		echo "Resposta enviada\n";
		break;


	case "updateScheduleByDay":
		echo "Solicitacao de select de agenda recebida - " . $messageObj->request->data->date . "\n";
		if(!validar_estrutura_data($messageObj->request->data, array("date"))){
			$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("updateScheduleByDay" => false)));
			echo "Dados necessarios nao recebidos\n";
			echo "Resposta enviada\n";
			break;
		}
		$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
		
		$inicio_servico = "07:00:00";
		$termino_servico = "18:00:00"; //FAZER CONSULTA PARA BUSCAR ESSES PADROES
		$intervalo_padrao = "01:00:00";

		$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, getJWT($messageObj->token)->id, $messageObj->request->client);
		$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",$agenda[0]));
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
				echo "Dados necessarios nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			echo "Validando autorizacao de agendamento\n";
			if(!validarAgendamentoUsuario(getJWT($messageObj->token)->id, $messageObj->request->client)){
				echo "Agendamento nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"612","Error - Unauthorized Scheduling","1.0.5","setSchedule",array("isSchedule" => false)));
				echo "Resposta enviada\n";
				break;
			}else{
				echo "Agendamento autorizado\n";
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
						$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $conexoes["$client->resourceId"]["userId"], $messageObj->request->client);
						echo "Enviando agenda para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
						$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",(array)$agenda[0]));
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
				echo "Dados necessarios nao recebidos\n";
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
						$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $conexoes["$client->resourceId"]["userId"], $messageObj->request->client);
						echo "Enviando agenda para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
						$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",(array)$agenda[0]));
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
			echo "Dados necessarios nao recebidos\n";
			echo "Resposta enviada\n";
			break;
		}
		$user;
		echo "Validando autorizacao de cadastro\n";
		if(validaCadastroUsuario($messageObj->request->data, $messageObj->request->client)){
			echo "Cadastro de usuario autorizado\n";
   			$user = cadastraUsuario($messageObj->request->data, $messageObj->request->client);
		}else{
			echo "Cadastro de usuario nao autorizado\n";//------------------------------------------------------------------------------------------------------------------------------------------ CONTINUAR AQUI
			$from->send(message_setProtocol($messageObj->request->id,"613","Error - Unauthorized Register","1.0.5","setUser",array("isUser" => false)));
			echo "Resposta enviada\n";
			break;
		}
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
			case "-2":
				echo "Erro, nome invalido\n";
				$from->send(message_setProtocol($messageObj->request->id,"614","Error - Invalid name","1.0.5","setUser",array("isUser" => false)));
				echo "Resposta enviada\n";
				break;
			case "-3":
				echo "Erro, cpf invalido\n";
				$from->send(message_setProtocol($messageObj->request->id,"615","Error - Invalid cpf","1.0.5","setUser",array("isUser" => false)));
				echo "Resposta enviada\n";
				break;
			case "-4":
				echo "Erro, e-mail invalido\n";
				$from->send(message_setProtocol($messageObj->request->id,"616","Error - Invalid e-mail","1.0.5","setUser",array("isUser" => false)));
				echo "Resposta enviada\n";
				break;
			case "-5":
				echo "Erro, telefone invalido\n";
				$from->send(message_setProtocol($messageObj->request->id,"617","Error - Invalid phone","1.0.5","setUser",array("isUser" => false)));
				echo "Resposta enviada\n";
				break;
			case "-6":
				echo "Erro, senha invalida\n";
				$from->send(message_setProtocol($messageObj->request->id,"618","Error - Invalid password","1.0.5","setUser",array("isUser" => false)));
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
				echo "Dados necessarios nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			$user = consultaUsuario($messageObj->request->data, $messageObj->request->client);
		}else{
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
				echo "Dados necessarios nao recebidos\n";
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
			echo "Dados necessarios nao recebidos\n";
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
			echo "Dados necessarios nao recebidos\n";
			echo "Resposta enviada\n";
			break;
		}
		global $conexoes;
		$novaSenha = gravarSenha($messageObj->request->data, $messageObj->request->client, $conexoes["{$from->resourceId}"]["cpf"]);
		switch($novaSenha){
			case "success":
				echo "NEWID: " . $conexoes["{$from->resourceId}"]["newUserId"] . "\n";
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


	case "addConsumption":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","addConsumption",array("isConsumption" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao para adicionar consumo de refeicao recebida\n";
			if(!validar_estrutura_data($messageObj->request->data, array("mealId","foodId","planId"))){
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","addConsumption",array("isConsumption" => false)));
				echo "Dados necessarios nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			$consumo = addConsumo($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			
			switch($consumo){
				case "success":
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","addConsumption",array("isConsumption" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo\n";
					$plano_alimentar = carregarPlanoAlimentar($messageObj->request->client, getJWT($messageObj->token)->id);
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
					echo "Resposta enviada\n";
					break;
				case "failed":
					echo "Erro ao gravar registro no consumo de alimento\n";
					$from->send(message_setProtocol($messageObj->request->id,"603","Error - Could not insert record","1.0.5","addConsumption",array("isConsumption" => false)));
					echo "Resposta enviada\n";
					break;
			}
		}
		break;


	case "cancelConsumption":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelConsumption",array("isCanceledConsumption" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao para cancelar consumo de refeicao recebida\n";
			if(!validar_estrutura_data($messageObj->request->data, array("mealId","foodId","planId"))){
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","cancelConsumption",array("isCanceledConsumption" => false)));
				echo "Dados necessarios nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			$consumo = cancelarConsumo($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);

			switch($consumo){
				case "success":
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelConsumption",array("isCanceledConsumption" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo\n";
					$plano_alimentar = carregarPlanoAlimentar($messageObj->request->client, getJWT($messageObj->token)->id);
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
					echo "Resposta enviada\n";
					break;
				case "failed":
					echo "Erro ao cancelar registro no consumo de alimento\n";
					$from->send(message_setProtocol($messageObj->request->id,"604","Error - Could not canceled record","1.0.5","cancelConsumption",array("isCanceledConsumption" => false)));
					echo "Resposta enviada\n";
					break;
			}
		}
		break;


	case "updateUserData":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","addConsumption",array("isConsumption" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao para update de dados de usuario recebida\n";
			if(!validar_estrutura_data($messageObj->request->data, array("cpf","email","name","phone"))){
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","updateUserData",array("isUpdateUserData" => false)));
				echo "Dados necessarios nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			$user = alterarDadosUsuario($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);

			switch($user){
				case "success":
					echo "Atualizando dispositivo\n";
					$user = consultaUsuario(getJWT($messageObj->token), $messageObj->request->client);
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateUserData",array("isUpdateUserData" => true, "user" => (array)$user, "token" => setJWT($user['id']))));
					global $conexoes;
					$conexoes["{$from->resourceId}"] = array("userId"=>$user['id'], "userEmail"=>$user['email'], "userName"=>$user['name']);
					echo "Resposta enviada\n";
					break;
				case "failed":
					echo "Erro ao atualizar usuario\n";
					$from->send(message_setProtocol($messageObj->request->id,"604","Error - Could not canceled record","1.0.5","updateUserData",array("isUpdateUserData" => false)));
					echo "Resposta enviada\n";
					break;
			}
		}
		break;


	case "updateNews":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateNews",array("updateNews" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao de updateNews recebida\n";
			$news = carregarNew($messageObj->request->client);
			print_r($news);
			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateNews",$news));
			echo "Resposta enviada\n";
		}
		break;


	case "updateCategoriesNews":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateCategoriesNews",array("updateCategoriesNews" => false)));
			echo "Resposta enviada\n";
		}else{
			echo "Solicitacao de updateCategoriesNews recebida\n";
			$categorias = carregarCategoriasNew($messageObj->request->client);
			print_r($categorias);
			$categoriasUsuario = carregarCategoriasNewUser(getJWT($messageObj->token)->id, $messageObj->request->client);
			print_r($categoriasUsuario);
			$categoriasUsuarioFinal = array();
			foreach ($categorias as $categoria){
						$encontrado = false;
						foreach ($categoriasUsuario as $categoriaUsuario){
							if($categoria["id"] == $categoriaUsuario["categoria"]){
								$categoriasUsuarioFinal[] = array("id"=>$categoria["id"], "name"=>$categoria["name"], "selected"=>false);
								$encontrado = true;
							}
						}
						if(!$encontrado){
							$categoriasUsuarioFinal[] = array("id"=>$categoria["id"], "name"=>$categoria["name"], "selected"=>true);
						}
					}
			print_r($categoriasUsuarioFinal);	
			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateCategoriesNews",$categoriasUsuarioFinal));
			echo "Resposta enviada\n";
		}
		break;
		
		


	case "updateCategoriesNewsSelected":
		if(isVisitante($messageObj->token)){
			echo "Token de visitante nao autorizado\n";
			$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateCategoriesNews",array("updateCategoriesNews" => false)));
			echo "Resposta enviada\n";
		}else{
			echo $msg . "\n";
			/*
			if(!validar_estrutura_data($messageObj->request->data, array("id"))){
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","updateCategoriesNewsSelected",array("isUpdate" => false)));
				echo "Dados necessarios nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			*/
			echo "Solicitacao de insertCategoriesNews recebida\n";
			echo getJWT($messageObj->token)->id . "\n";
			$inserir;
			if($messageObj->request->data->selected){
				$inserir = deletarCategoriasNewUser($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			}else{
				$inserir = inserirCategoriasNewUser($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			}
			switch($inserir){
				case "success":
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateCategoriesNewsSelected",array("isUpdate" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo\n";
					echo "Solicitacao de updateCategoriesNews recebida\n";
					$categorias = carregarCategoriasNew($messageObj->request->client);
					print_r($categorias);
					$categoriasUsuario = carregarCategoriasNewUser(getJWT($messageObj->token)->id, $messageObj->request->client);
					print_r($categoriasUsuario);
					$categoriasUsuarioFinal = array();
					foreach ($categorias as $categoria){
						$encontrado = false;
						foreach ($categoriasUsuario as $categoriaUsuario){
							if($categoria["id"] == $categoriaUsuario["categoria"]){
								$categoriasUsuarioFinal[] = array("id"=>$categoria["id"], "name"=>$categoria["name"], "selected"=>false);
								$encontrado = true;
							}
						}
						if(!$encontrado){
							$categoriasUsuarioFinal[] = array("id"=>$categoria["id"], "name"=>$categoria["name"], "selected"=>true);
						}
					}
					print_r($categoriasUsuarioFinal);	
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateCategoriesNews",$categoriasUsuarioFinal));
					echo "Resposta enviada\n";
					break;
				case "failed":
					echo "Erro ao gravar registro de filtro news\n";
					$from->send(message_setProtocol($messageObj->request->id,"603","Error - Could not insert record","1.0.5","updateCategoriesNewsSelected",array("isUpdate" => false)));
					echo "Resposta enviada\n";
					break;
			}			
		}
		break;
		
		
	case "jwt":
		echo "Solicitacao de TESTE recebida\n";
		
		//$img = imagecreatefromjpeg('img/walnut-2566274_640.jpg');
		//$image = imagecreatefromjpeg($img);
			//$imagem = file_get_contents('img/walnut-2566274_640.jpg');
		//converte a imagem em string base64
		//echo base64_encode($imagem);
		
		
		$path = 'img/walnut-2566274_640.jpg';
		// faça as verificações de validade da imagem...
   		$image = WideImage::load($path);
		// scala:
		$width = $_GET['width']; // verifique se é válido.
		if($width > 0){
			$scale = $image->getWidth()/$width;
		}else{
			//tratar caso de largura inválida.
		}   
		// Calcula a altura equivalente a largura passada.
		$height = $image->getHeight() * $scale;
   
		$resizedImage = $image->resize($width, $height);
   
		// Daqui para frente você faz o que deve fazer com sua imagem.
		// Por exemplo:
		//$resizedImage->saveToFile('imagem_redimencionada.jpg');
		// Ou:
		//header("Content-type: image/jpeg");
		//$resizedImage->output('jpg', 100); // Onde 100 é a qualidade em %
		
		
		
		//echo "IMAGEM = " . base64_encode($imagem) . "\n";
		$from->send(message_setProtocol($messageObj->request->id,"604","Error - Could not canceled record","1.0.5","teste",array("img" => "teste")));
		echo date('H:i:s d-m-Y') . "\n";
		$from->send(message_setProtocol($messageObj->request->id,"604","Error - Could not canceled record","1.0.5","testeimg",array("img" => base64_encode($resizedImage))));
		echo date('H:i:s d-m-Y') . "\n";
		
		break;


	case "getImage":
		echo "Solicitacao de imagem recebida\n";
		ini_set('memory_limit', '1000M');
		echo "Memória aumentada para 256mb\n";
		$imagem = file_get_contents('img/20171005_074251.jpg');
		$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","getImage",array("img" => base64_encode($imagem))));
		echo "Resposta enviada\n";
		//base64_encode(file_get_contents('img/walnut-2566274_640.jpg'))
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