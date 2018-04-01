<?php

function message_setProtocol($id,$status_cod,$status_message,$version,$method,$data){
	$protocol_message = array(
		"request" => array(
			"id" => $id,
			"status" => array(
				"cod" => $status_cod, 
				"message" => $status_message),
			"lastVersion" => $version, //corrigir para pegar a versão no global
			"method" => $method,
			"data" => $data
		)
	);	
	return json_encode($protocol_message);
}

function message_getProtocol($message){
	if(isJson($message)){
		return json_decode($message);	
	}else{
		$provisionalMessage = array(
			"token" => "visitante", //ver necessidade desse item posteriormente e, caso necessário, gerar 'visitante'
			"request" => array(
				"id" => "601",
				"status" => "601",
				"version" => "1.0.5", //corrigir para pegar a versão no global
				"method" => "Error - Incorrect protocol",
				"data" => array()
			)
		);
		return json_decode($provisionalMessage);
	}	
}

function isJson($string){
	return is_string($string) && is_array(json_decode($string, true)) ?true : false;
}

function validar_estrutura_data($data, $array){
	echo "Verificando estrutura de dados\n";
	foreach ($array as $dado) {
		echo $dado . "\n";
		if(!array_key_exists($dado, $data)	){
			return false;
		}else{
			if(empty($data->$dado)){
				return false;
			}
		}
	}
	return true;
}

?>