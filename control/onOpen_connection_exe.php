<?php
// Store the new connection to send messages to later
$this->clients->attach($conn);

echo "Novo cliente conectado! ({$conn->resourceId})\n";

?>