<?php

function envia_email($nome, $destinatario, $assunto, $mensagem){

	$envio = file_get_contents("http://www.souzapapaleo.com.br/mailer/enviar_contato.php?email=" . urlencode($destinatario) . "&nome=" . urlencode($nome) . "&assunto=" . urlencode($assunto) . "&mensagem=" . urlencode($mensagem));
	
	if($envio == "sucess"){
		return true;
	}else{
		return false;
	}
}

?>