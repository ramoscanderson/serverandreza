<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Connection implements MessageComponentInterface {
	protected $clients;

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