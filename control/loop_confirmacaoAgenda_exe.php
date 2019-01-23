<?php

if(strtotime(date('H:i')) == strtotime("07:00")){
	
	echo "LOOP - Confirmação agenda\n";
	
	$data = date('Y-m-d'); 
	$data_conferir = date('Y-m-d', strtotime("+3 days",strtotime($data)));
	echo $data_conferir . "\n";
	
	
	$agenda = carregarAgendaConfirmacao(array("date"=>$data_conferir));
	
	foreach ($agenda as $item) {
		//var_dump($item);
		if($item['id_agenda']){
			$hora = substr($item['hora_inicio'], 0, -3) . " às " . substr($item['hora_fim'], 0, -3);
			$hora = str_replace(":", "h", $hora);
			
			$usuario_fcm = consultaFCMUsuario($item['usuario_id'], $item['usuario_cliente']);
			//echo "FCM NOTIFICATION: " . $usuario_fcm["fcm"] . "\n";
			if($usuario_fcm["fcm"]){
				echo $usuario_fcm["fcm"] . "\n";
				gerar_notificacao($usuario_fcm["fcm"], "Confirmação de agendamento", "Olá " . $item['usuario_nome'] . "\n\n" . "Você tem uma consulta agendada para:\nData: " . 
				explode("-",$item['data'])[2] . "/" . explode("-",$item['data'])[1] . "/" . explode("-",$item['data'])[0] . ".\nHorário: " . $hora . "\n\n" . "Confirme sua consulta.", array());
			}
			/*
			if(envia_email("Confirmação de agendamento", $item['usuario_email'], "Agendamento Andreza Matteussi", "Olá " . $item['usuario_nome'] . "\n\n" . "Você tem uma consulta agendada para:\nData: " . 
			explode("-",$item['data'])[2] . "/" . explode("-",$item['data'])[1] . "/" . explode("-",$item['data'])[0] . ".\nHorário: " . $hora . "\n\n" . "Acesse o aplicativo e confirme sua consulta.")){
			*/
			if(envia_email(iconv("UTF-8", "WINDOWS-1252", "Confirmação de agendamento"), 
							iconv("UTF-8", "WINDOWS-1252", $item['usuario_email']), 
							iconv("UTF-8", "WINDOWS-1252", "Agendamento Andreza Matteussi"), 
							iconv("UTF-8", "WINDOWS-1252", "Olá " . $item['usuario_nome'] . "\n\n" . "Você tem uma consulta agendada para:\nData: " . 
			explode("-",$item['data'])[2] . "/" . explode("-",$item['data'])[1] . "/" . explode("-",$item['data'])[0] . ".\nHorário: " . $hora . "\n\n" . "Acesse o aplicativo e confirme sua consulta."))){
				echo "E-mail de confirmacao enviado com sucesso\n";
			}else{
				echo "ERRO ao enviar e-mail de confirmacao\n";
			}
		}
	}
	
}else{
	//else     $csv = iconv("UTF-8", "WINDOWS-1252", $csv);
}


?>