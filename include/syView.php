<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class syView {
	public $engine = null;
	public $displayed = FALSE;
	public function __construct()
	{
		if(FALSE == $GLOBALS['G_DY']['view']['enabled'])return FALSE;
		if(FALSE != $GLOBALS['G_DY']['view']['auto_ob_start'])ob_start();
		$this->engine = syClass($GLOBALS['G_DY']['view']['engine_name'],null,$GLOBALS['G_DY']['view']['engine_path']);
		if( $GLOBALS['G_DY']['view']['config'] && is_array($GLOBALS['G_DY']['view']['config']) ){
			$engine_vars = get_class_vars(get_class($this->engine));
			foreach( $GLOBALS['G_DY']['view']['config'] as $key => $value ){
				if( array_key_exists($key,$engine_vars) )$this->engine->{$key} = $value;
			}
		}
		if( !empty($GLOBALS['G_DY']['sp_app_id']) && isset($this->engine->compile_id) )$this->engine->compile_id = $GLOBALS['G_DY']['sp_app_id'];
		if( empty($this->engine->no_compile_dir) && (!is_dir($this->engine->compile_dir) || !is_writable($this->engine->compile_dir)))__mkdirs($this->engine->compile_dir);
		spAddViewFunction('spUrl', array( 'syView', '__template_spUrl'));
	}

	public function display($tplname)
	{
		try {
				$this->addfuncs();
				$this->displayed = TRUE;
				if($GLOBALS['G_DY']['view']['debugging'] && SP_DEBUG)$this->engine->debugging = TRUE;
				$this->engine->display($tplname);
		} catch (Exception $e) {
			syError( $GLOBALS['G_DY']['view']['engine_name']. ' Error: '.$e->getMessage() );
		}
	}

	public function addfuncs()
	{
		if( is_array($GLOBALS['G_DY']["view_registered_functions"]) ){
			foreach( $GLOBALS['G_DY']["view_registered_functions"] as $alias => $func ){
				if( is_array($func) && !is_object($func[0]) )$func = array(syClass($func[0]),$func[1]);
				$this->engine->registerPlugin("function", $alias, $func);
				unset($GLOBALS['G_DY']["view_registered_functions"][$alias]);
			}
		}
	}

	public function __template_spUrl($params)
	{
		$geturl = basename(__FILE__);
		$controller = $GLOBALS['G_DY']["default_controller"];
		$action = $GLOBALS['G_DY']["default_action"];
		$args = array();
		$anchor = null;
		foreach($params as $key => $param){
			if( $key == $GLOBALS['G_DY']["url_controller"] ){
				$controller = $param;
			}elseif( $key == $GLOBALS['G_DY']["url_action"] ){
				$action = $param;
			}elseif( $key == 'anchor' ){
				$anchor = $param;
			}else{
				$args[$key] = $param;
			}
		}
		return spUrl($geturl, $controller, $action, $args, $anchor);
	}
}