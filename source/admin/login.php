<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class login extends syController
{	
	function __construct(){
		parent::__construct();
	}
	function index(){
		$this->display("login.html");
	}
	function go(){
		if($this->syArgs("adminuser",1) && $this->syArgs("adminpass",1)){
			if($GLOBALS['G_DY']['vercode']==1){
			if(md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message_a("验证码错误");
			}
			$conditions = array('auser' => $this->syArgs("adminuser",1),'apass' => md5(md5($this->syArgs("adminpass",1)).$this->syArgs("adminuser",1)));
			$r = syDB('admin_user')->find($conditions);
			if(!$r){
				message_a("用户名或密码错误");
			}else{
				$_SESSION['auser'] = array(
					'auser' => $r['auser'],
					'auid' => $r['auid'],
					'level' => $r['level'],
					'gid' => $r['gid'],
					'pclasstype' => $r['pclasstype'],
				);
				jump("?");
			}
		}else{
			message_a("请输入用户名和密码");
		}
	}
	function out(){
		$_SESSION['auser'] = array();
		if (isset($_COOKIE[session_name()])) {setcookie(session_name(), '', time()-42000, '/');}
		session_destroy();
		jump("?c=login");
	}
}	