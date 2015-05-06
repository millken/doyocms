<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_labelcus extends syController
{
	function __construct(){
		parent::__construct();
		$this->Class=syClass('c_labelcus');
		$this->custom_Class=syClass('c_custom');
		$this->newrow = array(
			'name' => $this->syArgs('name',1),
			'body' => $this->syArgs('body',4),
			'type' => $this->syArgs('type'),
		);
	}
	function index(){
		$this->toptxt='自定义模板标签管理';
		$this->lists = $this->Class->findAll(null,null,'id,name');
		$this->display("labelcus.html");
	}
	function add(){
		if ($this->syArgs('run')==1){
			$newVerifier=$this->Class->syVerifier($this->newrow);
			if(false == $newVerifier){
				if($this->Class->create($this->newrow)){
					message_a("自定义模板标签创建成功","?c=a_labelcus");
				}else{message_a("自定义模板标签创建失败，请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='添加自定义模板标签';
		$this->postgo='add';
		$this->display("labelcus.html");
	}
	function edit(){
		$this->d=$this->Class->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			$newVerifier=$this->Class->syVerifier($this->newrow);
			if(false == $newVerifier){
				deleteDir($GLOBALS['G_DY']['sp_cache']);
				if($this->Class->update(array('id'=>$this->syArgs('id')),$this->newrow)){
					$this->Class->syCache(-1)->find(array('id'=>$this->syArgs('id')));
					message_a("自定义模板标签修改成功","?c=a_labelcus");
				}else{message_a("自定义模板标签修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='修改自定义模板标签';
		$this->postgo='edit';
		$this->display("labelcus.html");
	}
	function del(){
		$this->toptxt='删除自定义模板标签';
		$this->d=$this->Class->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			deleteDir($GLOBALS['G_DY']['sp_cache']);
			if($this->Class->delete(array('id'=>$this->syArgs('id'))))
			{message_a("自定义模板标签删除成功","?c=a_labelcus");}else{message_a("自定义模板标签删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除自定义模板标签 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c=a_labelcus&a=del&run=1&id='.$this->d['id'].'">确定删除</a><a href="?c=a_labelcus">取消操作</a>';
		$this->display("msg.html");
	}
	function custom_index(){
		$this->toptxt='自定义页面管理';
		$this->lists = syDB('custom')->findAll();
		$this->display("custom.html");
	}
	function custom_add(){
		if ($this->syArgs('run')==1){
			$this->newr = array(
				'name' => $this->syArgs('name',1),
				'dir' => $this->syArgs('dir',1),
				'template' => $this->syArgs('template',1),
				'file' => $this->syArgs('file',1),
				'html' => $this->syArgs('html')
			);
			$newVerifier=$this->custom_Class->syVerifier($this->newr);
			if(false == $newVerifier){
				if($this->custom_Class->create($this->newr)){
					message_a("自定义页面创建成功","?c=a_labelcus&a=custom_index");
				}else{message_a("自定义页面创建失败，请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='添加自定义页面';
		$this->postgo='custom_add';
		$this->display("custom.html");
	}
	function custom_edit(){
		$this->d=$this->custom_Class->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			$this->newr = array(
				'name' => $this->syArgs('name',1),
				'dir' => $this->syArgs('dir',1),
				'template' => $this->syArgs('template',1),
				'file' => $this->syArgs('file',1),
				'html' => $this->syArgs('html')
			);
			$newVerifier=$this->custom_Class->syVerifier($this->newr);
			if(false == $newVerifier){
				if($this->custom_Class->update(array('id'=>$this->syArgs('id')),$this->newr)){
					$this->custom_Class->syCache(-1)->find(array('id'=>$this->syArgs('id')));
					message_a("自定义页面修改成功","?c=a_labelcus&a=custom_index");
				}else{message_a("自定义页面修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='修改自定义页面';
		$this->postgo='custom_edit';
		$this->display("custom.html");
	}
	function custom_del(){
		$this->toptxt='删除自定义页面';
		$this->d=$this->custom_Class->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			if($this->custom_Class->delete(array('id'=>$this->syArgs('id'))))
			{message_a("自定义页面删除成功","?c=a_labelcus&a=custom_index");}else{message_a("自定义页面删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除自定义页面 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c=a_labelcus&a=custom_del&run=1&id='.$this->d['id'].'">确定删除</a><a href="?c=a_labelcu&a=custom_index">取消操作</a>';
		$this->display("msg.html");
	}
	function custom_html(){
		$a=syDB('custom')->findAll(array('html'=>1));
		foreach($a as $v){
			if($v['dir']==''){
				$c_html_f=syExt("site_html_dir").'/';
			}else{
				$c_html_f=$v['dir'].'/';
			}
			$c_html_f.=$v['file'];
			syClass('syhtml')->c_labelcus_custom(array('file'=>$v['file']),$c_html_f);
		}
		message_a("自定义页面生成完成","?c=a_labelcus&a=custom_index");
	}
}	