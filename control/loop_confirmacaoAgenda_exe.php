<?php

if(strtotime(date('H:i')) == strtotime("22:49")){
	$agenda = carregarAgendaConfirmacao(array("date"=>"2018-04-16"/*date('Y-m-d')*/));
	
	
	
	foreach ($agenda as $item) {
		if(envia_email("Sistema de confirmaчуo", $item['usuario_email'], "Confirmaчуo de agendamento", "Olс " . $item['usuario_nome'] . "\n\n" . "Vocъ tem uma consulta agendada para: " . 
		explode("-",$item['data'])[2] . "/" . explode("-",$item['data'])[1] . "/" . explode("-",$item['data'])[0] . ".\n" . "Para confirmar, acesse o link abaixo:\n\nLINK")){
			echo "E-mail de confirmacao enviado com sucesso\n";
		}else{
			echo "ERRO ao enviar e-mail de confirmacao\n";
		}
	}
}else{
	//else
}


?>