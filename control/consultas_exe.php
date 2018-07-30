﻿<?php

function inserirConsulta($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$date = date("Y-m-d H-i-s");
	$paciente = $data->patient;
	$prontuario = $data->medicalRecords;
	
	echo "Inserindo consulta\n";
	
	$sql = "INSERT INTO consultas (data, prontuario, paciente, usuario, cliente) VALUES (?, ?, ?, ?, ?)"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $date);
	$consulta->bindValue(2, $prontuario);
	$consulta->bindValue(3, $paciente);
	$consulta->bindValue(4, $usuario);
	$consulta->bindValue(5, $client);
	$consulta->execute();
	
	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}

function carregarAcompanhamento($client){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$ativo = "0"; // 0 = não cancelado
	
	$news = array();
	$categoria = array();
	
	$sql = "SELECT
				usuario.nome as usuario_nome,
				usuario.telefone as usuario_telefone,
				usuario.id as usuario_id,
				usuario.img as usuario_avatar,
				consultas.data as consultas_data,
				IF(log.data is null, 'Sem registros', max(log.data)) as log_data,
				50 as total_consumo,
				100 as total_registro
			FROM
				(consultas , usuario) LEFT JOIN log
			ON
				consultas.paciente = usuario.id AND
				consultas.paciente = log.usuario
			WHERE
				consultas.paciente = usuario.id AND
				consultas.cliente = ?
			GROUP BY 
				usuario.id
			ORDER BY
				consultas.data"; 
	
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $client);
	$consulta->execute();
	
	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
	   		$dados[] = array("avatar"=>$row->usuario_avatar, "userName"=>$row->usuario_nome, "userPhone"=>$row->usuario_telefone, "userId"=>$row->usuario_id, "lastAttendance"=>$row->consultas_data, "lastAccess"=>$row->log_data, "totalConsuption"=>$row->total_consumo, "totalRegistered"=>$row->total_registro);
		}
	} else {
		$dados[] = array("avatar"=>null, "userName"=>null, "userPhone"=>null, "userId"=>null, "lastAttendance"=>null, "lastAccess"=>null, "totalConsuption"=>null, "totalRegistered"=>null);
		echo "Nenhum registro encontrado\n";
	}
	return $dados;	
}

function updateTimeLinePatient ($data, $client, $usuario){
	require ("lib/bd.php");

	$paciente = $data->idUser;
	
	$sql = "SELECT id, data, prontuario, paciente, usuario, cliente FROM consultas WHERE cliente = ? AND paciente = ? ORDER BY data";

	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $client);
	$consulta->bindParam(2, $paciente);
	$consulta->execute();

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$dados[] = array("date"=>$row->data, "medicalRecord"=>$row->prontuario);
		}
	} else {
		$dados[] = array("date"=>null, "medicalRecord"=>null);
		echo "Nenhum registro encontrado\n";
	}
	return $dados;
}


?>