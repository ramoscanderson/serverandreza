<?php

function consultaUsuario($data, $client){
	require ("lib/bd.php");
	
	$id = $data->id;
	$cpf = $data->user;
	$senha = $data->password;
	
	if($id == ""){
		if($cpf == ""){
			return false;
		}
		if($senha == ""){
			return false;
		}
	}
	
	
	
	
	echo "CLIENTE:" . $client . "\n";

	$sql = "";
	$consulta;
	if(is_null($id)){
		echo "Verificando login e senha\n";
		$sql = "SELECT * FROM usuario WHERE cpf = ? and senha = ? and cliente = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
		$consulta = $bd->prepare($sql);
		$consulta->bindParam(1, $cpf);
		$consulta->bindParam(2, $senha);
		$consulta->bindParam(3, $client);
	}else{
		echo "Verificando informacoes do token\n";
		$sql = "SELECT * FROM usuario WHERE id = ? and cliente = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
		$consulta = $bd->prepare($sql);
		$consulta->bindParam(1, $id);
		$consulta->bindParam(2, $client);
	}
	
	$consulta->execute();
	
	$usuarios;
	while($row = $consulta->fetch(PDO::FETCH_OBJ)){
		$usuarios = array("id" => $row->id, "client" => $row->cliente,"name" => $row->nome, "cpf"=>$row->cpf, "email"=>$row->email, "phone"=>$row->telefone, "img"=>$row->img);
		echo $row->id . " - " . $row->cliente . " - " . $row->nome . " - " . $row->cpf . " - " . $row->email . " - " . $row->telefone . "\n";
		
    }
	
	if (is_array($usuarios)) {
	    return $usuarios;
	} else {
		echo "Nenhum registro encontrado\n";
		return false;
	}			
}

function consultaFCMUsuario($id, $client){
	require ("lib/bd.php");

	$sql = "SELECT * FROM usuario WHERE id = ? and cliente = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $id);
	$consulta->bindParam(2, $client);
	
	$consulta->execute();

	$usuarios;
	while($row = $consulta->fetch(PDO::FETCH_OBJ)){
		$usuarios = array("id" => $row->id, "fcm" => $row->fcm);
	}

	if (is_array($usuarios)) {
		return $usuarios;
	} else {
		echo "Nenhum registro encontrado\n";
		return false;
	}			
}

function carregarPacientes($client){ //FAZER C�DIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$ativo = "0"; // 0 = n�o cancelado
	$retorno_sim = 1;
	$retorno_nao = 0;

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
				usuario
			LEFT JOIN
				log
			ON
				usuario.id = log.usuario AND
				log.cliente = ?
			WHERE 
				usuario.cliente = ? 
			GROUP BY 
				usuario.id 
			ORDER BY 
				usuario.nome"; 

	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $retorno_nao);
	$consulta->bindParam(2, $retorno_sim);
	$consulta->bindParam(3, $client);
	$consulta->bindParam(4, $client);
	$consulta->execute();

	if ($consulta->rowCount() > 0) {
	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){
			$dados[] = array("avatar"=>$row->usuario_avatar, "birthdayUser"=>$row->usuario_data_nascimento, "userName"=>$row->usuario_nome, "cpfUser"=>$row->usuario_cpf, "emailUser"=>$row->usuario_email, "userPhone"=>$row->usuario_telefone, "userId"=>$row->usuario_id, "lastAttendance"=>$row->consultas_data, "lastAccess"=>$row->log_data, "totalAttendanceUser"=>$row->total_consumo, "totalReturnUser"=>$row->total_registro);
		}
	} else {
		//$dados[] = array("avatar"=>null, "birthdayUser"=>null, "userName"=>null, "cpfUser"=>null, "emailUser"=>null, "userPhone"=>null, "userId"=>null, "lastAttendance"=>null, "lastAccess"=>null, "totalAttendanceUser"=>null, "totalReturnUser"=>null);
		$dados[] = array();
		echo "Nenhum registro encontrado\n";
	}
	return $dados;	
}

