<?php
/**
 * Factory .
 *
 * @author Jcbarreno <jcbarreno.tpp@gmail.com>
 * @version 1.0
 * @package Modelo login
 */
class modelTab extends modelAplication{

	protected $mail;
	protected $app;
	protected $prefix;
	protected $logError;
	protected $image;
	/**
	* Recibe instancia de phpMailer
	*
	* @var Instancia phpMailer
	* @return null
	*/
	public function __construct( $phpMailer, $app, $prefix, $logError, $image )
	{
		#instancia phpMailer
		$this->mail 	= $phpMailer;
		$this->app 		= $app;
		$this->prefix 	= $prefix;
		$this->logError	= $logError;
		$this->image	= $image;
	}

	/**
	* registra pestaña
	* @var array
	* @var int
	* @return object
	*/
	public function _saveTab($params, $option )
	{

		_pre($params);
		exit;


		$response 		= new stdClass();
		$params 		= $this->_sanitizeVar($params,true);
		$content_params = new stdClass();

		switch ($option) {
			
			case 0: #pestaña normal 1 imagen

				$upload 				= $this->_uploadImage($_FILES['uploadr_imagen_pestana']);
				$tab 					= "{$this->prefix}p_tab";
				$date_reg				= date("Y-m-d H:i:s");

				try{

					$query 					= "INSERT INTO {$tab} (name, date ) VALUE( '{$params->nombre_pestana}', '{$date_reg}' )";
					$this->app['db']->executeQuery($query);
					$id_tab 				= $this->app['db']->lastInsertId('id');
					$content_params->id_tab = $id_tab;

				}catch(Exception $e){

					$response->status 	= FALSE;
					$response->message 	= "Error #01: No se pudo insertar en en la tabla p_tab.";
					return $response;

				}	
					
				$detail 						= $this->_registerDetailTab($upload, $id_tab, $_POST['pais'], 0 );
				if( $detail->status ):
					$content_params->id_detail 	= $detail->id_detail;
					$content_params->img_name 	= $upload->name;
					$content_params->id_ctype	= 1;
					$content 					= $this->_saveContentTab($content_params);
					$response->status 			= $content->status;
					$response->message 			= $content->message;
				else:
					$response->status 	= FALSE;
					$response->message 	= "Error #02: No se pudo insertar en en la tabla p_tab_detail.";
				endif;


			break;
			
			default:
			break;
		}
		return $response;
	}

	/**
	* Registra en tabla contenido
	* @var object
	* @return object
	*/
	public function _saveContentTab($cparams)
	{
		$tab 				= "{$this->prefix}p_content";
		$response 			= new stdClass();
		$response->message 	= '';
		try{
			$query 	= "INSERT INTO {$tab} ( c_img_tab, id_content_type, id_detail, id_tab ) VALUE( '{$cparams->img_name}', {$cparams->id_ctype}, {$cparams->id_detail}, {$cparams->id_tab} )";
			$this->app['db']->executeQuery($query);
			$this->app['db']->lastInsertId('id');
			$response->status = TRUE;
		}catch(Exception $e){
			$response->status = FALSE;
			$response->message = "Error #03: No se pudo insertar en en la tabla p_content.";
		}
		return $response;
	}

	/**
	* Registra en tabla detalle de la tab
	* @var object
	* @var int
	* @return object
	*/
	public function _registerDetailTab($upload=null, $id_tab, $country, $option )
	{
		$tab 	= "{$this->prefix}p_tab_detail";
		$return = new stdClass();

		switch ($option) {
			
			case 0:
				$token_id	= encode($id_tab);
				try{
					$query = "INSERT INTO {$tab} ( tab_url, tab_url_secure, fan, id_tab, id_country )";
					$query.= "VALUE ('{$token_id}', '{$token_id}', 0, {$id_tab}, {$country} )";
					$this->app['db']->executeQuery($query);
					$return->id_detail 	= $this->app['db']->lastInsertId('id');
					$return->status 	= TRUE;
				}catch(Exception $e){
					$return->status 	= FALSE;
				}

			break;
			
			default:
			break;
		}

		return $return;
		
	}

	/**
	* Suber Imagen al servidor
	* @var array
	* @return object
	*/
	public function _uploadImage($file)
	{
		$path				= PATH_ROOT_WEB.DS.'tmp'.DS;
		$type 				= explode('/', $file['type']);
		$list_format 		= array("jpg", "jpeg", "png", "gif" );
		$imagen 			= new stdClass();
		$imagen->temp_name	= $file['tmp_name'];
		$imagen->type 		= $type[1];
		$imagen->name 		= md5(date('Y-m-d H:i:s')).'.'.$imagen->type;
		$imagen->date 		= date('Y-m-d H:i:s');

		if( in_array( $imagen->type, $list_format ) ){

			if ( is_uploaded_file($imagen->temp_name) ){
				

				if( move_uploaded_file( $imagen->temp_name , $path.$imagen->name ) ){

					$this->image->open($path.$imagen->name);
					$this->image->resize(200, 200)->save($path.'thumb/'.'thumb_'.$imagen->name, $imagen->type);
					$imagen->thumb_name = 'thumb_'.$imagen->name;
					return $imagen;

				}
			}

		}		
	}

