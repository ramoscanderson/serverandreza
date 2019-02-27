<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



require("model/class.phpmailer.php");
require("model/class.smtp.php");

require ("control/functions.php");
require ("lib/adress.php");
require ("control/messageProtocol_exe.php");
require ("control/agendaConsulta_exe.php");
require ("control/usuario_exe.php");
require ("control/token_exe.php");
require ("control/email_exe.php");
require ("control/planoAlimentar_exe.php");
require ("control/news_exe.php");
require ("control/consultas_exe.php");
require ("control/medidas_exe.php");
require ("control/pushNotification_exe.php");
require ("control/log_exe.php");


date_default_timezone_set('America/Sao_Paulo');

class Connection implements MessageComponentInterface {
	public $clients;
	public $conexoes = array();
	
	public function __construct() {
		$this->clients = new \SplObjectStorage;
	}
	
	public function getClient(){
		return $this->clients;
	}

	public function onOpen(ConnectionInterface $conn) {
		//caminho a partir da raiz pois está classe está sendo requerida no connection_srv
		require ("control/onOpen_connection_exe.php");		
	}

	public function onMessage(ConnectionInterface $from, $msg) {
		//caminho a partir da raiz pois está classe está sendo requerida no connection_srv
		try{
			require ("control/onMessage_connection_exe.php");
		}catch(Error $e){
			echo "\n\n\n\nFATAL ERROR CAPTURADO: " . $e->getMessage() . "\n";
			echo "ARQUIVO: " . $e->getFile() . "\n";
			echo "LINHA: " . $e->getLine() . "\n";
			echo "REGISTRANDO ERRO" . "\n\n\n\n";
			require ("control/erro_exe.php");
			$from->send(message_setProtocol("00000","900","Failed","1.0.5","fatalError",array()));
			// retorno de mensagem ao cliente
		}
	}

	public function onClose(ConnectionInterface $conn) {
		//caminho a partir da raiz pois está classe está sendo requerida no connection_srv
		require ("control/onClose_connection_exe.php");
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		//caminho a partir da raiz pois está classe está sendo requerida no connection_srv
		require ("control/onError_connection_exe.php");		
	}
}
?>