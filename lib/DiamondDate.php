<?php
	class DiamondDate{
			
		function getLiteralDayOfWeek()
		{
			switch(date("D"))
			{
				case 'Mon':
						return 'Segunda-Feira';
						break;
				case 'Tue':
						return 'Terça-Feira';
						break;
				case 'Wed':
						return 'Quarta-Feira';
						break;
				case 'Thu':
						return 'Quinta-Feira';
						break;
				case 'Fri':
						return 'Sexta-Feira';
						break;
				case 'Sat':
						return 'Sábado';
						break;
				case 'Sun':
						return 'Domingo';
						break;
			}
		}

public function getLiteralMonth()
{
	switch(date("m"))
	{
		case 01:
		return 'Janeiro';
		break;
		
		case 02:
		return 'Fevereiro';
		break;
		
		case 03:
		return 'Março';
		break;
		
		case 04:
		return 'Abril';
		break;
		
		case 05:
		return 'Maio';
		break;
		
		case 06:
		return 'Junho';
		break;
		
		case 07:
		return 'Julho';
		break;
		
		case 08:
		return 'Agosto';
		break;
		
		case 09:
		return 'Setembro';
		break;
		
		case 10:
		return 'Outubro';
		break;
		
		case 11:
		return 'Novembro';
		break;
		
		case 12:
		return 'Dezembro';
		break;
	}
}

	public function getDay(){
		return date("d");
	}

	public function getYear(){
		return date("Y");
	}
	
	public function getWelcomeMessage(){
		$hora = date("G");
		
		if($hora > 0 && $hora <= 11){
			return "Bom Dia, ";
		}
		
		if($hora >= 12 && $hora <= 17){
			return "Boa Tarde, ";
		}
		
		if($hora >= 18 && $hora <= 23){
			return "Boa Noite, ";
		}
	}
}
?>