	/**
	* Load info tab
	*
	* @return string
	*/
	public function _loadListTab()
	{

		$port 		= ( $_SERVER['SERVER_PORT'] != 443 )? 'http://':'https://';
		$path 		= explode( 'index.php', $_SERVER['SCRIPT_NAME'] );
		$path 		= $port.$_SERVER['SERVER_NAME'].$path[0];
		$table 		= "{$this->prefix}p_tab";
		$query 		= "SELECT * FROM {$table} ORDER BY id DESC";
		$tab_name  	= $this->app['db']->fetchAll($query);

		$tab_html 	= '<table id="datos-usuarios" class="table table-bordered">
						<thead>
							<tr>
								<th>No.</th>
								<th>Pais</th>
								<th>Nombre</th>
								<th>Imagen</th>
								<th>Detalle</th>
								<th>Estado</th>
							</tr>
						</thead>';
						
						
		$tab_html.= '<tbody>';

		foreach ($tab_name as $key => $value) {

			$tab_detail 		= $this->_getTabDetail($value['id']);



			$path_image 		= $path.'tmp/'.$tab_detail['image'];
			$path_image_thumb 	= $path.'tmp/thumb/thumb_'.$tab_detail['image'];

			$tab_html.= '<tr>';
			$tab_html.= '<td>'.($key+1).'</td>';
			$tab_html.= '<td>'.$tab_detail['label'].'</td>';
			$tab_html.= '<td>'.$value['name'].'</td>';
			$tab_html.= '<td><center><img class="thumb_pestana" width="20%" src="'.$path_image_thumb.'" title="'.$path_image.'" /></center></td>';
			$tab_html.= '<td>';
			$tab_html.= '<a class="tab-detail" href="#Detalle"><center><span class="glyphicon glyphicon-th-list"></span> Detalle</center></a>';

			#tabla de detalles de la pestaña
			$tab_html.= '<ul style="display: none;" >';
			
			$tab_html.= '<li>';
			$tab_html.= '<span><b>Fecha Creación:</b> '.$value['date'].'</span>';
			$tab_html.= '</li>';

			$tab_html.= '<li>';
			$tab_html.= '<span><b>Pais:</b> '.$tab_detail['label'].'</span>';
			$tab_html.= '</li>';
			
			$tab_html.= '<li>';
			$tab_html.= '<span><b>Nombre Pestaña:</b> '.$value['name'].'</span>';
			$tab_html.= '</li>';

			$tab_html.= '<li>';
			$tab_html.= '<span><b>Url:</b> '.$path.'index.php/fanpage/'.$tab_detail['tab_url'].'</span>';
			$tab_html.= '</li>';

			$tab_html.= '</ul>';
			#fin tabla de detalle

			$tab_html.= '</td>';
			$tab_html.= '<td>'.(($value['state'] == 1 )?'<img id="item-'.encode($value['id']).'" class="tab-status" src="'.$path.'img/tick.png" />':'<img id="item-'.encode($value['id']).'" class="tab-status" src="'.$path.'img/publish_x.png" />').'</td>';
			$tab_html.= '</tr>';
		}

		$tab_html.= '</tbody>';
		$tab_html.= '</table>';

		return $tab_html;
	}

	/**
	* Load detail table
	* @var int
	* @return object
	*/
	public function _getTabDetail($id)
	{
		$tab_detail 	= "{$this->prefix}p_tab_detail";
		$tab_country 	= "{$this->prefix}p_country";

		$query.= "SELECT dt.id, dt.id_app, dt.tab_url, cy.label ";

		$query.= ', CASE dt.fan ';
		$query.= 'WHEN 1 ';
		$query.= 'THEN "' .$this->_getTabContent($id,1). '" ';
		$query.= 'ELSE ';
		$query.= ' "' .$this->_getTabContent($id). '" ';
		$query.= 'END AS image ';

		$query.= "FROM {$tab_detail} AS dt ";
		$query.= "LEFT JOIN {$tab_country} AS cy ";
		$query.= "ON dt.id_country = cy.id ";
		$query.= "WHERE id_tab = {$id} ";

		$tab_detail  	= $this->app['db']->fetchAll($query);

		return $tab_detail[0];
	}

