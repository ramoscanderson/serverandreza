<?php

//PUSH NOTIFICATION
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;
use sngrl\PhpFirebaseCloudMessaging\Notification;
//PUSH NOTIFICATION

function gerar_notificacao($device, $titulo, $corpo, $atributos){
	//echo "NOTIFICACAO\n";
	//echo $device . "\n";
	//echo $titulo . "\n";
	//echo $corpo . "\n";
	
	//$server_key = 'AAAAGuMVir0:APA91bEtLYBfQKcuIn38W43Kj6LNbpJWeQJ61DO8VJ4IYYduiJFvo6dD55U9g83YuqMj0Qrkv1zNSVxxdPzTWeEIMzbEcf2G8TJKRsy_fOnEedNR57TshjhMx9PFkM62RTilin-pxkaVRgbrSfm3aiZKpU_PLyWOvA';
	$server_key = 'AAAA3ChXSTQ:APA91bEQ8BFCLanbZEviJPBC5CTtEeTlT102lHYI-eoO7n5Nx7fyADdrVkg7Yi0jyDbDg8J6RVrGAcc3_pPG5v_RCAPuWcw9trdrBWqgTwjiosZed6cQ-ozeGuxUtnA9YJFibppa69zFvmUrMyB4tT2Yl9Idj7Ua-Q';
	$client = new Client();
	$client->setApiKey($server_key);
	$client->injectGuzzleHttpClient(new \GuzzleHttp\Client());
   
	$message = new Message();
	$message->setPriority('high');
	//$message->addRecipient(new Device('dPxbEjyTIVM:APA91bEimZalybGz_S6Wd5L-j6WppkKip3pk26ryWxuOfigL5NJY8N4X-9CoQS0IZwpLL18C2o0ZuP7JXdnefPozh5gw4Sa53KJfdciKPB7A9ct_WwuBjdubhZ4jUKElTf2FZNLSnWItcQnKKElI1iS5wcAZo5yjGg'));
	$message->addRecipient(new Device($device));
	//$message->addRecipient(new Topic('Teste TOPICO'));
	$message
		/*->setNotification(new Notification('Título teste', 'Teste de corpo'))*/
		->setNotification(new Notification($titulo, $corpo))
		/*->setData(['data' => 'valor'])*/
		->setData($atributos)
	;
   
	$response = $client->send($message);
	var_dump($response->getStatusCode());
	var_dump($response->getBody()->getContents());
}



/*

fo5gCZqeL_Y:APA91bEUkctZiAt-qT0C7jnNHqSnPO6VVJfYBhBfkHaLmBzRDpLWAbclYj9XR02bmuG4LIzTUKQHqs5jb-kqfsleyHWI9Ev6uq8R6GLyaFrnxseyB6EstnoE-VcyNBWWYDH05jyfW-UwEP4qUpjPhOnQp8AnsL6RHQ

dPxbEjyTIVM:APA91bEimZalybGz_S6Wd5L-j6WppkKip3pk26ryWxuOfigL5NJY8N4X-9CoQS0IZwpLL18C2o0ZuP7JXdnefPozh5gw4Sa53KJfdciKPB7A9ct_WwuBjdubhZ4jUKElTf2FZNLSnWItcQnKKElI1iS5wcAZo5yjGg

*/

?>