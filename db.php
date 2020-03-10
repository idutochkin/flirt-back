<?
class Database {
	private $_connection;
	private static $_instance;
	private $_host = "miroplat.mysql";
	private $_username = "miroplat_flirt";
	private $_password = "N+BKFhs9";
	private $_database = "miroplat_flirt";
	
	public static function getInstance() {
		if(!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function __construct() {
		$this->_connection = new mysqli($this->_host, $this->_username, $this->_password, $this->_database);
	
		if(mysqli_connect_error()) {
			trigger_error("Failed to connect to MySQL: " . mysql_connect_error(), E_USER_ERROR);
		}
	}
	
	public function query($query, $bind = array()) {
		$mysqli = $this->_connection;
		$arResult = array();
		if(!empty($bind)) {
			if($stmt = $mysqli->prepare($query)) {
				$bind_params = array();				
				$bind_params[] = & $bind[0];
				array_shift($bind);
				for($i = 0; $i < count($bind); $i++) {
					$bind_params[] = & $bind[$i];
				}
				call_user_func_array(array($stmt, 'bind_param'), $bind_params);
				$stmt->execute();
				$result = $stmt->get_result();
				if($result === false) {
					return $stmt->insert_id;
				} else {
					while($row = $result->fetch_array(MYSQLI_ASSOC)) {
						$arResult[] = $row;
					}
				}
				$stmt->close();
			}
		} else {
			if($result = $mysqli->query($query)) {
				while($row = $result->fetch_assoc()) {
					$arResult[] = $row;
				}
				$result->free();
			}
		}
		return $arResult;
	}
	
	private function __clone() { }
	
	/*public function getConnection() {
		return $this->_connection;
	}*/
}
?>