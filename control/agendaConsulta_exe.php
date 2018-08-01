﻿<?php

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

function validarAgendamentoUsuario($usuario, $client){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$ativo = "0"; // 0 = não cancelado

	$sql = "SELECT count(*) as qtd FROM agenda_consulta WHERE data > DATE_FORMAT(now(), '%Y-%m-%d') and cancelado = ? and cliente = ? and usuario = ?"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	// DATE_FORMAT(DATE_ADD(now(), INTERVAL -3 DAY), '%Y-%m %d')
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $ativo);
	$consulta->bindParam(2, $client);
	$consulta->bindParam(3, $usuario);
	$consulta->execute();
	
	$retorno = true;

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			if($row->qtd >= 3){
				$retorno = false;
			}
	   }
	} 
	return $retorno;
}

function carregarAgenda($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$date = $data->date;
	$ativo = "0"; // 0 = não cancelado
	
	$agenda = array();
	$usuarios = array();
	$agendamentos = array();
	
	$sql2 = "SELECT * FROM agenda_consulta WHERE data = ? and cancelado = ? and cliente = ? ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	
	$sql = "SELECT 
				(DATE_FORMAT(agenda_consulta.data, '%w')),
				agenda_consulta.id as agenda_id, 
				agenda_consulta.data as agenda_data, 
				agenda_consulta.hora_inicio as agenda_hora_inicio, 
				agenda_consulta.hora_fim as agenda_hora_fim, 
				agenda_consulta.usuario as agenda_usuario, 
				agenda_consulta.cliente as agenda_cliente, 
				agenda_consulta.cancelado as agenda_cancelado, 
				agenda_consulta.confirmado as agenda_confirmado,
				localizacao.id as localizacao_id,
				localizacao.titulo_endereco as localizacao_titulo_endereco,
				localizacao.subtitulo_endereco as localizacao_subtitulo_endereco,
				localizacao.coordenada as localizacao_coordenada,
				localizacao.img as localizacao_img,
				localizacao_semana.indisponivel as localizacao_semana_indisponivel
			FROM 
				(agenda_consulta, localizacao, localizacao_semana) LEFT JOIN localizacao_excecao 
			ON
				IF(localizacao_excecao.localizacao = null, localizacao_semana.localizacao, localizacao_excecao.localizacao) = localizacao.id and
				agenda_consulta.data = localizacao_excecao.data and
				localizacao_excecao.data = agenda_consulta.data and
				localizacao_excecao.cliente = ? and
				localizacao_excecao.cancelado = ?
			WHERE 
				localizacao.id = localizacao_semana.localizacao and
				(DATE_FORMAT(agenda_consulta.data, '%w')) = localizacao_semana.dia_semana and
				agenda_consulta.data = ? and 
				agenda_consulta.cancelado = ? and
				agenda_consulta.cliente = ? and
				localizacao.cliente = ? and
				localizacao_semana.cliente = ? 
			ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES       if(500<1000, "yes", "no")
	
	$consulta = $bd->prepare($sql);
	/*
	$consulta->bindParam(1, $date);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $client);
	*/
	
	$consulta->bindParam(1, $client);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $date);
	$consulta->bindParam(4, $ativo);
	$consulta->bindParam(5, $client);
	$consulta->bindParam(6, $client);
	$consulta->bindParam(7, $client);
	
	
	$consulta->execute();
	
	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
	   		$agendamentos[] = array("id" => $row->agenda_id, "data" => $row->agenda_data, "hora_inicio"=>$row->agenda_hora_inicio, "hora_fim"=>$row->agenda_hora_fim, "usuario"=>$row->agenda_usuario, "titleAdress"=>$row->localizacao_titulo_endereco, "subTitleAdress"=>$row->localizacao_subtitulo_endereco, "destination"=>$row->localizacao_coordenada, "imgDestination"=>$row->localizacao_img, "indisponivel"=>$row->localizacao_semana_indisponivel);
			//$agendamentos[] = array("id" => $row->id, "data" => $row->data, "hora_inicio"=>$row->hora_inicio, "hora_fim"=>$row->hora_fim, "usuario"=>$row->usuario);
			echo $row->agenda_id . " - " . $row->agenda_data . " - " . $row->agenda_hora_inicio . " - " . $row->agenda_hora_fim . " - " . $row->agenda_usuario . "\n";
			//echo $row->id . " - " . $row->data . " - " . $row->hora_inicio . " - " . $row->hora_fim . " - " . $row->usuario . "\n";
	   }
	} else {
		$agendamentos[] = array("id" => null, "data" => $date, "hora_inicio"=>null, "hora_fim"=>null, "usuario"=>0);
		echo "Nenhum registro encontrado\n";
	}
	
	return $agendamentos;
	
}

