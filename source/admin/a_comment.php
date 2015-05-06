<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_comment extends syController
{
	function __construct(){
		parent::__construct();
		$this->Classc=syClass('c_comment');
		$this->gopage=$this->syArgs('page',0,1);
		$this->Get_c='?c=a_comment';
		$this->news = array(
			'molds' => $this->syArgs('molds',1),
			'aid' => $this->syArgs('aid'),
			'isshow' => $this->syArgs('isshow'),
			'body' => $this->syArgs('body',1),
			'reply' => $this->syArgs('reply',1),
			'addtime' => strtotime($this->syArgs('addtime',1)),
			'retime' => strtotime($this->syArgs('retime',1)),
			'user' => $this->syArgs('user',1),
			'ruser' => $_SESSION['auser']['auser'],
		);
	}
	function index(){
		$this->toptxt='评论管理';
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'comment');
		$this->lists = $this->Classc->syPager($this->gopage,10,$total_page)->findAll(null,' addtime desc,cid desc '); 
		$this->pages = pagetxt($this->Classc->syPager()->getPager());
		$this->display("comment.html");
	}
	function edit(){
		$this->d=$this->Classc->find(array('cid'=>$this->syArgs('cid')));
		if ($this->syArgs('run')==1){
			$newVerifier=$this->Classc->syVerifier($this->news);
			if(false == $newVerifier){
				if($this->Classc->update(array('cid'=>$this->d['cid']),$this->news)){
					deleteDir($GLOBALS['G_DY']['sp_cache']);
					message_a("评论修改成功",$this->Get_c);
				}else{message_a("评论修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='修改评论';
		$this->display("comment.html");
	}
	function del(){
		$this->toptxt='删除评论';
		$this->d=$this->Classc->find(array('cid'=>$this->syArgs('cid')));
		if ($this->syArgs('run')==1){
			if($this->Classc->delete(array('cid'=>$this->syArgs('cid')))){
				deleteDir($GLOBALS['G_DY']['sp_cache']);
				message_a("评论删除成功",$this->Get_c);
			}else{message_a("评论删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除这条评论吗？';
		$this->msg=$this->d['body'];
		$this->msggo='<a href="'.$this->Get_c.'&a=del&run=1&cid='.$this->d['cid'].'">确定删除</a><a href="'.$this->Get_c.'">取消操作</a>';
		$this->display("msg.html");
	}

}	