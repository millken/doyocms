<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class syaccount{
	private $row;
	private $extra;
	public function __construct($row){
		$this->account=array(
            1=>'在线充值',
            2=>'手工入款',
            3=>'订单支付',
            4=>'购买内容',
			6=>'手工扣款',
            9=>'退款',
		);
		$this->row=$row;
	}
	public function payment()
	{
		switch($this->row['type']){
			case 1:
				$cz=syDB('account')->find(array('uid'=>$this->row['uid'],'paymentno'=>$this->row['paymentno']));
				if(!$cz){
					syDB('member')->incrField(array('id'=>$this->row['uid']),'money',$this->row['money']);
					$this->account_add();
				}
				$r['msg']='恭喜您成功充值'.$this->row['money'].'元！';
				$r['url']='../../../index.php?c=member';
				return $r;
			break;
			case 3:
				$cz=syDB('account')->find(array('uid'=>$this->row['uid'],'orderid'=>$this->row['orderid']));
				if(!$cz){
					$virtualtype=syDB('order')->find(array('orderid'=>$this->row['orderid'],'uid'=>$this->row['uid']),null,'id,virtual,goods');
					$vgoods=unserialize($virtualtype['goods']);
					$this->account_add();
					if($virtualtype['virtual']==1){
						$quantity=$vgoods[0]['quantity'];
						syDB('product_virtual')->runSql('update '.$GLOBALS['G_DY']['db']['prefix'].'product_virtual SET state=1,oid='.$virtualtype['id'].' WHERE aid = '.$vgoods[0]['aid'].' and state=0 order by id limit '.$quantity);
						syDB('order')->update(array('orderid'=>$this->row['orderid'],'uid'=>$this->row['uid']),array('state'=>9,'payment'=>$this->row['payment'],'paymentno'=>$this->row['paymentno'],'paytime'=>$this->row['addtime'],'actualpay'=>$this->row['money']));
						if($this->row['paymenttype']!=1&&$this->row['payment']=='alipay'){
							$sg=array(
								'trade_no'=>$this->row['paymentno'],
								'logistics_name'=>'自动发货',
								'invoice_no'=>$this->row['orderid'],
								'transport_type'=>'DIRECT',
							);
							syClass('alipay',null,'include/payment/alipay/alipay.php')->sendgoods($sg);				
						}
						$r['msg']='恭喜您，订单'.$this->row['orderid'].'支付成功！虚拟货物信息已发送至您的订单，请在订单详情查看。';
					}else{
						syDB('order')->update(array('orderid'=>$this->row['orderid'],'uid'=>$this->row['uid']),array('state'=>1,'payment'=>$this->row['payment'],'paymentno'=>$this->row['paymentno'],'paytime'=>$this->row['addtime'],'actualpay'=>$this->row['money']));
						$r['msg']='恭喜您，订单'.$this->row['orderid'].'支付成功！我们将尽快为您处理。';
					}
					foreach($vgoods as $g){
						$sr=syDB('sales_record')->find(array('aid'=>$g['aid'],'oid'=>$this->row['orderid']));
						if(!$sr){
							$user=syDB('member')->find(array('id'=>$this->row['uid']),null,'user');
							syDB('sales_record')->create(array('aid'=>$g['aid'],'oid'=>$virtualtype['id'],'user'=>$user['user'],'num'=>$g['quantity'],'stime'=>$this->row['addtime']));
							syDB('product')->incrField(array('id'=>$g['aid']),'record',$g['quantity']);
						}
					}
					$r['url']='../../../index.php?c=member&a=myorder&orderid='.$this->row['orderid'];
				}
				return $r;
			break;
			case 4:
				$cz=syDB('account')->find(array('uid'=>$this->row['uid'],'molds'=>$this->row['molds'],'aid'=>$this->row['aid']));
				if(!$cz){
					$money=$this->mymoney($this->row['uid']);
					if($money!==false&&$money>=$this->row['money']){
						syDB('member')->decrField(array('id'=>$this->row['uid']),'money',$this->row['money']);
						$this->account_add();
					}else{message("余额不足以支付");}
				}
				$m=syDB($this->row['molds'])->find(array('id'=>$this->row['aid']),null,'title');
				$r['msg']='恭喜您，['.$m['title'].']购买成功！';
				$r['url']='../../../index.php?c=member&a=mymolds';
				return $r;
			break;
		}
	}
	public function account_add()
	{
		syDB('account')->create($this->row);
	}
	public function mymoney($id)
	{
		$money=syDB('member')->find(array('id'=>$id),null,'money');
		if(!$money)return false;
		return $money['money'];
	}
	public function info($v)
	{
        switch($v['type']){
            case 1:return $this->account[1].' ['.payment($v['payment']).'] '.$v['paymentno'];break;
            case 2:return $this->account[2].' [操作人：'.$v['auser'].']';break;
            case 3:return $this->account[3].' '.$v['orderid'];break;
            case 4:return $this->account[4].' '.contentinfo($v['molds'],$v['aid'],'title');break;
			case 6:return $this->account[6].' [操作人：'.$v['auser'].']';break;
            case 9:return $this->account[7].' [操作人：'.$v['auser'].']';break;
            default:return $v['typeinfo'];break;
        }
	}
	public function userinfo($v)
	{
        switch($v['type']){
            case 1:return $this->account[1].' ['.payment($v['payment']).'] '.$v['paymentno'];break;
            case 2:return '管理员'.$this->account[2];break;
            case 3:return $this->account[3].' '.$v['orderid'];break;
            case 4:return $this->account[4].' '.contentinfo($v['molds'],$v['aid'],'title');break;
			case 6:return '管理员'.$this->account[6];break;
            case 9:return $this->account[7];break;
            default:return $v['typeinfo'];break;
        }
	}
	public function option()
	{
        foreach(array(2,6) as $v){
			$t.='<option value="'.$v.'">'.$this->account[$v].'</option>';
		}
		echo $t;
	}
	public function pn($type)
	{
        if(in_array($type,array(1,2,9))){return '+';}else{return '-';}
	}
}