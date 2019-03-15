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
	$ativado = "1";
	
	$sql = "SELECT 
		ciclo.id as ciclo_id,
		ciclo.descricao as ciclo_descricao,
		ciclo.data_criacao as ciclo_data_criacao,
		ciclo.data_fechamento as ciclo_data_fechamento,
		ciclo.feedback as ciclo_feedback,
		ciclo.cancelado as ciclo_cancelado,
		ciclo.ativo as ciclo_ativo,
		ciclo.cliente as ciclo_cliente,
		ciclo.usuario as ciclo_usuario,
		alimento.receita as receita_id, 
		alimento.nome as alimento_nome, 
		alimento.imagem as alimento_imagem, 
		alimento.ingrediente as alimento_ingrediente, 
		alimento.modo_preparo as alimento_modo_preparo, 
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
		ciclo LEFT JOIN  
		 (plano_alimentar LEFT JOIN (refeicao 
	LEFT JOIN 
		((alimento) LEFT JOIN consumo_alimento
				ON
					consumo_alimento.alimento = alimento.id AND
					consumo_alimento.cancelado = ?)
		   ON
				refeicao.id = alimento.refeicao AND
				refeicao.cancelado = ? AND
				alimento.cancelado = ?)
	ON 
		plano_alimentar.id = refeicao.plano_alimentar AND
		plano_alimentar.cancelado = ? AND
		refeicao.cancelado = ? ) ON 
		 ciclo.id = plano_alimentar.ciclo AND
		 plano_alimentar.cancelado = ?

	WHERE 	
		ciclo.cancelado = ? and 
		ciclo.cliente = ?  and 
		ciclo.usuario = ?
	GROUP BY 
		alimento.id, refeicao.id, plano_alimentar.id, ciclo.id
	ORDER BY 
        ciclo.id, plano_alimentar.id, refeicao.hora, refeicao.id, quant";

	/*
	$sql = "SELECT 
		ciclo.id as ciclo_id,
		ciclo.descricao as ciclo_descricao,
		ciclo.data_criacao as ciclo_data_criacao,
		ciclo.data_fechamento as ciclo_data_fechamento,
		ciclo.feedback as ciclo_feedback,
		ciclo.cancelado as ciclo_cancelado,
		ciclo.ativo as ciclo_ativo,
		ciclo.cliente as ciclo_cliente,
		ciclo.usuario as ciclo_usuario,
		alimento.receita as receita_id, 
		alimento.nome as alimento_nome, 
		alimento.imagem as alimento_imagem, 
		alimento.ingrediente as alimento_ingrediente, 
		alimento.modo_preparo as alimento_modo_preparo, 
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
		(ciclo LEFT JOIN plano_alimentar 
		 ON 
		 ciclo.id = plano_alimentar.ciclo) LEFT JOIN (refeicao 
	LEFT JOIN 
		((alimento) LEFT JOIN consumo_alimento
				ON
					consumo_alimento.alimento = alimento.id AND
					consumo_alimento.cancelado = ?)
		   ON
				refeicao.id = alimento.refeicao AND
				refeicao.cancelado = ? AND
				alimento.cancelado = ?)
	ON 
		plano_alimentar.id = refeicao.plano_alimentar AND
		plano_alimentar.cancelado = ? AND
		refeicao.cancelado = ? 

	WHERE 	
		ciclo.cancelado = ? and 
		ciclo.cliente = ?  and 
		ciclo.usuario = ? and
		plano_alimentar.cancelado = ? and 
		plano_alimentar.cliente = ?  and 
		plano_alimentar.usuario = ? and
		ciclo.ativo = ?
	GROUP BY 
		alimento.id, refeicao.id, plano_alimentar.id
	ORDER BY 
		plano_alimentar.id, refeicao.hora, refeicao.id, quant"; //FAZER CORREÇÃO PARA MAIS CLIENTES  , alimento.consumo
	*/


	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $ativo);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $ativo);
	$consulta->bindParam(4, $ativo);
	$consulta->bindParam(5, $ativo);
	$consulta->bindParam(6, $ativo);
	$consulta->bindParam(7, $client);
	$consulta->bindParam(8, $usuario);
	$consulta->bindParam(9, $ativo);
	$consulta->bindParam(10, $client);
	$consulta->bindParam(11, $usuario);
	$consulta->bindParam(12, $ativado);
	$consulta->execute();
	
	$plano_alimentar = array();
	$ciclo_cont = 0;
	$plano_alimentar_cont = -1;
	$refeicao_cont = -1;
	if ($consulta->rowCount() > 0) {
		$refeicao_id;
		$plano_alimentar_id;
		$ciclo_id = "";
		//echo "\n\n\n";
		while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			
			
			
			

			/*
			//ALTERAR PARA VERIFICAR SE PLANO EXISTE, CASO SIM, CONTINUA IGUAL ABAIXO COM ALTERAÇÕES PARA CICLO, CASO NÃO ADICIONA APENAS O CICLO
			if(is_null($row->refeicao_id)){
				//echo "isnull\n";
				$refeicao_cont = -1;
				$plano_alimentar[] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
					array(array("mealId" => null, "hour" => null, "description" => null, "content" => 
						array(array("idRecipe" => null, "foodId" => null, "foodName" => null, "imgFood" => null, "ingredients" => array(), "modePrepare" => null, "consumption" => false, "obs" => null)))));
				$plano_alimentar_id = $row->plano_alimentar_id;
				$refeicao_id = "";
				$plano_alimentar_cont++;
				$refeicao_cont++;
			}else{
				//echo "else isnull\n";
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
			*/


			
			//ALTERAR PARA VERIFICAR SE PLANO EXISTE, CASO SIM, CONTINUA IGUAL ABAIXO COM ALTERAÇÕES PARA CICLO, CASO NÃO ADICIONA APENAS O CICLO
			if(is_null($row->refeicao_id)){
				//echo "isnull\n";
				$refeicao_cont = -1;
				$plano_alimentar[] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
					array());
				$plano_alimentar_id = $row->plano_alimentar_id;
				$refeicao_id = "";
				$plano_alimentar_cont++;
				$refeicao_cont++;
			}else{
				//echo "else isnull\n";
				$partes = explode(":", $row->refeicao_hora);
				$hora = $partes[0];
				$minuto = $partes[1];
   
				if($plano_alimentar_id == $row->plano_alimentar_id){
					if($refeicao_id == $row->refeicao_id){
						if(is_null($row->receita_id)){
							$plano_alimentar[$plano_alimentar_cont]["foodPlan"][$refeicao_cont]["content"][] = array();
						}else{
							$plano_alimentar[$plano_alimentar_cont]["foodPlan"][$refeicao_cont]["content"][] = array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs);							
						}
					}else{
						if(is_null($row->receita_id)){
							$plano_alimentar[$plano_alimentar_cont]["foodPlan"][] = array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => array());
						}else{
							$plano_alimentar[$plano_alimentar_cont]["foodPlan"][] = array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
								array(array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)));
						}
						$refeicao_id = $row->refeicao_id;
						$refeicao_cont++;	
					}
				}else{
					$refeicao_cont = -1;
					if(is_null($row->receita_id)){
						$plano_alimentar[] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
							array(array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => array())));
					}else{
						$plano_alimentar[] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
							array(array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
								array(array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)))));
					}					
					$plano_alimentar_id = $row->plano_alimentar_id;
					$refeicao_id = $row->refeicao_id;
					$plano_alimentar_cont++;
					$refeicao_cont++;
				}
			}
			


			echo $row->plano_alimentar_usuario . " - " . $row->plano_alimentar_id . " - " . $row->plano_alimentar_titulo . " - " . 
				 $row->refeicao_id . " - " . $row->refeicao_hora . " - " . $row->refeicao_descricao . " - " . 
				 $row->alimento_id . " - " . $row->alimento_nome . " - " . (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? "true" : "false"), "\n";
			
			//print_r($plano_alimentar);
			//echo "---------------------------------------------------------------------------------------\n";
		}
				
	} else {
		/*
		$plano_alimentar[] = array("planId" => null, "title" => null, "foodPlan" => 
						array(array("mealId" => null, "hour" => null, "description" => null, "content" => 
							array(array("idRecipe" => $row->receita_id, "foodId" => null, "imgFood" => null, "ingredients" => array(), "modePrepare" => null, "obs" => null)))));
		*/
		$plano_alimentar = array();
		echo "Nenhum registro encontrado\n";
	}
	
	return $plano_alimentar;
	
}

