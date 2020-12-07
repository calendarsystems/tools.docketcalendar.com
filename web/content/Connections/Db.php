<?php
class Db extends mysqli 
{
	// single instance of self shared among all instances
	private static $instance = null;
	// db connection config vars
	private $user = "375786_dlsub";
	private $pass = "D0cketLaw123";
	private $dbName = "375786_dlsub";
	private $dbHost = "mariadb-066.wc1.dfw3.stabletransit.com";

	//This method must be static, and must return an instance of the object if the object
	//does not already exist.
	public static function getInstance() {
	if (!self::$instance instanceof self) {
	self::$instance = new self;
	}
	return self::$instance;
	}
	// The clone and wakeup methods prevents external instantiation of copies of the Singleton class,
	// thus eliminating the possibility of duplicate objects.
	public function __clone() {
	trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
	public function __wakeup() {
	trigger_error('Deserializing is not allowed.', E_USER_ERROR);
	}
	private function __construct() 
	{
		parent::__construct($this->dbHost, $this->user, $this->pass, $this->dbName);
		if (mysqli_connect_error()) {
		exit('Connect Error (' . mysqli_connect_errno() . ') '
		. mysqli_connect_error());
		}
	parent::set_charset('utf-8');
	}
	
	public function dbquery($query)
	{
		if($this->query($query))
		{
		return true;
		}
	}
	public function get_result($query) 
	{
		$result = $this->query($query);
		if ($result->num_rows > 0){
		$row = $result->fetch_assoc();
		return $row;
		} else
		return null;
	}
	
	/*Insert new record from given array */
	function db_insert_from_array($data,$table)
	{
		
		foreach( $data as $key=>$value ) {
			$fields[] = "`$key`";
            $values[] = "'".$this->real_escape_string($value)."'";
			
		}
		
		$fields = @implode(",", $fields);
		$values = @implode(",",$values);
		$query = "INSERT INTO `$table` ($fields) VALUES ($values) ";
		
		//echo $query; 
        $result = $this->dbquery($query);
		
		return $result;
	} 

	/*update record from given array */
	function db_update_from_array($updateData,$conditionData,$table)
	{
		
		$query = "UPDATE $table SET ";
		
		foreach( $updateData as $key=>$value ) {
			$valueSets[] = $key . " = '" . $this->real_escape_string($value) . "'";
		}
		$query .= @implode(",", $valueSets);
		
		
		if(is_array($conditionData))
		{
			$k=1;
			foreach($conditionData as $key => $value)
			{
				if($k==1)
					$query .= " WHERE ".$key."='".$value."'";		
				else
					$query .= " AND ".$key."='".$value."'";		
				$k++;
			}
		}
		
		//echo $query; 
        $result = $this->dbquery($query);
		
		return $result;
	}	
}
?>