<?php

function addMedicao($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$medida = $data->idDescription;
	$data = date("Y-m-d H:i:s");
	$valor = $data->value;
	$paciente = $data->idUser;
	$cancelado = 0;

	echo "Inserindo refeicao\n";

	$sql = "INSERT INTO medicoes (medida, data, valor, usuario, cliente, cancelado) VALUES (?, ?, ?, ?, ?, ?)"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $medida);
	$consulta->bindValue(2, $data);
	$consulta->bindValue(3, $valor);
	$consulta->bindValue(4, $paciente);
	$consulta->bindValue(5, $client);
	$consulta->bindValue(6, $cancelado);
	$consulta->execute();


	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function carregarMedidas($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	echo "Carregando medidas\n";
	
	if(isset($data->patient)){
		$usuario = $data->patient;
	}
	
	$cancelado = 0;

	$sql = "SELECT 
				medidas.id as medidas_id,
				medidas.nome as medidas_nome,
				medicoes.medida as medicoes_medida,
				IF(medicoes.data is null, CURDATE(),medicoes.data) as medicoes_data,
				IF(medicoes.valor is null, '0', medicoes.valor) as medicoes_valor
			FROM 
				medidas LEFT JOIN medicoes
			ON
				medidas.id = medicoes.medida AND
				medicoes.cliente = ? AND
				medicoes.cancelado = ? AND
				medicoes.usuario = ?
			WHERE 
				medidas.cliente = ? AND 
				medidas.cancelado = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $client);
	$consulta->bindParam(2, $cancelado);
	$consulta->bindParam(3, $usuario);
	$consulta->bindParam(4, $client);
	$consulta->bindParam(5, $cancelado);
	$consulta->execute();


	if($consulta->rowCount()){
		$receitas[] = array("idRecipes" => $row->id, "nameRecipes" => $row->nome, "imageRecipes" => $row->imagem, "ingredientsRecipes" => $row->ingrediente, "prepareModeRecipes" => $row->modo_preparo);
	}else{
		$receitas[] = array("idRecipes" => null, "nameRecipes" => null, "imageRecipes" => null, "ingredientsRecipes" => null, "prepareModeRecipes" => null);
		echo "Nenhum registro encontrado\n";
	}

	return $receitas;
}

?>