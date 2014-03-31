<?php
//CLIENTE QUE CONSUME EL PUENTE  DE RBT 
error_reporting(E_ALL);
ini_set("display_errors", 1);
/** Top de descargas de RBT  **/
require_once('cliws.php');

class Services{

 public function es_claro($phone,$country,$by_name=false)
 {      
 	    $sms = new libws('ws:sms');

		$params=array('user' => '#SMS_USER','pass' => '#SMS_PASS','area' => $country,'phone' => $phone);
        $is = $sms->IsClaro_Phone($params);

		if (isset($is->IsClaro_PhoneResult)) {
			$response = (int) $is->IsClaro_PhoneResult;
				
			if ($by_name !== false) {
				switch ($response) {
					case -1: $response 	= 'ESPE'; break;
					case -104: $response = 'ESPE'; break;
					case 1: $response 	= 'PREP'; break;
					case 2: $response 	= 'HIBR'; break;
					case 3: $response 	= 'POST'; break;
				}
			}
			return $response;
		}
		
		return false;
		
 }//fin de la funcion es_claro


function mensaje($country,$phone,$message)
  {
    $sms = new libws('ws:sms');
        $mes = $sms->__claro_sms($country,$phone,$message);
          //$price = $rbt->__queryDownFee($phone,$idcontent);
          return $mes;
          //return $price->queryToneInfos->tariffPrice;

  }//fin de la funcion price    




}//fin de la clase		
?>