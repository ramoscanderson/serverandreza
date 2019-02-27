<?php
// Store the new connection to send messages to later
$this->clients->attach($conn);
sleep(1);





// echo "POST: " . $_POST['token'];
// $buffer = '';
// $bytes = socket_recvfrom($socket, $buffer, 4096, 0, $ipaddress, $port);/*$bytes = @socket_recv($socket, $buffer, 4096, 0);*/

// if ($bytes === false) {
// 	// error on recv, remove client socket (will check to send close frame)
// 	// $this->wsSendClientClose($clientID, self::WS_STATUS_PROTOCOL_ERROR);
// 	echo "bytes === false \n";
// }
// elseif ($bytes > 0) {
// 	echo $buffer, PHP_EOL;
// 	echo 'IP: ', $ipaddress, ' e porta ', $port, PHP_EOL;
// 	echo '-------------------------', PHP_EOL;
// }






$conn->send(message_setProtocol(null,"200","Success","1.0.5","firstConnection",array("token"=>setJWT(1, false, false))));
echo "Novo cliente conectado! ({$conn->resourceId}) - " . date('H:i:s d-m-Y') . "\n";

global $conexoes, $session;
$conexoes["{$conn->resourceId}"] = array("userId"=>"-1");

//print_r($conexoes);

echo "Resposta de firstConnection enviada\n";
?>