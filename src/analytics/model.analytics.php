<?php
/**
* @version 1.0
* @package Analytics
*/
Class modelAnalytics{
	
	protected $app;
	protected $prefix;

	public function __construct($app,$prefix){
		$this->app = $app;
		$this->prefix = $prefix;
	}


  
 /**
  * Funcion que recibe la llamada a una funcion de analytics, genera la estructura y la retorna
  * con los parametros recibidos
  *
  */ 
  public function _getFunctionAnalytics($nameFunction)
   { 


        $tabfunct=$this->prefix."admin_analytics_functions";
        $tabanaly=$this->prefix."admin_analytics_content";
        $tabanapro=$this->prefix."admin_analytics_property";
         
        $patern = "/{{var}}/";
 
        $query 		= 'SELECT * FROM '.$tabfunct.' where name like "%'.$nameFunction.'%"';
        $function   = $this->app['dbs']['mysql_silex']->fetchAssoc($query);



        if($function){
			        $query 		= 'SELECT * FROM '.$tabanaly.' where id_admin_analytics ='.$function['id_admin_analytics'];
					$params	 	= $this->app['dbs']['mysql_silex']->fetchAll($query);


			         $parameters=array();

					foreach ($params as $row) {

			             $query 				= 'SELECT value FROM `'.$tabanapro.'` WHERE `id_admin_analytics`= '.$function['id_admin_analytics'].' AND `id_admin_analytics_content` = '.$row['id'].' ORDER BY date_register DESC LIMIT 1';
						 $values 				= $this->app['dbs']['mysql_silex']->fetchAssoc($query);
			         
			             if ($row['name']=='structure')
			               	    $structure=$values['value']; 
			             else
			              {    
			                     
			                     $parameters[]=$row['id'];
			              }     

					} 


            $val=array();
            
            foreach ($parameters as $id) {
            	  $query 		= 'SELECT * FROM '.$tabanapro.' where id_admin_analytics='.$function['id_admin_analytics'].' and id_admin_analytics_content='.$id.' and id_admin_function='.$function['id'];
                  $values   = $this->app['dbs']['mysql_silex']->fetchAssoc($query); 
                  $val[]=$values['value'];
            }

			foreach ($val as $value) {
			  	  $structure = preg_replace($patern, $value, $structure, 1); 
			} 
			         
	        return $structure;
      
      }
      else
      	return 'La funcion no existe';

  }
	

	/*public function _gAnalyticsForm()
	{
		
		$query 			= 'SELECT id FROM '.$this->prefix.'admin_params_content WHERE `id_admin_params_conf`= 2';
		$id_content	 	= $this->app['dbs']['mysql_silex']->fetchAssoc($query);
		$id_content 	= $id_content['id'];

		$query 			= 'SELECT value FROM '.$this->prefix.'admin_params_property WHERE `id_admin_params_conf`= 2 AND `id_admin_params_content`= '.$id_content;
		$ua_code 		= $this->app['dbs']['mysql_silex']->fetchAssoc($query);
		$ua_code		= $ua_code['value'];

		$google = '<script type="text/javascript">';
		$google.= "var _gaq = _gaq || []; ";
		$google.= "_gaq.push(['_setAccount', 'UA-{$ua_code}']); ";
		$google.= "_gaq.push(['_trackPageview']); ";
		$google.= '(function() { ';
		$google.= "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ";
		$google.= "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; ";
		$google.= "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); ";
		$google.= "})(); ";
		$google.= '</script>';

		return $google;
	}*/
}
?>