function carregarAgendaAdministrativo($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$date = $data->date;
	$ativo = "0"; // 0 = não cancelado

	$agenda = array();
	$usuarios = array();
	$agendamentos = array();

	$sql2 = "SELECT * FROM agenda_consulta WHERE data = ? and cancelado = ? and cliente = ? ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES

	$sql = "SELECT 
				(DATE_FORMAT(agenda_consulta.data, '%w')),
				agenda_consulta.id as agenda_id, 
				agenda_consulta.data as agenda_data, 
				agenda_consulta.hora_inicio as agenda_hora_inicio, 
				agenda_consulta.hora_fim as agenda_hora_fim, 
				agenda_consulta.usuario as agenda_usuario, 
				agenda_consulta.cliente as agenda_cliente, 
				agenda_consulta.cancelado as agenda_cancelado, 
				agenda_consulta.confirmado as agenda_confirmado,
				localizacao.id as localizacao_id,
				localizacao.titulo_endereco as localizacao_titulo_endereco,
				localizacao.subtitulo_endereco as localizacao_subtitulo_endereco,
				localizacao.coordenada as localizacao_coordenada,
				localizacao.img as localizacao_img,
				localizacao_semana.indisponivel as localizacao_semana_indisponivel,
				usuario.id as usuario_id,
				usuario.nome as usuario_nome,
				usuario.cpf as usuario_cpf,
				usuario.email as usuario_email,
				usuario.telefone as usuario_telefone,
				usuario.data_nascimento as usuario_data_nascimento,
				usuario.img as usuario_img
			FROM 
				(agenda_consulta, localizacao, localizacao_semana, usuario) LEFT JOIN localizacao_excecao 
			ON
				agenda_consulta.usuario = usuario.id and
				IF(localizacao_excecao.localizacao = null, localizacao_semana.localizacao, localizacao_excecao.localizacao) = localizacao.id and
				agenda_consulta.data = localizacao_excecao.data and
				localizacao_excecao.data = agenda_consulta.data and
				localizacao_excecao.cliente = ? and
				localizacao_excecao.cancelado = ?
			WHERE 
				agenda_consulta.usuario = usuario.id and
				localizacao.id = localizacao_semana.localizacao and
				(DATE_FORMAT(agenda_consulta.data, '%w')) = localizacao_semana.dia_semana and
				agenda_consulta.data = ? and 
				agenda_consulta.cancelado = ? and
				agenda_consulta.cliente = ? and
				usuario.cliente = ? and
				localizacao.cliente = ? and
				localizacao_semana.cliente = ? 
			ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES       if(500<1000, "yes", "no")

	$consulta = $bd->prepare($sql);
	/*
	$consulta->bindParam(1, $date);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $client);
	*/

	$consulta->bindParam(1, $client);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $date);
	$consulta->bindParam(4, $ativo);
	$consulta->bindParam(5, $client);
	$consulta->bindParam(6, $client);
	$consulta->bindParam(7, $client);
	$consulta->bindParam(8, $client);


	$consulta->execute();

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$agendamentos[] = array("id" => $row->agenda_id, 
			"data" => $row->agenda_data, 
			"hora_inicio"=>$row->agenda_hora_inicio, 
			"hora_fim"=>$row->agenda_hora_fim, 
			"usuario"=>$row->agenda_usuario, 
			"titleAdress"=>$row->localizacao_titulo_endereco, 
			"subTitleAdress"=>$row->localizacao_subtitulo_endereco, 
			"destination"=>$row->localizacao_coordenada, 
			"imgDestination"=>$row->localizacao_img, 
			"idUser"=>$row->usuario_id, 
			"nameUser"=>$row->usuario_nome, 
			"cpfUser"=>$row->usuario_cpf, 
			"emailUser"=>$row->usuario_email, 
			"phoneUser"=>$row->usuario_telefone, 
			"birthdayUser"=>$row->usuario_data_nascimento, 
			"imageUser"=>$row->usuario_img);
			//$agendamentos[] = array("id" => $row->id, "data" => $row->data, "hora_inicio"=>$row->hora_inicio, "hora_fim"=>$row->hora_fim, "usuario"=>$row->usuario);
			echo $row->agenda_id . " - " . $row->agenda_data . " - " . $row->agenda_hora_inicio . " - " . $row->agenda_hora_fim . " - " . $row->agenda_usuario . "\n";
			//echo $row->id . " - " . $row->data . " - " . $row->hora_inicio . " - " . $row->hora_fim . " - " . $row->usuario . "\n";
	   }
	} else {
		$agendamentos[] = array("id" => $row->agenda_id, 
			"data" => null, "hora_inicio"=>null, "hora_fim"=>null, "usuario"=>"0", "titleAdress"=>null, "subTitleAdress"=>null, "destination"=>null, "imgDestination"=>null, "idUser"=>null, "nameUser"=>null, "cpfUser"=>null, "emailUser"=>null, "phoneUser"=>null, "birthdayUser"=>null, "imageUser"=>null);;
		echo "Nenhum registro encontrado\n";
	}

	return $agendamentos;

}

