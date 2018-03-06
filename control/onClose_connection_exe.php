<?php
// The connection is closed, remove it, as we can no longer send it messages
$this->clients->detach($conn);

echo "Conexao {$conn->resourceId} esta desconectada\n\n";

?>