function cadastraUsuario($data, $client, $admin, $atualizar){
	require ("lib/bd.php");
	
	$cliente = $client;
	$nome = $data->name;
	if(count(explode(" ", $nome)) == 1){
		return "-2";
	}
	$cpf = $data->cpf;
	if(strlen($cpf) < 11){
		return "-3";
	}
	$email = $data->email;
	if(count(explode("@", $email)) == 1){
		return "-4";
	}
	$telefone = $data->phone;
	if(strlen($telefone) < 14){
		return "-5";
	}
	$senha = $data->password;
	/*
	if($senha == ""){
		return "-6";
	}
	*/
	//$data_nascimento = $data->birthdayUser;
	
	$chave = mt_rand(1000,9999);
	$id_usuario_atualizado;
	
	if($atualizar){
		echo "Atualizando registro usuario:\n";
		
		$sql = "SELECT id FROM usuario WHERE cpf = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
		$consulta2 = $bd->prepare($sql);
		$consulta2->bindParam(1, $cpf);
		$consulta2->execute();
		
		$row = $consulta2->fetch(PDO::FETCH_OBJ);
		$id_usuario_atualizado = $row->id;
		
		$sql = "UPDATE usuario SET nome = ?, senha = ?, email = ?, telefone = ? WHERE cpf = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
		$consulta = $bd->prepare($sql);
		$consulta->bindParam(1, $nome);
		$consulta->bindParam(2, $senha);
		$consulta->bindParam(3, $email);
		$consulta->bindParam(4, $telefone);
		$consulta->bindParam(5, $cpf);
		$consulta->execute();
	}else{
		echo "Inserindo registro usuario:\n";
   
		$sql = "INSERT INTO usuario (cliente, nome, cpf, senha, email, telefone, chave, ativo, cadastro_por_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; //FAZER CORRE��O PARA MAIS CLIENTES
		$consulta = $bd->prepare($sql);
		$consulta->bindValue(1, $cliente);
		$consulta->bindValue(2, $nome);
		$consulta->bindValue(3, $cpf);
		$consulta->bindValue(4, $senha);
		$consulta->bindValue(5, $email);
		$consulta->bindValue(6, $telefone);
		$consulta->bindValue(7, $chave);
		$consulta->bindValue(8, false);
		$consulta->bindValue(9, $admin);
		//$consulta->bindValue(9, $data_nascimento);
		$consulta->execute();
	}
	
	if($consulta){
		if($admin){
			if(envia_email("Andreza Matteussi", $email, "Bem vindo!", "Seja bem vindo " . $nome . "\n\n" . "A Nutricionista Andreza Matteussi possui um aplicativo que possibilita o acompanhamento de seus planos alimetares diretamento do seu dispositivo m�vel. V� at� a loja do seu aparelho e baixe o aplicativo.\n\nEm caso de d�vidas � s� entrar em contato.\n\nTenha uma �tima semana!")){
				echo "E-mail de boas vindas enviado com sucesso\n";
				return $bd->lastInsertId();
			}else{
				return "-1";
			}
		}else{
			if(envia_email("Sistema de confirma��o", $email, "C�digo de confirma��o de cadastro", "Seja bem vindo " . $nome . "\n\n" . "Este � seu c�digo de verifica��o: " . $chave)){
				echo "E-mail de confirmacao enviado com sucesso\n";
				echo "ID UPDATE: " . $id_usuario_atualizado . " - " . $bd->lastInsertId() .  "\n";
				return $id_usuario_atualizado;
			}else{
				return "-1";
			}
		}
		
	}else{
		return "0";
		//return "failed"; //NA VERIFICA��O SE OS DADOS VIERAM CORRETOS, CASO N�O TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO N�O � TRUE E FALSE
	}			
}


function validaUsuario($data, $client, $user){ //FAZER C�DIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$chave = $data->cod;

	echo "Validando usuario: $id\n";

	$sql = "UPDATE usuario SET ativo = 1 WHERE chave = ? and id = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $chave);
	$consulta->bindParam(2, $user);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICA��O SE OS DADOS VIERAM CORRETOS, CASO N�O TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO N�O � TRUE E FALSE
	}else{
		return "failed";
	}
}


function validaCadastroUsuario($data, $client){
	require ("lib/bd.php");
	$cpf = $data->cpf;
	
	echo "Verificando usuario pelo cpf\n";
	$sql = "SELECT cadastro_por_admin, ativo FROM usuario WHERE cpf = ? and cliente = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cpf);
	$consulta->bindParam(2, $client);

	$consulta->execute();

	$encontrado = false;
	$atualizar = false;
	$ativo = false;
	while($row = $consulta->fetch(PDO::FETCH_OBJ)){
		echo "ENCONTRADO" . "\n";
		$encontrado = true;
		if($row->cadastro_por_admin){
			$atualizar = true;
		}
		if($row->ativo){
			$ativo = true;
		}
	}
	return array("encontrado"=>$encontrado, "atualizar"=>$atualizar, "ativo"=>$ativo);
}


// FUN��ES ESQUECI A SENHA

function consultaUsuarioCpf($data, $client){
	require ("lib/bd.php");
	$cpf = $data->cpf;

	echo "Verificando usuario pelo cpf\n";
	$sql = "SELECT * FROM usuario WHERE cpf = ? and cliente = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cpf);
	$consulta->bindParam(2, $client);
	
	$consulta->execute();

	$usuarios;
	while($row = $consulta->fetch(PDO::FETCH_OBJ)){
		$usuarios = array("id" => $row->id, "client" => $row->cliente,"name" => $row->nome, "cpf"=>$row->cpf, "email"=>$row->email, "phone"=>$row->telefone, "img"=>$row->img);
		echo $row->id . " - " . $row->cliente . " - " . $row->nome . " - " . $row->cpf . " - " . $row->email . " - " . $row->telefone . "\n";

	}

	if (is_array($usuarios)) {
		return $usuarios;
	} else {
		echo "Nenhum registro encontrado\n";
		return false;
	}			
}