function carregarAgenda7Days($client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$date = date("Y-m-d");
	$date7  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+7, date("Y")));
	echo $date . "\n";
	echo $date7 . "\n";
	$ativo = "0"; // 0 = não cancelado

	$agenda = array();
	$usuarios = array();
	$agendamentos = array();

	$sql2 = "SELECT * FROM agenda_consulta WHERE data = ? and cancelado = ? and cliente = ? ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES

	$sql = "SELECT 
				(DATE_FORMAT(agenda_consulta.data, '%w')),
				agenda_consulta.id as agenda_id, 
				agenda_consulta.data as agenda_data, 
				agenda_consulta.hora_inicio as agenda_hora_inicio, 
				agenda_consulta.hora_fim as agenda_hora_fim, 
				agenda_consulta.usuario as agenda_usuario, 
				agenda_consulta.cliente as agenda_cliente, 
				agenda_consulta.cancelado as agenda_cancelado, 
				agenda_consulta.confirmado as agenda_confirmado,
				localizacao.id as localizacao_id,
				localizacao.titulo_endereco as localizacao_titulo_endereco,
				localizacao.subtitulo_endereco as localizacao_subtitulo_endereco,
				localizacao.coordenada as localizacao_coordenada,
				localizacao.img as localizacao_img,
				localizacao_semana.indisponivel as localizacao_semana_indisponivel, 
				usuario.nome as usuario_nome,
				usuario.telefone as usuario_telefone
			FROM 
				(agenda_consulta, localizacao, localizacao_semana, usuario) LEFT JOIN localizacao_excecao 
			ON
				IF(localizacao_excecao.localizacao = null, localizacao_semana.localizacao, localizacao_excecao.localizacao) = localizacao.id and
				agenda_consulta.data = localizacao_excecao.data and
				localizacao_excecao.data = agenda_consulta.data and
				agenda_consulta.usuario = usuario.id and
				localizacao_excecao.cliente = ? and
				localizacao_excecao.cancelado = ?
			WHERE 
				localizacao.id = localizacao_semana.localizacao and
				(DATE_FORMAT(agenda_consulta.data, '%w')) = localizacao_semana.dia_semana and
				agenda_consulta.data >= ? and 
				agenda_consulta.data <= ? and 
				agenda_consulta.usuario = usuario.id and
				agenda_consulta.cancelado = ? and
				agenda_consulta.cliente = ? and
				localizacao.cliente = ? and
				localizacao_semana.cliente = ? 
			ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES       if(500<1000, "yes", "no")

	$consulta = $bd->prepare($sql);
	/*
	$consulta->bindParam(1, $date);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $client);
	*/

	$consulta->bindParam(1, $client);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $date);
	$consulta->bindParam(4, $date7);
	$consulta->bindParam(5, $ativo);
	$consulta->bindParam(6, $client);
	$consulta->bindParam(7, $client);
	$consulta->bindParam(8, $client);


	$consulta->execute();

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$agendamentos[] = array("id" => $row->agenda_id, "data" => $row->agenda_data, "horario"=>substr($row->agenda_hora_inicio, 0, -3) . " às " . substr($row->agenda_hora_fim, 0, -3), "usuario_id"=>$row->agenda_usuario, "usuario_nome"=>$row->usuario_nome, "usuario_telefone"=>$row->usuario_telefone, "titleAdress"=>$row->localizacao_titulo_endereco, "subTitleAdress"=>$row->localizacao_subtitulo_endereco, "destination"=>$row->localizacao_coordenada, "imgDestination"=>$row->localizacao_img);
			//$agendamentos[] = array("id" => $row->id, "data" => $row->data, "hora_inicio"=>$row->hora_inicio, "hora_fim"=>$row->hora_fim, "usuario"=>$row->usuario);
			echo $row->agenda_id . " - " . $row->agenda_data . " - " . $row->agenda_hora_inicio . " - " . $row->agenda_hora_fim . " - " . $row->agenda_usuario . "\n";
			//echo $row->id . " - " . $row->data . " - " . $row->hora_inicio . " - " . $row->hora_fim . " - " . $row->usuario . "\n";
	   }
	} else {
		$agendamentos[] = array("id" => null, "data" => null, "horario"=>null, "usuario_id"=>null, "usuario_nome"=>null, "usuario_telefone"=>null, "titleAdress"=>null, "subTitleAdress"=>null, "destination"=>null, "imgDestination"=>null);
		echo "Nenhum registro encontrado\n";
	}

	return $agendamentos;

}

