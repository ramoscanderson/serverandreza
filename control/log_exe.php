<?php

function inserirLog($acao, $client, $usuario){ //FAZER CÓDIGO QUE VERIFIQUE SE OS DADOS VIERAM CORRETOS
	require ("lib/bd.php");
	
	$date = date("Y-m-d H-i-s");
	
	echo "Inserindo LOG\n";
	
	$sql = "INSERT INTO log (data, acao, usuario, cliente) VALUES (?, ?, ?, ?)"; //FAZER CORREÇÃO PARA MAIS CLIENTES
	$consulta = $bd->prepare($sql);
	$consulta->bindValue(1, $date);
	$consulta->bindValue(2, $acao);
	$consulta->bindValue(3, $usuario);
	$consulta->bindValue(4, $client);
	$consulta->execute();
	
	if($consulta->rowCount()){
		return "success"; //NA VERIFICAÇÃO SE OS DADOS VIERAM CORRETOS, CASO NÃO TENHAM VINDO DEVE-SE RETORNAR ERROR, POR ISSO NÃO É TRUE E FALSE
	}else{
		return "failed";
	}
}
?>