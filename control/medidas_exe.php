<?php

function addMedicao($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$medicao = $data->measurement->measurementId;
	
	$medida = $data->idMeasure;
	$date = date("Y-m-d H:i:s");
	$valor = str_replace(",", ".", $data->measurement->value);
	$paciente = $data->idUser;
	$cancelado = 0;

	if($medicao){
		echo "Alterando medicao\n";

		$sql = "UPDATE medicoes SET valor = ? WHERE id = ?"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindParam(1, $valor);
		//$consulta->bindParam(2, $date);
		$consulta->bindParam(2, $medicao);
		$consulta->execute();
	}else{
		echo "Inserindo medicao no usuario: " . $paciente . "\n";
   
		$sql = "INSERT INTO medicoes (medida, data, valor, usuario, cliente, cancelado) VALUES (?, ?, ?, ?, ?, ?)"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindValue(1, $medida);
		$consulta->bindValue(2, $date);
		$consulta->bindValue(3, $valor);
		$consulta->bindValue(4, $paciente);
		$consulta->bindValue(5, $client);
		$consulta->bindValue(6, $cancelado);
		$consulta->execute();
	}


	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}

function addMedida($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");


	$medida = $data->idMeasure;
	$nome = $data->descriptionMeasure;
	$unidade = $data->unityMeasure;
	$cancelado = 0;

	if($medida){
		echo "Alterando medida\n";

		$sql = "UPDATE medidas SET nome = ?, unidade = ? WHERE id = ?"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindParam(1, $nome);
		$consulta->bindParam(2, $unidade);
		$consulta->bindParam(3, $medida);
		$consulta->execute();
	}else{
		echo "Inserindo medida\n";

		$sql = "INSERT INTO medidas (nome, unidade, cliente, cancelado) VALUES (?, ?, ?, ?)"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindValue(1, $nome);
		$consulta->bindParam(2, $unidade);
		$consulta->bindValue(3, $client);
		$consulta->bindValue(4, $cancelado);
		$consulta->execute();
	}
	
	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function deleteMedida($data){
	require ("lib/bd.php");

	$medida = $data->idMeasure;
	$cancelado = 1;

	echo "Deletando medida\n";

	$sql = "UPDATE medidas SET cancelado = ? WHERE id = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cancelado);
	$consulta->bindParam(2, $medida);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function deleteMedicao($data){
	require ("lib/bd.php");

	$medicao = $data->measurement->measurementId;
	$cancelado = 1;

	echo "Deletando medicao\n";

	$sql = "UPDATE medicoes SET cancelado = ? WHERE id = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cancelado);
	$consulta->bindParam(2, $medicao);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function carregarMedidas($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	if(isset($data->idUser)){
		$usuario = $data->idUser;
	}
	
	echo "Carregando medidas do usuario: " . $usuario . "\n";
	
	$cancelado = 0;
	
	$sql = "SELECT 
				medidas.id as medidas_id,
				medidas.nome as medidas_nome,
				medidas.unidade as medidas_unidade,
				medicoes.medida as medicoes_medida,
				IF(medicoes.data is null, CURDATE(),medicoes.data) as medicoes_data,
				IF(medicoes.valor is null, '0', medicoes.valor) as medicoes_valor,
				IF(medicoes.id is null, '0', medicoes.id) as medicoes_id
			FROM 
				medidas LEFT JOIN medicoes
			ON
				medidas.id = medicoes.medida AND
				medicoes.cliente = ? AND
				medicoes.cancelado = ? AND
				medicoes.usuario = ?
			WHERE 
				medidas.cliente = ? AND 
				medidas.cancelado = ?
			ORDER BY
				medidas.id,
				medicoes.data"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $client);
	$consulta->bindParam(2, $cancelado);
	$consulta->bindParam(3, $usuario);
	$consulta->bindParam(4, $client);
	$consulta->bindParam(5, $cancelado);
	$consulta->execute();


	if($consulta->rowCount()){
		$medida_atual = "";
		$medida_nome = "";
		$medida_unidade = "";
		$array_values = array();
		$medidas = array();
		while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			if($medida_atual == ""){
				$medida_atual = $row->medidas_id;
				$medida_nome = $row->medidas_nome;
				$medida_unidade = $row->medidas_unidade;
				$array_values[] = array("date" => $row->medicoes_data, "value" => $row->medicoes_valor, "measurementId" => $row->medicoes_id);
			}else{
				if($medida_atual == $row->medidas_id){
					$array_values[] = array("date" => $row->medicoes_data, "value" => $row->medicoes_valor, "measurementId" => $row->medicoes_id);
				}else{
					$medidas[] = array("descriptionMeasure" => $medida_nome, "unityMeasure" => $medida_unidade, "idMeasure" => $medida_atual, "values" => $array_values);
					$medida_atual = $row->medidas_id;
					$medida_nome = $row->medidas_nome;
					$medida_unidade = $row->medidas_unidade;
					$array_values = array();
					$array_values[] = array("date" => $row->medicoes_data, "value" => $row->medicoes_valor, "measurementId" => $row->medicoes_id);
				}
			}
		}
		$medidas[] = array("descriptionMeasure" => $medida_nome, "unityMeasure" => $medida_unidade, "idMeasure" => $medida_atual, "values" => $array_values);
		
	}else{
		$medidas[] = array("descriptionMeasure" => null, "unityMeasure" => null, "idMeasure" => null, "values" => null);
		echo "Nenhum registro encontrado\n";
	}

	return $medidas;
}

?>