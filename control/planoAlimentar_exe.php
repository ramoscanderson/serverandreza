<?php

function carregarPlanoAlimentar($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	/*
	if($usuario != "2"){
		$usuario = 3;
	}
	*/
	
	if(isset($data->idUser)){
		$usuario = $data->idUser;
	}
	
	echo "Plano alimentar do usuario:" . $usuario . "\n";
	
	$ativo = "0"; // 0 = não cancelado
	
	$sql = "SELECT 
		receita.id as receita_id, 
		receita.nome as alimento_nome, 
		receita.imagem as alimento_imagem, 
		receita.ingrediente as alimento_ingrediente, 
		receita.modo_preparo as alimento_modo_preparo, 
		alimento.id as alimento_id, 
		alimento.consumo as alimento_consumo, 
		alimento.obs as alimento_obs,
		refeicao.id as refeicao_id, 
		refeicao.hora as refeicao_hora, 
		refeicao.descricao as refeicao_descricao,
		plano_alimentar.id as plano_alimentar_id, 
		plano_alimentar.titulo as plano_alimentar_titulo, 
		plano_alimentar.usuario as plano_alimentar_usuario,
		MAX(consumo_alimento.data) as ultimo_consumo,
		SUM(alimento.consumo) as quant
	FROM 
		plano_alimentar LEFT JOIN (refeicao 
	LEFT JOIN 
		((alimento, receita) LEFT JOIN consumo_alimento
				ON
					consumo_alimento.alimento = alimento.id AND
					consumo_alimento.cancelado = ?)
		   ON
				refeicao.id = alimento.refeicao AND
				alimento.receita = receita.id AND
				refeicao.cancelado = ? AND
				alimento.cancelado = ?)
	ON 
		plano_alimentar.id = refeicao.plano_alimentar AND
		plano_alimentar.cancelado = ?

	WHERE 	
		plano_alimentar.cancelado = ? and 
		plano_alimentar.cliente = ?  and 
		plano_alimentar.usuario = ? 
	GROUP BY 
		alimento.id, refeicao.id, plano_alimentar.id
	ORDER BY 
		plano_alimentar.id, refeicao.hora, refeicao.id, quant"; //FAZER CORREÇÃO PARA MAIS CLIENTES  , alimento.consumo
	
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $ativo);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $ativo);
	$consulta->bindParam(4, $ativo);
	$consulta->bindParam(5, $ativo);
	$consulta->bindParam(6, $client);
	$consulta->bindParam(7, $usuario);
	$consulta->execute();
	
	$plano_alimentar = array();
	$plano_alimentar_cont = -1;
	$refeicao_cont = -1;
	if ($consulta->rowCount() > 0) {
		$refeicao_id;
		$plano_alimentar_id;
		while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			if(is_null($row->refeicao_id)){
				$plano_alimentar[] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
					array(array("mealId" => null, "hour" => null, "description" => null, "content" => 
						array(array("idRecipe" => null, "foodId" => null, "foodName" => null, "imgFood" => null, "ingredients" => array(), "modePrepare" => null, "consumption" => false, "obs" => null)))));
			}else{
				$partes = explode(":", $row->refeicao_hora);
				$hora = $partes[0];
				$minuto = $partes[1];
   
				if($plano_alimentar_id == $row->plano_alimentar_id){
					if($refeicao_id == $row->refeicao_id){
						$plano_alimentar[$plano_alimentar_cont]["foodPlan"][$refeicao_cont]["content"][] = array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs);
					}else{
						$plano_alimentar[$plano_alimentar_cont]["foodPlan"][] = array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
							array(array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)));
						$refeicao_id = $row->refeicao_id;
						$refeicao_cont++;	
					}
				}else{
					$refeicao_cont = -1;
					$plano_alimentar[] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
						array(array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
							array(array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)))));
					$plano_alimentar_id = $row->plano_alimentar_id;
					$refeicao_id = $row->refeicao_id;
					$plano_alimentar_cont++;
					$refeicao_cont++;
				}
			}
			echo $row->plano_alimentar_usuario . " - " . $row->plano_alimentar_id . " - " . $row->plano_alimentar_titulo . " - " . 
				 $row->refeicao_id . " - " . $row->refeicao_hora . " - " . $row->refeicao_descricao . " - " . 
				 $row->alimento_id . " - " . $row->alimento_nome . " - " . (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? "true" : "false"), "\n";
		}
	} else {
		$plano_alimentar[] = array("planId" => null, "title" => null, "foodPlan" => 
						array(array("mealId" => null, "hour" => null, "description" => null, "content" => 
							array(array("idRecipe" => $row->receita_id, "foodId" => null, "imgFood" => null, "ingredients" => array(), "modePrepare" => null, "obs" => null)))));
		echo "Nenhum registro encontrado\n";
	}
	
	return $plano_alimentar;
	
}