	/**
	* Load content table
	* @var int
	* @var int
	* @return string
	*/
	public function _getTabContent($idtab, $fan=0)
	{
		$tab_content 	= "{$this->prefix}p_content";

		switch ($fan) {
			case 1:
				$query 		= "SELECT * FROM {$tab_content} WHERE id_tab = {$idtab}";
			break;
			
			default:
				$query 		= "SELECT * FROM {$tab_content} WHERE id_tab = {$idtab}";
			break;
		}
		$content  	= $this->app['db']->fetchAll($query);

		foreach ($content as $key => $value) {
			$response = $value['c_img_tab'];
		}

		return $response;
	}

	/**
	* Change status tab
	* @var int
	* @return string
	*/
	public function _changeStatusTab( $itemId )
	{

		$response 	= new stdClass();
		$tab 		= "{$this->prefix}p_tab";
		$query 		= "SELECT state FROM {$tab} WHERE id = {$itemId}";
		$status 	= $this->app['db']->fetchAssoc($query);

		switch ($status['state']) {
			
			case 0: #is inactive
				$query 	= "UPDATE {$tab} SET state = 1 WHERE id = ".$itemId;
				$result = $this->app['db']->executeQuery($query);
				$response->status = (!$result)? 'Error':'Success';
				$response->action = 'active';
			break;
			
			
			case 1: #is active
			default:
				$query 	= "UPDATE {$tab} SET state = 0 WHERE id = ".$itemId;
				$result = $this->app['db']->executeQuery($query);
				$response->status = (!$result)? 'Error':'Success';
				$response->action = 'inactive';
			break;
		}
		return $response;
	}


	/**
	* valida tipo de tab
	* @var int
	* @return string
	*/
	public function selectQueryGetHtmlTab( $id )
	{
		$resp 			= new stdClass();
		$id 			= decode($id);
		$tab_content 	= "{$this->prefix}p_content";
		$tab 			= "{$this->prefix}p_tab";
		$query 			= "SELECT id_content_type FROM {$tab_content} WHERE id_tab = {$id}";
		$html 			= $this->app['db']->fetchAssoc($query);

		$resp->type = $html['id_content_type'];
		
		if ( $html['id_content_type'] == 2 ){

			$query 	= "SELECT c.c_html FROM {$tab_content} c, {$tab} t WHERE c.id_tab  = {$id} AND t.id = c.id_tab AND t.state = 1";
			$q 		= $this->app['db']->fetchAssoc($query);

			if ( !$q ){
				$resp->status = false;
			} else {
				$resp->html = $q['c_html'];	
				$resp->status = true;
			}
			

		}
		return $resp;
	}

	public function selectQueryGetImageTab( $id )
	{
		
		$resp 			= new stdClass();
		$id 			= decode($id);
		$tab_content 	= "{$this->prefix}p_content";
		$tab_detail 	= "{$this->prefix}p_tab_detail";
		$tab 			= "{$this->prefix}p_tab";
		$query 			= "SELECT fan FROM {$tab_detail} WHERE id_tab = {$id} " ;
		$fan 			= $this->app['db']->fetchAssoc($query);
		
			if ( $fan['fan'] == 1 ){

				$query 	= "SELECT c.c_img_fan, c.c_img_no_fan FROM {$tab_content} c, {$tab} t WHERE c.id_tab  = {$id} AND t.id = c.id_tab AND t.state = 1";
				$q 		= $this->app['db']->fetchAssoc($query);

				if( !$q ){
					$resp->type 	= '2';
				} else {
					$resp->type 	= '1';
					$resp->imgnofan = $q['c_img_fan'];
					$resp->imgfan 	= $q['c_img_no_fan'];
				}

				return $resp;

			} else {

				$query 	= "SELECT c.c_img_tab FROM {$tab_content} c, {$tab} t WHERE c.id_tab  = {$id} AND t.id = c.id_tab AND t.state = 1";
				$q 		= $this->app['db']->fetchAssoc($query);

				if( !$q ){
					$resp->type 	= '2';
				} else {
					$resp->type 	= '0';
					$resp->imgtab 	= $q['c_img_tab'];
				}

 				return $resp;

			}

		
	}

	public function parsePageSignedRequest()
	{
		if (isset($_REQUEST['signed_request'])) {
			
			$encoded_sig 	= null;
			$payload 		= null;
			list($encoded_sig, $payload) = explode('.', $_REQUEST['signed_request'], 2);
			$sig 	= base64_decode(strtr($encoded_sig, '-_', '+/'));
			$data 	= json_decode(base64_decode(strtr($payload, '-_', '+/'), true));

			return $data;
		}

		return false;		
	}

}
?>