<?php
	
	
	class DiamondDataBase
	{
		private $username;
		private $password;
		private $database_name;
		private $server ;
		private $database_type;
		private $result;
		
		function __construct()
		{
			$tmp = new DiamondConfig();
			$arr_Temp = get_object_vars($tmp);
			$this->username = $arr_Temp['user'];
			$this->password = $arr_Temp['password'];
			$this->database_name = $arr_Temp['database'];
			$this->server = $arr_Temp['server_db'];
			$this->database_type = $arr_Temp['database_type'];
			
		}
		
		private function openConnection()
		{
			switch($this->database_type)
			{
				case 0:
						//MySQL
						$conn = mysql_connect($this->server,$this->username,$this->password)or die(mysql_error());
						mysql_select_db($this->database_name,$conn)or die(mysql_error());
						return $conn;
						break;
	
				case 1:
						//PostGRESQL
						$stringConnection = "host=".$this->server." port=5432 dbname=".$this->database_name." user=".$this->username." password=".$this->password;
						$conn = pg_connect($stringConnection);
						return $conn;
						break;
			
			}
		}
		
		public function queryCount(){
			return sizeof($this->result);
		}
		
		public function Command($query){
			$this->openConnection();
			switch($this->database_type)
			{
				case 0:
						//MySQL
						//error_log($query, 3, "/var/tmp/query_php.log");
						mysql_query($query)or die(mysql_error());
						
						return true;
						break;
				
				
				case 1:
						//PostGRESQL
						$queryResult = pg_query($query)or die(pg_result_error($queryResult));
						return true;
						break;
			
			}
		}
		public function selectQuery($query)
		{
			$this->openConnection();
			switch($this->database_type)
			{
				case 0:
						//MySQL
						//error_log($query, 3, "/var/tmp/query_php.log");
						$queryResult = mysql_query($query)or die(mysql_error());
						$rowResult = array();
						while($row = mysql_fetch_assoc($queryResult)){
							$rowResult[] = $row;
						}
						$this->result = $rowResult;
						return $rowResult;
						break;
				
				
				case 1:
						//PostGRESQL
						$queryResult = pg_query($query)or die(pg_result_error($queryResult));
						$rowResult = array();
						while($row = pg_fetch_assoc($queryResult)){
							$rowResult[] = $row;
						}
						$this->result = $rowResult;
						return $rowResult;
						break;
			
			}
		}
		
		public function insertQuery($query)
		{
		
			$this->openConnection();
			switch($this->database_type)
			{
				case 0:
						//MySQL
						$queryResult = mysql_query($query);
						if(!$queryResult){
							echo mysql_error();
							return false;
						}else{
							return true;
						}
						break;
				
				
				case 1:
						//PostGRESQL
						$queryResult = pg_query($query);
						if(!$queryResult){
							echo pg_result_error($queryResult);
							return false;
						}else{
							return true;
						}
						break;
			
			}
		}
		
		public function getLastInsertID(){
			
			switch($this->database_type)
			{
				case 0:
						//MySQL
						return mysql_insert_id();
						break;
				case 1:
						//PostGRESQL
						return pg_last_oid($this->result);
						break;
			
			}
			
		}
	}
?>