function gerarNovaChave($client, $user){ //FAZER C�DIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$chave = mt_rand(1000,9999);
	$dados = consultaUsuario(json_decode(json_encode(array("id"=>$user))), $client);
	$email = $dados["email"];

	echo "Gerando nova chave de usuario\n";

	$sql = "UPDATE usuario SET chave = ? WHERE cliente = ? and id = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $chave);
	$consulta->bindParam(2, $client);
	$consulta->bindParam(3, $user);
	$consulta->execute();

	if($consulta->rowCount()){
		if(envia_email("Recuperação de senha", $email, "Código de recuperação de senha", "Olá " . $nome . "\n\n" . "Você solicitou a recuperação de sua senha em " . date('H:i:s d-m-Y') . "\n" . "Este é seu código: " . $chave . "\n" . "Informe ele no aplicativo para poder cadastrar uma nova senha.")){
			return array("success", $email); //NA VERIFICA��O SE OS DADOS VIERAM CORRETOS, CASO N�O TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO N�O � TRUE E FALSE
		}else{
			return array("failed");
		}
	}else{
		return array("failed");
	}
}

function gravarSenha($data, $client, $cpf){ //FAZER C�DIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$senha = $data->password;
	$chave = $data->cod;

	echo "Gravando nova senha de usuario\n";

	$sql = "UPDATE usuario SET senha = ? WHERE cliente = ? and cpf = ? and chave = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $senha);
	$consulta->bindParam(2, $client);
	$consulta->bindParam(3, $cpf);
	$consulta->bindParam(4, $chave);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICA��O SE OS DADOS VIERAM CORRETOS, CASO N�O TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO N�O � TRUE E FALSE
	}else{
		return "failed";
	}
}

function alterarDadosUsuario($data, $client, $usuario){ //FAZER C�DIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");

	$cpf = $data->cpf;
	$email = $data->email;
	$nome = $data->name;
	$telefone = $data->phone;
	$data_nascimento = $data->birthdayUser;
	if($data->id){
		$usuario = $data->id;
	}
	
	echo "Gravando novos dados de usuario\n";

	$sql = "UPDATE usuario SET cpf = ?, email = ?, nome = ?, telefone = ?, data_nascimento = ? WHERE id = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $cpf);
	$consulta->bindParam(2, $email);
	$consulta->bindParam(3, $nome);
	$consulta->bindParam(4, $telefone);
	$consulta->bindParam(5, $data_nascimento);
	$consulta->bindParam(6, $usuario);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICA��O SE OS DADOS VIERAM CORRETOS, CASO N�O TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO N�O � TRUE E FALSE
	}else{
		return "failed";
	}
}

function atualizaAvatar($data, $user, $client){ //FAZER C�DIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	require ("lib/adress.php");

	$path = $data;
	
	echo $data . "\n";
	echo $user . "\n";
	echo $client . "\n";
	
	echo "Procurando arquivo antigo para deletar\n";
	
	$sql = "SELECT * FROM usuario WHERE cliente = ? and id = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $client);
	$consulta->bindParam(2, $user);
	$consulta->execute();
	
	while($row = $consulta->fetch(PDO::FETCH_OBJ)){
		if($row->img != ""){
			$path_server = str_replace($server_outside, "", $row->img);
			if($path_server != "img/avatar/avatar.png"){
				if(unlink($path_server)){
					echo "Arquivo antigo deletado com sucesso\n";
				}else{
					echo "Erro ao deletar arquivo antigo\n";
					return "failed";
				}
			}
		}else{
			echo "Nenhum arquivo encontrado\n";
		}
	}
	
	echo "Atualizando imagem do usuario: $user\n";
	
	$sql = "UPDATE usuario SET img = ? WHERE cliente = ? and id = ?"; //FAZER CORRE��O PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $path);
	$consulta->bindParam(2, $client);
	$consulta->bindParam(3, $user);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; //NA VERIFICA��O SE OS DADOS VIERAM CORRETOS, CASO N�O TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO N�O � TRUE E FALSE
	}else{
		return "failed";
	}
}


function alterarFCM($data){
	require ("lib/bd.php");

	$userId = $data->userId;
	$fcm = $data->fcm;

	echo "Registrando FCM\n";
	
	//var_dump($data);

	$sql = "UPDATE usuario SET fcm = ? WHERE id = ?"; 
	$consulta = $bd->prepare($sql);
	$consulta->bindParam(1, $fcm);
	$consulta->bindParam(2, $userId);
	$consulta->execute();

	if($consulta->rowCount()){
		return "success"; 
	}else{
		return "failed";
	}
}

?>