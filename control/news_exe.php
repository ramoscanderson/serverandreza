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

function inserirNew($img, $data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$date = date("Y-m-d H-i-s");
	echo $date . "\n";
	$imagem = $img; //$data->img;
	echo $imagem . "\n";
	$avatar = "http://souzapapaleo.com.br/mailer/img/icone.png";
	echo $avatar . "\n";
	$conteudo = $data->content;
	echo $conteudo . "\n";
	echo $usuario . "\n";
	echo $client . "\n";
	$cancelado = 0;
	echo $cancelado . "\n";
	
	echo "Inserindo new\n";
	
	$sql = "INSERT INTO news (data_postagem, img, avatar, conteudo, usuario, cliente, cancelado) VALUES (?, ?, ?, ?, ?, ?, ?)"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $date);
	$consulta->bindValue(2, $imagem);
	$consulta->bindValue(3, $avatar);
	$consulta->bindValue(4, $conteudo);
	$consulta->bindValue(5, $usuario);
	$consulta->bindValue(6, $client);
	$consulta->bindValue(7, $cancelado);
	$consulta->execute();
	
	if($consulta->rowCount()){
		foreach ($data->categories as $categorie) {
			$sql = "INSERT INTO news_categorias (new, categoria, cancelado) VALUES ((select max(id) from news where cliente = ?), (select id from categorias where nome = ? and cliente = ?), ?)"; //FAZER CORREÇÃO PARA MAIS CLIENTES
			$consulta = $bd->prepare($sql);
			$consulta->bindValue(1, $client);
			$consulta->bindValue(2, $categorie);
			$consulta->bindValue(3, $client);
			$consulta->bindValue(4, $cancelado);
			$consulta->execute();
		}		
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

function inserirCategoria($data, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$categoria = $data->categorie;	

	echo "Inserindo registro de Categoria\n";

	$sql = "INSERT INTO categorias (nome, usuario, cliente) VALUES (?, ?, ?)"; //FAZER CORREÇÃO PARA MAIS CLIENTES
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