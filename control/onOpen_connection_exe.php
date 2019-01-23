<?php
// Store the new connection to send messages to later
$this->clients->attach($conn);

sleep(1);
//var_dump($conn);

/*
remoteAdress
remoteId
*/

//echo $conn->getClient();

if(array_key_exists ( "remoteAdress" , $conn)){
	echo "EXISTE\n";
}else{
	echo "NAO\n";
}

$conn->send(message_setProtocol(null,"200","Success","1.0.5","firstConnection",array("token"=>setJWT(1, false, false))));
echo "Novo cliente conectado! ({$conn->resourceId}) - " . date('H:i:s d-m-Y') . "\n";

global $conexoes, $session;
$conexoes["{$conn->resourceId}"] = array("userId"=>"-1");

//print_r($conexoes);

echo "Resposta de firstConnection enviada\n";
?>