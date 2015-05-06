<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class index extends syController
{
	function __construct(){
		parent::__construct();
		$this->c=syClass('syauser');
		$this->m=syDB('molds')->findAll(array('isshow'=>1),' orders desc,mid ','mid,molds,moldname,sys');
		$this->funs=syDB('funs')->findAll(array('isshow'=>1),null,'fid,funs,name');
	}
	function index(){
		$this->display("index.html");
	}
	function headers(){
		$this->a='headers';
		$this->display("main.html");
	}
	function main(){
		$this->a='main';
		$this->display("main.html");
	}
	function left(){
		$this->a='left';
		switch($this->syArgs("l",1)){
			case "classtypes":
			$this->lefttop='栏目管理';
			if($this->c->checkgo('a_classtypes'))$this->leftmenu.='<a href="?c=a_classtypes" target="main">栏目管理</a>';
			if($this->c->checkgo('a_classtypes'))$this->leftmenu.='<a href="?c=a_classtypes&a=add" target="main">添加栏目</a>';
			break;
			case "molds":
			$this->lefttop='频道管理';
			if($this->c->checkgo('a_molds'))$this->leftmenu.='<a href="?c=a_molds" target="main">频道管理</a>';
			if($this->c->checkgo('a_molds'))$this->leftmenu.='<a href="?c=a_molds&a=add" target="main">添加频道</a>';
			break;
			case "funs":
			$this->lefttop='其他管理';
			foreach($this->funs as $v){
				if($this->c->checkgo('a_'.$v['funs']))$this->leftmenu.='<a href="?c=a_'.$v['funs'].'" target="main">'.$v['name'].'管理</a>';
			}
			if($this->c->checkgo('a_funs'))$this->leftmenu.='<p></p><a href="?c=a_funs" target="main">插件安装卸载</a>';
			break;
			case "sys":
			$this->lefttop='系统管理';
			if($this->c->checkgo('a_sys'))$this->leftmenu.='<a href="?c=a_sys" target="main">系统设置</a>';
			if($this->c->checkgo('a_adminuser'))$this->leftmenu.='<a href="?c=a_adminuser" target="main">管理员管理</a>';
			$this->leftmenu.='<p></p>';
			if($this->c->checkgo('a_template'))$this->leftmenu.='<a href="?c=a_template" target="main">选择模板</a>';
			if($this->c->checkgo('a_labelcus'))$this->leftmenu.='<a href="?c=a_labelcus" target="main">自定义模板标签</a>';
			if($this->c->checkgo('a_labelcus'))$this->leftmenu.='<a href="?c=a_labelcus&a=custom_index" target="main">自定义页面</a>';
			$this->leftmenu.='<a href="?c=a_label" target="main">模板调用生成器</a>';
			$this->leftmenu.='<p></p>';
			if($this->c->checkgo('a_html'))$this->leftmenu.='<a href="?c=a_html" target="main">更新静态html</a>';
			if($this->c->checkgo('a_dbbak')||$this->c->checkgo('a_files'))$this->leftmenu.='<p></p>';
			if($this->c->checkgo('a_files'))$this->leftmenu.='<a href="?c=a_files" target="main">清理多余附件</a>';
			if($this->c->checkgo('a_dbbak'))$this->leftmenu.='<a href="?c=a_dbbak" target="main">数据备份恢复</a>';
			if($this->c->checkgo('a_update'))$this->leftmenu.='<a href="?c=a_update" target="main">在线升级</a>';
			include_once('include/fun/verification.php');
			$this->leftmenu.=verification(1);
			break;
			default: 
			$this->lefttop='内容管理';
			foreach($this->m as $v){
				if($v['molds']!='message'){
					if($this->c->checkgo('a_'.$v['molds'],'add')||$this->c->checkgo('a_channel','add',0,$v['molds'])){
						if($v['sys']==1){
							$this->leftmenu.='<a href="?c=a_'.$v['molds'].'&a=add" target="main">添加'.$v['moldname'].'</a>';
						}else{
							$this->leftmenu.='<a href="?c=a_channel&molds='.$v['molds'].'&a=add" target="main">添加'.$v['moldname'].'</a>';
						}
					}
				}
				if($this->c->checkgo('a_'.$v['molds'].'_','',1)||$this->c->checkgo('a_channel','',1,$v['molds'])){
					if($v['sys']==1){
						$this->leftmenu.='<a href="?c=a_'.$v['molds'].'" target="main">'.$v['moldname'].'管理</a><p></p>';
					}else{
						$this->leftmenu.='<a href="?c=a_channel&molds='.$v['molds'].'" target="main">'.$v['moldname'].'管理</a><p></p>';
					}
				}
			}
			$this->leftmenu.='<a href="?c=a_sys&a=ecache" target="main">更新系统缓存</a>';
			break;
		}
		$this->display("main.html");
	}
	function bottom(){
		$this->a='bottom';
		$this->display("main.html");
	}
	function template_cache(){
		$d='include/cache/log/';
		$f=date('Ym').'.txt';
		deleteDir($d);__mkdirs($d);
		$wt=@fopen($d.$f,"w");@fclose($wt);
		exit('true');
	}
	function href_session(){
		exit('true,'.date('Ym'));
	}
}	