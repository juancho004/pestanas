<?php
/**
 * Factory .
 *
 * @author Jcbarreno <jcbarreno.tpp@gmail.com>
 * @version 1.0
 * @package Class Busireporter
 */
class BusiReporter extends modelAplication  {

	protected $conf;
	public $nusoap;
	protected $app;
	protected $mail;
	protected $prefix;

	/**
	* Recibe instancia de archivo INI y NUSOAP
	*
	* @var Object arhivo INI
	* @var Object NUSOAP
	* @var array APP silex
	* @return null
	*/
	public function __construct($confp, $app, $phpMailer,$prefix)
	{
		$this->mail 	= $phpMailer;
		$this->confp 	= $confp;
		$this->app 		= $app;
		$this->prefix	=$prefix;
		$cbusi 			= $this->_getCredentialsBusi();
		$cbusi 			= $this->_getCredentialsBusi();
		$this->nusoap 	= new nusoap_client($cbusi->clientbusi);
		return NULL;
	}

	/**
	* Obtiene campos definidos en el formulario BUSI
	*
	* @return Object
	*/


	public function _getForm()
	{
		$return			= new stdClass();
		$response 		= $this->_getParamsTableBusi();#Consume campos del formulario Busi
		//_pre($response);exit;

		#crear tags html para formulario
		foreach ($response as $key => $value) {
			$tagHtml[] = $this->_constructTagHtml($value);
		}

		$formHtml = '<div id="content_form"><form id="register_form" >';
		foreach ( $tagHtml as $i => $value) {
			$formHtml.= '<div class="block_'.$i.'">';
			$formHtml.= @$value->label;
			$formHtml.= @$value->input;
			$formHtml.= '</div>';
		}

		$formHtml.= '<div class="block_'.($i+1).'" >';
		$formHtml.= '<div class="box-title" ><span>'. TITLE_ACCEPT_TERMS_FORM .'</span></div>';
        $formHtml.= '<div class="box-input" ><input type="checkbox" id="jFormaterms" name="aterms" value="1" checked> </div>';
        $formHtml.= '</div>';
		$formHtml.= '<div class="block_'.($i+2).'" ><button type="submit" class="btn">&nbsp;</button></div>';
		$formHtml.= '</div></form>';

		return $return->form = $formHtml;
	}

