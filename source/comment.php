<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class comment extends syController
{
	function __construct(){
		if(funsinfo('comment','isshow')!=1)message("评论功能已关闭");
		parent::__construct();
	}
	function index(){
		if(syExt('comment_user')==1&&empty($_SESSION['member']))message("请登录后提交");
		if($this->syArgs('body',1)==''||!$this->syArgs('aid'))message("请输入内容");
		if($GLOBALS['G_DY']['vercode']==1){
		if(md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
		}
		if(syExt('comment_audit')==0){$isshow=1;}else{$isshow=0;}
		if(empty($_SESSION['member'])){$user='游客';}else{$user=$_SESSION['member']['user'];}
		$newrow = array(
			'molds' => $this->syArgs('m',1),
			'aid' => $this->syArgs('aid'),
			'body' => $this->syArgs('body',1),
			'addtime' => time(),
			'user' => $user,
			'isshow' => $isshow,
			'reply' => '',
			'ruser' => '',
			'ip' => GetIP()
		);
		$newVerifier=syClass('c_comment')->syVerifier($newrow);
		if(false == $newVerifier){
			if(syClass('c_comment')->create($newrow)){
				message("评论成功",$_SERVER['HTTP_REFERER']);
			}else{message("评论失败，请重新提交");}
		}else{message_err($newVerifier);}
	}
}	