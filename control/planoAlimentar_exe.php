<?php

function carregarPlanoAlimentar($client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	if($usuario != "1"){
		$usuario = 3;
	}
	
	$ativo = "0"; // 0 = não cancelado
	
	$sql = "SELECT 
	receita.id as receita_id, receita.nome as alimento_nome, receita.imagem as alimento_imagem, receita.ingrediente as alimento_ingrediente, receita.modo_preparo as alimento_modo_preparo, 
	alimento.id as alimento_id, alimento.consumo as alimento_consumo, alimento.obs as alimento_obs,
	refeicao.id as refeicao_id, refeicao.hora as refeicao_hora, refeicao.descricao as refeicao_descricao,
	plano_alimentar.id as plano_alimentar_id, plano_alimentar.titulo as plano_alimentar_titulo, plano_alimentar.usuario as plano_alimentar_usuario, count(*) as quant, max(consumo_alimento.data) as ultimo_consumo
	FROM (plano_alimentar, refeicao, alimento, receita) LEFT JOIN consumo_alimento ON 
	consumo_alimento.alimento = alimento.id and 
	consumo_alimento.plano_alimentar = plano_alimentar.id and 
	consumo_alimento.refeicao = refeicao.id WHERE 
	alimento.receita = receita.id and 
	alimento.refeicao = refeicao.id and 
	refeicao.plano_alimentar = plano_alimentar.id and
	consumo_alimento.cancelado = ? and 
	alimento.cancelado = ? and 
	refeicao.cancelado = ? and 
	plano_alimentar.cancelado = ? and 
	plano_alimentar.cliente = ?  and 
	plano_alimentar.usuario = ? 
	GROUP BY receita.id, alimento.id, refeicao.id, plano_alimentar.id
	ORDER BY plano_alimentar.id, refeicao.hora, refeicao.id, quant"; //FAZER CORREÇÃO PARA MAIS CLIENTES  , alimento.consumo
	
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $ativo);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $ativo);
	$consulta->bindParam(4, $ativo);
	$consulta->bindParam(5, $client);
	$consulta->bindParam(6, $usuario);
	$consulta->execute();
	
	$plano_alimentar = array();
	$plano_alimentar_cont = -1;
	$refeicao_cont = -1;
	if ($consulta->rowCount() > 0) {
		$refeicao_id;
		$plano_alimentar_id;
		while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$partes = explode(":", $row->refeicao_hora);
			$hora = $partes[0];
			$minuto = $partes[1];
			
			if($plano_alimentar_id == $row->plano_alimentar_id){
				if($refeicao_id == $row->refeicao_id){
					$plano_alimentar[$plano_alimentar_cont]["foodPlan"][$refeicao_cont]["content"][] = array("foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs);
				}else{
					$plano_alimentar[$plano_alimentar_cont]["foodPlan"][] = array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
						array(array("foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)));
					$refeicao_id = $row->refeicao_id;
					$refeicao_cont++;	
				}
			}else{
				$refeicao_cont = -1;
				$plano_alimentar[] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
					array(array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
						array(array("foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)))));
				$plano_alimentar_id = $row->plano_alimentar_id;
				$refeicao_id = $row->refeicao_id;
				$plano_alimentar_cont++;
				$refeicao_cont++;
				
			}
			echo $row->plano_alimentar_usuario . " - " . $row->plano_alimentar_id . " - " . $row->plano_alimentar_titulo . " - " . 
				 $row->refeicao_id . " - " . $row->refeicao_hora . " - " . $row->refeicao_descricao . " - " . 
				 $row->alimento_id . " - " . $row->alimento_nome . " - " . (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? "true" : "false"), "\n";
		}
	} else {
		$plano_alimentar[] = array("planId" => null, "title" => null, "foodPlan" => 
						array(array("mealId" => null, "hour" => null, "description" => null, "content" => 
							array(array("foodId" => null, "imgFood" => null, "ingredients" => array(), "modePrepare" => null, "obs" => null)))));
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
	$date_hour = date('Y-m-d H:i:s');

	echo "Cancelando consumo de refeicao\n";

	$sql = "UPDATE consumo_alimento SET cancelado = 1 where alimento = ? and refeicao = ? and plano_alimentar = ? and FORMAT(data, 'yyyy-MM-dd') = ? and cliente = ? and usuario = ?"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $alimento);
	$consulta->bindValue(2, $refeicao);
	$consulta->bindValue(3, $plano);
	$consulta->bindValue(4, $date_hour);
	$consulta->bindValue(5, $client);
	$consulta->bindValue(6, $usuario);
	$consulta->execute();


	if($consulta){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}

?>