function carregarAgendaUsuario($client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$ativo = "0"; // 0 = não cancelado

	$agenda = array();
	$usuarios = array();
	$agendamentos = array();

	$sql = "SELECT 
				(DATE_FORMAT(agenda_consulta.data, '%w')),
				agenda_consulta.id as agenda_id, 
				agenda_consulta.data as agenda_data, 
				agenda_consulta.hora_inicio as agenda_hora_inicio, 
				agenda_consulta.hora_fim as agenda_hora_fim, 
				agenda_consulta.usuario as agenda_usuario, 
				agenda_consulta.cliente as agenda_cliente, 
				agenda_consulta.cancelado as agenda_cancelado, 
				agenda_consulta.confirmado as agenda_confirmado,
				localizacao.id as localizacao_id,
				localizacao.titulo_endereco as localizacao_titulo_endereco,
				localizacao.subtitulo_endereco as localizacao_subtitulo_endereco,
				localizacao.coordenada as localizacao_coordenada,
				localizacao.img as localizacao_img,
				localizacao_semana.indisponivel as localizacao_semana_indisponivel
			FROM 
				(agenda_consulta, localizacao, localizacao_semana) LEFT JOIN localizacao_excecao 
			ON
				IF(localizacao_excecao.localizacao = null, localizacao_semana.localizacao, localizacao_excecao.localizacao) = localizacao.id and
				agenda_consulta.data = localizacao_excecao.data and
				localizacao_excecao.data = agenda_consulta.data and
				localizacao_excecao.cliente = ? and
				localizacao_excecao.cancelado = ?
			WHERE 
				localizacao.id = localizacao_semana.localizacao and
				(DATE_FORMAT(agenda_consulta.data, '%w')) = localizacao_semana.dia_semana and
				agenda_consulta.data >= ? and
				agenda_consulta.usuario = ? and 
				agenda_consulta.cancelado = ? and
				agenda_consulta.cliente = ? and
				localizacao.cliente = ? and
				localizacao_semana.cliente = ? 
			ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES

	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $client);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, date("Y-m-d"));
	$consulta->bindParam(4, $usuario);
	$consulta->bindParam(5, $ativo);
	$consulta->bindParam(6, $client);
	$consulta->bindParam(7, $client);
	$consulta->bindParam(8, $client);
	$consulta->execute();

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$agendamentos[] = array("id" => $row->agenda_id, "data" => $row->agenda_data, "hora_inicio"=>$row->agenda_hora_inicio, "hora_fim"=>$row->agenda_hora_fim, "usuario"=>$row->agenda_usuario, "titleAdress"=>$row->localizacao_titulo_endereco, "subTitleAdress"=>$row->localizacao_subtitulo_endereco, "destination"=>$row->localizacao_coordenada, "imgDestination"=>$row->localizacao_img, "indisponivel"=>$row->localizacao_semana_indisponivel);
			echo $row->agenda_id . " - " . $row->agenda_data . " - " . $row->agenda_hora_inicio . " - " . $row->agenda_hora_fim . " - " . $row->agenda_usuario . "\n";
	   }
	} else {
		$agendamentos[] = array("id" => null, "data" => $date, "hora_inicio"=>null, "hora_fim"=>null, "usuario"=>0);
		echo "Nenhum registro encontrado\n";
	}

	return $agendamentos;

}

function carregarAgendaConfirmacao($data){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$date = $data['date'];
	$ativo = "0"; // 0 = não cancelado
	$confirmado = "0"; // 0 = não confirmado

	$agenda = array();
	$usuarios = array();
	$agendamentos = array();

	$sql = "SELECT agenda_consulta.id as agenda_id, agenda_consulta.data as agenda_data, agenda_consulta.hora_inicio as agenda_hora_inicio, 
			agenda_consulta.hora_fim as agenda_hora_fim, agenda_consulta.usuario as agenda_usuario, agenda_consulta.cancelado as agenda_cancelado, 
			agenda_consulta.confirmado as agenda_confirmado, usuario.nome as usuario_nome, usuario.email as usuario_email 
			FROM agenda_consulta inner join usuario on agenda_consulta.usuario = usuario.id WHERE data = ? and cancelado = ? and confirmado = ? ORDER BY hora_inicio"; //FAZER CORREÇÃO PARA MAIS CLIENTES

	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $date);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $confirmado);
	$consulta->execute();

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$agendamentos[] = array("id_agenda" => $row->agenda_id, "data" => $row->agenda_data, "hora_inicio"=>$row->agenda_hora_inicio, "hora_fim"=>$row->agenda_hora_fim, "usuario"=>$row->agenda_usuario,
									"cancelado"=>$row->agenda_cancelado, "confirmado"=>$row->agenda_confirmado, "usuario_nome"=>$row->usuario_nome, "usuario_email"=>$row->usuario_email);
			echo $row->agenda_id . " - " . $row->agenda_data . " - " . $row->agenda_hora_inicio . " - " . $row->agenda_hora_fim . " - " . $row->agenda_usuario . "\n";
	   }
	} else {
		$agendamentos[] = array("id" => null, "data" => $date, "hora_inicio"=>null, "hora_fim"=>null, "usuario"=>0);
		echo "Nenhum registro encontrado\n";
	}

	return $agendamentos;

}