function carregarCicloAlimentar($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
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
		ciclo.id as ciclo_id,
		ciclo.descricao as ciclo_descricao,
		ciclo.data_criacao as ciclo_data_criacao,
		ciclo.data_fechamento as ciclo_data_fechamento,
		ciclo.feedback as ciclo_feedback,
		ciclo.cancelado as ciclo_cancelado,
		ciclo.ativo as ciclo_ativo,
		ciclo.cliente as ciclo_cliente,
		ciclo.usuario as ciclo_usuario,
		alimento.receita as receita_id, 
		alimento.nome as alimento_nome, 
		alimento.imagem as alimento_imagem, 
		alimento.ingrediente as alimento_ingrediente, 
		alimento.modo_preparo as alimento_modo_preparo, 
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
		ciclo LEFT JOIN  
		 (plano_alimentar LEFT JOIN (refeicao 
	LEFT JOIN 
		((alimento) LEFT JOIN consumo_alimento
				ON
					consumo_alimento.alimento = alimento.id AND
					consumo_alimento.cancelado = ?)
		   ON
				refeicao.id = alimento.refeicao AND
				refeicao.cancelado = ? AND
				alimento.cancelado = ?)
	ON 
		plano_alimentar.id = refeicao.plano_alimentar AND
		plano_alimentar.cancelado = ? AND
		refeicao.cancelado = ? ) ON 
		 ciclo.id = plano_alimentar.ciclo AND
		 plano_alimentar.cancelado = ?

	WHERE 	
		ciclo.cancelado = ? and 
		ciclo.cliente = ?  and 
		ciclo.usuario = ?
	GROUP BY 
		alimento.id, refeicao.id, plano_alimentar.id, ciclo.id
	ORDER BY 
        ciclo.id, plano_alimentar.id, refeicao.hora, refeicao.id, quant"; //FAZER CORREÇÃO PARA MAIS CLIENTES  , alimento.consumo
	
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $ativo);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $ativo);
	$consulta->bindParam(4, $ativo);
	$consulta->bindParam(5, $ativo);
	$consulta->bindParam(6, $ativo);
	$consulta->bindParam(7, $ativo);
	$consulta->bindParam(8, $client);
	$consulta->bindParam(9, $usuario);
	$consulta->execute();
	
	$plano_alimentar = array();
	$ciclo_cont = 0;
	$plano_alimentar_cont = -1;
	$refeicao_cont = -1;
	if ($consulta->rowCount() > 0) {
		$refeicao_id;
		$plano_alimentar_id;
		$ciclo_id = "";
		//echo "\n\n\n";
		while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			
			
			if(is_null($row->plano_alimentar_id)){
				// echo "PLANO ALIMENTAR isnull\n";
				$refeicao_cont = -1;
				$plano_alimentar[] = array("cycleId" => $row->ciclo_id, "description" => $row->ciclo_descricao, "dateCreate" => $row->ciclo_data_criacao, "dateEnd" => $row->ciclo_data_fechamento, "feedback" => $row->ciclo_feedback, "activate" => ($row->ciclo_ativo ? true : false), "plans" => array());
				$ciclo_cont++;
				$ciclo_id = $row->ciclo_id;
				// echo "ADICIONA INTEIRO - CICLO - " . $ciclo_cont . " - " . $plano_alimentar_id . " - " . $refeicao_id . " - " . $plano_alimentar_cont . " - " . $refeicao_cont . "\n";
			}else{
				// echo "ELSE - PLANO ALIMENTAR isnull\n";
				if($ciclo_id == ""){
					// echo "CICLO = vazio\n";
					$ciclo_id = $row->ciclo_id;
					if(is_null($row->refeicao_id)){
						// echo "REFEICAO isnull\n";
						$plano_alimentar[] = array("cycleId" => $row->ciclo_id, "description" => $row->ciclo_descricao, "dateCreate" => $row->ciclo_data_criacao, "dateEnd" => $row->ciclo_data_fechamento, "feedback" => $row->ciclo_feedback, "activate" => ($row->ciclo_ativo ? true : false), "plans" =>
								array(array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => array(array()))));
						$plano_alimentar_id = $row->plano_alimentar_id;
						$refeicao_cont = -1;
						$refeicao_id = "";
						$plano_alimentar_cont++;
						// echo "ADICIONA INTEIRO - CICLO - " . $ciclo_cont . " - " . $plano_alimentar_id . " - " . $refeicao_id . " - " . $plano_alimentar_cont . " - " . $refeicao_cont . "\n";
					}else{
						// echo "ELSE - REFEICAO isnull\n";
						$partes = explode(":", $row->refeicao_hora);
						$hora = $partes[0];
						$minuto = $partes[1];
   
   						$plano_alimentar[] = array("cycleId" => $row->ciclo_id, "description" => $row->ciclo_descricao, "dateCreate" => $row->ciclo_data_criacao, "dateEnd" => $row->ciclo_data_fechamento, "feedback" => $row->ciclo_feedback, "activate" => ($row->ciclo_ativo ? true : false), "plans" =>
								array(array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
								array(array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
								array(array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)))))));
						$plano_alimentar_id = $row->plano_alimentar_id;
						$refeicao_id = $row->refeicao_id;
						$plano_alimentar_cont++;
						$refeicao_cont++;
						// echo "ADICIONA INTEIRO - CICLO - " . $ciclo_cont . " - " . $plano_alimentar_id . " - " . $refeicao_id . " - " . $plano_alimentar_cont . " - " . $refeicao_cont . "\n";
						//var_dump($plano_alimentar);
					}					
				}else{
					// echo "ELSE - CICLO = vazio\n";
					if($ciclo_id == $row->ciclo_id){
						// echo "CICLO ID = CICLO ID\n";
						if(is_null($row->refeicao_id)){
							// echo "REFEICAO isnull\n";
							$plano_alimentar[$ciclo_cont]["plans"][] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => array());
							$plano_alimentar_id = $row->plano_alimentar_id;
							$refeicao_cont = -1;
							$refeicao_id = "";
							$plano_alimentar_cont++;
							// echo "ADICIONA PLAN - " . $ciclo_cont . " - " . $plano_alimentar_id . " - " . $refeicao_id . " - " . $plano_alimentar_cont . " - " . $refeicao_cont . "\n";
						}else{
							// echo "ELSE - REFEICAO isnull\n";
							$partes = explode(":", $row->refeicao_hora);
							$hora = $partes[0];
							$minuto = $partes[1];
   
							if($plano_alimentar_id == $row->plano_alimentar_id){
								// echo "PLANO ID = PLANO ID\n";
								if($refeicao_id == $row->refeicao_id){
									// echo "REFEICAO ID = REFEICAO ID\n";
									if(is_null($row->receita_id)){
										$plano_alimentar[$ciclo_cont]["plans"][$plano_alimentar_cont]["foodPlan"][$refeicao_cont]["content"][] = array();
									}else{
										$plano_alimentar[$ciclo_cont]["plans"][$plano_alimentar_cont]["foodPlan"][] = array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
											array(array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)));
									}
									$refeicao_id = $row->refeicao_id;
									$refeicao_cont++;
									// echo "ADICIONA REFEICAO - " . $ciclo_cont . " - " . $plano_alimentar_id . " - " . $refeicao_id . " - " . $plano_alimentar_cont . " - " . $refeicao_cont . "\n";	
								}							
							}else{
								// echo "1ELSE - PLANO ID = PLANO ID\n";
								$refeicao_cont = -1;
								if(is_null($row->receita_id)){
									$plano_alimentar[$ciclo_cont]["plans"][] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
										array(array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
											array())));
								}else{
									$plano_alimentar[$ciclo_cont]["plans"][] = array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
										array(array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
											array(array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)))));
								}
								$plano_alimentar_id = $row->plano_alimentar_id;
								$refeicao_id = $row->refeicao_id;
								$plano_alimentar_cont++;
								$refeicao_cont++;
								// echo "ADICIONA PLANO - " . $ciclo_cont . " - " . $plano_alimentar_id . " - " . $refeicao_id . " - " . $plano_alimentar_cont . " - " . $refeicao_cont . "\n";
							}
						}
					}else{
						// echo "ELSE - CICLO ID = CICLO ID\n";
						if(is_null($row->refeicao_id)){
							// echo "REFEICAO isnull\n";
							$refeicao_cont = -1;
							$plano_alimentar[] = array("cycleId" => $row->ciclo_id, "description" => $row->ciclo_descricao, "dateCreate" => $row->ciclo_data_criacao, "dateEnd" => $row->ciclo_data_fechamento, "feedback" => $row->ciclo_feedback, "activate" => (($row->ciclo_ativo)?true:false), "plans" =>
									array(array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => array())));
							$plano_alimentar_id = $row->plano_alimentar_id;
							$refeicao_id = "";
							$plano_alimentar_cont++;
							$refeicao_cont++;
							$ciclo_cont++;
							$ciclo_id = $row->ciclo_id;
							// echo "ADICIONA INTEIRO - CICLO - " . $ciclo_cont . " - " . $plano_alimentar_id . " - " . $refeicao_id . " - " . $plano_alimentar_cont . " - " . $refeicao_cont . "\n";
						}else{
							// echo "ELSE - REFEICAO isnull\n";
							$partes = explode(":", $row->refeicao_hora);
							$hora = $partes[0];
							$minuto = $partes[1];
   							
							$refeicao_cont = -1;
							if(is_null($row->receita_id)){
								$plano_alimentar[] = array("cycleId" => $row->ciclo_id, "description" => $row->ciclo_descricao, "dateCreate" => $row->ciclo_data_criacao, "dateEnd" => $row->ciclo_data_fechamento, "feedback" => $row->ciclo_feedback, "activate" => (($row->ciclo_ativo)?true:false), "plans" =>
								array(array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
									array(array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
										array())))));
							}else{
								$plano_alimentar[] = array("cycleId" => $row->ciclo_id, "description" => $row->ciclo_descricao, "dateCreate" => $row->ciclo_data_criacao, "dateEnd" => $row->ciclo_data_fechamento, "feedback" => $row->ciclo_feedback, "activate" => (($row->ciclo_ativo)?true:false), "plans" =>
								array(array("planId" => $row->plano_alimentar_id, "title" => $row->plano_alimentar_titulo, "foodPlan" => 
									array(array("mealId" => $row->refeicao_id, "hour" => $hora . "h" . $minuto, "description" => $row->refeicao_descricao, "content" => 
										array(array("idRecipe" => $row->receita_id, "foodId" => $row->alimento_id, "foodName" => $row->alimento_nome, "imgFood" => $row->alimento_imagem, "ingredients" => explode("<br>", $row->alimento_ingrediente), "modePrepare" => $row->alimento_modo_preparo, "consumption" => (date('Y-m-d') == explode(" ", $row->ultimo_consumo)[0] ? true : false), "obs" => $row->alimento_obs)))))));
							}
							$plano_alimentar_id = $row->plano_alimentar_id;
							$refeicao_id = $row->refeicao_id;
							$plano_alimentar_cont++;
							$refeicao_cont++;
							// echo "ADICIONA INTEIRO - CICLO - " . $ciclo_cont . " - " . $plano_alimentar_id . " - " . $refeicao_id . " - " . $plano_alimentar_cont . " - " . $refeicao_cont . "\n";
							
						}
					}
				}				
			}
			
			//print_r($plano_alimentar);
			//echo "---------------------------------------------------------------------------------------\n";
		}
				
	} else {
		/*
		$plano_alimentar[] = array("planId" => null, "title" => null, "foodPlan" => 
						array(array("mealId" => null, "hour" => null, "description" => null, "content" => 
							array(array("idRecipe" => $row->receita_id, "foodId" => null, "imgFood" => null, "ingredients" => array(), "modePrepare" => null, "obs" => null)))));
		*/
		$plano_alimentar = array();
		echo "Nenhum registro encontrado\n";
	}
	
	return $plano_alimentar;
	
}


