<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("model/class.phpmailer.php");
require("model/class.smtp.php");

require ("control/messageProtocol_exe.php");
require ("control/agendaConsulta_exe.php");
require ("control/usuario_exe.php");
require ("control/token_exe.php");
require ("control/email_exe.php");
require ("control/planoAlimentar_exe.php");

date_default_timezone_set('America/Sao_Paulo');

class Connection implements MessageComponentInterface {
	protected $clients;
	public $conexoes = array();

	public function __construct() {
		$this->clients = new \SplObjectStorage;
	}

	public function onOpen(ConnectionInterface $conn) {
		//caminho a partir da raiz pois está classe está sendo requerida no connection_srv
		require ("control/onOpen_connection_exe.php");
	}

	public function onMessage(ConnectionInterface $from, $msg) {
		//caminho a partir da raiz pois está classe está sendo requerida no connection_srv
		require ("control/onMessage_connection_exe.php");
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