function carregarLocalizacaoSemana($date, $cliente){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$localizacao;

	$sql = "SELECT localizacao.id as localizacao_id,
					localizacao.titulo_endereco as localizacao_titulo_endereco,
					localizacao.subtitulo_endereco as localizacao_subtitulo_endereco,
					localizacao.coordenada as localizacao_coordenada,
					localizacao.img as localizacao_img, 
					localizacao_semana.id as localizacao_semana_id,
					localizacao_semana.localizacao as localizacao_semana_localizacao,
					localizacao_semana.dia_semana as localizacao_semana_dia_semana,
					localizacao_semana.indisponivel as localizacao_semana_indisponivel
			FROM localizacao_semana INNER JOIN localizacao ON
					localizacao_semana.localizacao = localizacao.id 
			WHERE 
					(DATE_FORMAT(?, '%w')) = localizacao_semana.dia_semana and 
					localizacao.cliente = ? and
					localizacao_semana.cliente = ? "; //FAZER CORREÇÃO PARA MAIS CLIENTES

	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $date);
	$consulta->bindParam(2, $cliente);
	$consulta->bindParam(3, $cliente);
	$consulta->execute();

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$localizacao = array("titleAdress"=>$row->localizacao_titulo_endereco, "subTitleAdress"=>$row->localizacao_subtitulo_endereco, "destination"=>$row->localizacao_coordenada, "imgDestination"=>$row->localizacao_img, "indisponivel"=>$row->localizacao_semana_indisponivel);
	   }
	} 

	return $localizacao;

}

