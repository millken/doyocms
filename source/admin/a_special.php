<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_special extends syController
{
	function __construct(){
		parent::__construct();
		$this->opers='<a href="?c=a_special">专题管理</a><a href="?c=a_special&a=add">添加专题</a>';
		$this->ClassS=syClass('c_special');
		$this->auser=syClass('syauser');
		$this->chtml = syClass("syhtml");
		$this->moldsop=syDB('molds')->findAll(array('isshow'=>1));
		$this->Get_c='a_special';
		$_SESSION['gos']=date("His").mt_rand(1000,9999);
		if($this->syArgs('title',1)){$title=$this->syArgs('title',1);}else{$title=$this->syArgs('name',1);}
		if($this->syArgs('htmldir',1)!='/'){$htmldir=trim($this->syArgs('htmldir',1),'/');}else{$htmldir=$this->syArgs('htmldir',1);}
		$this->newrow = array(
			'molds' => $this->syArgs('molds',1),
			'name' => $this->syArgs('name',1),
			'gourl' => $this->syArgs('gourl',1),
			'litpic' => $this->syArgs('litpic',1),
			'title' => $title,
			'keywords' => $this->syArgs('keywords',1),
			'description' => $this->syArgs('description',1),
			'isindex' => $this->syArgs('isindex'),
			't_index' => $this->syArgs('t_index',1),
			't_list' => $this->syArgs('t_list',1),
			't_listb' => $this->syArgs('t_listb',1),
			'listnum' => $this->syArgs('listnum'),
			'htmldir' => strtolower($htmldir),
			'htmlfile' => strtolower($this->syArgs('htmlfile',1)),
			'orders' => $this->syArgs('orders'),
			'body' => $this->syArgs('body',4),
			'isshow' => $this->syArgs('isshow'),
		);
	}
	function index(){
		$this->toptxt='专题管理';
		$this->lists = $this->ClassS->findAll();
		$this->display("special.html");
	}
	function add(){
		if ($this->syArgs('run')==1){
			$newVerifier=$this->ClassS->syVerifier($this->newrow);
				if(false == $newVerifier){
					if($this->ClassS->create($this->newrow)){
						if(syExt('site_html')==1){
							if($this->newrow['htmldir']==''){
								$c_html_f=$GLOBALS['WWW'].syExt('site_html_dir').'/s/'.$addnew.'/';
							}else{
								$c_html_f=$this->newrow['htmldir'].'/';
							}
							if($this->newrow['htmlfile']==''){
								$c_html_f.='index'.syExt('site_html_suffix');
							}else{
								$c_html_f.=$this->newrow['htmlfile'].syExt('site_html_suffix');
							}
							$this->chtml->c_special(array('sid'=>$addnew),$c_html_f);
						}
						deleteDir($GLOBALS['G_DY']['sp_cache']);
						message_a("专题创建成功","?c=".$this->Get_c);
					}else{message_a("专题创建失败，请重新提交");}
				}else{message_b($newVerifier);}
		}
		$this->toptxt='添加专题';
		$this->postgo='add';
		$this->display("special_edit.html");
	}
	function edit(){
		$this->carray=$this->ClassS->find(array('sid'=>$this->syArgs('sid')));
		if ($this->syArgs('run')==1){
			$newVerifier=$this->ClassS->syVerifier($this->newrow);
			if(false == $newVerifier){
				if($this->ClassS->update(array('sid'=>$this->carray['sid']),$this->newrow)){
						if(syExt('site_html')==1){
							if($this->newrow['htmldir']==''){
								$c_html_f=$GLOBALS['WWW'].syExt('site_html_dir').'/s/'.$this->carray['sid'].'/';
							}else{
								$c_html_f=$this->newrow['htmldir'].'/';
							}
							if($this->newrow['htmlfile']==''){
								$c_html_f.='index'.syExt('site_html_suffix');
							}else{
								$c_html_f.=$this->newrow['htmlfile'].syExt('site_html_suffix');
							}
							$this->chtml->c_special( array('sid'=>$this->carray['sid']),$c_html_f);
						}
					deleteDir($GLOBALS['G_DY']['sp_cache']);
					message_a("专题修改成功","?c=".$this->Get_c);
				}else{message_a("专题修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='修改专题';
		$this->postgo='edit';
		$this->display("special_edit.html");
	}
	function del(){
		$this->toptxt='删除专题';
		$this->d=$this->ClassS->find(array('sid'=>$this->syArgs('sid')));
		$sid=$this->d['sid'];
		if ($this->syArgs('run')==1){
			if($this->ClassS->delete(array('sid'=>$sid))){
				deleteDir($GLOBALS['G_DY']['sp_cache']);
				message_a("专题删除成功","?c=".$this->Get_c);
			}else{message_a("专题删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除专题 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c='.$this->Get_c.'&a=del&run=1&sid='.$sid.'">确定删除</a><a href="?c='.$this->Get_c.'">取消操作</a>';
		$this->display("msg.html");
	}

}	