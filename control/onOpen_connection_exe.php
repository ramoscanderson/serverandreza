<?php
// Store the new connection to send messages to later
$this->clients->attach($conn);

sleep(1);
$conn->send(message_setProtocol(null,"200","Success","1.0.5","firstConnection",array("token"=>setJWT(1))));
echo "Novo cliente conectado! ({$conn->resourceId})\n";
echo "Resposta de firstConnection enviada\n";
?>