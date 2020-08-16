<?php
	// connection credentials
	$CFGUsername = getenv("MySQLAdminUsername");
	$CFGPassword = getenv("MySQLAdminPassword");
	$CFGHostAddr = "192.168.1.68";
	$CFGDBName   = "mtg_ru";

	/* error codes for monitoring class behaviour */
	define("DBC_CONN_SUCCESS",          0x00);
	define("DBC_CONN_ERR_UNKNOWN",      0x02);
	define("DBC_CONN_ERR_UNREACHABLE",  0x04);
	define("DBC_ERR_NOFIELDS",          0x08);
	define("DBC_QUER_ERR_NOCONNECTION", 0x10);
	/* more errors TODO */

	class DBConnector {
		private $handle;        // mysqli object handle
		private $result;        // mysqli_result object
		private $fields;        // field to select from DB
		private $conn_host;
		private $conn_username;
		private $conn_password;
		private $conn_dbname;
		public $error_code;     // last error
		public $rows;           // array with assoc arrays containing rows from the table
		public $dict;           // assoc array to match table columns to JSON keys (e.g. "EDT" => "set")

		public function __construct(string $_host, string $_usrname, string $_passwd, string $_db) {
			$this->conn_host     = $_host;
			$this->conn_username = $_usrname;
			$this->conn_password = $_passwd;
			$this->conn_dbname   = $_db;
			$this->handle        = null;
			$this->error_code    = DBC_CONN_SUCCESS;
			$this->result        = null;
			$this->fields        = array();
			$this->rows          = array();
			$this->dict          = array();
		}

		public function __destruct() {
			if (!empty($this->handle)) @$this->handle->close();
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
				$this->error_code = DBC_CONN_ERR_UNKNOWN; // if the more precise error will still be unknown

				// check if host available using cURL
				$c = curl_init($this->conn_host);
				curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($c, CURLOPT_HEADER,         true);
				curl_setopt($c, CURLOPT_NOBODY,         true);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
				$resp = curl_exec($c);
				curl_close($c);
				$this->error_code = $resp ? DBC_CONN_SUCCESS : DBC_CONN_ERR_UNREACHABLE;
			}

			return $this->error_code === DBC_CONN_SUCCESS ? true : false;
		}

		public function SetFields($_fields = array(), $_dict = array()) { // sets fields in table to search and fields assoc array
			if (empty($_fields)) {
				$this->error_code = DBC_ERR_NOFIELDS; // no fields to set
				return false;
			}
			$this->fields = $_fields; /* TODO: implement some array checks and sanitizing */

			if (empty($_dict)) { // table columns and JSON keys match (e.g. "EDT" => "EDT")
				foreach($this->fields as $field) $this->dict[$field] = $field;
			}
			else {
				$this->dict = $_dict;
			}

			return true;
		}

		public function FetchSelect($keyword = "%", $field_index = 0, $table = "cards") { // returns number of rows fetched. -1 means failure (see $error_code)
			if (empty($this->handle)) {
				$this->error_code = DBC_QUER_ERR_NOCONNECTION;
				return -1;
			}
			if (empty($this->fields)) {
				$this->error_code = DBC_ERR_NOFIELDS;
				return -1;
			}

			$Query = "SELECT ";
			$flds = $this->fields; // to keep the original fields safe
			foreach($flds as &$f) $f = "`$table`.`$f`";
			$Query = $Query.join(", ", $flds)." FROM `$table` WHERE `$table`.`{$this->fields[$field_index]}` LIKE '$keyword';";

			$this->result = $this->handle->query($Query);
			$this->rows = array();
			while($row = $this->result->fetch_assoc()) {
				$unit = array();
				foreach($this->fields as $field) $unit[$this->dict[$field]] = $row[$field];
				$this->rows[] = $unit;
			}

			return $this->result->num_rows;
		}

	}

?>
