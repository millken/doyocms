<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_traits extends syController
{
	function __construct(){
		parent::__construct();
		$this->molds=$this->syArgs('molds',1);
		$this->moldname=moldsinfo($this->molds,'moldname');
		$this->Get_c='a_traits&molds='.$this->molds;
		$this->newrow = array(
			'molds' => $this->molds,
			'name' => $this->syArgs('name',1),
		);
	}
	function index(){
		$this->toptxt=$this->moldname.'-推荐属性管理';
		$this->lists = syDB('traits')->findAll(array('molds'=>$this->molds));
		$this->display("traits.html");
	}
	function add(){
		if ($this->syArgs('run')==1){
			if(syDB('traits')->create($this->newrow)){
				message_a("推荐属性创建成功","?c=".$this->Get_c);
			}else{message_a("推荐属性创建失败，请重新提交");}
		}
		$this->toptxt=$this->moldname.'-添加推荐属性';
		$this->postgo='add';
		$this->display("traits.html");
	}
	function edit(){
		$this->d=syDB('traits')->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			if(syDB('traits')->update(array('id'=>$this->d['id']),$this->newrow)){
				message_a("推荐属性修改成功","?c=".$this->Get_c);
			}else{message_a("推荐属性修改失败,请重新提交");}
		}
		$this->toptxt=$this->moldname.'-修改推荐属性';
		$this->postgo='edit';
		$this->display("traits.html");
	}
	function del(){
		$this->toptxt=$this->moldname.'-删除推荐属性';
		$this->d=syDB('traits')->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			if(syDB('traits')->delete(array('id'=>$this->syArgs('id'))))
			{message_a("推荐属性删除成功","?c=".$this->Get_c);}else{message_a("推荐属性删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除推荐属性 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c='.$this->Get_c.'&a=del&run=1&id='.$this->d['id'].'">确定删除</a><a href="?c='.$this->Get_c.'">取消操作</a>';
		$this->display("msg.html");
	}

}	