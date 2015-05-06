<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class syController { 
	public $v;
	private $__template_vals = array();
	public function __construct()
	{	
		if(TRUE == $GLOBALS['G_DY']['view']['enabled']){
			$this->v = syClass('syView');
		}
	}

	public function __set($name, $value)
	{
		if(TRUE == $GLOBALS['G_DY']['view']['enabled'] && false !== $value){
			$this->v->engine->assign(array($name=>$value));
		}
		$this->__template_vals[$name] = $value;
	}

	public function __get($name)
	{
		return $this->__template_vals[$name];
	}

	public function display($tplname, $output = TRUE)
	{
		@ob_start();
		if(TRUE == $GLOBALS['G_DY']['view']['enabled']){
			$this->v->display($tplname);
		}else{
			extract($this->__template_vals);
			require($tplname);
		}
		if( TRUE != $output )return ob_get_clean();
	}

	public function auto_display($tplname)
	{
		if( TRUE != $this->v->displayed && FALSE != $GLOBALS['G_DY']['view']['auto_display']){
			if( method_exists($this->v->engine, 'templateExists') && TRUE == $this->v->engine->templateExists($tplname))$this->display($tplname);
		}
	}

	public function __call($name, $args)
	{
		if(in_array($name, $GLOBALS['G_DY']["auto_load_controller"])){
			return syClass($name)->__input($args);
		}elseif(!method_exists( $this, $name )){
			syError("方法 {$name}未定义！<br />请检查是否控制器类(".get_class($this).")与数据模型类重名？");
		}
	}

	public function getView()
	{
		$this->v->addfuncs();
		return $this->v->engine;
	}
	
}

class syArgs {
	private $args = null;
	public function __construct(){
		$this->args = $_REQUEST;
		$this->mescape=syClass('syModel');
	}

	public function get($name = null, $ftype = 0, $default = FALSE, $method = null)
	{
		if(null != $name){
			if( $this->has($name) ){
				if( null != $method ){
					switch (strtolower($method)) {
						case 'get':
							return $this->filters($_GET[$name]);
						case 'post':
							return $this->filters($_POST[$name]);
						case 'cookie':
							return $this->filters($_COOKIE[$name]);
					}
				}
			return $this->filters($ftype,$this->args[$name]);
			}else{
				return (FALSE === $default) ? FALSE : $default;
			}
		}else{
			return $this->args;
		}
	}

	public function set($name, $value)
	{
		$this->args[$name] = $value;
	}

	public function filters($ftype,$value)
	{
		switch ($ftype) {
				case 0://整数
					return (int)$value;
				case 1://字符串
					$value=htmlspecialchars(trim($value), ENT_QUOTES);
					if(!get_magic_quotes_gpc())$value = addslashes($value);
					return $value;
				case 2://数组
					if($value=='')return '';
					array_walk_recursive($value, '$this->arrays');
					return $value;
				case 3://浮点
					return (float)$value;
				case 4:
					if(!get_magic_quotes_gpc())$value = addslashes($value);
					return trim($value);
		}
	}
	
	public function arrays(&$item, $key)
	{
		$item=trim($item);
		$item=htmlspecialchars($item, ENT_QUOTES);
		if(!get_magic_quotes_gpc())$item = addslashes($item);
	}

	public function has($name)
	{
		return isset($this->args[$name]);
	}

	public function __input($args = -1)
	{
		if( -1 == $args )return $this;
		@list( $name, $default, $method ) = $args;
		return $this->get($name, $default, $method);
	}

	public function request(){
		return $_SERVER["QUERY_STRING"];
	}
}

