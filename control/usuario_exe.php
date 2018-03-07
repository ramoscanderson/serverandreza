<?phpfunction consultaUsuario($data){	require ("lib/bd.php");		$id = $data->id;	$cpf = $data->user;	$senha = $data->password;	echo "Consultando registro usuario: $id\n";	$sql = "";	$consulta;	if(is_null($id)){		echo "Verificando login e senha\n";		$sql = "SELECT * FROM usuario WHERE cpf = ? and senha = ?"; //FAZER CORRE��O PARA MAIS CLIENTES		$consulta = $bd->prepare($sql);		$consulta->bindParam(1, $cpf);		$consulta->bindParam(2, $senha);	}else{		echo "Verificando informacoes do token\n";		$sql = "SELECT * FROM usuario WHERE id = ?"; //FAZER CORRE��O PARA MAIS CLIENTES		$consulta = $bd->prepare($sql);		$consulta->bindParam(1, $id);	}		$consulta->execute();	if ($consulta->rowCount() > 0) {	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){			$usuarios = array("id" => $row->id, "client" => $row->cliente,"name" => $row->nome, "cpf"=>$row->cpf, "email"=>$row->email, "phone"=>$row->telefone);			echo $row->id . " - " . $row->cliente . " - " . $row->nome . " - " . $row->cpf . " - " . $row->email . " - " . $row->telefone . "\n";			return $usuarios;	   }	} else {		echo "Nenhum registro encontrado\n";		return false;	}			}function cadastraUsuario($data){	require ("lib/bd.php");	//require ("lib/constants.php");	//require ("lib/envia_email.php");	$cliente = $data->client;	$nome = $data->name;	$cpf = $data->cpf;	$email = $data->email;	$telefone = $data->phone;	$senha = $data->password;	$chave = mt_rand(1000,9999);		echo "Inserindo registro usuario:\n";	$sql = "INSERT INTO usuario (cliente, nome, cpf, senha, email, telefone, chave, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; //FAZER CORRE��O PARA MAIS CLIENTES	$consulta = $bd->prepare($sql);	$consulta->bindValue(1, $cliente);	$consulta->bindValue(2, $nome);	$consulta->bindValue(3, $cpf);	$consulta->bindValue(4, $senha);	$consulta->bindValue(5, $email);	$consulta->bindValue(6, $telefone);	$consulta->bindValue(7, $chave);	$consulta->bindValue(8, false);	$consulta->execute();	if($consulta){		//envia_email($email, G_USER, "System Confirmation", "C�digo e verifica��o", "Seja bem vindo " . $nome . "\n\n" . "Este � seu c�digo de verifica��o: " . $chave);		//echo "E-mail de confirmacao enviado\n";		return "success"; //NA VERIFICA��O SE OS DADOS VIERAM CORRETOS, CASO N�O TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO N�O � TRUE E FALSE	}else{		return "failed";	}			}?>