function ativarCiclo($data){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$cicloId = $data->cycleId;
	$usuario = $data->userId;
	$desativo = 0;
	$ativo = 1;

	$sql = "UPDATE ciclo SET ativo = ? where usuario = ?"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $desativo);
	$consulta->bindValue(2, $usuario);
	$consulta->execute();

	$sql1 = "UPDATE ciclo SET ativo = ? where id = ?"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta1 = $bd->prepare($sql1);
	$consulta1->bindValue(1, $ativo);
	$consulta1->bindValue(2, $cicloId);
	$consulta1->execute();

	if($consulta->rowCount() && $consulta1->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function deleteCicloAlimentar($data){
	require ("lib/bd.php");

	$ciclo = $data->cycleId;
	$cancelado = 1;

	echo "Deletando ciclo alimentar\n";

	$sql = "UPDATE ciclo SET cancelado = ? WHERE id = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cancelado);
	$consulta->bindParam(2, $ciclo);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
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

	$plano = $data->foodPlan->planId;
	echo "PLANO:" . $plano . "\n";
	$paciente = $data->idUser;
	$nome = $data->foodPlan->title;
	$ciclo = $data->foodPlan->cycleId;
	echo "NOME:" . $nome . "\n";
	$date = date('Y-m-d');
	$cancelado = 0;
	$consulta;
	
	if($plano){
		echo "Alterando plano alimentar\n";	
		
		$sql = "UPDATE plano_alimentar SET titulo = ? WHERE id = ?"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindParam(1, $nome);
		$consulta->bindParam(2, $plano);
		$consulta->execute();
	}else{
		echo "Inserindo plano alimentar\n";
   
		$sql = "INSERT INTO plano_alimentar (data_criacao, titulo, ciclo, usuario, cliente, cancelado) VALUES (?, ?, ?, ?, ?, ?)"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindValue(1, $date);
		$consulta->bindValue(2, $nome);
		$consulta->bindValue(3, $ciclo);
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


function deletePlanoAlimentar($data){
	require ("lib/bd.php");

	$plano = $data->foodPlan->planId;
	$cancelado = 1;

	echo "Deletando plano alimentar\n";

	$sql = "UPDATE plano_alimentar SET cancelado = ? WHERE id = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cancelado);
	$consulta->bindParam(2, $plano);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}

function addAlimentoUsuario($img, $data, $client, $usuario){
	require ("lib/bd.php");
	
	//print_r($data);
	
	$refeicao = $data->mealId;
	$receita = "0";
	$ingredientes = implode("<br>", $data->ingredients);
	$modo_preparo = $data->prepareMode;
	$imagem = $img;
	$descricao = $data->description;
	$obs = $data->obs;
	$cancelado = 0;
	$consumo = 0;

	$sql = "INSERT INTO alimento (receita, refeicao, consumo, obs, cancelado, nome, imagem, ingrediente, modo_preparo, cliente) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $receita);
	$consulta->bindValue(2, $refeicao);
	$consulta->bindValue(3, $consumo);
	$consulta->bindValue(4, $obs);
	$consulta->bindValue(5, $cancelado);
	$consulta->bindValue(6, $descricao);
	$consulta->bindValue(7, $imagem);
	$consulta->bindValue(8, $ingredientes);
	$consulta->bindValue(9, $modo_preparo);
	$consulta->bindValue(10, $client);
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
			echo "Carregando informacoes da receita\n";

			$sql = "SELECT 
						nome, imagem, ingrediente, modo_preparo, cliente
					FROM 
						receita
					WHERE 
						id = ?"; 
			$consulta = $bd->prepare($sql);
			$consulta->bindParam(1, $receita->idRecipe);
			$consulta->execute();
   
   			$receita_nome = "";   
			$receita_imagem = "";
			$receita_ingrediente = "";
			$receita_modo_preparo = "";
			$receita_cliente = "";

			if($consulta->rowCount()){
				$row = $consulta->fetch(PDO::FETCH_OBJ);
				$receita_nome = $row->nome;   
				$receita_imagem = $row->imagem;
				$receita_ingrediente = $row->ingrediente;
				$receita_modo_preparo = $row->modo_preparo;
				$receita_cliente = $row->cliente;
			}
			
			
			$sql = "INSERT INTO alimento (receita, refeicao, consumo, obs, cancelado, nome, imagem, ingrediente, modo_preparo, cliente) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
			$consultaReceita = $bd->prepare($sql);
			$consultaReceita->bindValue(1, $receita->idRecipe);
			$consultaReceita->bindValue(2, $refeicao);
			$consultaReceita->bindValue(3, $consumo);
			$consultaReceita->bindValue(4, $receita->obs);
			$consultaReceita->bindValue(5, $cancelado);
			$consultaReceita->bindValue(6, $receita_nome);
			$consultaReceita->bindValue(7, $receita_imagem);
			$consultaReceita->bindValue(8, $receita_ingrediente);
			$consultaReceita->bindValue(9, $receita_modo_preparo);
			$consultaReceita->bindValue(10, $receita_cliente);
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
			echo "Carregando informacoes da receita\n";

			$sql = "SELECT 
						nome, imagem, ingrediente, modo_preparo, cliente
					FROM 
						receita
					WHERE 
						id = ?"; 
			$consulta = $bd->prepare($sql);
			$consulta->bindParam(1, $receita->idRecipe);
			$consulta->execute();

			$receita_nome = "";   
			$receita_imagem = "";
			$receita_ingrediente = "";
			$receita_modo_preparo = "";
			$receita_cliente = "";

			if($consulta->rowCount()){
				$row = $consulta->fetch(PDO::FETCH_OBJ);
				$receita_nome = $row->nome;   
				$receita_imagem = $row->imagem;
				$receita_ingrediente = $row->ingrediente;
				$receita_modo_preparo = $row->modo_preparo;
				$receita_cliente = $row->cliente;
			}

			$sql = "INSERT INTO alimento (receita, refeicao, consumo, obs, cancelado, nome, imagem, ingrediente, modo_preparo, cliente) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
			$consultaReceita = $bd->prepare($sql);
			$consultaReceita->bindValue(1, $receita->foodId);
			$consultaReceita->bindValue(2, $lastId);
			$consultaReceita->bindValue(3, $consumo);
			$consultaReceita->bindValue(4, $receita->obs);
			$consultaReceita->bindValue(5, $cancelado);
			$consultaReceita->bindValue(6, $receita_nome);
			$consultaReceita->bindValue(7, $receita_imagem);
			$consultaReceita->bindValue(8, $receita_ingrediente);
			$consultaReceita->bindValue(9, $receita_modo_preparo);
			$consultaReceita->bindValue(10, $receita_cliente);
			$consultaReceita->execute();
		}
	}
	
	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function deleteRefeicao($data){
	require ("lib/bd.php");

	$refeicao = $data->meal->mealId;
	$cancelado = 1;
	
	echo "Deletando refeicao\n";

	$sql = "UPDATE refeicao SET cancelado = ? WHERE id = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cancelado);
	$consulta->bindParam(2, $refeicao);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function addReceita($img, $data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$receita = $data->idRecipe;
	$alimento = $data->foodId;
	$ingredientes = implode("<br>", $data->ingredients);
	$modo_preparo = $data->prepareMode;
	$imagem = $img;
	$descricao = $data->description;
	$cancelado = 0;
	$flag = $data->flagImage;
	
	if($alimento){
		echo "Alterando receita do paciente: \n";

		if($flag){
			$sql = "UPDATE alimento SET nome = ?, imagem = ?, ingrediente = ?, modo_preparo = ?, obs = ? WHERE id = ?"; 
			$consulta = $bd->prepare($sql);
			$consulta->bindParam(1, $descricao);
			$consulta->bindParam(2, $imagem);
			$consulta->bindParam(3, $ingredientes);
			$consulta->bindParam(4, $modo_preparo);
			$consulta->bindParam(5, $data->obs);
			$consulta->bindParam(6, $alimento);
			$consulta->execute();
		}else{
			$sql = "UPDATE alimento SET nome = ?, ingrediente = ?, modo_preparo = ?, obs = ? WHERE id = ?"; 
			$consulta = $bd->prepare($sql);
			$consulta->bindParam(1, $descricao);
			$consulta->bindParam(2, $ingredientes);
			$consulta->bindParam(3, $modo_preparo);
			$consulta->bindParam(4, $data->obs);
			$consulta->bindParam(5, $alimento);
			$consulta->execute();
		}
	}else{
		if($receita){
			echo "Alterando receita: \n";
   			
			if($flag){
				$sql = "UPDATE receita SET nome = ?, imagem = ?, ingrediente = ?, modo_preparo = ? WHERE id = ?"; 
				$consulta = $bd->prepare($sql);
				$consulta->bindParam(1, $descricao);
				$consulta->bindParam(2, $imagem);
				$consulta->bindParam(3, $ingredientes);
				$consulta->bindParam(4, $modo_preparo);
				$consulta->bindParam(5, $receita);
				$consulta->execute();
			}else{
				$sql = "UPDATE receita SET nome = ?, ingrediente = ?, modo_preparo = ? WHERE id = ?"; 
				$consulta = $bd->prepare($sql);
				$consulta->bindParam(1, $descricao);
				$consulta->bindParam(2, $ingredientes);
				$consulta->bindParam(3, $modo_preparo);
				$consulta->bindParam(4, $receita);
				$consulta->execute();
			}
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
	}
		
	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function deleteReceita($data){
	require ("lib/bd.php");

	$receita = $data->recipe->foodId;
	$cancelado = 1;

	echo "Deletando receita\n";

	$sql = "UPDATE receita SET cancelado = ? WHERE id = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cancelado);
	$consulta->bindParam(2, $receita);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


function deleteAlimento($data){
	require ("lib/bd.php");

	$alimento = $data->recipe->foodId;
	$cancelado = 1;

	echo "Deletando alimento do usuario\n";

	$sql = "UPDATE alimento SET cancelado = ? WHERE id = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cancelado);
	$consulta->bindParam(2, $alimento);
	$consulta->execute();

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
		//$receitas[] = array("idRecipe" => null, "nameRecipe" => null, "imageRecipe" => null, "ingredientsRecipe" => null, "prepareModeRecipe" => null);
		$receitas[] = array();
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


function duplicarPlano($data, $client){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$plano = $data->idPlanImport;
	$usuario = $data->userId;
	$ciclo = $data->cycleId;
	$date = date('Y-m-d');
	$cancelado = 0;
		
	echo "USUARIO: " . $usuario . " - PLANO: " . $plano . "\n";

	echo "Duplicando plano\n";

	$sql = "INSERT INTO plano_alimentar (data_criacao, titulo, ciclo, usuario, cliente, cancelado) (SELECT ? as data_criacao, titulo, ? as ciclo, ? as usuario, cliente, ? as cancelado FROM plano_alimentar WHERE id = ?)"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $date);
	$consulta->bindValue(2, $ciclo);
	$consulta->bindValue(3, $usuario);
	$consulta->bindValue(4, $cancelado);
	$consulta->bindValue(5, $plano);
	$consulta->execute();

	$plano_inserido = $bd->lastInsertId();
	
	echo "Duplicando refeicoes\n";

		$sql = "SELECT id, plano_alimentar, hora, descricao, cancelado FROM refeicao WHERE plano_alimentar = ?"; 
		$consulta3 = $bd->prepare($sql);
		$consulta3->bindParam(1, $plano);
		$consulta3->execute();
   
		while($row = $consulta3->fetch(PDO::FETCH_OBJ)){
			echo $plano_inserido . " - " . $row->descricao . "\n";
			$sql = "INSERT INTO refeicao (plano_alimentar, hora, descricao, cancelado) VALUES (?, ?, ?, ?)"; 
			$consulta1 = $bd->prepare($sql);
			$consulta1->bindValue(1, $plano_inserido);
			$consulta1->bindValue(2, $row->hora);
			$consulta1->bindValue(3, $row->descricao);
			$consulta1->bindValue(4, $row->cancelado);
			$consulta1->execute();

			echo "Duplicando alimentos\n";

			$sql = "INSERT INTO alimento (receita, nome, imagem, ingrediente, modo_preparo, cliente, refeicao, consumo, obs, cancelado) 
			(SELECT receita, nome, imagem, ingrediente, modo_preparo, cliente, ? as refeicao, ? as consumo, obs, cancelado FROM alimento WHERE refeicao = ?)"; 
			$consulta2 = $bd->prepare($sql);
			$consulta2->bindValue(1, $bd->lastInsertId());
			$consulta2->bindValue(2, "0");
			$consulta2->bindValue(3, $row->id);
			$consulta2->execute();				
		}		

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}			
}


function addCiclo($data, $client){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	// PEGAR O CICLO A SER COPIADO E ISERIR OUTRO CICLO COPIANDO TODOS OS PLANOS PARA ESSE CICLO INSERIDO
	// CASO PASSE O ID DO CICLO ANTIGO "0", ENTÃO É APENAS A CRIAÇÃO DE UM CICLO NOVO
	$ciclo = $data->cycleId;
	$impotado = $data->cycleImportId;
	$date = date('Y-m-d H:i:s');
	$descricao = $data->description;
	$feedback = $data->feedback;
	$cancelado = 0;
	$ativo = 0;
	$usuario = $data->userId;

	print_r($data);


	if($ciclo){
		$sql = "UPDATE ciclo SET descricao = ?, feedback = ? WHERE id = ?"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindParam(1, $descricao);
		$consulta->bindParam(2, $feedback);
		$consulta->bindParam(3, $ciclo);
		$consulta->execute();

		if($consulta->rowCount()){
			return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
		}else{
			return "failed";
		}
	}else{
		$sql = "INSERT INTO ciclo (descricao, data_criacao, feedback, cancelado, cliente, usuario, ativo) VALUES (?,?,?,?,?,?,?)"; 
		$consulta = $bd->prepare($sql);
		$consulta->bindValue(1, $descricao);
		$consulta->bindValue(2, $date);
		$consulta->bindValue(3, $feedback);
		$consulta->bindValue(4, $cancelado);
		$consulta->bindValue(5, $client);
		$consulta->bindValue(6, $usuario);
		$consulta->bindValue(7, $ativo);
		$consulta->execute();
	
		$ciclo_inserido = $bd->lastInsertId();
	
		if($impotado){
            echo "importando ciclo\n";
            $sql = "SELECT id, usuario FROM plano_alimentar WHERE ciclo = ? AND cancelado = ?"; 
			$consulta1 = $bd->prepare($sql);
			// $consulta->bindParam(1, $ciclo);
			$consulta1->bindParam(1, $impotado);
			$consulta1->bindParam(2, $cancelado);
			$consulta1->execute();
	
			while($row = $consulta1->fetch(PDO::FETCH_OBJ)){
				//$dados = (object)array("idPlanImport"=>$row->id, "userId"=>$row->usuario, "cycleId"=>$ciclo_inserido);
                echo "importando planos\n";
                $dados = (object)array("idPlanImport"=>$row->id, "userId"=>$usuario, "cycleId"=>$ciclo_inserido);
				duplicarPlano($dados, $client);
			}		
		}
		return "success";
	}

}

?>