<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class syauser{
	private $ausers;
	private $auserserr;
	private $c;
	public function __construct()
	{	
		if($_SESSION['auser']){
			$this->ausers = array_merge($_SESSION['auser'],syDB('admin_group')->find(array('gid'=>$_SESSION['auser']['gid'])));
		}else{
			$this->ausers = false;
		}
	}
	public function info()
	{	
		return $this->ausers;
	}
	public function check()
	{
		GLOBAL $__controller, $__action;
		if($__controller=='login'){return true;}
		if(!$this->ausers){echo "<script>parent.location.href='?c=login';</script>";exit;}
		if($__controller=='index'){return true;}
			$this->c=$__controller;
			if($this->checkgo($this->c,$__action,0,$_GET['molds'])){
				return true;
			}else{
				message_a($this->auserserr);
			};

	}
	public function checkgo($c,$a='',$li=0,$molds='')
	{
		if($this->ausers['level']==1){
			return true;
		}else{
			if($c=='a_channel'){
				if($li&&strpos($this->ausers['paction'],',channel_')!==false){return true;}
				if(strpos($this->ausers['paction'],',channel_'.$molds.',')!==false){return true;}
				if(strpos($this->ausers['paction'],',channel_'.$molds.'_'.$a.',')!==false){return true;}
			}else{
				if($li==0){$y1=','.$c.',';$y2=','.$c.'_'.$a.',';}else{$y1=$c;$y2=$c.'_'.$a;}
				if(strpos($this->ausers['paction'],$y1)!==false){return true;}
				if(strpos($this->ausers['paction'],$y2)!==false){return true;}
			}
			$err1=syDB('admin_per')->find(array('action'=>$c),null,'pid,name');
			$err2=syDB('admin_per')->find(array('up'=>$err1['pid'],'action'=>$a),null,'name');
			$this->auserserr='您没11有 ['.$err1['name'].'-'.$err2['name'].'] 权限';
			return false;
		}
	}
	public function checkclass($tid)
	{
		if($tid){
			if($this->ausers['level']==1){
				return true;
			}else{
				if(strpos($this->ausers['pclasstype'],','.$tid.',')!==false){
					return true;
				}else{
					return false;
				}
			}
		}else{
			if($this->ausers['level']==1){
				return true;
			}else{
				if($this->ausers['pclasstype']==''){
					return false;
				}else{
					return true;
				}
			}
		}
	}
	public function audit()
	{
		if($this->ausers['level']==1){
			return 1;
		}else{
			return $this->ausers['audit'];
		}
	}
}