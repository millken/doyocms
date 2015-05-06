<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_funs extends syController
{
	function __construct(){
		parent::__construct();
		$this->Get_c='a_funs';
		$this->a=$this->syArgs('a',1);
		$this->db=$GLOBALS['G_DY']['db']['prefix'];
		$this->newrow = array(
			'name' => $this->syArgs('name',1),
			'isshow' => $this->syArgs('isshow'),
		);
	}
	function index(){
		$this->toptxt='插件管理';
		$this->lists = syDB('funs')->findAll(array('isshow'=>1));
		$this->lists_no = syDB('funs')->findAll(array('isshow'=>0));
		$this->display("funs.html");
	}
	function edit(){
		$this->d=syDB('funs')->find(array('fid'=>$this->syArgs('fid')));
		if ($this->syArgs('run')==1){
			if(syDB('funs')->update(array('fid'=>$this->d['fid']),$this->newrow)){
				echo "<script>window.parent.left.location.reload();</script>";
				message_a("插件修改成功","?c=".$this->Get_c);
			}else{message_a("插件修改失败,请重新提交");}
		}
		$this->toptxt='修改插件';
		$this->postgo='edit';
		$this->display("funs.html");
	}
}	