	/**
	* Crear estructura html del formulario BUSI
	*
	* @return Object
	*/
	private function _constructTagHtml($value)
	{
		$tagHtml 	= new stdClass();
		$cbusi 		= $this->_getCredentialsBusi();

		switch ($value->type ) {
			
			case 'text':
			case 'email':

				switch ($value->name) {

					case 'dateBirth':
						$tagHtml->label 	.= '<div class="box-title">';
						$tagHtml->label 	.= '<label>'. $value->label .':</label>';
						$tagHtml->label 	.= '</div>';

						$tagHtml->input 	.= '<div id="datetimepicker" class="input-append data_form_register box-input" >';
						$tagHtml->input 	.= '<input id="jForm' . $value->name . '" data-format="yyyy-MM-dd" type="text" size="22" name="' . $value->name . '" value="" placeholder="' . $value->label . '" autocomplete="off" title="' . $value->label . '" ></input>';
						$tagHtml->input 	.= '<div class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></div>';
						$tagHtml->input 	.= '</div>';
					break;

					case 'email':
						$tagHtml->label 	.= '<div class="box-title">';
						$tagHtml->label 	.= '<label>'. $value->label .':</label>';
						$tagHtml->label 	.= '</div>';

						$tagHtml->input 	.= '<div class="box-input">';
						$tagHtml->input 	.= '<input id="jForm'.$value->name.'" type="email" class="input-email" size="22" name="' . $value->name . '" maxlength="32" value="" placeholder="' . $value->label . '" title="' . $value->label . '" />';
						$tagHtml->input 	.= '</div>';
					break;

					case 'alternativeEmail':
						$tagHtml->label 	.= '<div class="box-title">';
						$tagHtml->label 	.= '<label>'. $value->label .':</label>';
						$tagHtml->label 	.= '</div>';

						$tagHtml->input 	.= '<div class="box-input">';
						$tagHtml->input 	.= '<input id="jForm'.$value->name.'" type="email" class="input-email" size="22" name="' . $value->name . '" maxlength="32" value="" placeholder="' . $value->label . '" title="' . $value->label . '" />';
						$tagHtml->input 	.= '</div>';
					break;

					default:
						$tagHtml->label 	.= '<div class="box-title">';
						$tagHtml->label 	.= '<label>'. $value->label .':</label>';
						$tagHtml->label 	.= '</div>';

						$tagHtml->input 	.= '<div class="box-input">';
						$tagHtml->input 	.= '<input id="jForm'.$value->name.'" type="text" size="22" name="' . $value->name . '" maxlength="32" value="" placeholder="' . $value->label . '" title="' . $value->label . '" />';
						$tagHtml->input 	.= '</div>';
					break;

				}
			break;

			case 'checkbox':

				#name de checkbox
				$name_services = $value->name;
				#_pre($value->name);
				#claroContact
				#electronicTicket

				switch ($value->name) {
					
					case 'claroContact':
					case 'electronicTicket':

						$tagHtml->label 	.= '<div class="box-input span3">';
						$tagHtml->label 	.= '<input type="checkbox" value="1" id="jForm'.$value->name.'" name="'.$value->name.'" />';
						$tagHtml->label 	.= '</div>';
						
						$tagHtml->label 	.= '<div class="box-title-header '.$value->name.'">';
						$tagHtml->label 	.= '<label>' .  $value->label . '</label>';
						$tagHtml->label 	.= '</div>';
					
					break;
					
					default:
					break;
				}

				switch ($value->idField) {

					case '32':
						#Código "32" Indica que el formulario tiene activo servicios
						$tagHtml->label 	.= '<div class="box-title-header '.$value->idServ.'">';
						$tagHtml->label 	.= '<label>' . $value->label . '</label>';
						$tagHtml->label 	.= '</div>';

						$tagHtml->label 	.= '<div class="box-combo-checkbox row">';
						foreach ($this->_getServices($value->idServ) as $key => $value) {
							$tagHtml->label 	.= '<div class="span4 item-services-'.$key.'" >';
							$tagHtml->label 	.= '<div class="box-title span3">';
							$tagHtml->label 	.= '<label>'.$value->value.':</label>';
							$tagHtml->label 	.= '</div>';

							$tagHtml->label 	.= '<div class="box-input span3">';
							$tagHtml->label 	.= '<input type="checkbox" value="' . $value->id . '" id="jForm'.$name_services.'" name="'.$name_services.'" />';
							$tagHtml->label 	.= '</div>';
							$tagHtml->label 	.= '</div>';
						}
						$tagHtml->label 	.= '</div>';

					break;
					
					default:
					# code...
					break;
				}

			break;

			case 'textarea';
				$tagHtml->label 	.= '<div class="box-title">';
				$tagHtml->label 	.= '<label>'. $value->label .':</label>';
				$tagHtml->label 	.= '</div>';

				$tagHtml->input 	.= '<div class="box-input">';
				$tagHtml->input 	.= '<textarea id="jForm'.$value->name.'" name="' . $value->name . '" title="' . $value->label . '" ></textarea>';
				$tagHtml->input 	.= '</div>';
			break;

			case 'radio':
				switch ($value->name) {
					case 'gender':

						$tagHtml->label 	.= '<div class="box-title">';
						$tagHtml->label 	.= '<label>'. $value->label .':</label>';
						$tagHtml->label 	.= '</div>';
						
						$tagHtml->input 	.= '<div class="box-input-male"><label>'. TITLE_GENDER_MALE_FORM .'</label>';
						$tagHtml->input 	.= '<input id="jForm'.$value->name.'" type="radio" name="' . $value->name . '" value="1" checked/>';
						$tagHtml->input 	.= '</div>';

						$tagHtml->input 	.= '<div class="box-input-female"><label>'. TITLE_GENDER_FEMALE_FORM .'</label>';
						$tagHtml->input 	.= '<input id="jForm'.$value->name.'" type="radio" name="' . $value->name . '" value="2" />';
						$tagHtml->input 	.= '</div>';

					break;
				}
			break;

			case 'select':

				switch ($value->name) {
					

					case 'municipality':
					case 'canton':

						$tagHtml->label 	.= '<div class="box-title">';
						$tagHtml->label 	.= '<label>'. $value->label .':</label>';
						$tagHtml->label 	.= '</div>';

						$tagHtml->input 	.= '<div class="box-input">';
						$tagHtml->input 	.= '<select class="span2 jFormmunicipality" id="jForm'.$value->name.'" name="'. $value->name .'" title="' . $value->label . '" >';
						$tagHtml->input 	.= '<option value="" >Municipio</option>';
						$tagHtml->input 	.= '</select>';
						$tagHtml->input 	.= '</div>';
						
					break;

					case 'department':
					case 'province':
						
						$tagHtml->label 	.= '<div class="box-title">';
						$tagHtml->label 	.= '<label>'. $value->label .':</label>';
						$tagHtml->label 	.= '</div>';

						$tagHtml->input 	.= '<div class="box-input">';
						$tagHtml->input 	.= $this->_getHtmlMunicipalityOrDepartment($cbusi->countrybusi,FALSE, $value->name);
						$tagHtml->input 	.= '</div>';

					break;
					
					default:
					break;
				}
			break;
			
			default:
				###############################
			break;
		}
		return $tagHtml;
	}

