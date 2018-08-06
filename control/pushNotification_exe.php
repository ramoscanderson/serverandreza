<?php

function gerar_notificacao(){
	$server_key = '_YOUR_SERVER_KEY_';
	$client = new Client();
	$client->setApiKey($server_key);
	$client->injectGuzzleHttpClient(new \GuzzleHttp\Client());
   
	$message = new Message();
	$message->setPriority('high');
	$message->addRecipient(new Device('_YOUR_DEVICE_TOKEN_'));
	$message
		->setNotification(new Notification('some title', 'some body'))
		->setData(['key' => 'value'])
	;
   
	$response = $client->send($message);
	var_dump($response->getStatusCode());
	var_dump($response->getBody()->getContents());
}

?>