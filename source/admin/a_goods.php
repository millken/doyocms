<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_goods extends syController
{
	function __construct(){
		parent::__construct();
		$this->types=syClass('syclasstype');
		$this->typesdb=$this->types->type_txt();
		$this->Gets=$this->syArgs('a',1);
		$this->Class=syClass('c_attribute');
		$this->Classo=syClass('c_order');
		$this->opers='<a href="?c=a_goods">订单管理</a><a href="?c=a_goods&a=attribute">规格管理</a><a href="?c=a_goods&a=tadd">添加规格</a><a href="?c=a_goods&a=logistics">物流管理</a>';
		
		if($this->Gets=='add' || $this->Gets=='edit'){
			$this->newrow = array(
				'tid' => $this->syArgs('tid'),
				'name' => $this->syArgs('name',1),
				'isshow' => $this->syArgs('isshow'),
				'orders' => $this->syArgs('orders'),
			);
		}
		if($this->Gets=='tadd' || $this->Gets=='tedit'){
			$this->newrow = array(
				'name' => $this->syArgs('name',1),
				'isshow' => $this->syArgs('isshow',1),
				'orders' => $this->syArgs('orders'),
				'classtype' => '|'.implode('|',$this->syArgs('classtype',2)).'|',
			);
		}
	}
	function index(){
		$this->toptxt='订单管理';
		if($this->syArgs('state',1)!=''){$w.='and state='.$this->syArgs('state').' ';$this->state=$this->syArgs('state',1);}
		if($this->syArgs('orderid',1)!=''){$w.='and orderid='.$this->syArgs('orderid',1).' ';}
		if($this->syArgs('uid',1)!=''){$w.='and uid='.$this->syArgs('uid',1).' ';}
		if($w)$w=substr($w,3);
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'order');
		$this->lists =$this->Classo->syPager($this->gopage,15,$total_page)->findAll($w,' addtime desc,id desc ','id,uid,orderid,favorable,goods,state,addtime,logistics,paytime');
		$this->pages = pagetxt($this->Classo->syPager()->getPager());
		$this->display("goods.html");
	}
	function orderedit(){
		$this->d=syDB('order')->find(array('id'=>$this->syArgs('id')));
		$this->goods=order_goods(unserialize($this->d['goods']),$this->d['logistics']);
		if ($this->syArgs('run')==1){
			if($this->syArgs('state')>1&&$this->d['virtual']==0){
				if(!$this->syArgs('logistics_name',1)||!$this->syArgs('invoice_no',1)||!$this->syArgs('transport_type',1))message_a("请填写完整发货信息");
				$sendgoods['logistics_name']=$this->syArgs('logistics_name',1);
				$sendgoods['invoice_no']=$this->syArgs('invoice_no',1);
				$sendgoods['transport_type']=$this->syArgs('transport_type',1);
			}
			if($this->syArgs('paytime',1)!=0){$paytime=strtotime($this->syArgs('paytime',1));}else{$paytime=0;}
			$newrow=array(
			'favorable'=>$this->syArgs('favorable',3),
			'info'=>serialize($this->syArgs('info',2)),
			'sendgoods'=>serialize($sendgoods),
			'unote'=>$this->syArgs('unote',1),
			'state'=>$this->syArgs('state'),
			'rnote'=>$this->syArgs('rnote',1),
			'anote'=>$this->syArgs('anote',1),
			'addtime'=>strtotime($this->syArgs('addtime',1)),
			'paytime'=>$paytime,
			);
			$newVerifier=$this->Classo->syVerifier($newrow);
			if(false == $newVerifier){
				if(syDB('order')->update(array('id'=>$this->d['id']),$newrow)){
					if($this->d['state']!=2&&$newrow['state']==2){
						$pt=syDB('account')->find(array('orderid'=>$this->d['orderid'],'payment'=>$this->d['payment']),null,'paymenttype');
						if($pt['paymenttype']!=1){
							if($this->d['payment']=='alipay'){
								$sg=array(
									'trade_no'=>$this->d['paymentno'],
									'logistics_name'=>$sendgoods['logistics_name'],
									'invoice_no'=>$sendgoods['invoice_no'],
									'transport_type'=>$sendgoods['transport_type'],
								);
								$s=syClass('alipay',null,'include/payment/alipay/alipay.php')->sendgoods($sg);	
								if($s===false){message_a("订单修改成功,发送支付宝已发货状态失败");}					
							}
							if($this->d['payment']=='alipay'){
								message_a('订单修改成功，请登陆财付通修改订单号'.$this->d['paymentno'].'状态为已发货。','','<a href="http://mch.tenpay.com/" target="_blank">进入财付通</a><a href="?c=a_goods">以后再说</a>',0);
							}
						}
					}
					if($this->d['state']==0&&($newrow['state']==1||$newrow['state']==2||$newrow['state']==9)){
						$total=0;
						foreach($this->goods as $v){
							$total=calculate($total,$v['total']);
							$total=calculate($total,$v['logistics_price']);
						}
						$price=calculate($total, $this->d['favorable'],2);
						$row=array(
							'uid'=>$this->d['uid'],
							'paymenttype'=>1,
							'orderid'=>$this->d['orderid'],
							'money'=>$price,
							'custom'=>'',
							'payment'=>'admin',
							'paymentno'=>'',
							'molds'=>'',
							'aid'=>'',
							'addtime'=>time(),
							'auser'=>'',
							'type'=>3,
						);
						syClass('syaccount',array($row))->payment();
					}
					message_a("订单修改成功","?c=a_goods");
				}else{message_a("订单修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='修改订单';
		$this->postgo='orderedit';
		$this->display("goods.html");
	}
	function orderdel(){
		$this->toptxt='删除订单';
		$this->d=syDB('order')->find(array('id'=>$this->syArgs('id')));
		if($this->d['state']!=0)message_a("已支付订单不能删除，如确要删除，请先修改订单状态为未支付");
		if ($this->syArgs('run')==1){
			if(syDB('order')->delete(array('id'=>$this->syArgs('id')))){
				syDB('sales_record')->delete(array('oid'=>$this->syArgs('id')));
				syDB('account')->delete(array('orderid'=>$this->d['orderid']));
				message_a("订单删除成功","?c=a_goods");
			}else{message_a("订单删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除订单 <strong>['.$this->d['orderid'].']</strong> 吗？';
		$this->msg='警告：删除订单将自动删除关联财务及购买记录，并不可恢复！';
		$this->msggo='<a href="?c=a_goods&a=orderdel&run=1&id='.$this->d['id'].'">确定删除</a><a href="?c=a_goods">取消操作</a>';
		$this->display("msg.html");
	}
	function attribute(){
		$this->toptxt='规格管理';
		$this->lists = syDB('attribute_type')->findAll();
		$this->display("goods.html");
	}
	function option(){
		$this->toptxt='规格选项管理';
		if($this->syArgs('tid'))$c=array('tid'=>$this->syArgs('tid'));
		$this->lists = $this->Class->findAll($c,' orders desc,sid desc ');
		$this->display("goods.html");
	}
	function tadd(){
		if ($this->syArgs('run')==1){
			if($this->newrow['name']=='') message_a("规格名称不能为空");
			if(syDB('attribute_type')->create($this->newrow)){
				message_a("规格创建成功","?c=a_goods&a=attribute");
			}else{message_a("规格创建失败，请重新提交");}
		}
		$this->toptxt='添加规格';
		$this->postgo='tadd';
		$this->display("goods.html");
	}
	function tedit(){
		$this->d=syDB('attribute_type')->find(array('tid'=>$this->syArgs('tid')));
		if ($this->syArgs('run')==1){
			if($this->newrow['name']=='') message_a("规格不能为空");
			if(syDB('attribute_type')->update(array('tid'=>$this->d['tid']),$this->newrow)){
				message_a("规格修改成功","?c=a_goods&a=attribute");
			}else{message_a("规格修改失败,请重新提交");}
		}
		$this->toptxt='修改规格';
		$this->postgo='tedit';
		$this->display("goods.html");
	}
	function tdel(){
		$this->toptxt='删除规格';
		$this->d=syDB('attribute_type')->find(array('tid'=>$this->syArgs('tid')));
		if ($this->syArgs('run')==1){
			syDB('product_attribute')->delete(array('tid'=>$this->syArgs('tid')));
			syDB('attribute')->delete(array('tid'=>$this->syArgs('tid')));
			if(syDB('attribute_type')->delete(array('tid'=>$this->syArgs('tid'))))
			{message_a("规格删除成功","?c=a_goods&a=attribute");}else{message_a("规格删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除规格 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='删除规格，将自动删除本规格下的所有选项，及已添加产品的相关规格数据，删除后不可恢复';
		$this->msggo='<a href="?c=a_goods&a=tdel&run=1&tid='.$this->d['tid'].'">确定删除</a><a href="?c=a_goods&a=attribute">取消操作</a>';
		$this->display("msg.html");
	}
	function add(){
		$this->attribute_type=syDB('attribute_type')->findAll();
		if($this->syArgs('tid'))$this->ctid=$this->syArgs('tid');
		if ($this->syArgs('run')==1){
			$newVerifier=$this->Class->syVerifier($this->newrow);
			if(false == $newVerifier){
				if(syDB('attribute')->create($this->newrow)){
					deleteDir($GLOBALS['G_DY']['sp_cache']);
					message_a("规格选项创建成功","?c=a_goods&a=option");
				}else{message_a("规格选项创建失败，请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='添加规格选项';
		$this->postgo='add';
		$this->display("goods.html");
	}
	function edit(){
		$this->attribute_type=syDB('attribute_type')->findAll();
		$this->d=syDB('attribute')->find(array('sid'=>$this->syArgs('sid')));
		if ($this->syArgs('run')==1){
			$newVerifier=$this->Class->syVerifier($this->newrow);
				if(false == $newVerifier){
				if(syDB('attribute')->update(array('sid'=>$this->d['sid']),$this->newrow)){
					if($this->newrow['tid']!=$this->d['tid'])syDB('product_attribute')->update(array('sid'=>$this->d['sid']),array('tid'=>$this->newrow['tid']));
					deleteDir($GLOBALS['G_DY']['sp_cache']);
					message_a("规格选项修改成功","?c=a_goods&a=option");
				}else{message_a("规格选项修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='修改规格选项';
		$this->postgo='edit';
		$this->display("goods.html");
	}
	function del(){
		$this->toptxt=$this->moldname.'删除规格选项';
		$this->d=syDB('attribute')->find(array('sid'=>$this->syArgs('sid')));
		if ($this->syArgs('run')==1){
			if(syDB('attribute')->delete(array('sid'=>$this->syArgs('sid')))){
				syDB('product_attribute')->delete(array('sid'=>$this->syArgs('sid')));
				deleteDir($GLOBALS['G_DY']['sp_cache']);
				message_a("规格选项删除成功","?c=a_goods&a=option");
			}else{message_a("规格选项删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删规格选项 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='删除此选项，将自动删除已添加产品的相关选项数据，删除后不可恢复';
		$this->msggo='<a href="?c=a_goods&a=del&run=1&sid='.$this->d['sid'].'">确定删除</a><a href="?c=a_goods&a=option">取消操作</a>';
		$this->display("msg.html");
	}
	function attribute_ajax(){
		$id=$this->syArgs('id');
		if(!$this->syArgs('tid')){echo '<dl><dt>规格选项：</dt><dd>未选择栏目，无法加载规格选项，请先选择栏目。</dd></dl>';}
		$t=syDB('attribute_type')->findAll('isshow=1 and classtype like "%|'.$this->syArgs('tid').'|%"');
		foreach($t as $v){
			$a=syDB('attribute')->findAll(array('isshow'=>1,'tid'=>$v['tid']));
			$t='';
			foreach($a as $vv){
				if($id)$c=syDB($this->syArgs('molds',1).'_attribute')->find(array('aid'=>$id,'sid'=>$vv['sid']));
				if($c){$checked='checked="checked"';$aprice=$c['price'];}else{$checked='';$aprice=0;}
				$t.='<input name="attribute'.$v['tid'].'[]" type="checkbox" value="'.$vv['sid'].'" '.$checked.' /> <strong>'.$vv['name'].'</strong>&nbsp;&nbsp;&nbsp;价格增减：<input name="aprice'.$vv['sid'].'" type="text" class="int" style="width:50px; height:12px;" value="'.$aprice.'" /> 元<br />';
			}
			echo '<dl><dt>'.$v['name'].'：</dt><dd>'.$t.'</dd></dl>';
		}
	}
	function logistics(){
		$this->toptxt='物流管理';
		$this->lists = $GLOBALS['G_DY']['logistics'];
		$this->display("goods.html");
	}
	function logisticsedit(){
		$incfile='include/inc.php';
		$inc_tp=@fopen($incfile,"r");
		$inc_txt=@fread($inc_tp,filesize($incfile));
		@fclose($inc_tp);
		$name=$this->syArgs('name',2);
		$price=$this->syArgs('price',2);
		foreach($name as $k=>$v){
			if($name[$k]){
				if(!is_numeric($price[$k]))$price[$k]=0;
				$txt.="'".$name[$k]."'=>".$price[$k].",";
			}
		}
		$inc_txt=preg_replace("/'logistics' => array\(.*?\),\s/","'logistics' => array(".$txt."),",$inc_txt);
		$inc_tpl=@fopen($incfile,"w");
		@fwrite($inc_tpl,$inc_txt);
		@fclose($inc_tpl);
		message_a("物流修改成功","?c=a_goods&a=logistics");
	}
}	