function classificarAgenda($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $usuario, $cliente){
	
	$date = $agendamentos[0]["data"];
	$local = carregarLocalizacaoSemana($date, $cliente);
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
					"strAvailable" => ($usuario == $agendamentos[$index]["usuario"] ? "Meu horário" : "Indisponível"),
					"titleAdress" => (is_null($agendamentos[$index]["titleAdress"]) ? (isset($local["titleAdress"]) ? $local["titleAdress"] : null) : $agendamentos[$index]["titleAdress"]),
					"subTitleAdress" => (is_null($agendamentos[$index]["subTitleAdress"]) ? (isset($local["subTitleAdress"]) ? $local["subTitleAdress"] : null) : $agendamentos[$index]["subTitleAdress"]), 
					"destination" => (is_null($agendamentos[$index]["destination"]) ? (isset($local["destination"]) ? $local["destination"] : null) : $agendamentos[$index]["destination"]),
					"imgDestination" => (is_null($agendamentos[$index]["imgDestination"]) ? (isset($local["imgDestination"]) ? $local["imgDestination"] : null) : $agendamentos[$index]["imgDestination"])
				);
				$usuarios[] = $agendamentos[$index]["usuario"];
				
				$partes_hora_fim = explode(":", $agendamentos[$index]["hora_fim"]);
				$partes_hora_inicio = explode(":", $agendamentos[$index]["hora_inicio"]);
				$hora = $partes_hora_fim[0] - $partes_hora_inicio[0];
				$minuto = $partes_hora_fim[1] - $partes_hora_inicio[1];
				$hora_atual = strtotime($hora_atual) + ($minuto*60) + ($hora*60*60);
				$hora_atual = strftime('%H:%M:%S', $hora_atual);
				$adicional = "00:00:00";
				$adicional = strftime('%H:%M:%S', $adicional);
				//echo ($minuto*60) + ($hora*60*60) . "\n";
				//echo $agendamentos[$index]["hora_fim"] . "\n";
				//echo $agendamentos[$index]["hora_inicio"] . "\n";
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
					"strAvailable" => "Horário disponível",
					"titleAdress" => (is_null($agendamentos[$index]["titleAdress"]) ? (isset($local["titleAdress"]) ? $local["titleAdress"] : null) : $agendamentos[$index]["titleAdress"]),
					"subTitleAdress" => (is_null($agendamentos[$index]["subTitleAdress"]) ? (isset($local["subTitleAdress"]) ? $local["subTitleAdress"] : null) : $agendamentos[$index]["subTitleAdress"]), 
					"destination" => (is_null($agendamentos[$index]["destination"]) ? (isset($local["destination"]) ? $local["destination"] : null) : $agendamentos[$index]["destination"]),
					"imgDestination" => (is_null($agendamentos[$index]["imgDestination"]) ? (isset($local["imgDestination"]) ? $local["imgDestination"] : null) : $agendamentos[$index]["imgDestination"])
				);
				$usuarios[] = "0";
				
				//echo $hora_atual . "\n";
				$hora_atual = strtotime($hora_atual) + 60;
				$hora_atual = strftime('%H:%M:%S', $hora_atual);
				$adicional = strtotime($adicional) + 60;
				$adicional = strftime('%H:%M:%S', $adicional);
			}
			
		}else{
			if(is_int(array_search($hora_atual, array_column($agendamentos, "hora_inicio")))){
				$index = array_search($hora_atual, array_column($agendamentos, "hora_inicio"));
				$agenda[] = array(
					"id" => $agendamentos[$index]["id"],
					"date" => $agendamentos[$index]["data"],
					"time" => str_replace(":", "h", substr($agendamentos[$index]["hora_inicio"], 0, 5)) . " às " . str_replace(":", "h", substr($agendamentos[$index]["hora_fim"], 0, 5)),
					"available" => false,
					"mySchedule" => ($usuario == $agendamentos[$index]["usuario"] ? true : false),
					"strAvailable" => ($usuario == $agendamentos[$index]["usuario"] ? "Meu horário" : "Indisponível"),
					"titleAdress" => (is_null($agendamentos[$index]["titleAdress"]) ? (isset($local["titleAdress"]) ? $local["titleAdress"] : null) : $agendamentos[$index]["titleAdress"]),
					"subTitleAdress" => (is_null($agendamentos[$index]["subTitleAdress"]) ? (isset($local["subTitleAdress"]) ? $local["subTitleAdress"] : null) : $agendamentos[$index]["subTitleAdress"]), 
					"destination" => (is_null($agendamentos[$index]["destination"]) ? (isset($local["destination"]) ? $local["destination"] : null) : $agendamentos[$index]["destination"]),
					"imgDestination" => (is_null($agendamentos[$index]["imgDestination"]) ? (isset($local["imgDestination"]) ? $local["imgDestination"] : null) : $agendamentos[$index]["imgDestination"])
				);
				$usuarios[] = $agendamentos[$index]["usuario"];
				
				$partes_hora_fim = explode(":", $agendamentos[$index]["hora_fim"]);
				$partes_hora_inicio = explode(":", $agendamentos[$index]["hora_inicio"]);
				$hora = $partes_hora_fim[0] - $partes_hora_inicio[0];
				$minuto = $partes_hora_fim[1] - $partes_hora_inicio[1];
				$hora_atual = strtotime($hora_atual) + ($minuto*60) + ($hora*60*60);
				$hora_atual = strftime('%H:%M:%S', $hora_atual);
				$adicional = "00:00:00";
				$adicional = strftime('%H:%M:%S', $adicional);
				//echo ($minuto*60) + ($hora*60*60) . "\n";
				//echo $agendamentos[$index]["hora_fim"] . "\n";
				//echo $agendamentos[$index]["hora_inicio"] . "\n";
			}else{
				//echo $hora_atual . "\n";
				$hora_atual = strtotime($hora_atual) + 60;
				$index = array_search($hora_atual, array_column($agendamentos, "hora_inicio"));
				$hora_atual = strftime('%H:%M:%S', $hora_atual);
				$adicional = strtotime($adicional) + 60;
				$adicional = strftime('%H:%M:%S', $adicional);
			}			
		}
		if(strtotime($adicional) >= strtotime($intervalo_padrao)){
			$adicional = "00:00:00";
			//echo "adicional = 00" . "\n";
		}
	}
	//return array($agenda, $usuarios);
	$meus_horarios = array();
	
	foreach ($agenda as $item){
		if($item["mySchedule"]){
			$meus_horarios[] = $item;
		}
	}
	
	return array($agenda, $meus_horarios);
}

