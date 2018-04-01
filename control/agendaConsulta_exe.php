<?php

function cancelarAgenda($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$id = $data->id;
	$motivo_id = $data->reasonForCancellation->id;
	$motivo_descricao = $data->reasonForCancellation->description;
	$motivo = ($motivo_id == "-1" ? $motivo_descricao : $motivo_id);
	
	echo "MOTIVO" . $motivo_id . "-" . $motivo_descricao . "\n";
	
	echo "Cancelando registro: $id\n";

	$sql = "UPDATE agenda_consulta SET cancelado = ? WHERE id = ?"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $motivo);
	$consulta->bindParam(2, $id);
	$consulta->execute();

	if($consulta){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}

function inserirAgenda($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$date = $data->date;
	$hour = $data->hour;
	$partes = explode(" às ", $hour);
	$hora_inicio = str_replace("h", ":" ,$partes[0]) . ":00";
	$hora_fim = str_replace("h", ":" ,$partes[1]) . ":00";
	
	echo "Inserindo registro: $date - $hora_inicio - $hora_fim - $usuario\n";
	
	$sql = "INSERT INTO agenda_consulta (data, hora_inicio, hora_fim, usuario, cliente) VALUES (?, ?, ?, ?, ?)"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $date);
	$consulta->bindValue(2, $hora_inicio);
	$consulta->bindValue(3, $hora_fim);
	$consulta->bindValue(4, $usuario);
	$consulta->bindValue(5, $client);
	$consulta->execute();
	
	if($consulta->rowCount()){
		/*
		global $conexoes;
		if(!envia_email("Você tem um novo agendamento", $conexoes["{$from->resourceId}"]["userEmail"], "Agendamento do nutricionista", "Olá " . $conexoes["{$from->resourceId}"]["userName"] . "\n\n" . "Você tem uma nova consulta agendada:\n " . $date  . "\n" . "Horário: " . $hour . "\n\n" . "Uma notificação de confirmação será enviada à você com 24 horas de antecedência.")){
			return "failed";
		}
		*/
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}

function carregarAgenda($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$date = $data->date;
	$ativo = "0"; // 0 = não cancelado
	
	$agenda = array();
	$usuarios = array();
	$agendamentos = array();
	
	$sql = "SELECT * FROM agenda_consulta WHERE data = ? and cancelado = ? and cliente = ? ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $date);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $client);
	$consulta->execute();
	
	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
	   		$agendamentos[] = array("id" => $row->id, "data" => $row->data, "hora_inicio"=>$row->hora_inicio, "hora_fim"=>$row->hora_fim, "usuario"=>$row->usuario);
			echo $row->id . " - " . $row->data . " - " . $row->hora_inicio . " - " . $row->hora_fim . " - " . $row->usuario . "\n";
	   }
	} else {
		$agendamentos[] = array("id" => null, "data" => $date, "hora_inicio"=>null, "hora_fim"=>null, "usuario"=>0);
		echo "Nenhum registro encontrado\n";
	}
	
	return $agendamentos;
	
}

function classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $usuario){
	
	$date = $agendamentos[0]["data"];
	$hora_atual = $inicio_servico;
	$adicional = "00:00:00";
	while((strtotime($hora_atual) != strtotime($termino_servico) || strtotime($hora_atual) < strtotime($termino_servico))){// && $adicional == "00:00:00"
		if($adicional == "00:00:00"){
			if(is_int(array_search($hora_atual, array_column($agendamentos, "hora_inicio")))){
				$index = array_search($hora_atual, array_column($agendamentos, "hora_inicio"));
				$agenda[] = array(
					"id" => $agendamentos[$index]["id"],
					"date" => $agendamentos[$index]["data"],
					"time" => str_replace(":", "h", substr($agendamentos[$index]["hora_inicio"], 0, 5)) . " às " . str_replace(":", "h", substr($agendamentos[$index]["hora_fim"], 0, 5)),
					"available" => false,
					"mySchedule" => ($usuario == $agendamentos[$index]["usuario"] ? true : false),
					"strAvailable" => ($usuario == $agendamentos[$index]["usuario"] ? "Meu horário" : "Indisponível")
				);
				$usuarios[] = $agendamentos[$index]["usuario"];
			}else{
				$partes = explode(":", $intervalo_padrao);
				$hora_final = strtotime($hora_atual) + $partes[0]*3600 + $partes[1]*60 + $partes[2];
				$hora_final = strftime('%H:%M:%S', $hora_final);
				$agenda[] = array(
					"id" => null,
					"date" => $date,
					"time" => str_replace(":", "h", substr($hora_atual, 0, 5)) . " às " . str_replace(":", "h", substr($hora_final, 0, 5)),
					"available" => true,
					"mySchedule" => false,
					"strAvailable" => "Horário disponível"
				);
				$usuarios[] = "0";
			}
			$hora_atual = strtotime($hora_atual) + 60;
			$hora_atual = strftime('%H:%M:%S', $hora_atual);
			$adicional = strtotime($adicional) + 60;
			$adicional = strftime('%H:%M:%S', $adicional);
		}else{
			if(is_int(array_search($hora_atual, array_column($agendamentos, "hora_inicio")))){
				$index = array_search($hora_atual, array_column($agendamentos, "hora_inicio"));
				$agenda[] = array(
					"id" => $agendamentos[$index]["id"],
					"date" => $agendamentos[$index]["data"],
					"time" => str_replace(":", "h", substr($agendamentos[$index]["hora_inicio"], 0, 5)) . " às " . str_replace(":", "h", substr($agendamentos[$index]["hora_fim"], 0, 5)),
					"available" => false,
					"mySchedule" => ($usuario == $agendamentos[$index]["usuario"] ? true : false),
					"strAvailable" => ($usuario == $agendamentos[$index]["usuario"] ? "Meu horário" : "Indisponível")
				);
				$usuarios[] = $agendamentos[$index]["usuario"];
			}
			$hora_atual = strtotime($hora_atual) + 60;
			$hora_atual = strftime('%H:%M:%S', $hora_atual);
			$adicional = strtotime($adicional) + 60;
			$adicional = strftime('%H:%M:%S', $adicional);			
		}
		if(strtotime($adicional) == strtotime($intervalo_padrao)){
			$adicional = "00:00:00";
		}
	}
	//return array($agenda, $usuarios);
	return $agenda;
}


function carregarMotivosCancelamento($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	//$date = $data->date;

	$motivos = array();

	$sql = "SELECT * FROM motivos_cancelamento WHERE ativo = 1 "; //FAZER CORREÇÃO PARA MAIS CLIENTES

	$consulta = $bd->prepare($sql);
	$consulta->execute();

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$motivos[] = array("id" => $row->id, "description" => $row->descricao);
			echo $row->id . " - " . $row->descricao . "\n";
	   }
	   $motivos[] = array("id" => "-1", "description" => "Outros");
	} else {
		$motivos[] = array("id" => "-1", "description" => "Outros");
		echo "Nenhum registro encontrado\n";
	}

	return $motivos;

}
?>