function addConsumo($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$alimento = $data->foodId;
	$refeicao = $data->mealId;
	$plano = $data->planId;
	$date_hour = date('Y-m-d H:i:s');
	
	echo "Inserindo consumo de refeicao\n";

	$sql = "INSERT INTO consumo_alimento (alimento, refeicao, plano_alimentar, data, cliente, usuario) VALUES (?, ?, ?, ?, ?, ?)"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $alimento);
	$consulta->bindValue(2, $refeicao);
	$consulta->bindValue(3, $plano);
	$consulta->bindValue(4, $date_hour);
	$consulta->bindValue(5, $client);
	$consulta->bindValue(6, $usuario);
	$consulta->execute();


	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function cancelarConsumo($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$alimento = $data->foodId;
	$refeicao = $data->mealId;
	$plano = $data->planId;
	$date_hour = date('Y-m-d');
	$cancelado = 1; 

	echo "Cancelando consumo de refeicao . $date_hour . \n";

	$sql = "UPDATE consumo_alimento SET cancelado = ? where alimento = ? and refeicao = ? and plano_alimentar = ? and CAST(data AS DATE) = ? and cliente = ? and usuario = ?"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $cancelado);
	$consulta->bindValue(2, $alimento);
	$consulta->bindValue(3, $refeicao);
	$consulta->bindValue(4, $plano);
	$consulta->bindValue(5, $date_hour);
	$consulta->bindValue(6, $client);
	$consulta->bindValue(7, $usuario);
	$consulta->execute();


	if($consulta){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function addPlanoAlimentar($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$paciente = $data->idUser;
	$nome = $data->name;
	$date = date('Y-m-d');
	$cancelado = 0;

	echo "Inserindo plano alimentar\n";

	$sql = "INSERT INTO plano_alimentar (data_criacao, titulo, usuario, cliente, cancelado) VALUES (?, ?, ?, ?, ?)"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $date);
	$consulta->bindValue(2, $nome);
	$consulta->bindValue(3, $paciente);
	$consulta->bindValue(4, $client);
	$consulta->bindValue(5, $cancelado);
	$consulta->execute();


	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function addRefeicao($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$refeicao = $data->idMeal;
	$plano = $data->idFoodPlan;
	$horario = $data->hour;
	$descricao = $data->description;
	$receitas = $data->content;
	$cancelado = 0;
	$cancelado2 = 1;
	$consumo = 0;
	
	if($refeicao){
		echo "Alterando refeicao\n";

		$sql = "UPDATE refeicao SET plano_alimentar = ?, hora = ?, descricao = ? WHERE id = ?"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindParam(1, $plano);
		$consulta->bindParam(2, $horario);
		$consulta->bindParam(3, $descricao);
		$consulta->bindParam(4, $refeicao);
		$consulta->execute();
				
		echo "Cancelando alimento\n";

		$sql = "UPDATE alimento SET cancelado = ? WHERE refeicao = ?"; 
		$consultaReceita = $bd->prepare($sql);
		$consultaReceita->bindValue(1, $cancelado2);
		$consultaReceita->bindValue(2, $refeicao);
		$consultaReceita->execute();
		
		foreach ($receitas as $receita){
			echo "Inserindo alimento\n";

			$sql = "INSERT INTO alimento (receita, refeicao, consumo, obs, cancelado) VALUES (?, ?, ?, ?, ?)"; 
			$consultaReceita = $bd->prepare($sql);
			$consultaReceita->bindValue(1, $receita->idRecipe);
			$consultaReceita->bindValue(2, $refeicao);
			$consultaReceita->bindValue(3, $consumo);
			$consultaReceita->bindValue(4, $receita->obs);
			$consultaReceita->bindValue(5, $cancelado);
			$consultaReceita->execute();
		}
		
	}else{
		echo "Inserindo refeicao\n";

		$sql = "INSERT INTO refeicao (plano_alimentar, hora, descricao, cancelado) VALUES (?, ?, ?, ?)"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindValue(1, $plano);
		$consulta->bindValue(2, $horario);
		$consulta->bindValue(3, $descricao);
		$consulta->bindValue(4, $cancelado);
		$consulta->execute();
		$lastId = $bd->lastInsertId();
		echo $lastId . "\n";
		
		foreach ($receitas as $receita){
			echo "Inserindo alimento\n";

			$sql = "INSERT INTO alimento (receita, refeicao, consumo, obs, cancelado) VALUES (?, ?, ?, ?, ?)"; 
			$consultaReceita = $bd->prepare($sql);
			$consultaReceita->bindValue(1, $receita->foodId);
			$consultaReceita->bindValue(2, $lastId);
			$consultaReceita->bindValue(3, $consumo);
			$consultaReceita->bindValue(4, $receita->obs);
			$consultaReceita->bindValue(5, $cancelado);
			$consultaReceita->execute();
		}
	}
	
	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function addReceita($img, $data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$receita = $data->idRecipe;
	$ingredientes = implode("<br>", $data->ingredients);
	$modo_preparo = $data->prepareMode;
	$imagem = $img;
	$descricao = $data->description;
	$cancelado = 0;
	
	if($receita){
		echo "Alterando receita: \n";

		$sql = "UPDATE receita SET nome = ?, imagem = ?, ingrediente = ?, modo_preparo = ? WHERE id = ?"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindParam(1, $descricao);
		$consulta->bindParam(2, $imagem);
		$consulta->bindParam(3, $ingredientes);
		$consulta->bindParam(4, $modo_preparo);
		$consulta->bindParam(5, $receita);
		$consulta->execute();
	}else{
		echo "Inserindo receita\n";

		$sql = "INSERT INTO receita (nome, imagem, ingrediente, modo_preparo, cliente, cancelado) VALUES (?, ?, ?, ?, ?, ?)"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindValue(1, $descricao);
		$consulta->bindValue(2, $imagem);
		$consulta->bindValue(3, $ingredientes);
		$consulta->bindValue(4, $modo_preparo);
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


function carregarReceitas($client){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	echo "Carregando receitas\n";
	
	echo $client. "\n";
	$cancelado = 0;

	$sql = "SELECT id, nome, imagem, ingrediente, modo_preparo FROM receita WHERE cliente = ? AND cancelado = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $client);
	$consulta->bindParam(2, $cancelado);
	$consulta->execute();

	$receitas = array();
	if($consulta->rowCount()){
		while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$receitas[] = array("idRecipe" => $row->id, "nameRecipe" => $row->nome, "imageRecipe" => $row->imagem, "ingredientsRecipe" => explode("<br>", $row->ingrediente), "prepareModeRecipe" => $row->modo_preparo);
		}
	}else{
		$receitas[] = array("idRecipe" => null, "nameRecipe" => null, "imageRecipe" => null, "ingredientsRecipe" => null, "prepareModeRecipe" => null);
		echo "Nenhum registro encontrado\n";
	}

	return $receitas;
}


function addAlimento($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$receita = $data->idRecipes;
	$refeicao = $data->idMeal;
	$obs = $data->observation;
	$cancelado = 0;
	$consumo = 0;

	echo "Inserindo alimento\n";

	$sql = "INSERT INTO alimento (receita, refeicao, consumo, obs, cancelado) VALUES (?, ?, ?, ?, ?)"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $receita);
	$consulta->bindValue(2, $refeicao);
	$consulta->bindValue(3, $consumo);
	$consulta->bindValue(4, $obs);
	$consulta->bindValue(5, $cancelado);
	$consulta->execute();


	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}

?>