<?php

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
	$retorno_sim = 1;
	$retorno_nao = 0;
	$dias_decorridos = 30;
	
	$news = array();
	$categoria = array();
	
	$sql = "SELECT
				usuario.nome as usuario_nome, 
				usuario.cpf as usuario_cpf, 
				usuario.email as usuario_email, 
				usuario.telefone as usuario_telefone, 
				usuario.id as usuario_id, 
				usuario.data_nascimento as usuario_data_nascimento, 
				usuario.img as usuario_avatar, 
				(select IF(max(consultas.data) is null, 'Sem registros', max(consultas.data)) from consultas where usuario.id = consultas.paciente) as consultas_data, 
				IF(log.data is null, 'Sem registros', max(log.data)) as log_data, 
				(select count(*) from consultas where consultas.paciente = usuario_id AND consultas.retorno = ?) as total_consumo, 
				(select count(*) from consultas where consultas.paciente = usuario_id AND consultas.retorno = ?) as total_registro 
			FROM
				(usuario, consultas)
			LEFT JOIN 
				log
			ON
				usuario.id = log.usuario AND
				usuario.id = consultas.paciente AND
				log.cliente = ?
			WHERE 
				usuario.cliente = ? AND
				usuario.id = consultas.paciente AND
				consultas.cliente = ? AND
				(DATEDIFF(CURDATE(), log.data) < ? OR DATEDIFF(CURDATE(), consultas.data) < ?)
			GROUP BY 
				usuario.id
			ORDER BY 
				usuario.nome"; 
	
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $retorno_nao);
	$consulta->bindParam(2, $retorno_sim);
	$consulta->bindParam(3, $client);
	$consulta->bindParam(4, $client);
	$consulta->bindParam(5, $client);
	$consulta->bindParam(6, $dias_decorridos);
	$consulta->bindParam(7, $dias_decorridos);
	$consulta->execute();
	
	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
	   		$dados[] = array("avatar"=>$row->usuario_avatar, "birthdayUser"=>$row->usuario_data_nascimento, "userName"=>$row->usuario_nome, "cpfUser"=>$row->usuario_cpf, "emailUser"=>$row->usuario_email, "userPhone"=>$row->usuario_telefone, "userId"=>$row->usuario_id, "lastAttendance"=>$row->consultas_data, "lastAccess"=>$row->log_data, "totalAttendanceUser"=>$row->total_consumo, "totalReturnUser"=>$row->total_registro);
		}
	} else {
		$dados[] = array("avatar"=>null, "birthdayUser"=>null, "userName"=>null, "cpfUser"=>null, "emailUser"=>null, "userPhone"=>null, "userId"=>null, "lastAttendance"=>null, "lastAccess"=>null, "totalAttendanceUser"=>null, "totalReturnUser"=>null);
		echo "Nenhum registro encontrado\n";
	}
	return $dados;	
}

function updateTimeLinePatient ($data, $client, $usuario){
	require ("lib/bd.php");

	$paciente = $data->idUser;
	
	$sql = "SELECT id, data, prontuario, paciente, usuario, cliente FROM consultas WHERE cliente = ? AND paciente = ? ORDER BY data DESC";

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