<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class symember{
	private $member;
	public function __construct(){
		if($_SESSION['member']){
			$member = syDB('member')->find(array('id'=>$_SESSION['member']['id']),null,'id,user,email,gid,regtime');
			$member['group']=syDB('member_group')->find(array('gid'=>$member['gid']));
		}else{
			$member['id'] = 0;
			$member['group']=syDB('member_group')->find(array('weight'=>0));
		}
		$this->member=$member;
	}
	public function islogin($login=1,$url=0){
		if($login&&$this->member['id']==0){
			GLOBAL $__controller, $__action;
			if($__action!='login'&&$__action!='out'&&$__action!='reg'&&$__action!='rules'&&$__action!='retrieve_password'&&$__action!='reset_password'){
				if($url==1)$url=$this->backurl();
				jump($GLOBALS['WWW'].'index.php?c=member&a=login&url='.$url);
			}
		}
		return $this->member;
	}

	public function p_v($mrank,$mgold,$molds,$id){
		if(membergroup($mrank,'weight')>$this->member['group']['weight'])message('本栏目需要['.membergroup($mrank,'name').']及以上用户才可访问'.$this->member['group']['name'].'-');
		if($mgold>0){
			echo '1';
			if($this->member['id']==0){
				$url=$this->backurl();
				message('本栏目需要登录才可访问',$GLOBALS["WWW"].'index.php?c=member&a=login&url='.$url);
			}
			$cz=syDB('account')->find(array('uid'=>$this->member['id'],'molds'=>$molds,'aid'=>$id));
			if(!$cz){
				jump($GLOBALS['WWW'].'index.php?c=pay&a=buymolds&molds='.$molds.'&id='.$id);
			}
		}
	}

	public function p_r($msubmit){
		if($this->member['group']['submit']==0||$msubmit==0||membergroup($msubmit,'weight')>$this->member['group']['weight'])message("本栏目无权发布");
	}

	public function backurl(){
		$c=is_escape($_GET['c']);
		$id=is_escape($_GET['id']);
		if($c=='pay'&&$id>0){
			$url=$_SERVER["HTTP_REFERER"];
		}else{
			$url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}
		return urlencode($url);
	}

}