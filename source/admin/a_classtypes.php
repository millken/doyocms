<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_classtypes extends syController
{
	function __construct(){
		parent::__construct();
		$this->ClassT=syClass('c_classtype');
		$this->auser=syClass('syauser');
		$classtype=syDB('classtype')->findAll(null,' orders desc,tid ','tid,classname,pid,molds');
		$this->types=syClass('syclasstype',array($classtype));
		$this->chtml = syClass("syhtml");
		$this->typesdb=$this->types->type_txt();
		if(funsinfo('member','isshow')==1){
		$this->member_group = syDB('member_group')->findAll(null,'weight');
		}
		$this->db=$GLOBALS['G_DY']['db']['prefix'];
		$this->moldsop=syDB('molds')->findAll(array('isshow'=>1));
		$this->Get_c='a_classtypes';
		
		if($this->syArgs('title',1)){$title=$this->syArgs('title',1);}else{$title=$this->syArgs('classname',1);}
		if($this->syArgs('htmldir',1)!='/'){$htmldir=trim($this->syArgs('htmldir',1),'/');}else{$htmldir=$this->syArgs('htmldir',1);}
		$this->newrow = array(
			'pid' => $this->syArgs('pid'),
			'molds' => $this->syArgs('molds',1),
			'classname' => $this->syArgs('classname',1),
			'gourl' => $this->syArgs('gourl',1),
			'litpic' => $this->syArgs('litpic',1),
			'title' => $title,
			'keywords' => $this->syArgs('keywords',1),
			'description' => $this->syArgs('description',1),
			'isindex' => $this->syArgs('isindex'),
			't_index' => $this->syArgs('t_index',1),
			't_list' => $this->syArgs('t_list',1),
			't_listimg' => $this->syArgs('t_listimg',1),
			't_listb' => $this->syArgs('t_listb',1),
			't_content' => $this->syArgs('t_content',1),
			'listnum' => $this->syArgs('listnum'),
			'mshow' => $this->syArgs('mshow'),
			'htmldir' => strtolower($htmldir),
			'htmlfile' => strtolower($this->syArgs('htmlfile',1)),
			'mrank' => $this->syArgs('mrank'),
			'msubmit' => $this->syArgs('msubmit'),
			'orders' => $this->syArgs('orders'),
			'body' => $this->syArgs('body',4),
			'imgw' => $this->syArgs('imgw'),
			'imgh' => $this->syArgs('imgh'),
			'unit' => $this->syArgs('unit',1),
		);
	}
	function index(){
		$this->toptxt='栏目管理';
		$this->lists = $this->typesdb;
		$this->display("classtypes.html");
	}
	function add(){
		$this->ptid=$this->syArgs('tid');
		$this->mm=$this->syArgs('molds',1);
		if ($this->syArgs('run')==1){
			$newVerifier=$this->ClassT->syVerifier($this->newrow);
				if(false == $newVerifier){
					deleteDir($GLOBALS['G_DY']['sp_cache']);
					$thismolds=syDB('molds')->find(array('molds'=>$this->newrow['molds']),null,'t_index,t_list,t_listimg,t_listb,t_content,sys');	
					if($this->newrow['t_index']=='' || $this->newrow['t_list']=='' || $this->newrow['t_listimg']=='' || $this->newrow['t_listb']=='' || $this->newrow['t_content']==''){
						$tm=array();
						if($this->newrow['pid']==0||$thistypes['molds']!=$this->newrow['molds']){
							$thismolds=syDB('molds')->find(array('molds'=>$this->newrow['molds']),null,'t_index,t_list,t_listimg,t_listb,t_content ');							
							if($this->newrow['t_index']=='')$tm['t_index']=$thismolds['t_index'];
							if($this->newrow['t_list']=='')$tm['t_list']=$thismolds['t_list'];
							if($this->newrow['t_listimg']=='')$tm['t_listimg']=$thismolds['t_listimg'];
							if($this->newrow['t_listb']=='')$tm['t_listb']=$thismolds['t_listb'];
							if($this->newrow['t_content']=='')$tm['t_content']=$thismolds['t_content'];
						}else{
							if($this->newrow['t_index']=='')$tm['t_index']=$thistypes['t_index'];
							if($this->newrow['t_list']=='')$tm['t_list']=$thistypes['t_list'];
							if($this->newrow['t_listimg']=='')$tm['t_listimg']=$thistypes['t_listimg'];
							if($this->newrow['t_listb']=='')$tm['t_listb']=$thistypes['t_listb'];
							if($this->newrow['t_content']=='')$tm['t_content']=$thistypes['t_content'];
						}
						$this->newrow=array_merge($this->newrow,$tm);
					}
					$addnew=$this->ClassT->create($this->newrow);
					if($addnew){
						$fields=syDB('fields')->findAll(' types like "%|'.$this->newrow['pid'].'|%" ',null,'fid,types');
						foreach ($fields as $v){
							syDB('fields')->update(array('fid'=>$v['fid']),array('types'=>$v['types'].$addnew.'|'));
						}
						syAccess('c','classtype');
						syAccess('w','classtype',syDB('classtype')->findAll(null,null,'tid,classname,pid,molds'));
						if(syExt('site_html')==1&&$this->newrow['mrank']==0){
							if($this->newrow['htmldir']==''){
								$c_html_f=syExt('site_html_dir').'/c/'.$addnew.'/';
							}else{
								$c_html_f=$this->newrow['htmldir'].'/';
							}
							if($this->newrow['htmlfile']==''){
								$c_html_f.='index'.syExt('site_html_suffix');
							}else{
								$c_html_f.=$this->newrow['htmlfile'].syExt('site_html_suffix');
							}
							if($thismolds['sys']==1){
								$this->chtml->c_classtype($this->newrow['molds'],array('tid'=>$addnew),$c_html_f);
							}else{
								$this->chtml->c_classtype('channel',array('tid'=>$addnew),$c_html_f);
							}
						}
						message_a("栏目创建成功","?c=".$this->Get_c,'<a href="?c='.$this->Get_c.'">返回列表</a><a href="?c='.$this->Get_c.'&a=add&tid='.$this->newrow['pid'].'&molds='.$this->newrow['molds'].'">继续添加</a>',"8");
					}else{message_a("栏目创建失败，请重新提交");}
				}else{message_b($newVerifier);}
		}
		$this->toptxt='添加栏目';
		$this->postgo='add';
		$this->display("classtypes_edit.html");
	}
	function edit(){
		if(!$this->auser->checkclass($this->syArgs('tid')))message_a("无权操作本栏目");
		$this->carray=$this->ClassT->find(array('tid'=>$this->syArgs('tid')));
		if ($this->syArgs('run')==1){
			$newVerifier=$this->ClassT->syVerifier($this->newrow);
			if(false == $newVerifier){
				deleteDir($GLOBALS['G_DY']['sp_cache']);
				$thismolds=syDB('molds')->find(array('molds'=>$this->newrow['molds']),null,'t_index,t_list,t_listimg,t_listb,t_content,sys');
				$m_t=($this->newrow['molds']!=$this->carray['molds']);
				if($this->newrow['t_index']=='' || $this->newrow['t_list']=='' || $this->newrow['t_listimg']=='' || $this->newrow['t_listb']=='' || $this->newrow['t_content']=='' || $m_t){
					$tm=array();
					if($this->newrow['t_index']=='' || $m_t)$tm['t_index']=$thismolds['t_index'];
					if($this->newrow['t_list']=='' || $m_t)$tm['t_list']=$thismolds['t_list'];
					if($this->newrow['t_listimg']=='' || $m_t)$tm['t_listimg']=$thismolds['t_listimg'];
					if($this->newrow['t_listb']=='' || $m_t)$tm['t_listb']=$thismolds['t_listb'];
					if($this->newrow['t_content']=='' || $m_t)$tm['t_content']=$thismolds['t_content'];
					$this->newrow=array_merge($this->newrow,$tm);
				}
				if($this->syArgs('t_all')==1){
					$c=array('t_index'=>$this->newrow['t_index'],'t_list'=>$this->newrow['t_list'],'t_listimg'=>$this->newrow['t_listimg'],'t_listb'=>$this->newrow['t_listb'],'t_content'=>$this->newrow['t_content']);
					$this->ClassT->update(' tid in('.$this->types->leafid($this->carray['tid']).') ',$c);
				}
				if($this->ClassT->update(array('tid'=>$this->carray['tid']),$this->newrow)){
					syAccess('c','classtype');
					syAccess('w','classtype',syDB('classtype')->findAll(null,null,'tid,classname,pid,molds'));
						if(syExt('site_html')==1&&$this->newrow['mrank']==0){
							if($this->newrow['htmldir']==''){
								$c_html_f=syExt('site_html_dir').'/c/'.$this->carray['tid'].'/';
							}else{
								$c_html_f=$this->newrow['htmldir'].'/';
							}
							if($this->newrow['htmlfile']==''){
								$c_html_f.='index'.syExt('site_html_suffix');
							}else{
								$c_html_f.=$this->newrow['htmlfile'].syExt('site_html_suffix');
							}
							if($thismolds['sys']==1){
								$this->chtml->c_classtype($this->carray['molds'],array('tid'=>$this->carray['tid']),$c_html_f);
							}else{
								$this->chtml->c_classtype('channel',array('tid'=>$this->carray['tid']),$c_html_f);
							}
						}
					message_a("栏目修改成功","?c=".$this->Get_c);
				}else{message_a("栏目修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='修改栏目';
		$this->postgo='edit';
		$this->display("classtypes_edit.html");
	}
	function del(){
		if(!$this->auser->checkclass($this->syArgs('tid')))message_a("无权操作本栏目");
		$this->toptxt='删除栏目';
		$this->d=$this->ClassT->find(array('tid'=>$this->syArgs('tid')));
		$tid=$this->d['tid'];
		if ($this->syArgs('run')==1){
			$tida=$this->types->leafid($tid);
			foreach (explode(',',$tida) as $v){
				$types=$this->ClassT->find(array('tid'=>$v),null,'tid,molds');
				$db=$this->db.$types['molds'];
				syDB($types['molds'])->findSql('DELETE '.$db.','.$db.'_field FROM '.$db.','.$db.'_field WHERE '.$db.'.id='.$db.'_field.aid and '.$db.'.tid='.$v);
				if($types['molds']=='product'){
					$attribute=syDB($types['molds'])->findAll(array('tid'=>$v),null,'id,tid');
					foreach ($attribute as $va){
						syDB($types['molds'].'_attribute')->delete(array('aid'=>$va['id']));
					}
				}
			}
			deleteDir($GLOBALS['G_DY']['sp_cache']);
			if($this->ClassT->delete(' tid in('.$tida.') ')){
				syAccess('c','classtype');
				syAccess('w','classtype',syDB('classtype')->findAll(null,null,'tid,classname,pid,molds'));
				message_a("栏目删除成功","?c=".$this->Get_c);
			}else{message_a("栏目删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除栏目 <strong>['.$this->d['classname'].']</strong> 吗？';
		$this->msg='警告：本操作将自动删除栏目下所有已发布内容（包括下级栏目内容）<br>本操作不可逆！建议删除前备份数据库！';
		$this->msggo='<a href="?c='.$this->Get_c.'&a=del&run=1&tid='.$tid.'">确定删除</a><a href="?c='.$this->Get_c.'">取消操作</a>';
		$this->display("msg.html");
	}
	function alledit(){
		$orders=$this->syArgs('orders',2);
		foreach($orders as $k=>$tp){
			if($this->auser->checkclass($k))$this->ClassT->update(array('tid'=>$k),array('orders'=>$orders[$k]));
		}
		deleteDir($GLOBALS['G_DY']['sp_cache']);
		jump('?c='.$this->Get_c);
	}

}	