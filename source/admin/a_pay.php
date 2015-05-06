<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_pay extends syController
{
	function __construct(){
		parent::__construct();
		$this->Gets=$this->syArgs('a',1);
		$this->Classa=syClass('c_account');
	}
	function index(){
		$this->toptxt='财务记录';
		$a=syClass('syaccount');
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'account');
		$this->lists =$this->Classa->syPager($this->gopage,15,$total_page)->findAll(null,'addtime desc,id desc');
		$lists = $this->lists;
		foreach($lists as $k=>$v){
			$lists[$k]['pn']=$a->pn($v['type']);
		}
		$this->lists = $lists;
		$this->pages = pagetxt($this->Classa->syPager()->getPager());
		$this->display("pay.html");
	}
	function accountadd(){
		if ($this->syArgs('run')==1){
			$type=$this->syArgs('type');
			$money=$this->syArgs('money',3);
			if($money<=0)message_a('录入金额必须大于0');
			$member=syDB('member')->find(array('user'=>$this->syArgs('member',1)));
			if(!$member)message_a('输入的用户名称不存在，请重新确认输入。');
			if($type==6){
				$tomoney=calculate($member['money'],$money,2);//减少
			}else{
				$tomoney=calculate($member['money'],$money);//增加
			}
			$row=array(
				'uid'=>$member['id'],
				'type'=>$this->syArgs('type'),
				'money'=>$money,
				'custom'=>$this->syArgs('custom',1),
				'addtime'=>time(),
				'auser'=>$_SESSION['auser']['auser'],
			);
			if(syDB('account')->create($row)){
				syDB('member')->update(array('id'=>$member['id']),array('money'=>$tomoney));
				message_a("财务记录创建成功","?c=a_pay");
			}else{message_a("财务记录创建失败，请重新提交");}
		}
		if($this->syArgs('uid',1))$this->user=syDB('member')->find(array('id'=>$this->syArgs('uid',1)),null,'user');
		$this->toptxt='添加财务记录';
		$this->postgo='accountadd';
		$this->display("pay.html");
	}
	function accountdel(){
		$this->toptxt='删除财务记录';
		$this->d=$this->Classa->find(array('id'=>$this->syArgs('id')));
		if($this->syArgs('run')==1){
			if($this->Classa->delete(array('id'=>$this->syArgs('id')))){
				message_a("财务记录删除成功","?c=a_pay");
			}else{message_a("财务记录删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除 <strong>['.syClass('syaccount')->info($this->d).']</strong> ？';
		$this->msg='警告：删除财务记录将导致相关财务账目关联丢失，并不可恢复！';
		$this->msggo='<a href="?c=a_pay&a=accountdel&run=1&id='.$this->d['id'].'">确定删除</a><a href="?c=a_pay">取消操作</a>';
		$this->display("msg.html");
	}
	function paymentlist(){
		$this->toptxt='支付平台管理';
		$this->lists =syDB('payment')->findAll();
		$this->display("pay.html");
	}
	function paymentedit(){
		$this->d=syDB('payment')->find(array('id'=>$this->syArgs('id')));
		$ykey=unserialize($this->d['keyv']);
		$keyv=$this->syArgs('keyv',2);
		foreach($keyv as $k=>$v){
			if($k=='key'&&$v!=''){				
				$x=dykeycode($ykey[$k]);
				$x=dykey_x($x);
				if($v==$x){$keyv[$k]=$ykey[$k];}else{$keyv[$k]=dykeycode($v,'ENCODE');}		
			}
		}
		if ($this->syArgs('run')==1){
			$newrow=array(
			'isshow'=>$this->syArgs('isshow'),
			'orders'=>$this->syArgs('orders'),
			'keyv'=>serialize($keyv),
			);
			if(syDB('payment')->update(array('id'=>$this->d['id']),$newrow)){
				message_a("支付平台修改成功","?c=a_pay&a=paymentlist");
			}else{message_a("支付平台修改失败,请重新提交");}
		}
		$this->toptxt='修改支付平台';
		$this->postgo='paymentedit';
		$this->display("pay.html");
	}

}	