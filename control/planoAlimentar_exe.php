<?php

function carregarPlanoAlimentar($client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	echo $usuario . "\n";
	echo $client . "\n";
	$ativo = "0"; // 0 = não cancelado
	
	$sql = "SELECT 
	receita.id as receita_id, receita.nome as alimento_nome, receita.imagem as alimento_imagem, receita.ingrediente as alimento_ingrediente, receita.modo_preparo as alimento_modo_preparo, 
	alimento.id as alimento_id, alimento.consumo as alimento_consumo, alimento.obs as alimento_obs,
	refeicao.id as refeicao_id, refeicao.hora as refeicao_hora, refeicao.descricao as refeicao_descricao,
	plano_alimentar.id as plano_alimentar_id, plano_alimentar.titulo as plano_alimentar_titulo, plano_alimentar.usuario as plano_alimentar_usuario
	FROM plano_alimentar, refeicao, alimento, receita WHERE 
	alimento.receita = receita.id and 
	alimento.refeicao = refeicao.id and 
	refeicao.plano_alimentar = plano_alimentar.id and
	alimento.cancelado = ? and 
	refeicao.cancelado = ? and 
	plano_alimentar.cancelado = ? and 
	plano_alimentar.cliente = ?  and 
	plano_alimentar.usuario = ? ORDER BY alimento.consumo"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $ativo);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $ativo);
	$consulta->bindParam(4, $client);
	$consulta->bindParam(5, $usuario);
	$consulta->execute();
	
	$plano_alimentar = array();
	$plano_alimentar_cont = -1;
	$refeicao_cont = -1;
	if ($consulta->rowCount() > 0) {
		$refeicao_id;
		$plano_alimentar_id;
		while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			if($refeicao_id == $row->refeicao_id){
				$plano_alimentar[$plano_alimentar_cont]["foodPlan"][$refeicao_cont]["content"][] = array("foodId" => $row->alimento_id, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "obs" => $row->alimento_obs);
			}else{
				if($plano_alimentar_id == $row->plano_alimentar_id){
   					$plano_alimentar[$plano_alimentar_cont]["foodPlan"][] = array("mealId" => $row->refeicao_id, "hour" => $row->refeicao_hora, "description" => $row->refeicao_descricao, "content" => 
						array(array("foodId" => $row->alimento_id, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "obs" => $row->alimento_obs)));
						
					$refeicao_id = $row->refeicao_id;
					$refeicao_cont++;
				}else{
   					$plano_alimentar[] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
						array(array("mealId" => $row->refeicao_id, "hour" => $row->refeicao_hora, "description" => $row->refeicao_descricao, "content" => 
							array(array("foodId" => $row->alimento_id, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "obs" => $row->alimento_obs)))));
					
					$plano_alimentar_id = $row->plano_alimentar_id;
					$refeicao_id = $row->refeicao_id;
					$plano_alimentar_cont++;
					$refeicao_cont++;
				}
			}
			
			echo $row->plano_alimentar_usuario . " - " . $row->plano_alimentar_id . " - " . $row->plano_alimentar_titulo . " - " . 
				 $row->refeicao_id . " - " . $row->refeicao_hora . " - " . $row->refeicao_descricao . " - " . 
				 $row->alimento_id . " - " . $row->alimento_nome . "\n";
		}
	} else {
		$plano_alimentar[] = array("planId" => null, "title" => null, "foodPlan" => 
						array(array("mealId" => null, "hour" => null, "description" => null, "content" => 
							array(array("foodId" => null, "imgFood" => null, "ingredients" => array(), "modePrepare" => null, "obs" => null)))));
		echo "Nenhum registro encontrado\n";
	}
	
	return $plano_alimentar;
	
}

?>