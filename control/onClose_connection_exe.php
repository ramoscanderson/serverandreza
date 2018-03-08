<?php
// The connection is closed, remove it, as we can no longer send it messages
$this->clients->detach($conn);
global $conexoes;
unset($conexoes["{$conn->resourceId}"]);

echo "Conexao {$conn->resourceId} esta desconectada - " . date('H:i:s d-m-Y') . "\n\n";

?>