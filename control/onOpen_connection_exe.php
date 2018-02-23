<?php
// Store the new connection to send messages to later
$this->clients->attach($conn);

$protocol_open = array(
"Token" => "visitante",
"request" => array(
	"id" => "200",
	"success" => true,
	"version" => "1.0.5",
	"method" => "connection",
	"data" => array()
	)
);

sleep(1);
$conn->send(json_encode($protocol_open));
//$conn->send("teste");
echo "Novo cliente conectado! ({$conn->resourceId})\n";

?>