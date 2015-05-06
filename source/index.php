<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class index extends syController
{
	function __construct(){
		parent::__construct();
		$this->sy_class_type=syClass('syclasstype');
	}
	function index(){
		if($this->syArgs('file',1)){
			$this->custom=custom_html($this->syArgs('file',1));
			if($this->custom['template']!=''){$this->display('custom/'.$this->custom['template']);}else{$this->display("index.html");}
		}else{
			$this->display("index.html");
		}
	}
	function href_session(){
		exit('true,'.date('Ym'));
	}

}