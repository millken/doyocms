<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class alipay{
	public function __construct(){
		$alipay=syDB('payment')->find(array('pay'=>'alipay'),null,'keyv');
		$alipay=unserialize($alipay['keyv']);
		$this->partner=$alipay['pid'];
		$this->seller_email=$alipay['user'];
		$this->keys=dykeycode($alipay['key']);
		if($alipay['service']==1){
			$this->service='create_direct_pay_by_user';
		}else{
			$this->service='create_partner_trade_by_buyer';
		}//支付类型，2担保，1即时
		$this->domain=get_domain();
	}
	public function payment($out_trade_no,$subject,$body,$total_fee,$member){
		$p = array(
			"service"			=> $this->service,
			"_input_charset"	=> 'utf-8',
			"payment_type"		=> '1',
			"partner"			=> $this->partner,
			"seller_email"		=> $this->seller_email,
			"return_url"		=> rtrim($this->domain,'/').'/include/payment/alipay/return.php',
			"notify_url"		=> rtrim($this->domain,'/').'/include/payment/alipay/notify.php',
			"out_trade_no"		=> $out_trade_no,
			"subject"			=> newstr($subject,120),
			"body"				=> newstr($body,500),
			"total_fee"			=> $total_fee,
			"extra_common_param"=> $member,
		);
		if($this->service=='create_partner_trade_by_buyer'){
			$p['logistics_type']='EXPRESS';
			$p['logistics_fee']='0.00';
			$p['logistics_payment']='SELLER_PAY';
			$p['price']=$total_fee;
			$p['quantity']=1;
			$p['total_fee']='';
		}
		$parameter=$this->paraFilter($p);
		$parameter['sign']=$this->getMysign($parameter);
		$this->buildForm($parameter,$gettxt);
	}
	public function sendgoods($l){
		$p = array(
				"service"			=> 'send_goods_confirm_by_platform',
				"sign_type"			=> 'MD5',
				"partner"			=> $this->partner,
				"_input_charset"	=> 'utf-8',
				"trade_no"			=> $trade_no= $l['trade_no'],
				"logistics_name"	=> $l['logistics_name'],
				"invoice_no"		=> $l['invoice_no'],
				"transport_type"	=> $l['transport_type'],
		);
		$parameter=$this->paraFilter($p);
		$parameter['sign']=$this->getMysign($parameter);
		foreach($parameter as $k=>$v){$g.='&'.$k.'='.$v;}
		$g=substr($g,1);
		return $this->getHttps($g);
	}
	public function getHttps($g) {
		$url='https://mapi.alipay.com/gateway.do?'.$g;
		if(function_exists('curl_init')){
			$sendgoods=curl_get($url);
			$x=xml_array($sendgoods);
		}else{
			$x=xml_array('',$url);
		}
		print_r($x);exit;
		if($x){
			if($x['is_success']=='T'){return true;}else{return false;}
		}else{
			if($sendgoods){
				preg_match_all('/<is_success>(.*)<\/is_success>/i',$sendgoods,$d);
				if(strpos($sendgoods,'<is_success>T</is_success>')!==FALSE&&$d[1][0]=='T'){
					return true;
				}else{
					return false;
				}
			}else{
				exit('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">无法与支付宝进行数据验证，请检查：<br />1. 建议安装CURL函数,或开启php的simplexml函数。<br />2. 检查是否防火墙阻止了APACHE/PHP访问网络。');
			}
		}
	}
	private function buildForm($parameter) {
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='https://mapi.alipay.com/gateway.do?_input_charset=utf-8' method='post'>";
		foreach($parameter as $k=>$v){
            $sHtml.= "<input type='hidden' name='".$k."' value='".$v."'/>";
        }
		$sHtml.= "<input type='hidden' name='sign_type' value='MD5'/>";
        $sHtml = $sHtml."<input type='submit' value='go' style='display:none'></form>";
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div style="margin:0 auto; width:300px;text-align:center; padding-top:150px; font-size:12px;"><img src="include/js/loading.gif" /><br /><br />正在进入支付中心 请稍后</div>'.$sHtml.'</body></html>';
	}
	
	public function verify_get(){
		if(empty($_POST)){
			if(empty($_GET)){return false;}else{$alipay_notify=$_GET;}
		}else{
			$alipay_notify=$_POST;
		}
		$mysign = $this->paraFilter($alipay_notify);
		$mysign = $this->getMysign($mysign);
		$responseTxt = 'true';
		if (! empty($alipay_notify["notify_id"])) {$responseTxt = $this->getResponse($alipay_notify["notify_id"]);}
		if ($responseTxt=='true' && $mysign == $alipay_notify["sign"] && ($alipay_notify['trade_status']=='TRADE_FINISHED' || $alipay_notify['trade_status']=='TRADE_SUCCESS' || $alipay_notify['trade_status'] == 'WAIT_SELLER_SEND_GOODS')) {
			return $alipay_notify;
		} else {
			return false;
		}
	}
	private function paraFilter($p) {
		$po=array();
		foreach($p as $k=>$v){
			if($v!=''&&$k!='sign'){$po[$k] = $v;}
		}
		ksort($po);reset($po);
		return $po;
	}
	private function getMysign($p) {
		$g='';
		foreach($p as $k=>$v){if($k!='sign_type'){$g.='&'.$k.'='.$v;}}
		return md5(substr($g,1).$this->keys);
	}
	public function getResponse($notify_id) {
		$url="http://notify.alipay.com/trade/notify_query.do?_input_charset=utf-8&partner=".$this->partner."&notify_id=".$notify_id;
		if(function_exists('curl_init')){
			$responseText=curl_get($url);
		}else{
			$ctx = stream_context_create(array('http' => array('timeout' => 120))); 
			$responseText=file_get_contents($url, 0, $ctx);
			if($responseText===FALSE)exit('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">无法与支付宝进行数据验证，请检查：<br />1. 设置php.ini的allow_url_fopen为On。<br />2. 检查是否防火墙阻止了APACHE/PHP访问网络。<br />3. 建议安装CURL函数');
		}
		return $responseText;
	}
	public function success($db) {
		if($db['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
			$order=syDB('order')->find(array('orderid'=>$db['out_trade_no']),null,'uid');
			$uid=$order['uid'];
			$paymenttype=2;
		}else{
			$extra=explode(',',$db['extra_common_param']);
			$uid=$db['extra_common_param'];
			$paymenttype=1;
		}
		$row=array(
			'uid'=>$uid,
			'paymenttype'=>$paymenttype,
			'orderid'=>$db['out_trade_no'],
			'money'=>$db['total_fee'],
			'custom'=>'',
			'payment'=>'alipay',
			'paymentno'=>$db['trade_no'],
			'molds'=>'',
			'aid'=>'',
			'addtime'=>strtotime($db['notify_time']),
			'auser'=>'',
		);
		if(substr($db['out_trade_no'],0,2)=='cz'){$row['type']=1;}else{$row['type']=3;}
		$a=syClass('syaccount',array($row))->payment();
		return $a;
	}
}