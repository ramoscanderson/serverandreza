<?php

function carregarAgenda($data, $usuario){
	require ("lib/bd.php");
	
	$agenda = array();
	$agendamentos = array();
	
	$inicio_servico = "07:00:00";
	$termino_servico = "18:00:00"; //FAZER CONSULTA PARA BUSCAR ESSES PADROES
	$intervalo_padrao = "01:00:00";
	
	$sql = "SELECT * FROM agenda_consulta WHERE data = ? ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $data);
	$consulta->execute();
	
	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
	   		$agendamentos[] = array("id" => $row->id, "data" => $row->data, "hora_inicio"=>$row->hora_inicio, "hora_fim"=>$row->hora_fim, "usuario"=>$row->usuario);
			echo $row->data . " - " . $row->hora_inicio . " - " . $row->hora_fim . " - " . $row->usuario . "\n";
	   }
	} else {
		echo "Nenhum registro encontrado\n";
	}
	
	$hora_atual = $inicio_servico;
	$adicional = "00:00:00";
	while((strtotime($hora_atual) != strtotime($termino_servico) || strtotime($hora_atual) < strtotime($termino_servico))){// && $adicional == "00:00:00"
		if($adicional == "00:00:00"){
			if(is_int(array_search($hora_atual, array_column($agendamentos, "hora_inicio")))){
				$index = array_search($hora_atual, array_column($agendamentos, "hora_inicio"));
				$agenda[] = array(
					"id" => $agendamentos[$index]["id"],
					"Date" => $agendamentos[$index]["data"],
					"time" => str_replace(":", "h", substr($agendamentos[$index]["hora_inicio"], 0, 5)) . " as " . str_replace(":", "h", substr($agendamentos[$index]["hora_fim"], 0, 5)),
					"available" => false,
					"mySchedule" => ($usuario == $agendamentos[$index]["usuario"] ? true : false),
					"strAvailable" => ($usuario == $agendamentos[$index]["usuario"] ? "Meu horario" : "Indisponivel")
				);
			}else{
				$partes = explode(":", $intervalo_padrao);
				$hora_final = strtotime($hora_atual) + $partes[0]*3600 + $partes[1]*60 + $partes[2];
				$hora_final = strftime('%H:%M:%S', $hora_final);
				$agenda[] = array(
					"id" => null,
					"Date" => $data,
					"time" => str_replace(":", "h", substr($hora_atual, 0, 5)) . " as " . str_replace(":", "h", substr($hora_final, 0, 5)),
					"available" => true,
					"mySchedule" => false,
					"strAvailable" => "Horario disponivel"
				);
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
					"Date" => $agendamentos[$index]["data"],
					"time" => str_replace(":", "h", substr($agendamentos[$index]["hora_inicio"], 0, 5)) . " as " . str_replace(":", "h", substr($agendamentos[$index]["hora_fim"], 0, 5)),
					"available" => false,
					"mySchedule" => ($usuario == $agendamentos[$index]["usuario"] ? true : false),
					"strAvailable" => ($usuario == $agendamentos[$index]["usuario"] ? "Meu horario" : "Indisponivel")
				);
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
	return $agenda;
}

?>