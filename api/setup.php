<?php
	// connection credentials
	$CFGUsername = getenv("MySQLAdminUsername");
	$CFGPassword = getenv("MySQLAdminPassword");
	$CFGHostAddr = "192.168.1.68";
	$CFGDBName   = "mtg_ru";

	/* error codes for monitoring class behaviour */
	define("CONN_SUCCESS",         0x00);
	define("CONN_ERR_UNKNOWN",     0x02);
	define("CONN_ERR_UNREACHABLE", 0x04);
	/* more errors TODO */

	class DBConnector {
		private $handle;        // mysqli object handle
		private $result;        // mysqli_result object
		private $fields;        // field to select from DB
		private $conn_host;
		private $conn_username;
		private $conn_password;
		private $conn_dbname;
		public $err_code;       // last error

		public function __construct($_host, $_usrname, $_passwd, $_db) {
			$this->conn_host     = $_host;
			$this->conn_username = $_usrname;
			$this->conn_password = $_passwd;
			$this->conn_dbname   = $_db;
			$this->handle        = null;
			$this->err_code      = CONN_SUCCESS;
			$this->results       = null;
			$this->fields        = array();
		}

		public function __destruct() {
			@$this->handle->close();
		}

		public function Connect() { // returns true if connection was successful
			$this->handle = @new mysqli(
				$this->conn_host,
				$this->conn_username,
				$this->conn_password,
				$this->conn_dbname
			);
			@$this->handle->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

			if ($this->handle->connect_error) { // error occured, checking further
				$this->err_code = CONN_ERR_UNKNOWN; // if the more precise error will still be unknown

				// check if host available using cURL
				$c = curl_init($this->conn_host);
				curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($c, CURLOPT_HEADER,         true);
				curl_setopt($c, CURLOPT_NOBODY,         true);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
				$resp = curl_exec($c);
				curl_close($c);
				$this->err_code = $resp ? CONN_SUCCESS : CONN_ERR_UNREACHABLE;
			}

			return $this->err_code === CONN_SUCCESS ? true : false;
		}

		public function SetFields($_fields) { // TODO function to set fields to select from DB
			if (!isset($_fields)) return false;
			$this->fields = $_fields;
			return true;
		}

	}

?>