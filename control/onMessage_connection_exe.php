<?php
require ("lib/adress.php");
$numRecv = count($this->clients) - 1;
echo sprintf("\n" . 'Conexao %d enviou uma requisicao - ' . date('H:i:s d-m-Y') . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

$messageObj = message_getProtocol($msg);

//echo $messageObj->token . "\n";

echo $messageObj->request->method . "\n";
if( /*false*/ !validaToken($messageObj->token)){
	$from->send(message_setProtocol($messageObj->request->id,"605","Error - Invalid token","1.0.5","errorRequest",array()));
	echo "Token com estrutura Invalida recebido\n";
	echo "Resposta enviada\n";
}else{
	//echo "ID requisicao: " . $messageObj->request->id . "\n";
		
	switch($messageObj->request->method){
		
		case "updateMySchedules":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateMySchedules",array("updateMySchedules" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "Solicitacao de select de agenda individual recebida\n";
	
				$agendamentos = carregarAgendaUsuario($messageObj->request->client, getJWT($messageObj->token)->id);
				$opcoes = carregarOpcoesAgenda($messageObj->request->client);
				$inicio_servico = $opcoes["startService"];
				$termino_servico = $opcoes["endService"];
				$intervalo_padrao = $opcoes["defaultAttendance"];
				$intervalo_padrao_retorno = $opcoes["defaultAttendanceReturn"];
				
				//print_r($agendamentos); 
				
				$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $intervalo_padrao_retorno, getJWT($messageObj->token)->id, $messageObj->request->client);
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateMySchedules",$agenda[1]));
				//print_r($agenda[1]);
				echo "Resposta enviada\n";
			}
			break;


		case "updateConfigScheduleService": 
			echo "Solicitacao de nova configuracao de agenda administrador recebida" . "\n";

			$opcoes = carregarOpcoesAgenda($messageObj->request->client);

			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateConfigScheduleService",$opcoes));
			echo "Resposta enviada\n";
			break;


		case "updateLocations": 
			echo "Solicitacao listagem de locais administrador recebida" . "\n";

			$opcoes = carregarLocalizacoes($messageObj->request->client);

			//print_r($opcoes);

			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateLocations",$opcoes));
			echo "Resposta enviada\n";
			break;


		case "updateConfigLocations": 
			echo "Solicitacao configuracao de locais administrador recebida" . "\n";

			$opcoes = carregarLocalizacoesSemana($messageObj->request->client);

			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateConfigLocations",$opcoes));
			echo "Resposta enviada\n";
			break;


		case "setConfigScheduleService":
			echo "Solicitacao de atualizacao de opcoes da agenda administrador recebida\n";
			
			$opcoes = atualizarOpcoesAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			
			if($opcoes == "success"){
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setConfigScheduleService",array("isSetConfigScheduleService" => true)));
			}else{
				echo "erro ao atualizar opcoes da agenda \n";
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setConfigScheduleService",array("isSetConfigScheduleService" => false)));
			}
			echo "Resposta enviada\n";
			break;


		case "setConfigLocation":
			echo "Solicitacao de atualizacao de opcoes da localizacao administrador recebida\n";

			$opcoes = atualizarOpcoesLocalizacao($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);

			if($opcoes == "success"){
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setConfigLocation",array("isSetConfigLocation" => true)));
			}else{
				echo "erro ao atualizar opcoes da localizacao \n";
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setConfigLocation",array("isSetConfigLocation" => false)));
			}
			echo "Resposta enviada\n";
			break;


		case "setLocation":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setLocation",array("isSetLocation" => false)));
				echo "Resposta enviada\n";
			}else{
				//print_r($messageObj->request->data);
				echo "adicionando localizacao \n";
				$nome = date("YmdHis") . "_" .  mt_rand(10000,99999);
				echo "Novo arquivo de localizacao criado: " . $nome . "\n";
				$arquivo = fopen("img/locations/" . $nome . ".jpeg", "wb");
				$escreve = fwrite($arquivo, base64_decode(str_replace("data:image/jpeg;base64,", "", $messageObj->request->data->imageLocation)));
				fclose($arquivo);
				echo "Arquivo escrito com os dados da Localiazcao \n";
				$localizacao = addLocalizacao($server_outside . "img/locations/" . $nome . ".jpeg", $messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($localizacao == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setLocation",array("isSetLocation" => true)));
				}else{
					echo "erro ao inserir localizacao \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setLocation",array("isSetLocation" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;


		case "cancelLocation":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelLocation",array("isCanceledLocation" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "cancelando localizacao \n";
				$localizacao = deleteLocalizacao($messageObj->request->data);
				if($localizacao == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelLocation",array("isCanceledLocation" => true)));
				}else{
					echo "erro ao cancelar localizacao \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelLocation",array("isCanceledLocation" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;


		case "setConfirmedSchedule":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setConfirmedSchedule",array("isSetConfirmedSchedule" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "confirmando agedamento \n";
				
				$confirmacao = confirmarAgendamento($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($confirmacao == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setConfirmedSchedule",array("isSetConfirmedSchedule" => true)));
				}else{
					echo "erro ao confirmar agendamento \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setConfirmedSchedule",array("isSetConfirmedSchedule" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;


		case "scheduleFollowUp": 
			echo "Solicitacao de select de agenda administrador recebida" . "\n";
			
			$agendamentos = carregarAgenda7Days($messageObj->request->client, getJWT($messageObj->token)->id);
		
			//print_r($agendamentos);
			
			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","scheduleFollowUp",$agendamentos));
			//print_r($agenda);
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
			//print_r($agendamentos);
			$opcoes = carregarOpcoesAgenda($messageObj->request->client);
			$inicio_servico = $opcoes["startService"];
			$termino_servico = $opcoes["endService"];
			$intervalo_padrao = $opcoes["defaultAttendance"];
			$intervalo_padrao_retorno = $opcoes["defaultAttendanceReturn"];

			$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $intervalo_padrao_retorno, getJWT($messageObj->token)->id, $messageObj->request->client);
			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",$agenda[0]));
			//print_r($agenda);
			echo "Resposta enviada\n";
			break;


		case "updateScheduleDay":
			echo "Solicitacao de select de agenda administrativa recebida - " . $messageObj->request->data->date . "\n";
			if(!validar_estrutura_data($messageObj->request->data, array("date"))){
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","recoverPassword",array("updateScheduleDay" => false)));
				echo "Dados necessarios nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			$agendamentos = carregarAgendaAdministrativo($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
			//print_r($agendamentos);
			
			$opcoes = carregarOpcoesAgenda($messageObj->request->client);
			$inicio_servico = $opcoes["startService"];
			$termino_servico = $opcoes["endService"];
			$intervalo_padrao = $opcoes["defaultAttendance"];
			$intervalo_padrao_retorno = $opcoes["defaultAttendanceReturn"];

			$agenda = classificarAgendaAdministrativo($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, getJWT($messageObj->token)->id, $messageObj->request->client);
			$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleDay",$agenda[0]));
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
					$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","setSchedule",array("isSchedule" => false)));
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
				//print_r($messageObj->request->data);
				$agenda = inserirAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				switch($agenda){
					case "success":
						$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setSchedule",array("isSchedule" => true)));
						inserirLog("setSchedule - Usuário realizou um agendamento de consulta: " . $messageObj->request->data->date . " das " . $messageObj->request->data->hour . ".", $messageObj->request->client, getJWT($messageObj->token)->id);
						echo "Resposta enviada\n";
						echo "Atualizando demais dispositivos\n";
						//$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
						
						$agendamentosAdm = carregarAgendaAdministrativo($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
						$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
											
						global $conexoes;
						//print_r($conexoes);
						$opcoes = carregarOpcoesAgenda($messageObj->request->client);
						$inicio_servico = $opcoes["startService"];
						$termino_servico = $opcoes["endService"];
						$intervalo_padrao = $opcoes["defaultAttendance"];
						$intervalo_padrao_retorno = $opcoes["defaultAttendanceReturn"];

						foreach ($this->clients as $client) {
							if($conexoes["$client->resourceId"]["userId"] != "-1"){	
								//$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $conexoes["$client->resourceId"]["userId"], $messageObj->request->client);
								
								$agenda;
								if($conexoes["$client->resourceId"]["admin"]){
									$agenda = classificarAgendaAdministrativo($agendamentosAdm, $inicio_servico, $termino_servico, $intervalo_padrao, $conexoes["$client->resourceId"]["userId"], $messageObj->request->client);
								}else{
									$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $intervalo_padrao_retorno, $conexoes["$client->resourceId"]["userId"], $messageObj->request->client);
								}
								
								echo "Enviando agenda para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
								$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",(array)$agenda[0]));
							}
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
					$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","cancelSchedule",array("isScheduleCanceled" => false)));
					echo "Dados necessarios nao recebidos\n";
					echo "Resposta enviada\n";
					break;
				}
				$agenda = cancelarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				switch($agenda){
					case "success":
						$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelSchedule",array("isScheduleCanceled" => true)));
						inserirLog("cancelSchedule - Usuário realizou um cancelamento de consulta: " . $messageObj->request->data->id . ", motivo " . $messageObj->request->data->reasonForCancellation->id . " - " . $messageObj->request->data->reasonForCancellation->description . ".", $messageObj->request->client, getJWT($messageObj->token)->id);
						echo "Resposta enviada\n";
						echo "Atualizando demais dispositivos\n";
						//$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
						
						$agendamentosAdm = carregarAgendaAdministrativo($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
						$agendamentos = carregarAgenda($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
											
						global $conexoes;
						$opcoes = carregarOpcoesAgenda($messageObj->request->client);
						$inicio_servico = $opcoes["startService"];
						$termino_servico = $opcoes["endService"];
						$intervalo_padrao = $opcoes["defaultAttendance"];
						$intervalo_padrao_retorno = $opcoes["defaultAttendanceReturn"];

						foreach ($this->clients as $client) {
							if($conexoes["$client->resourceId"]["userId"] != "-1"){		
								//$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $conexoes["$client->resourceId"]["userId"], $messageObj->request->client);
								
								$agenda;
								if($conexoes["$client->resourceId"]["admin"]){
									$agenda = classificarAgendaAdministrativo($agendamentosAdm, $inicio_servico, $termino_servico, $intervalo_padrao, $conexoes["$client->resourceId"]["userId"], $messageObj->request->client);
								}else{
									$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $intervalo_padrao_retorno, $conexoes["$client->resourceId"]["userId"], $messageObj->request->client);
								}
								
								echo "Enviando agenda para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
								$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateScheduleByDay",(array)$agenda[0]));
							}
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
				$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","setUser",array("isUser" => false)));
				echo "Dados necessarios nao recebidos\n";
				echo "Resposta enviada\n";
				break;
			}
			$user;
			echo "Validando autorizacao de cadastro\n";
			$validacao = validaCadastroUsuario($messageObj->request->data, $messageObj->request->client);
			if(($validacao["encontrado"] && !$validacao["ativo"]) || (!$validacao["encontrado"])){
				echo "Cadastro de usuario autorizado\n";
				global $conexoes;
				print_r($validacao);
				$user = cadastraUsuario($messageObj->request->data, $messageObj->request->client, $conexoes["{$from->resourceId}"]["admin"], $validacao["atualizar"]);
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
					if(isVisitante($messageObj->token)){
						$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setUser",array("isUser" => true, "token" => setJWT($user, false, false))));
					}else{
						$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setUser",array("isUser" => true, "id" => $user)));
					}
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
				echo "CPF SENHA " . $messageObj->request->data->user . " " . $messageObj->request->data->password . " " . $messageObj->request->client . "\n";
				$user = consultaUsuario($messageObj->request->data, $messageObj->request->client);
			}else{
				echo "nao visitante ID " . getJWT($messageObj->token)->id . " " . $messageObj->request->client . "\n";
				$user = consultaUsuario(getJWT($messageObj->token), $messageObj->request->client);
			}
			if(is_array($user)){
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","signIn",array("isSignIn" => true, "user" => (array)$user, "token" => setJWT($user['id'], (str_replace("x","",$messageObj->request->version) != $messageObj->request->version), (str_replace("dev","",$messageObj->request->version) != $messageObj->request->version)))));
				global $conexoes;
				$conexoes["{$from->resourceId}"] = array("userId"=>$user['id'], 
														"userEmail"=>$user['email'], 
														"userName"=>$user['name'],
														"admin"=>(str_replace("x","",$messageObj->request->version) != $messageObj->request->version),
														"developer"=>(str_replace("dev","",$messageObj->request->version) != $messageObj->request->version));
				//print_r($conexoes);
				inserirLog("signIn - Usuário " . $user['name'] . " realizou login.", $messageObj->request->client, $user['id']);
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
					//print_r($conexoes["{$from->resourceId}"]);
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
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","recoverPassword",array("isRecovered" => true, "token" => setJWT($conexoes["{$from->resourceId}"]["newUserId"], false, false))));
					echo "Resposta enviada\n";				
					break;
				case "failed":
					echo "Erro ao gravar nova senha do usuario\n";
					$from->send(message_setProtocol($messageObj->request->id,"610","Error - Could not recovered password","1.0.5","recoverPassword",array("isRecovered" => false)));
					echo "Resposta enviada\n";
					break;
			}
			break;


		case "updateCiclePlan":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateCiclePlan",array("isCicloPlan" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "Solicitacao de update de ciclo alimentar recebida\n";
				$plano_alimentar = carregarCicloAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				//print_r($plano_alimentar);
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateCiclePlan",$plano_alimentar));
				//print_r($plano_alimentar);
				echo "Resposta enviada\n";
			}
			break;


		case "updateFoodPlan":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateFoodPlan",array("isFoodPlan" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "Solicitacao de update de plano alimentar recebida\n";
				$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				//print_r($plano_alimentar);
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
				//print_r($plano_alimentar);
				echo "Resposta enviada\n";
			}
			break;


		case "importPlan":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","addConsumption",array("isImportPlan" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "Solicitacao para duplicar plano alimentar recebida\n";
				if(!validar_estrutura_data($messageObj->request->data, array("idPlanImport","userId"))){
					$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","importPlan",array("isImportPlan" => false)));
					echo "Dados necessarios nao recebidos\n";
					echo "Resposta enviada\n";
					break;
				}
				$plano = duplicarPlano($messageObj->request->data, $messageObj->request->client);
				//$plano = "failed";

				switch($plano){
					case "success":
						$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","addConsumption",array("isImportPlan" => true)));
						echo "Resposta enviada\n";
						echo "Atualizando dispositivo\n";
						$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
						$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
						echo "Resposta enviada\n";
						break;
					case "failed":
						echo "Erro ao importar plano alimentar\n";
						$from->send(message_setProtocol($messageObj->request->id,"603","Error - Could not insert record","1.0.5","importPlan",array("isImportPlan" => false)));
						echo "Resposta enviada\n";
						break;
				}
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
						$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
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
						$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
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
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateUserData",array("isUpdateUserData" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "Solicitacao para update de dados de usuario recebida\n";
				if(!validar_estrutura_data($messageObj->request->data, array("cpf","email","name","phone"))){
					$from->send(message_setProtocol($messageObj->request->id,"611","Error - Missing data","1.0.5","updateUserData",array("isUpdateUserData" => false)));
					echo "Dados necessarios nao recebidos\n";
					echo "Resposta enviada\n";
					break;
				}
				//print_r($messageObj->request->data);
				$user = alterarDadosUsuario($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);

				switch($user){
					case "success":
						echo "Atualizando dispositivo\n";
						$user = consultaUsuario(getJWT($messageObj->token), $messageObj->request->client);
						$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateUserData",array("isUpdateUserData" => true, "user" => (array)$user, "token" => setJWT($user['id'], false, false))));
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
			if(/*isVisitante($messageObj->token)*/ false){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateNews",array("updateNews" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "Solicitacao de updateNews recebida\n";
				$news = carregarNew($messageObj->request->client);
				//print_r($news);
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateNews",$news));
				echo "Resposta enviada\n";
			}
			break;


		case "cancelNews":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelNews",array("isCanceledNew" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "Solicitacao de cancelNews recebida\n";
				$news = cancelarNew($messageObj->request->data);
				if($news == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelNews",array("isCanceledNew" => true)));
				}else{
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelNews",array("isCanceledNew" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;


		case "updateCategoriesNews":
			if(/*isVisitante($messageObj->token)*/ false){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateCategoriesNews",array("updateCategoriesNews" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "Solicitacao de updateCategoriesNews recebida\n";
				$categorias = carregarCategoriasNew($messageObj->request->client);
				//print_r($categorias);
				$categoriasUsuario = carregarCategoriasNewUser(getJWT($messageObj->token)->id, $messageObj->request->client);
				//print_r($categoriasUsuario);
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
				//print_r($categoriasUsuarioFinal);	
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateCategoriesNews",$categoriasUsuarioFinal));
				echo "Resposta enviada\n";
			}
			break;
			
			


		case "updateCategoriesNewsSelected":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateCategoriesNewsSelected",array("isUpdate" => false)));
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
						//print_r($categorias);
						$categoriasUsuario = carregarCategoriasNewUser(getJWT($messageObj->token)->id, $messageObj->request->client);
						//print_r($categoriasUsuario);
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
						//print_r($categoriasUsuarioFinal);	
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


		case "setFeedNews":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setFeedNews",array("isSetFeedNews" => false)));
				echo "Resposta enviada\n";
			}else{
				$nome = date("YmdHis") . "_" .  mt_rand(10000,99999);
				echo "Novo arquivo de imagem criado: " . $nome . "\n";
				$arquivo = fopen("img/news/" . $nome . ".jpeg", "wb");
				$escreve = fwrite($arquivo, base64_decode(str_replace("data:image/jpeg;base64,", "", $messageObj->request->data->img)));
				fclose($arquivo);
				echo "Arquivo escrito \n";

				$salvar = inserirNew($server_outside . "img/news/" . $nome . ".jpeg", $messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				
				if($salvar == "success"){
					echo "new inserida com sucesso \n";
					echo "Atualizando demais dispositivos\n";
					$news = carregarNew($messageObj->request->client);
					global $conexoes;
					foreach ($this->clients as $client) {
						echo "Enviando news para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
						$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateNews",$news));
					}
					echo "Dispositivos atualizados\n";
					
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setFeedNews",array("isSetFeedNews" => true)));
				}else{
					echo "erro ao inserir a new \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setFeedNews",array("isSetFeedNews" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;


		case "setCategory":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setFeedNews",array("isSetFeedNews" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "inserindo nova categoria \n";
				$salvar = inserirCategoria($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($salvar == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setCategory",array("isSetCategory" => true)));
				}else{
					echo "erro ao inserir a categoria \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setCategory",array("isSetCategory" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;


		case "patientFollowUp":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","patientFollowUp",null));
				echo "Resposta enviada\n";
			}else{
				echo "requisicao de acompanhamento de pacientes \n";
				$acompanhamento = carregarAcompanhamento($messageObj->request->client);
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","patientFollowUp",$acompanhamento));
				
				echo "Resposta enviada\n";
			}
			break;


		case "updatePatients":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updatePatient",null));
				echo "Resposta enviada\n";
			}else{
				echo "requisicao de pacientes \n";
				$pacientes = carregarPacientes($messageObj->request->client);
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updatePatient",$pacientes));
				
				echo "Resposta enviada\n";
			}
			break;


		case "setAttendance":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setAttendance",array("isSetAttendance" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "adicionando acompanhamento de paciente \n";
							
				$acompanhamento = inserirConsulta($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($acompanhamento == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setAttendance",array("isSetAttendance" => true)));
				}else{
					echo "erro ao inserir acompanhamento de paciente \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setAttendance",array("isSetAttendance" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;




		case "cancelAttendance":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelAttendance",array("isCancelAttendance" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "deletando consulta \n";

				//print_r($messageObj->request->data);

				$medida = deleteTimeLine($messageObj->request->data);
				if($medida == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelAttendance",array("isCancelAttendance" => true)));
					echo "Resposta enviada\n";
				}else{
					echo "erro ao deletar Consulta \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelAttendance",array("isCancelAttendance" => false)));
					echo "Resposta enviada\n";
				}
			}
			break;


		case "setCycle":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setCycle",array("isSetCycle" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "adicionando ciclo alimentar \n";

				$plano = addCiclo($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($plano == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setCycle",array("isSetCycle" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo do usuario\n";
					global $conexoes;
					
					// foreach ($this->clients as $client) {
					// 	if(($messageObj->request->data->idUser == $conexoes["{$client->resourceId}"]["userId"]) || ($conexoes["{$client->resourceId}"]["admin"] == "1")){
					// 		$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
					// 		echo "Enviando plano alimentar para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
					// 		$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
					// 	}
					// }
					
					// echo "Dispositivo atualizado\n";
				}else{
					echo "erro ao inserir ciclo alimentar \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setCycle",array("isSetCycle" => false)));
					echo "Resposta enviada\n";
				}
				
			}
			break;


		case "toActiveCycle":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","toActiveCycle",array("isToActiveCycle" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "Ativando ciclo alimentar \n";

				$plano = ativarCiclo($messageObj->request->data);
				if($plano == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","toActiveCycle",array("isToActiveCycle" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo do usuario\n";
					global $conexoes;
					
					// foreach ($this->clients as $client) {
					// 	if(($messageObj->request->data->idUser == $conexoes["{$client->resourceId}"]["userId"]) || ($conexoes["{$client->resourceId}"]["admin"] == "1")){
					// 		$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
					// 		echo "Enviando plano alimentar para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
					// 		$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
					// 	}
					// }
					
					// echo "Dispositivo atualizado\n";
				}else{
					echo "erro ao ativar ciclo alimentar \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","toActiveCycle",array("isToActiveCycle" => false)));
					echo "Resposta enviada\n";
				}
				
			}
			break;


		case "setFoodPlan":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setFoodPlan",array("isSetFoodPlan" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "adicionando plano alimentar \n";

				$plano = addPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($plano == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setFoodPlan",array("isSetFoodPlan" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo do usuario\n";
					global $conexoes;
					foreach ($this->clients as $client) {
						if(($messageObj->request->data->idUser == $conexoes["{$client->resourceId}"]["userId"]) || ($conexoes["{$client->resourceId}"]["admin"] == "1")){
							$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
							echo "Enviando plano alimentar para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
							$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
						}
					}
					echo "Dispositivo atualizado\n";
				}else{
					echo "erro ao inserir plano alimentar \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setFoodPlan",array("isSetFoodPlan" => false)));
					echo "Resposta enviada\n";
				}
				
			}
			break;


		case "cancelFoodPlan":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelFoodPlan",array("isCancelFoodPlan" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "deletando plano alimentar \n";

				//print_r($messageObj->request->data);

				$refeicao = deletePlanoAlimentar($messageObj->request->data);
				if($refeicao == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelFoodPlan",array("isCancelFoodPlan" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo do usuario\n";
					global $conexoes;
					foreach ($this->clients as $client) {
						if(($messageObj->request->data->idUser == $conexoes["{$client->resourceId}"]["userId"]) || ($conexoes["{$client->resourceId}"]["admin"] == "1")){
							$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
							echo "Enviando plano alimentar para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
							$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
						}
					}
					echo "Dispositivo atualizado\n";
				}else{
					echo "erro ao deletar refeicao \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelFoodPlan",array("isCancelFoodPlan" => false)));
					echo "Resposta enviada\n";
				}
			}
			break;


		case "cancelCyclePlan":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelCyclePlan",array("isCancelCyclePlan" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "deletando ciclo alimentar \n";

				//print_r($messageObj->request->data);

				$refeicao = deleteCicloAlimentar($messageObj->request->data);
				if($refeicao == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelCyclePlan",array("isCancelCyclePlan" => true)));
					echo "Resposta enviada\n";
					// echo "Atualizando dispositivo do usuario\n";
					// global $conexoes;
					// foreach ($this->clients as $client) {
					// 	if(($messageObj->request->data->idUser == $conexoes["{$client->resourceId}"]["userId"]) || ($conexoes["{$client->resourceId}"]["admin"] == "1")){
					// 		$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
					// 		echo "Enviando plano alimentar para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
					// 		$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
					// 	}
					// }
					// echo "Dispositivo atualizado\n";
				}else{
					echo "erro ao deletar ciclo alimentar \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelCyclePlan",array("isCancelCyclePlan" => false)));
					echo "Resposta enviada\n";
				}
			}
			break;


		case "setMeal":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setMeal",array("isSetMeal" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "adicionando refeicao \n";
				
				//print_r($messageObj->request->data);

				$refeicao = addRefeicao($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($refeicao == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setMeal",array("isSetMeal" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo do usuario\n";
					global $conexoes;
					foreach ($this->clients as $client) {
						if(($messageObj->request->data->idUser == $conexoes["{$client->resourceId}"]["userId"]) || ($conexoes["{$client->resourceId}"]["admin"] == "1")){
							$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
							echo "Enviando plano alimentar para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
							$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
						}
					}
					echo "Dispositivo atualizado\n";
				}else{
					echo "erro ao inserir refeicao \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setMeal",array("isSetMeal" => false)));
					echo "Resposta enviada\n";
				}
			}
			break;
			
		


		case "cancelMeal":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelMeal",array("isCancelMeal" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "deletando refeicao \n";

				//print_r($messageObj->request->data);

				$refeicao = deleteRefeicao($messageObj->request->data);
				if($refeicao == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelMeal",array("isCancelMeal" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo do usuario\n";
					global $conexoes;
					foreach ($this->clients as $client) {
						if(($messageObj->request->data->idUser == $conexoes["{$client->resourceId}"]["userId"]) || ($conexoes["{$client->resourceId}"]["admin"] == "1")){
							$plano_alimentar = carregarPlanoAlimentar($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
							echo "Enviando plano alimentar para conexao [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
							$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateFoodPlan",$plano_alimentar));
						}
					}
					echo "Dispositivo atualizado\n";
				}else{
					echo "erro ao deletar refeicao \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelMeal",array("isCancelMeal" => false)));
					echo "Resposta enviada\n";
				}
			}
			break;


		case "setRecipes":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setRecipes",array("isSetRecipes" => false)));
				echo "Resposta enviada\n";
			}else{
				//print_r($messageObj->request->data);
				echo "adicionando receita \n";
				$nome = date("YmdHis") . "_" .  mt_rand(10000,99999);
				echo "Novo arquivo de imagem criado: " . $nome . "\n";
				$arquivo = fopen("img/recipes/" . $nome . ".jpeg", "wb");
				$escreve = fwrite($arquivo, base64_decode(str_replace("data:image/jpeg;base64,", "", $messageObj->request->data->img)));
				fclose($arquivo);
				echo "Arquivo escrito com os dados da receita \n";
				$receita;
				if(isset($messageObj->request->data->mealId) && $messageObj->request->data->mealId != 0){
					$receita = addAlimentoUsuario($server_outside . "img/recipes/" . $nome . ".jpeg", $messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				}else{
					$receita = addReceita($server_outside . "img/recipes/" . $nome . ".jpeg", $messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				}
				if($receita == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setRecipes",array("isSetRecipes" => true)));
				}else{
					echo "erro ao inserir receita \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setRecipes",array("isSetRecipes" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;




		case "cancelRecipe":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelRecipe",array("isCancelRecipe" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "deletando receita \n";

				//print_r($messageObj->request->data);

				$medida = deleteReceita($messageObj->request->data);
				if($medida == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelRecipe",array("isCancelRecipe" => true)));
					echo "Resposta enviada\n";
				}else{
					echo "erro ao deletar Receita \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelRecipe",array("isCancelRecipe" => false)));
					echo "Resposta enviada\n";
				}
			}
			break;




		case "cancelFood":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelFood",array("isCancelRecipe" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "deletando alimento do usuario \n";

				//print_r($messageObj->request->data);

				$medida = deleteAlimento($messageObj->request->data);
				if($medida == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelFood",array("isCancelRecipe" => true)));
					echo "Resposta enviada\n";
				}else{
					echo "erro ao deletar Alimento do Usuario \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelFood",array("isCancelRecipe" => false)));
					echo "Resposta enviada\n";
				}
			}
			break;


		case "setFood":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setFood",array("isSetFood" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "adicionando alimento \n";

				$receita = addAlimento($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($receita == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setFood",array("isSetFood" => true)));
				}else{
					echo "erro ao inserir alimento \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setFood",array("isSetFood" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;


		case "updateRecipes":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateRecipes",null));
				echo "Resposta enviada\n";
			}else{
				echo "requisicao de carregamento de receitas \n";
				$receitas = carregarReceitas($messageObj->request->client);
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateRecipes",$receitas));
				
				echo "Resposta enviada\n";
			}
			break;


		case "setMeasurement":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setMeasurement",array("isSetMeasurement" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "adicionando medicao \n";

				$medicao = addMedicao($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($medicao == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setMeasurement",array("isSetMeasurement" => true)));
				}else{
					echo "erro ao inserir medicao \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setMeasurement",array("isSetMeasurement" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;




		case "cancelMeasurement":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelMeasurement",array("isCancelMeasurement" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "deletando medicao \n";

				//print_r($messageObj->request->data);

				$medida = deleteMedicao($messageObj->request->data);
				if($medida == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelMeasurement",array("isCancelMeasurement" => true)));
					echo "Resposta enviada\n";
				}else{
					echo "erro ao deletar Medicao \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelMeasurement",array("isCancelMeasurement" => false)));
					echo "Resposta enviada\n";
				}
			}
			break;


		case "setMeasure":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","setMeasure",array("isSetMeasure" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "adicionando medida \n";

				$medicao = addMedida($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				if($medicao == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setMeasure",array("isSetMeasure" => true)));
				}else{
					echo "erro ao inserir medida \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setMeasure",array("isSetMeasure" => false)));
				}
				echo "Resposta enviada\n";
			}
			break;




		case "cancelMeasure":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","cancelMeasure",array("isCancelMeasure" => false)));
				echo "Resposta enviada\n";
			}else{
				echo "deletando medida \n";

				//print_r($messageObj->request->data);

				$medida = deleteMedida($messageObj->request->data);
				if($medida == "success"){
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelMeasure",array("isCancelMeasure" => true)));
					echo "Resposta enviada\n";
					echo "Atualizando dispositivo do usuario\n";
					global $conexoes;
					foreach ($this->clients as $client) {
						if(($messageObj->request->data->idUser == $conexoes["{$client->resourceId}"]["userId"]) || ($conexoes["{$client->resourceId}"]["admin"] == "1")){
							echo "requisicao de carregamento de medidas \n";
							$medidas = carregarMedidas($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
							$client->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateMeasures",$medidas));
						}
					}
					echo "Dispositivo atualizado\n";
				}else{
					echo "erro ao deletar Medida \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","cancelMeasure",array("isCancelMeasure" => false)));
					echo "Resposta enviada\n";
				}
			}
			break;


		case "updateMeasures":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateMeasures",null));
				echo "Resposta enviada\n";
			}else{
				echo "requisicao de carregamento de medidas \n";
				$medidas = carregarMedidas($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateMeasures",$medidas));
				
				echo "Resposta enviada\n";
			}
			break;


		case "updateTimelinePatient":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","updateTimelinePatient",null));
				echo "Resposta enviada\n";
			}else{
				echo "requisicao de carregamento TimeLine \n";
				$timeLine = updateTimeLinePatient($messageObj->request->data, $messageObj->request->client, getJWT($messageObj->token)->id);
				$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateTimelinePatient",$timeLine));

				echo "Resposta enviada\n";
			}
			break;


		
		case "changeImageAvatar":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
				$from->send(message_setProtocol($messageObj->request->id,"605","Error - Requisition requires login","1.0.5","changeImageAvatar",array("isChange" => false)));
				echo "Resposta enviada\n";
			}else{
				//echo $msg . "\n";
				$nome = date("YmdHis") . "_" .  mt_rand(10000,99999);
				echo "Novo arquivo de imagem criado: " . $nome . "\n";
				$arquivo = fopen("img/avatar/" . $nome . ".jpeg", "wb");
				echo "arquivo aberto \n";
				$escreve = fwrite($arquivo, base64_decode(str_replace("data:image/jpeg;base64,", "", $messageObj->request->data->img)));
				fclose($arquivo);
				echo "Arquivo escrito com os dados do usuario \n";
				$salvar = atualizaAvatar($server_outside . "img/avatar/" . $nome . ".jpeg", getJWT($messageObj->token)->id, $messageObj->request->client);
				if($salvar == "success"){
					echo "arquivo salvo com sucesso \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","changeImageAvatar",array("isChange" => true, "path" => $server_outside . "img/avatar/" . $nome . ".jpeg")));
				}else{
					echo "erro ao salvar o arquivo \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","changeImageAvatar",array("isChange" => false, "path" => null)));
				}
				echo "Resposta enviada\n";
			}
			break;



		case "setFCMRegistrationId":
			if(isVisitante($messageObj->token)){
				echo "Token de visitante nao autorizado\n";
			}else{
				global $conexoes;
				$alterar = alterarFCM((object)array("userId"=>$conexoes["{$from->resourceId}"]["userId"], "fcm"=>$messageObj->request->data->registrationId));
				if($alterar == "success"){
					echo "registro FCM alterado com sucesso \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setFCMRegistrationId",array("isSetFCM" => true)));
				}else{
					echo "erro ao registrar FCM \n";
					$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","setFCMRegistrationId",array("isSetFCM" => false)));
				}
				
				echo "Resposta enviada\n";
			}
			break;
			
			
		case "testquery":
			//echo $from->socket_getsockname();
			//var_dump(openssl_get_cert_locations());
			
			echo "ENVIANDO NOTIFICACAO\n";
			
			//gerar_notificacao();
			gerar_notificacao("fo5gCZqeL_Y:APA91bEUkctZiAt-qT0C7jnNHqSnPO6VVJfYBhBfkHaLmBzRDpLWAbclYj9XR02bmuG4LIzTUKQHqs5jb-kqfsleyHWI9Ev6uq8R6GLyaFrnxseyB6EstnoE-VcyNBWWYDH05jyfW-UwEP4qUpjPhOnQp8AnsL6RHQ", "teste", "corpo", array());
			
			echo "NOTIFICACAO ENVIADA\n";
			
			/*
			echo "Solicitacao de TESTE recebida\n";
			$nome = date("YmdHis") . "_" .  mt_rand(10000,99999);
			echo "NOME " . $nome . "\n";
			$arquivo = fopen("img/" . $nome . ".png", "wb");
			//echo "CRIANDO ARQUIVO: " . $messageObj->request->data . " \n";
			$escreve = fwrite($arquivo, base64_decode($messageObj->request->data->img));
			//echo "CRIANDO ARQUIVO: " . $img_b64 . " \n";
			//$escreve = fwrite($arquivo, base64_decode($img_b64));
			fclose($arquivo);
			echo "ARQUIVO: " . base64_decode($messageObj->request->data->img) . " \n";
			//echo "ARQUIVO: " . base64_decode($img_b64) . " \n";
			echo "ARQUIVO CRIADO \n";
			*/
			
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
			echo $msg . "\n";
			$from->send(message_setProtocol($messageObj->request->id,"602","Error - Unrecognized request","1.0.5","errorRequest",array()));
			echo "Resposta default enviada\n";
			break;
	}
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