<?phpfunction consultaUsuario($data){	require ("lib/bd.php");	$id = $data->id;	echo "Consultando registro: $id\n";	$sql = "SELECT * FROM usuario WHERE id = ?"; //FAZER CORRE��O PARA MAIS CLIENTES	$consulta = $bd->prepare($sql);	$consulta->bindParam(1, $id);	$consulta->execute();	if ($consulta->rowCount() > 0) {	   while($row = $consulta->fetch(PDO::FETCH_OBJ)){			$usuarios[] = array("id" => $row->id, "cliente" => $row->cliente,"nome" => $row->nome, "cpf"=>$row->cpf, "email"=>$row->email, "telefone"=>$row->telefone);			echo $row->id . " - " . $row->cliente . " - " . $row->nome . " - " . $row->cpf . " - " . $row->email . " - " . $row->telefone . "\n";	   }	} else {		echo "Nenhum registro encontrado\n";	}			}?>