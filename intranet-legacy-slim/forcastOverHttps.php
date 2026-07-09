<?php
	class DiamondForecast {
		private $currentC;
		private $temp;
		private $msg;
		private $img;
		private $errorF;
		
		function __construct(){
			$this->errorF = false;
			try{
				$fetchData = file_get_contents("http://weather.yahooapis.com/forecastrss?w=455825&u=c");
				$xmlData = simplexml_load_string($fetchData);
				$location = $xmlData->channel->xpath('yweather:location');
			}catch (Exception $e){
				$this->errorF = true;
			}

			foreach($xmlData->channel->item as $data)
				{
					$current_condition = $data->xpath('yweather:condition');
					$forecast = $data->xpath('yweather:forecast');
					$this->currentC = $current_condition[0];
				}	
			}
	
		function Information(){
			if($this->errorF){
				$jTemp = array(
					'temperature' => 0,
					'img' => "<span class='icon-loading place-right'></span>",
					'msg' => "Unknow"
				);
				return $jTemp;
			}
			
			$temp = $this->currentC['temp'][0];
			$infW = $this->getWeather($this->currentC['code'][0]);
			$jTemp = array(
				'temperature' => $temp,
				'img' => $infW['img'],
				'msg' => $infW['msg']
			);
			return $jTemp;
		}
			

		private function getWeather($code){
			switch($code){
			case 10:
				//Chuva
			$msg = "Chuvoso ";
			$img = "<span class='icon-rainy-2 place-right'></span>";
			
			break;
			
			
			case 11:
				//Chuva
				
			$msg = "Chuvoso ";
			$img = "<span class='icon-rainy-2 place-right'></span>";
			break;
			
			
			case 12:
				//Chuva
			$msg = "Chuvoso ";
			$img = "<span class='icon-rainy-2 place-right'></span>";
			break;
			
			case 17:
				//Chuva
			$msg = "Chuvoso ";	
			$img = "<span class='icon-rainy-2 place-right'></span>";
			break;
			
			case 25:
				//Frio
			$msg = "Frio ";
			$img = "<span class='icon-cloudy-2 place-right'></span>";

			
			break;
			
			case 26:
				//Nublado
				$msg = "Nublado ";
				$img = "<span class='icon-cloud-4 place-right'></span>";
				break;
				
			case 27:
				//Nublado Noite
				$msg = "Nublado ";
				$img = "<span class='icon-cloud-3 place-right'></span>";
				break;
				
			case 28:
				//Nublado Dia
			$msg = "Nublado ";
			$img = "<span class='icon-cloudy place-right'></span>";
			break;
			
			case 29:
				//Nublado Noite
				$msg = "Nublado ";
				$img = "<span class='icon-cloud-3 place-right'></span>";
				break;
				
			case 30:
				//Nublado Dia
				$msg = "Nublado ";
				$img = "<span class='icon-cloudy place-right'></span>";
				break;
				
			case 31:
				//Noite
			$msg = "Limpo ";	
			$img = "<span class='icon-moon place-right'></span>";
			break;
			 
			case 32:
				//Dia
			$msg = "Limpo ";
			$img = "<span class='icon-sun place-right'></span>";
			break;
			
			case 33:
				//Noite
			$msg = "Limpo ";
			$img = "<span class='icon-moon place-right'></span>";
			break;
			
			case 34:
				//Dia
			$msg = "Limpo ";
			$img = "<span class='icon-sun place-right'></span>";
			break;
			
			case 36:
				//Dia
			$msg = "Limpo ";	
			$img = "<span class='icon-sun place-right'></span>";
			break;
			
			
			case 38:
				//Trovoadas
			$msg = "Trovoada ";	
			$img = "<span class='icon-lightning-4 place-right'></span>";
			break;
			
			case 39:
				//Trovoadas
			$msg = "Trovoada ";
			$img = "<span class='icon-lightning-4 place-right'></span>";
			break;
			
			
			case 40:
				//Chuva
			$msg = "Chuvoso ";	
			$img = "<span class='icon-rainy-2 place-right'></span>";
			break;
			
			case 44:
				//Nublado
			$msg = "Nublado ";
			$img = "<span class='icon-cloud-4 place-right'></span>";
			break;
			
				case 45:
					//Trovoadas
					$msg = "Trovoada ";
					$img = "<span class='icon-lightning-4 place-right'></span>";
				break;
			default:
				$msg = "Normal ";	
				$img = "<span class='icon-sun place-right'></span>";
				
	}
		$arrW = array (
			"msg" => $msg,
			"img" => $img
		);
		
	return $arrW;
}

}

$foreas = new DiamondForecast();
$information = $foreas->Information();

$cont = "<div class='tile-content'>
					 <div class='padding10'>
						 <h1 class='fg-white ntm'>".$information['temperature']." C°".$information['img']."</h1>
					     <h4 class='fg-white no-margin'>".$information['msg']."</h4>
					 </div>
                </div>";

$f = @fopen("forec.html", "w");
// Write text line
fwrite($f, $cont); 

// Close the text file
fclose($f);


?>