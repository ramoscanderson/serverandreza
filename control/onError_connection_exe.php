<?php

echo "FatalError: {$e->getMessage()}\n";
$conn->send(message_setProtocol($messageObj->request->id,"600","FatalError - {$e->getMessage()}","1.0.5","errorRequest",array()));
echo "Resposta enviada\n";
$conn->close();

?>