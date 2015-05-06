<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_links extends syController
{
	function __construct(){
		parent::__construct();
		$this->Gets=$this->syArgs('a',1);
		$this->Class=syClass('c_links');
		$this->opers='<a href="?c=a_links">链接分类管理</a><a href="?c=a_links&a=tadd">添加链接分类</a><a href="?c=a_links&a=adlist">链接管理</a><a href="?c=a_links&a=add">添加链接</a>';
		
		if($this->Gets=='add' || $this->Gets=='edit'){
			$this->newrow = array(
				'taid' => $this->syArgs('taid'),
				'name' => $this->syArgs('name',1),
				'image' => $this->syArgs('image',1),
				'gourl' => $this->syArgs('gourl',1),
				'isshow' => $this->syArgs('isshow'),
				'orders' => $this->syArgs('orders'),
			);
		}
		if($this->Gets=='tadd' || $this->Gets=='tedit'){
			$this->newrow = array(
				'name' => $this->syArgs('name',1),
			);
		}
	}
	function index(){
		$this->toptxt='链接分类管理';
		$this->lists = syDB('linkstype')->findAll();
		$this->display("links.html");
	}
	function adlist(){
		$this->toptxt='链接管理';
		if($this->syArgs('taid'))$c=array('taid'=>$this->syArgs('taid'));
		$this->lists = $this->Class->findAll($c,' orders desc,id desc ');
		$this->display("links.html");
	}
	function tadd(){
		if ($this->syArgs('run')==1){
			if($this->newrow['name']=='') message_a("链接分类名称不能为空");
			if(syDB('linkstype')->create($this->newrow)){
				message_a("链接分类创建成功","?c=a_links");
			}else{message_a("链接分类创建失败，请重新提交");}
		}
		$this->toptxt='添加链接分类';
		$this->postgo='tadd';
		$this->display("links.html");
	}
	function tedit(){
		$this->d=syDB('linkstype')->find(array('taid'=>$this->syArgs('taid')));
		if ($this->syArgs('run')==1){
			if($this->newrow['name']=='') message_a("链接分类名称不能为空");
			if(syDB('linkstype')->update(array('taid'=>$this->d['taid']),$this->newrow)){
				message_a("链接分类修改成功","?c=a_links");
			}else{message_a("链接分类修改失败,请重新提交");}
		}
		$this->toptxt='修改链接分类';
		$this->postgo='tedit';
		$this->display("links.html");
	}
	function tdel(){
		$this->toptxt='删除链接分类';
		$this->d=syDB('linkstype')->find(array('taid'=>$this->syArgs('taid')));
		if ($this->syArgs('run')==1){
			syDB('links')->delete(array('taid'=>$this->syArgs('taid')));
			if(syDB('linkstype')->delete(array('taid'=>$this->syArgs('taid'))))
			{message_a("链接分类删除成功","?c=a_links");}else{message_a("链接分类删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除链接分类 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='删除链接分类，将自动删除本链接分类下的所有链接，删除后不可恢复';
		$this->msggo='<a href="?c=a_links&a=tdel&run=1&taid='.$this->d['taid'].'">确定删除</a><a href="?c=a_links">取消操作</a>';
		$this->display("msg.html");
	}
	function add(){
		$this->linkstype=syDB('linkstype')->findAll();
		if($this->syArgs('taid'))$this->ctaid=$this->syArgs('taid');
		if ($this->syArgs('run')==1){
			$newVerifier=$this->Class->syVerifier($this->newrow);
			if(false == $newVerifier){
				if(syDB('links')->create($this->newrow)){
					deleteDir($GLOBALS['G_DY']['sp_cache']);
					message_a("链接创建成功","?c=a_links&a=adlist");
				}else{message_a("链接创建失败，请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='添加链接';
		$this->postgo='add';
		$this->display("links.html");
	}
	function edit(){
		$this->linkstype=syDB('linkstype')->findAll();
		$this->d=syDB('links')->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			$newVerifier=$this->Class->syVerifier($this->newrow);
				if(false == $newVerifier){
				if(syDB('links')->update(array('id'=>$this->d['id']),$this->newrow)){
					deleteDir($GLOBALS['G_DY']['sp_cache']);
					message_a("链接修改成功","?c=a_links&a=adlist");
				}else{message_a("链接修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='修改链接';
		$this->postgo='edit';
		$this->display("links.html");
	}
	function del(){
		$this->toptxt=$this->moldname.'删除链接';
		$this->d=syDB('links')->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			if(syDB('links')->delete(array('id'=>$this->syArgs('id')))){
				deleteDir($GLOBALS['G_DY']['sp_cache']);
				message_a("链接删除成功","?c=a_links&a=adlist");
			}else{message_a("链接删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删链接 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c=a_links&a=del&run=1&id='.$this->d['id'].'">确定删除</a><a href="?c=a_links&a=adlist">取消操作</a>';
		$this->display("msg.html");
	}

}	