	/**
	* Consume WS para obtener Municipios o derpartamentos
	*
	* @var int
	* @var boolean, default false
	* @return Object
	*/
	public function _getHtmlMunicipalityOrDepartment($idparent, $type = false, $namelabel )
	{

		$response 			= $this->_getInfoByIdBusi($idparent);#Municipios o departamentos (busqueda por ID)
		$chage_option 		= ( !$type )? '  class="span2 jFormdepartment"  id="jForm'.$namelabel.'" name="'.$namelabel.'" title="'.$namelabel.'" ': ' class="span2 jFormmunicipality" id="jForm'.$namelabel.'" name="'.$namelabel.'" title="'.$namelabel.'" ';

		$selectHtml = '';
		if( !$type ) {
			$selectHtml = '<select'. $chage_option .' >';
			$selectHtml .= '<option value="" >Departamento</option>';
		}
		foreach ($response as $key => $value) {
			$selectHtml.= '<option value="' .$value->id. '" > ' .$value->name. ' </option>';
		}
		if( !$type ) {
			$selectHtml.= '</select>';
		}
		$selectHtml.= '';
		return $selectHtml;
	}

	/**
	* Recibe parametros para armar el correo a enviar
	*
	* @var object
	* @return retorna objeto con estado  y mensaje de envio de mail
	*/
	public function _sendMailBusi( $paramsMail )
	{

		$response = new stdClass();

		$this->mail->IsSendmail();
		$this->mail->SetFrom($paramsMail->setFrom, $paramsMail->setFromName);
		$this->mail->AddAddress($paramsMail->addAddress, $paramsMail->nameAddress);
		$this->mail->Subject    = $paramsMail->subject;
		$this->mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";#optional
		$this->mail->MsgHTML($paramsMail->body);
		
		$response->status 	= $this->mail->Send();
		$response->msnerror = $this->mail->ErrorInfo;
		return $response;
	}
/**
	Consumo Metodos WS Busi
*/
	/**
	* Obtener servicios asignados a formulario BUSI
	*
	* @var Object or array()
	* @return TRUE
	*/
	public function _getServices($id)
	{
		$pbusi 				= array();
		$cbusi 				= $this->_getCredentialsBusi();
		$pbusi['app'] 		= $cbusi->appbusi;
		$pbusi['user'] 		= $cbusi->userbusi;
		$pbusi['pass'] 		= $cbusi->passbusi;
		$pbusi['id'] 		= $id;
		$permsbusi 			= json_encode($pbusi);
		return json_decode( $this->nusoap->call( 'getServices', array( 'params' => $permsbusi ) ) );#user,pass & id 453

	}



   /**
	* Obtener valores por id en tabla BUSI
	*
	* @var Object or array()
	* @return TRUE
	*/
	public function _getValueByIdBusi($id)
	{
		$pbusi 				= array();
		$cbusi 				= $this->_getCredentialsBusi();
		$pbusi['app'] 		= $cbusi->appbusi;
		$pbusi['user'] 		= $cbusi->userbusi;
		$pbusi['pass'] 		= $cbusi->passbusi;
		$pbusi['idValue']	= $id;
		$permsbusi 			= json_encode($pbusi);
		return json_decode( $this->nusoap->call( 'getValue', array( 'params' => $permsbusi ) ) );#Devuele el valor de la de cualquier campo (busqueda por ID)
	}


	/**
	* Obtener valores por id en tabla BUSI
	*
	* @var Object or array()
	* @return TRUE
	*/
	public function _getInfoByIdBusi($id)
	{
		$pbusi 				= array();
		$cbusi 				= $this->_getCredentialsBusi();
		$pbusi['app'] 		= $cbusi->appbusi;
		$pbusi['user'] 		= $cbusi->userbusi;
		$pbusi['pass'] 		= $cbusi->passbusi;
		$pbusi['idParent']	= $id;
		$permsbusi 			= json_encode($pbusi);
		return json_decode( $this->nusoap->call( 'getInfo', array( 'params' => $permsbusi ) ) );#Municipios o departamentos (busqueda por ID)
	}

