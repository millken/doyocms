<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class pay extends syController
{	
	function __construct(){
		parent::__construct();
		$fun_pay=funsinfo('pay','isshow');$fun_goods=funsinfo('goods','isshow');
		if($fun_pay!=1&&$fun_goods!=1)message("支付与购物功能已关闭");
		$this->member=syClass('symember');
		$this->my=$this->member->islogin(1,1);
		$money=syDB('member')->find(array('id'=>$this->my['id']),null,'money');
		$this->mymoney=$money['money'];
		$this->sy_class_type=syClass('syclasstype');
		$this->c=syClass('c_order');
		$this->m=syClass('c_product');
		$this->id=$this->syArgs('id');
		$this->db=$GLOBALS['G_DY']['db']['prefix'];
		$payment=syDB('payment')->findAll(array('isshow'=>1),'orders desc,id desc');
		if($payment){$payment[0]['n']=1;$this->payment=$payment;}
		
	}
	function index(){
		$this->cart=$this->syArgs('cart');
		if(!$this->id&&!$this->cart)message("请指定购买内容");
		if($this->cart){
			$cart_db=$this->cart_db();
			$this->goods=$this->goods_db($cart_db);
			$this->title='购物车';
		}else{
			$va=$this->m->find(array('id'=>$this->id,'isshow'=>1),null,'virtual');
			if(!$va)message('请指定内容不存在');
			$this->goods=$this->goods_db();
			if($va['virtual']==1){
				$this->virtual=1;
/*				$goods[0]=array('aid'=>$this->goods[0]['aid'],'attribute'=>$this->goods[0]['attribute'],'quantity'=>$this->goods[0]['quantity']);
				$o=$this->order_add($goods);
				jump($GLOBALS['WWW'].'index.php?c=pay&a=order&oid='.$o);*/
			}
			$this->title='生成订单';
		}
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>  &gt;  '.$this->title;
		$this->display("pay/order.html");
	}
	function cartadd(){
		$aid=$this->syArgs('id');
		$quantity=$this->syArgs('quantity');
		$attribute=$this->syArgs('attribute',2);
		if(!$aid)exit('err,请指定内容');
		if(!$quantity)$quantity=1;
		$g=syDB('goodscart')->find(array('aid'=>$aid,'uid'=>$this->my['id'],'attribute'=>serialize($attribute)));
		if($g){
			syDB('goodscart')->incrField(array('aid'=>$aid,'uid'=>$this->my['id'],'attribute'=>serialize($attribute)),'num',$quantity);
		}else{
			$va=$this->m->find(array('id'=>$aid,'isshow'=>1),null,'tid,virtual');
			if(!$va)exit('err,指定内容不存在');
			if($va['virtual']==1)exit('err,本商品可直接购买，自动发货。');
			$p_type=syDB('attribute_type')->findSql('select distinct a.tid,a.aid,b.tid,b.isshow,b.orders,b.name from '.$this->db.'product_attribute a left join '.$this->db.'attribute_type b on (a.tid=b.tid) where a.aid='.$aid.' and b.isshow=1 order by b.orders desc,b.tid desc');
			foreach($p_type as $v){if(!$attribute[$v['tid']])exit('err,请选择['.$v['name'].']');}
			syDB('goodscart')->create(array('aid'=>$aid,'num'=>$quantity,'uid'=>$this->my['id'],'attribute'=>serialize($attribute),'addtime'=>time()));
		}
		echo 'ok';
	}
	function cartdel(){
		$id=$this->syArgs('id');
		$d=syDB('goodscart')->delete(array('id'=>$id,'uid'=>$this->my['id']));
		if($d)echo 'ok';
	}
	function buymolds(){
		$this->id=$this->syArgs('id');
		$this->molds=$this->syArgs('molds',1);
		if(!$this->id&&!$this->molds)message("请指定购买内容");
		$this->info=syDB($this->molds)->find(array('id'=>$this->id,'isshow'=>1),null,'title,mgold,litpic');
		if(!$this->info)message("指定购买内容不存在或未审核。");
		if($this->syArgs('run')){
			if($this->mymoney<$this->info['mgold'])message("您的余额不足，请先充值");
			$row=array(
				'type'=>4,
				'uid'=>$this->my['id'],
				'orderid'=>'',
				'money'=>$this->info['mgold'],
				'custom'=>'',
				'payment'=>'',
				'paymentno'=>'',
				'molds'=>$this->molds,
				'aid'=>$this->id,
				'addtime'=>time(),
				'auser'=>'',
			);
			$a=syClass('syaccount',array($row))->payment();
			message($a['msg'],$a['url']);
		}
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>  &gt;  支付中心';
		$this->display("pay/buy_molds.html");
	}
	function order(){
		if($this->syArgs('oid')||$this->syArgs('orderid',1)!=''){
			if($this->syArgs('oid')){$r=array('id'=>$this->syArgs('oid',1));}else{$r=array('orderid'=>$this->syArgs('orderid',1));}
			$order=$this->c->find($r);
			if($order['state']!=0){jump('?c=member&a=myorder&oid='.$order['id']);}
			$this->goods=$this->goods_db(unserialize($order['goods']),$order['logistics']);
		}else{
			if($GLOBALS['G_DY']['vercode']==1){
				if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
			}
			$this->cart=$this->syArgs('cart');
			$virtual=$this->syArgs('virtual');
			if(!$this->id&&!$this->cart)message("请指定购买内容");
			$info=$this->syArgs('info',2);
			if($virtual!=1&&($info['name']==''||$info['phone']==''||$info['address1']==''||$info['address2']==''||$info['address']==''))message("姓名、手机、省、市、地址为必填");
			if($this->cart){
				syDB('goodscart')->delete(array('uid'=>$this->my['id']));
			}
			$this->goods=$this->syArgs('goods',2);
			if($virtual!=1){
				$o=$this->order_add($this->goods,0,$this->syArgs('logistics',1),$info,$this->syArgs('unote',1));
				jump($GLOBALS['WWW'].'index.php?c=pay&a=order&oid='.$o);
			}else{
				$payment=$this->syArgs('payment',1);
				if(!$payment)message("请指定支付平台");
				$vi=total_page($this->db.'product_virtual where aid='.$this->goods[0]['aid'].' and state=0');
				if($vi<$this->goods[0]['quantity'])message("库存不足，暂无法购买，请联系客服。",'?c=pay&id='.$this->goods[0]['aid']);
				$o=$this->order_add($this->goods,1,$this->syArgs('logistics',1),$info,$this->syArgs('unote',1));
				jump($GLOBALS['WWW'].'index.php?c=pay&a=pay&payment='.$payment.'&id='.$o);
			}
		}
		$order['info']=unserialize($order['info']);
		$this->goods=$this->goods_db(unserialize($order['goods']),$order['logistics']);
		$this->total=0;
		foreach($this->goods as $v){
			$this->total=calculate($this->total,$v['total']);
			$this->total=calculate($this->total,$v['logistics_price']);
		}
		$this->aggregate=calculate($this->total, $order['favorable'],2);
		$this->order=$order;
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>  &gt;  支付中心';
		$this->display("pay/buy.html");
	}
	function pay(){
		$oid=$this->syArgs('id',1);
		$pid=$this->syArgs('payment',1);
		if($oid=='')message("请指定订单号");
		if($pid=='')message("请选择支付平台");
		$order=$this->c->find(array('id'=>$oid));
		if(!$order)message("订单不存在或已被删除");
		if($order['state']!=0){jump('?c=member&a=myorder&oid='.$order['id']);}
		$subject='订单号：'.$order['orderid'];
		$body='会员'.$this->my['user'].'在线付款';
		
		$goods=$this->goods_db(unserialize($order['goods']),$order['logistics']);
		$total=0;
		foreach($goods as $v){
			$total=calculate($total,$v['total']);
			$total=calculate($total,$v['logistics_price']);
		}
		$price=calculate($total,$order['favorable'],2);//优惠处理
		switch($pid){
			case 'offline'://线下付款
				message("订单生成成功，请付款后联系客服安排后续处理。",'?c=member&a=myorder&oid='.$order['id']);
			break;
			case 'cashbalance'://余额支付
				$member=syDB('member')->find(array('id'=>$this->my['id']),null,'money');
				if($member['money']<$price)message('您的余额不足以支付本订单，请先充值。','?c=member&a=recharge');
				$accounts=syDB('member')->decrField(array('id'=>$this->my['id']),'money',$price);
				if(!$accounts)message('扣费失败，请重新提交订单付款操作','?c=member&a=myorder&oid='.$order['id']);
				$row=array(
					'uid'=>$this->my['id'],
					'paymenttype'=>1,
					'orderid'=>$order['orderid'],
					'money'=>$price,
					'custom'=>'',
					'payment'=>'cashbalance',
					'paymentno'=>'',
					'molds'=>'',
					'aid'=>'',
					'addtime'=>time(),
					'auser'=>'',
					'type'=>3,
				);
				$a=syClass('syaccount',array($row))->payment();
				message($a['msg'],$a['url']);
			break;
			default://在线支付
				syClass($pid,null,'include/payment/'.$pid.'/'.$pid.'.php')->payment($order['orderid'],$subject,$body,$price,$this->my['id']);
			break;
		}
	}
	function recharge(){
		$pid=$this->syArgs('payment',1);
		$price=$this->syArgs('recharge',3);
		if($pid=='')message("请选择支付平台");
		if($price<=0)message("输入充值金额");
		$subject='会员'.$this->my['user'].'在线充值';
		$body='会员'.$this->my['user'].'在线充值';
		$payment=syDB('payment')->find(array('pay'=>$pid));
		$service=unserialize($payment['keyv']);
		if($service['service']==2)message($payment['name'].'担保交易不能进行在线充值');
		syClass($pid,null,'include/payment/'.$pid.'/'.$pid.'.php')->payment('cz'.time().mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9),$subject,$body,$price,$this->my['id']);
	}
	private function order_add($goods,$virtual=1,$logistics='',$info='',$unote=''){
		$uidlong=strlen($this->my['id']);
		if($uidlong<8)$uidlong=sprintf("%08d",$uidlong);
		$orderid=time().$uidlong.mt_rand(0,9).mt_rand(0,9);
		$row=array(
			'goods'=>serialize($goods),
			'logistics'=>$logistics,
			'virtual'=>$virtual,
			'uid'=>$this->my['id'],
			'orderid'=>$orderid,
			'addtime'=>time(),
			'payment'=>'',
			'paymentno'=>'',
			'info'=>serialize($info),
			'unote'=>$unote,
			'rnote'=>'',
			'anote'=>'',
			'sendgoods'=>'',
		);
		$newv == $this->c->syVerifier($row);
		if($newv ==false){
			$o=$this->c->create($row);
			if(!$o)message('订单保存失败，请重新提交');	
			return $o;
		}else{message_err($newv);}
	}
	private function goods_db($ids,$logistics){
		if(!is_array($ids)){
			$va=$this->m->find(array('id'=>$this->id,'isshow'=>1),null,'title,tid,price');
			if($va){
				$goods[0]['aid']=$this->id;
				$goods[0]['attribute']=$this->syArgs('attribute',2);
				$goods[0]['quantity']=$this->syArgs('quantity',0,1);
				$goods[0]['title']=$va['title'];
				$goods[0]['tid']=$va['tid'];
				$attribute_db=$this->attribute_db($goods[0]['attribute'],$this->id,$va['price']);
				$goods[0]['attribute_txt']=$attribute_db['txt'];
				$priceva=$attribute_db['price'];
				$discount=syDB('product_discount')->find(array('aid'=>$this->id,'mgid'=>$this->my['group']['gid']));
				if($discount&&$discount['type']!=0){
					$goods[0]['discount'][0]=$attribute_db['price'];
					if($discount['type']==1&&$discount['discount']>0&&$discount['discount']<10){
						$priceva=round($attribute_db['price']*($discount['discount']/10), 2);
						$goods[0]['discount'][1]=rtrim(rtrim($discount['discount'],'0'),'.0').'折';
					}
					if($discount['type']==2&&$discount['discount']>0&&$discount['discount']<$attribute_db['price']){
						$priceva=$attribute_db['price']-$discount['discount'];
						$goods[0]['discount'][1]='直减'.$discount['discount'].'元';
					}
				}				
				$goods[0]['price']=$priceva;
				$goods[0]['total']=$priceva*$goods[0]['quantity'];
			}
		}else{
			$goods=array();
			foreach($ids as $k=>$v){
				$va=$this->m->find(array('id'=>$v['aid'],'isshow'=>1),null,'title,tid,price,logistics');
				if($va){
					$goods[$k]['cartid']=$v['cartid'];
					$goods[$k]['aid']=$v['aid'];
					$goods[$k]['attribute']=$v['attribute'];
					$goods[$k]['quantity']=$v['quantity'];
					$goods[$k]['title']=$va['title'];
					$goods[$k]['tid']=$va['tid'];
					$logistics_price=unserialize($va['logistics']);
					$goods[$k]['logistics_price']=$logistics_price[$logistics]*$v['quantity'];
					$attribute_db=$this->attribute_db($v['attribute'],$v['aid'],$va['price']);
					$goods[$k]['attribute_txt']=$attribute_db['txt'];
					$priceva=$attribute_db['price'];
					$discount=syDB('product_discount')->find(array('aid'=>$v['aid'],'mgid'=>$this->my['group']['gid']));
					if($discount&&$discount['type']!=0){
						$goods[$k]['discount'][0]=$attribute_db['price'];
						if($discount['type']==1&&$discount['discount']>0&&$discount['discount']<10){
							$priceva=round($attribute_db['price']*($discount['discount']/10), 2);
							$goods[$k]['discount'][1]=rtrim(rtrim($discount['discount'],'0'),'.0').'折';
						}
						if($discount['type']==2&&$discount['discount']>0&&$discount['discount']<$attribute_db['price']){
							$priceva=$attribute_db['price']-$discount['discount'];
							$goods[$k]['discount'][1]='直减'.$discount['discount'].'元';
						}
					}
					$goods[$k]['price']=$priceva;
					$goods[$k]['total']=$priceva*$v['quantity'];
				}
			}
		}
		return $goods;
	}
	private function attribute_db($ara,$m_id,$price){
		$p_type=syDB('attribute_type')->findSql('select distinct a.tid,a.aid,b.tid,b.isshow,b.orders,b.name from '.$this->db.'product_attribute a left join '.$this->db.'attribute_type b on (a.tid=b.tid) where a.aid='.$m_id.' and b.isshow=1 order by b.orders desc,b.tid desc');
		$ov['price']=$price;
		foreach($p_type as $v){
			if(!$ara[$v['tid']])message('请选择'.$v['name']);
			$p=syDB('product_attribute')->find(array('aid' => $m_id,'tid' => $v['tid'],'sid' => $ara[$v['tid']]),null,'price');
			if(!$p)message('规格['.$v['name'].']选择有误或不存在');
			$ov['price']=$ov['price']+$p['price'];
			$a=syDB('attribute')->find(array('sid' => $ara[$v['tid']]),null,'name');
			$ov['txt'].=$v['name'].'('.$a['name'].') ';
		}
		return $ov;
	}
	private function cart_db(){
		$g=syDB('goodscart')->findAll(array('uid'=>$this->my['id']),'aid desc,id desc');
		$gs=array();$i=0;
		foreach($g as $v){
			$gs[$i]= array(
				'cartid' => $v['id'],
				'aid' => $v['aid'],
				'attribute' => unserialize($v['attribute']),
				'quantity' => $v['num'],
			);
			$i++;
		}
		if(!$gs)message('您的购物车是空的哦','?c=member');
		return $gs;
	}
	private function goods_array($item, $key){
		if($key=='a') echo "$item<br>";
	}
}	