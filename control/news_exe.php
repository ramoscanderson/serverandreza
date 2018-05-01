<?php

function cancelarNew($id){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$id = $data->id;
	
	echo "Cancelando registro: $id\n";

	$sql = ""; //FAZER CORREÇÃO PARA MAIS CLIENTES
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

function inserirNew($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
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

function carregarNew($client){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$ativo = "0"; // 0 = não cancelado
	
	$news = array();
	$categoria = array();
	
	$sql = "SELECT 
				news.id as new_id, news.data_postagem as new_data_postagem, news.avatar as new_avatar, news.conteudo as new_conteudo, news.img as new_img,
				categorias.id as categoria_id, categorias.nome as categoria_nome,
				usuario.id as usuario_id, usuario.nome as usuario_nome
			FROM 
				(news, news_categorias, categorias) INNER JOIN usuario 
			ON 
				news.id = news_categorias.new and
				categorias.id = news_categorias.categoria and
				news.usuario = usuario.id
			WHERE 
				news.cancelado = ? and
				news_categorias.cancelado = ? and
				categorias.cancelado = ? and 
				news.cliente = ? and
				usuario.cliente = ? 
			ORDER BY 
				news.data_postagem, 
				categorias.id"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $ativo);
	$consulta->bindParam(2, $ativo);
	$consulta->bindParam(3, $ativo);
	$consulta->bindParam(4, $client);
	$consulta->bindParam(5, $client);
	$consulta->execute();
	
	if ($consulta->rowCount() > 0) {
	   $ult_id;
	   $dados;
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
	   		if($ult_id == ""){
				$ult_id = $row->new_id;
			}
			if($ult_id == $row->new_id){
				$categoria[] = $row->categoria_nome;
				$dados = array("id" => $row->new_id, "date" => $row->new_data_postagem, "img"=>$row->new_img, "imgAvatar"=>$row->new_avatar, "content"=>$row->new_conteudo, "userName"=>$row->usuario_nome, "categories"=>$categoria);
			}else{
				$news[] = $dados;
				$categoria = array();
				$ult_id = $row->new_id;
				$categoria[] = $row->categoria_nome;
				$dados = array("id" => $row->new_id, "date" => $row->new_data_postagem, "imgAvatar"=>$row->new_avatar, "content"=>$row->new_conteudo, "userName"=>$row->usuario_nome, "categories"=>$categoria);
			}
		}
	} else {
		$news[] = array("id"=>null, "date"=>null, "imgAvatar"=>null, "content"=>null, "userName"=>null, "categories"=>null);
		echo "Nenhum registro encontrado\n";
	}
	return $news;	
}

function carregarCategoriasNew($client){
	require ("lib/bd.php");

	$ativo = "0"; // 0 = não cancelado
	$categorias = array();

	$sql = "SELECT * FROM categorias WHERE cancelado = ? and cliente = ? ORDER BY nome"; //FAZER CORREÇÃO PARA MAIS CLIENTES

	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $ativo);
	$consulta->bindParam(2, $client);
	$consulta->execute();

	if ($consulta->rowCount() > 0) {	   
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$categorias[] = array("id" => $row->id, "name" => $row->nome);
		}
	} else {
		$categorias[] = array("id"=>null, "name"=>null);
		echo "Nenhum registro encontrado\n";
	}
	return $categorias;
}

function carregarCategoriasNewUser($usuario, $client){
	require ("lib/bd.php");

	$ativo = "0"; // 0 = não cancelado
	$categorias = array();

	$sql = "SELECT * FROM usuario_categorias_cancelado WHERE usuario = ? and cliente = ?"; //FAZER CORREÇÃO PARA MAIS CLIENTES

	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $usuario);
	$consulta->bindParam(2, $client);
	$consulta->execute();

	if ($consulta->rowCount() > 0) {	   
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$categorias[] = array("id" => $row->id, "categoria" => $row->categoria, "usuario" => $row->usuario);
		}
	} else {
		$categorias[] = array("id"=>null, "name"=>null);
		echo "Nenhum registro encontrado\n";
	}
	return $categorias;
}


function inserirCategoriasNewUser($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$categoria = $data->id;	

	echo "Inserindo registro de CategoriasNewUser\n";

	$sql = "INSERT INTO usuario_categorias_cancelado (categoria, usuario, cliente) VALUES (?, ?, ?)"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $categoria);
	$consulta->bindValue(2, $usuario);
	$consulta->bindValue(3, $client);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}

function deletarCategoriasNewUser($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$categoria = $data->id;	

	echo "Deletando registro de CategoriasNewUser\n";

	$sql = "DELETE FROM usuario_categorias_cancelado WHERE usuario = ? and categoria = ? and cliente = ?";
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $usuario);
	$consulta->bindValue(2, $categoria);
	$consulta->bindValue(3, $client);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}


?>