function classificarAgendaAdministrativo($agendamentos, $inicio_servico, $termino_servico, $intervalo_padrao, $usuario, $cliente){

	$date = $agendamentos[0]["data"];
	$local = carregarLocalizacaoSemana($date, $cliente);
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
					"idUser" => $agendamentos[$index]["idUser"],
					"nameUser" => $agendamentos[$index]["nameUser"],
					"cpfUser" => $agendamentos[$index]["cpfUser"],
					"phoneUser" => $agendamentos[$index]["phoneUser"],
					"emailUser" => $agendamentos[$index]["emailUser"],
					"birthdayUser" => $agendamentos[$index]["birthdayUser"],
					"imageUser" => $agendamentos[$index]["imageUser"],
					"mySchedule" => ($usuario == $agendamentos[$index]["usuario"] ? true : false),
					"strAvailable" => ($usuario == $agendamentos[$index]["usuario"] ? "Meu horário" : "Indisponível"),
					"titleAdress" => (is_null($agendamentos[$index]["titleAdress"]) ? (isset($local["titleAdress"]) ? $local["titleAdress"] : null) : $agendamentos[$index]["titleAdress"]),
					"subTitleAdress" => (is_null($agendamentos[$index]["subTitleAdress"]) ? (isset($local["subTitleAdress"]) ? $local["subTitleAdress"] : null) : $agendamentos[$index]["subTitleAdress"]), 
					"destination" => (is_null($agendamentos[$index]["destination"]) ? (isset($local["destination"]) ? $local["destination"] : null) : $agendamentos[$index]["destination"]),
					"imgDestination" => (is_null($agendamentos[$index]["imgDestination"]) ? (isset($local["imgDestination"]) ? $local["imgDestination"] : null) : $agendamentos[$index]["imgDestination"])
				);
				$usuarios[] = $agendamentos[$index]["usuario"];

				$partes_hora_fim = explode(":", $agendamentos[$index]["hora_fim"]);
				$partes_hora_inicio = explode(":", $agendamentos[$index]["hora_inicio"]);
				$hora = $partes_hora_fim[0] - $partes_hora_inicio[0];
				$minuto = $partes_hora_fim[1] - $partes_hora_inicio[1];
				$hora_atual = strtotime($hora_atual) + ($minuto*60) + ($hora*60*60);
				$hora_atual = strftime('%H:%M:%S', $hora_atual);
				$adicional = "00:00:00";
				$adicional = strftime('%H:%M:%S', $adicional);
				//echo ($minuto*60) + ($hora*60*60) . "\n";
				//echo $agendamentos[$index]["hora_fim"] . "\n";
				//echo $agendamentos[$index]["hora_inicio"] . "\n";
			}else{
				$partes = explode(":", $intervalo_padrao);
				$hora_final = strtotime($hora_atual) + $partes[0]*3600 + $partes[1]*60 + $partes[2];
				$hora_final = strftime('%H:%M:%S', $hora_final);
				$agenda[] = array(
					"id" => null,
					"date" => $date,
					"time" => str_replace(":", "h", substr($hora_atual, 0, 5)) . " às " . str_replace(":", "h", substr($hora_final, 0, 5)),
					"available" => true,
					"idUser" => "0",
					"nameUser" => null,
					"cpfUser" => null,
					"phoneUser" => null,
					"emailUser" => null,
					"birthdayUser" => null,
					"imageUser" => null,
					"mySchedule" => false,
					"strAvailable" => "Horário disponível",
					"titleAdress" => (is_null($agendamentos[$index]["titleAdress"]) ? (isset($local["titleAdress"]) ? $local["titleAdress"] : null) : $agendamentos[$index]["titleAdress"]),
					"subTitleAdress" => (is_null($agendamentos[$index]["subTitleAdress"]) ? (isset($local["subTitleAdress"]) ? $local["subTitleAdress"] : null) : $agendamentos[$index]["subTitleAdress"]), 
					"destination" => (is_null($agendamentos[$index]["destination"]) ? (isset($local["destination"]) ? $local["destination"] : null) : $agendamentos[$index]["destination"]),
					"imgDestination" => (is_null($agendamentos[$index]["imgDestination"]) ? (isset($local["imgDestination"]) ? $local["imgDestination"] : null) : $agendamentos[$index]["imgDestination"])
				);
				$usuarios[] = "0";

				//echo $hora_atual . "\n";
				$hora_atual = strtotime($hora_atual) + 60;
				$hora_atual = strftime('%H:%M:%S', $hora_atual);
				$adicional = strtotime($adicional) + 60;
				$adicional = strftime('%H:%M:%S', $adicional);
			}

		}else{
			if(is_int(array_search($hora_atual, array_column($agendamentos, "hora_inicio")))){
				$index = array_search($hora_atual, array_column($agendamentos, "hora_inicio"));
				$agenda[] = array(
					"id" => $agendamentos[$index]["id"],
					"date" => $agendamentos[$index]["data"],
					"time" => str_replace(":", "h", substr($agendamentos[$index]["hora_inicio"], 0, 5)) . " às " . str_replace(":", "h", substr($agendamentos[$index]["hora_fim"], 0, 5)),
					"available" => false,
					"idUser" => $agendamentos[$index]["idUser"],
					"nameUser" => $agendamentos[$index]["nameUser"],
					"cpfUser" => $agendamentos[$index]["cpfUser"],
					"phoneUser" => $agendamentos[$index]["phoneUser"],
					"emailUser" => $agendamentos[$index]["emailUser"],
					"birthdayUser" => $agendamentos[$index]["birthdayUser"],
					"imageUser" => $agendamentos[$index]["imageUser"],
					"mySchedule" => ($usuario == $agendamentos[$index]["usuario"] ? true : false),
					"strAvailable" => ($usuario == $agendamentos[$index]["usuario"] ? "Meu horário" : "Indisponível"),
					"titleAdress" => (is_null($agendamentos[$index]["titleAdress"]) ? (isset($local["titleAdress"]) ? $local["titleAdress"] : null) : $agendamentos[$index]["titleAdress"]),
					"subTitleAdress" => (is_null($agendamentos[$index]["subTitleAdress"]) ? (isset($local["subTitleAdress"]) ? $local["subTitleAdress"] : null) : $agendamentos[$index]["subTitleAdress"]), 
					"destination" => (is_null($agendamentos[$index]["destination"]) ? (isset($local["destination"]) ? $local["destination"] : null) : $agendamentos[$index]["destination"]),
					"imgDestination" => (is_null($agendamentos[$index]["imgDestination"]) ? (isset($local["imgDestination"]) ? $local["imgDestination"] : null) : $agendamentos[$index]["imgDestination"])
				);
				$usuarios[] = $agendamentos[$index]["usuario"];

				$partes_hora_fim = explode(":", $agendamentos[$index]["hora_fim"]);
				$partes_hora_inicio = explode(":", $agendamentos[$index]["hora_inicio"]);
				$hora = $partes_hora_fim[0] - $partes_hora_inicio[0];
				$minuto = $partes_hora_fim[1] - $partes_hora_inicio[1];
				$hora_atual = strtotime($hora_atual) + ($minuto*60) + ($hora*60*60);
				$hora_atual = strftime('%H:%M:%S', $hora_atual);
				$adicional = "00:00:00";
				$adicional = strftime('%H:%M:%S', $adicional);
				//echo ($minuto*60) + ($hora*60*60) . "\n";
				//echo $agendamentos[$index]["hora_fim"] . "\n";
				//echo $agendamentos[$index]["hora_inicio"] . "\n";
			}else{
				//echo $hora_atual . "\n";
				$hora_atual = strtotime($hora_atual) + 60;
				$index = array_search($hora_atual, array_column($agendamentos, "hora_inicio"));
				$hora_atual = strftime('%H:%M:%S', $hora_atual);
				$adicional = strtotime($adicional) + 60;
				$adicional = strftime('%H:%M:%S', $adicional);
			}			
		}
		if(strtotime($adicional) >= strtotime($intervalo_padrao)){
			$adicional = "00:00:00";
			//echo "adicional = 00" . "\n";
		}
	}
	//return array($agenda, $usuarios);
	$meus_horarios = array();

	foreach ($agenda as $item){
		if($item["mySchedule"]){
			$meus_horarios[] = $item;
		}
	}

	return array($agenda, $meus_horarios);
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

function carregarOpcoesAgenda($client){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$cancelado = "0"; // 0 = não cancelado

	$sql = "SELECT
				hora_inicio,
				hora_termino,
				intervalo_padrao
			FROM
				opcoes_agenda
			WHERE
				cliente = ?"; 

	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $client);
	$consulta->execute();

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$dados = array("startService"=>$row->hora_inicio, "endService"=>$row->hora_termino, "defaultAttendance"=>$row->intervalo_padrao);
		}
	} else {
		$dados = array("startService"=>null, "endService"=>null, "defaultAttendance"=>null);
		echo "Nenhum registro encontrado\n";
	}
	return $dados;	
}

function atualizarOpcoesAgenda($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$hora_inicio = $data->startService;
	$hora_termino = $data->endService;
	$intervalo_padrao = $data->defaultAttendance;

	echo "Inserindo novo padrao de agenda\n";

	$sql = "UPDATE opcoes_agenda SET hora_inicio = ?, hora_termino = ?, intervalo_padrao = ? WHERE cliente = ?"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $hora_inicio);
	$consulta->bindParam(2, $hora_termino);
	$consulta->bindParam(3, $intervalo_padrao);
	$consulta->bindParam(4, $client);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}
?>