	/**
	* Recibe valores para almacenar en el BUSI
	*
	* @var Object or array()
	* @return TRUE
	*/
	public function saveInBusi( $params )
	{
		#validar si es array, pasar a objeto
		$pbusi 		= array();
		$defbusi	= array();
		$gettype 	= gettype($params);
		$cbusi 		= $this->_getCredentialsBusi();

		switch ($gettype) {

			case 'object':
			case 'array':
				#Almacenar en BUSI
				$params 			= ( is_object($params) )? (array)$params:$params;
				unset($params['aterms']);
				#$params 			= array();#debug
				$pbusi['app'] 	= $cbusi->appbusi;
				$pbusi['user'] 	= $cbusi->userbusi;
				$pbusi['pass'] 	= $cbusi->passbusi;
				$pbusi 				= array_merge($params, $pbusi);#merge de permisos de usuario y valores de formulario BUSI
				$pbusi 				= json_encode($pbusi);
				$response 			= $this->nusoap->call( 'newSave', array( 'params' => $pbusi ) );#guardar valores en Busi
				#$response 			= $this->nusoap->call( 'formInfo', array( 'params' => $pbusi ) );#Campos del formulario
				#$response 			= $this->nusoap->call( 'getInfo', array( 'params' => $pbusi ) );#Municipios o departamentos (busqueda por ID)
				#$response2		= $this->nusoap->call( 'getServices', array( 'params' => $permsbusi ) );#user,pass & id 453
				//_pre($response,true);exit;
				#$response 			= json_decode($response);
				return $response;
				#_pre($response,true);
			break;
			
			default:
				#Formato de parametro incorrecto (envia email al administrador)
				$userparams 				= $this->_getParamsUserAdmin();
				$paramsMail 	 			= new stdClass();
				$body 			 			= 'Error en el registro de datos.<br />';
				$body 						.= 'Formato no valido en envio de parametros al WS BUSI<br /><br />';
				$paramsMail->subject 		= utf8_decode( 'Error de registro de datos en WS BUSI.' );
				$paramsMail->addAddress 	= $userparams->mail;
				$paramsMail->body  			= $body;
				$paramsMail->nameAddress 	= $userparams->name;
				$paramsMail->setFrom 		= 'info@tpp.com';
				$paramsMail->setFromName 	= 'Soporte';
				$this->_sendMailBusi($paramsMail);
				return FALSE;

			break;

		}
	}

	/**
	* Consumo de WS para obtner campos del formulario
	*
	* @return array
	*/
	public function _getParamsTableBusi()
	{

		$cbusi 			= $this->_getCredentialsBusi();
		$pbusi 			= array();
		$return 		= new stdClass();
		$tagHtml 		= array();
		$pbusi['app'] 	= $cbusi->appbusi;
		$pbusi['user'] 	= $cbusi->userbusi;
		$pbusi['pass'] 	= $cbusi->passbusi;
		//_pre($pbusi);exit;
		$permsbusi 		= json_encode($pbusi);
		
		#_pre( json_decode($this->nusoap->call( 'formInfo', array( 'params' => $permsbusi ) )));
		#exit;

		return json_decode( $this->nusoap->call( 'formInfo', array( 'params' => $permsbusi ) ) );#Campos del formulario
	}

	public function _getCredentialsBusi()
	{

		$response 				= new stdClass();
		$query 					= 'SELECT value FROM '.$this->prefix.'admin_params_property WHERE `id_admin_params_conf`= 1';
		$busi 		 			= @$this->app['dbs']['mysql_silex']->fetchAll($query);
		$response->clientbusi 	= @$busi[0]['value'];
		$response->passbusi 	= @$busi[1]['value'];
		$response->appbusi 		= @$busi[2]['value'];
		$response->userbusi 	= @$busi[3]['value'];
		$response->countrybusi 	= @$busi[4]['value'];

		return $response;
	}

		public function _existInApp($idFb)
	{

		$cbusi 			= $this->_getCredentialsBusi();
		$pbusi 			= array();
		$return 		= new stdClass();
		$tagHtml 		= array();
		$pbusi['app'] 	= $cbusi->appbusi;
		$pbusi['user'] 	= $cbusi->userbusi;
		$pbusi['pass'] 	= $cbusi->passbusi;
		$pbusi['id_fb'] = $idFb;
		//_pre($pbusi);exit;
		$permsbusi 		= json_encode($pbusi);
		//_pre($permsbusi);exit;
		return  $this->nusoap->call( 'existsInAppFbid', array( 'params' => $permsbusi ) ) ;#Retorna 0 si no existe
	}

/**
End WS Busi
*/

}
?>