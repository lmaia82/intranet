<?php
	class SecurityRules{
		private $RULES;
		
		function addToDictionary($field,$value,$rule){
			$dictio = array("field" => $field, 
							"value" => $value,
							"rule"	=> $rule
							);
			$RULES[] = $dictio;			
		}
		
		function getRules(){
			return $RULES;
		}
	}
	
	class DiamondSecurity{
		private $DB;
		private $dict_rules;
		private $base_values;
		function __construct($srules,$dictionary)
		{
			$this->DB = new DiamondDataBase();
			$this->dict_rules = $srules;
			$this->base_values = $dictionary;
		}
		
		private function returnValueOfDictionary($field){
			return $this->base_values[$field];
		}
		
		function checkRules(){
			$results = array();
			foreach($this->dict_rules as $rul){
				switch($rul["rule"]){
					case "eq":
							if($this->returnValueOfDictionary($rul["field"]) == $rul["value"]){
								$results[] = true;
							}else{
								$results[] =  false;
							}
							break;
					case "dif":
							if($this->returnValueOfDictionary($rul["field"]) <> $rul["value"]){
								$results[] = true;
							}else{
								$results[] =  false;
							}
							break;
					case "stw":
							
							break;
					case "enw":
							break;
					case "has":
							break;
					case "mth":
							if($this->returnValueOfDictionary($rul["field"]) > $rul["value"]){
								$results[] = true;
							}else{
								$results[] =  false;
							}
							break;
					case "lth":
							if($this->returnValueOfDictionary($rul["field"]) < $rul["value"]){
								$results[] = true;
							}else{
								$results[] =  false;
							}
							break;
					case "meq":
							if($this->returnValueOfDictionary($rul["field"]) >= $rul["value"]){
								$results[] = true;
							}else{
								$results[] =  false;
							}
							break;
					case "leq":
							if($this->returnValueOfDictionary($rul["field"]) <= $rul["value"]){
								$results[] = true;
							}else{
								$results[] =  false;
							}
							break;		
				
				}
				
			}
			
			foreach($results as $rs){
				if(!$rs){
					return false;
				}
			}
			return true;
		}
		
		
		
	}
?>