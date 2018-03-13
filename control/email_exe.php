<?php

function envia_email($nome, $destinatario, $assunto, $mensagem){

	$mailer = new SimpleMail();
	
	$send = SimpleMail::make()
	->setTo("ticion@gmail.com", "Anderson")
	->setFrom("systemconfirmation@gmail.com", "Sistema")
	->setSubject("Teste de envio")
	->setMessage("Mensagem")
	//->setReplyTo($replyEmail, $replyName)
	//->setCc(['Bill Gates' => 'bill@example.com'])
	//->setBcc(['Steve Jobs' => 'steve@example.com'])
	->setHtml()
	->setWrap(78)
	->send();

	echo ($send) ? 'Email sent successfully' : 'Could not send email';

}

?>