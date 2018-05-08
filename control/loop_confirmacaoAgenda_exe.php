<?php

if(strtotime(date('H:i')) == strtotime("23:18")){
	
	$data = date('Y-m-d'); 
	$data_conferir = date('Y-m-d', strtotime("+1 days",strtotime($data)));
	echo $data_conferir . "\n";
	
	
	$agenda = carregarAgendaConfirmacao(array("date"=>$data_conferir));
	
	
	global $connection;
	global $conexoes;
	foreach ($connection->getClient() as $client) {
		foreach ($agenda as $item) {
			if($conexoes["{$client->resourceId}"]["userId"] == $item['usuario_id']){
				echo "Enviando atualizacao para confirmacao para [{$client->resourceId}] - usuario " . $conexoes["{$client->resourceId}"]["userId"] . "\n";
   				
				//$agendamentos = carregarAgendaUsuario($messageObj->request->client, getJWT($messageObj->token)->id);

				$inicio_servico = "07:00:00";
				$termino_servico = "18:00:00"; //FAZER CONSULTA PARA BUSCAR ESSES PADROES
				$intervalo_padrao = "01:00:00";
   				
				echo "userId: " . $conexoes["{$client->resourceId}"]["userId"] . " - userClient: " . $conexoes["{$client->resourceId}"]["userClient"] . "\n";
				//$agenda = classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, getJWT($messageObj->token)->id, $messageObj->request->client);
				//$from->send(message_setProtocol($messageObj->request->id,"200","Success","1.0.5","updateMySchedules",$agenda[1]));
				//print_r($agenda[1]);
				echo "Resposta enviada\n";
				
				
				//$client->send(message_setProtocol("000","500","Success","1.0.5","updateMySchedules",array("message"=>"Teste de envio automático")));
				//echo "Resposta enviada\n";
			}
		}
	}
	
	/*
	foreach ($agenda as $item) {
		if(envia_email("Sistema de confirmação", $item['usuario_email'], "Confirmação de agendamento", "Olá " . $item['usuario_nome'] . "\n\n" . "Você tem uma consulta agendada para: " . 
		explode("-",$item['data'])[2] . "/" . explode("-",$item['data'])[1] . "/" . explode("-",$item['data'])[0] . ".\n" . "Para confirmar, acesse o link abaixo:\n\nLINK")){
			echo "E-mail de confirmacao enviado com sucesso\n";
		}else{
			echo "ERRO ao enviar e-mail de confirmacao\n";
		}
	}
	*/
}else{
	//else
}


?>