<?php
	class DiamondEvents {
			private $DB;
		function __construct(){
			$this->DB = new DiamondDataBase();
		}
		
		public function getEvents(){
			$qryEvent = "select id_event,concat_ws('/',day(dt_start),month(dt_start)) as data,local,title  from events where dt_start > date(now()) limit 10;";
			$result = $this->DB->selectQuery($qryEvent);
			$arr = array();
			foreach($result as $row){
				$arr[] = array(
					"id" => $row['id_event'],
					"data" => $row['data'],
					"local" => $row['local'],
					"title" => $row['title']
				); 
			}

			return $arr;
		}
		public function getList(){
				$result = $this->DB->selectQuery("select * from events order by dt_start desc");
				return $result;
		}
		
		public function getInformation($id){
			$result = $this->DB->selectQuery("select * from events where id_event=".$id);
			return $result;
		}
		
		public function insertEvents($local,$title,$informacoes,$dt_start,$dt_end,$tm_start,$tm_end){
			$result = $this->DB->insertQuery("insert into events(local,title,informacoes,dt_start,dt_end,tm_start,tm_end)values('".$local."','".$title."','".$informacoes."','".$dt_start."','".$dt_end."','".$tm_start."','".$tm_end."')");
			if($result){
				return true;
			}else{
				return false;
			}
		}
		
		public function deleteEvents($id){
			foreach($id as $idchk){
				$result = $this->DB->Command("delete from events where id_event=".$idchk);
			}
				return true;
				
		}
		public function updateEvents($idUp,$local,$title,$informacoes,$dt_start,$dt_end,$tm_start,$tm_end){
			$result = $this->DB->insertQuery("update events set local='".$local."',title='".$title."',informacoes='".$informacoes."',dt_start='".$dt_start."',dt_end='".$dt_end."',tm_start='".$tm_start."',tm_end='".$tm_end."' where id_event=".$idUp);
			if($result){
				return true;
			}else{
				return false;
			}
		}
	}
?>