<?php
/* 
 * define uma função para envio de e-mail.
 * Como foi explicado em aula, como estamos utilizando a classe PHPMailer, não precisariamos necessariamente
 * ter definido essa função, mas isso é importante para encapsular o código que realmente faz o envio de e-mail e com 
 * isso deixar o código da nossa aplicação menos dependente de código externos e bibliotecas.
 */
function envia_email($email_destinatario, $email_remetente, $nome_remetente, $assunto, $mensagem){
    //define uma variavel global para guardar a mensagem de erro, se houver
    global $error;
  	$mail = new PHPMailer();  // cria um novo objeto PHPMailer
  	$mail->IsSMTP(); // habilita o modo SMTP
  	$mail->SMTPDebug = 0;  // desabilita o modo de debug
  	$mail->SMTPAuth = true;  // habilita a autenticacao
  	$mail->SMTPSecure = 'ssl'; // parametro necessario para enviar usando o GMAIL
  	$mail->Host = 'smtp.gmail.com';
  	$mail->Port = 465;
  	//as constantes G_USER e G_PASS estão no arquivo "constants.php"
  	$mail->Username = G_USER;
  	$mail->Password = G_PASS;
  	$mail->SetFrom($email_remetente, $nome_remetente);
  	$mail->Subject = $assunto;
  	$mail->Body = $mensagem;
  	$mail->AddAddress($email_destinatario);
  	if(!$mail->Send()) {
  		$error = 'Erro ao enviar e-mail: '.$mail->ErrorInfo; 
  		return false;
  	} else {
  		$error = 'Mensagem enviada com sucesso!';
  		return true;
  	}
}

 echo $error;

?>