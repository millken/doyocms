<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class tenpay{
	public function __construct(){
		$tenpay=syDB('payment')->find(array('pay'=>'tenpay'),null,'keyv');
		$tenpay=unserialize($tenpay['keyv']);
		$this->partner=$tenpay['pid'];
		$this->keys=dykeycode($tenpay['key']);
		$this->paymenttype=$tenpay['service'];//接口类型，2担保，1即时
		$this->domain=get_domain();
	}
	public function payment($out_trade_no,$subject,$body,$total_fee,$member,$virtualtype=0){
		$p = array(
			'trade_mode'		=> $this->paymenttype,
			'input_charset'		=> 'utf-8',
			'partner'			=> $this->partner,
			'return_url'		=> rtrim($this->domain,'/').'/include/payment/tenpay/return.php',
			'notify_url'		=> rtrim($this->domain,'/').'/include/payment/tenpay/notify.php',
			'out_trade_no'		=> $out_trade_no,
			'subject'			=> newstr($subject,120),
			'body'				=> newstr($body,500),
			'total_fee'			=> $total_fee*100,
			'fee_type'			=> 1,
			'spbill_create_ip'	=> GetIP(),
			'attach'=> $member.','.$this->paymenttype,
		);
		if($this->paymenttype!=1&&$virtualtype==1)$p['trans_type']=2;
		$parameter=$this->paraFilter($p);
		$parameter['sign']=$this->getMysign($parameter);
		$this->buildForm($parameter,$gettxt);
	}
	//确认发货接口
	public function sendgoods($l){

	}
	
	private function buildForm($parameter) {
		$sHtml = "<form id='tenpaysubmit' name='tenpaysubmit' action='https://gw.tenpay.com/gateway/pay.htm' method='post'>";
		foreach($parameter as $k=>$v){
            $sHtml.= "<input type='hidden' name='".$k."' value='".$v."'/>";
        }
        $sHtml = $sHtml."<input type='submit' value='go' style='display:none'></form>";
		$sHtml = $sHtml."<script>document.forms['tenpaysubmit'].submit();</script>";
		echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div style="margin:0 auto; width:300px;text-align:center; padding-top:150px; font-size:12px;"><img src="include/js/loading.gif" /><br /><br />正在进入支付中心 请稍后</div>'.$sHtml.'</body></html>';
	}
	
	public function verify_get(){
		$tenpay_notify=array();
		foreach($_GET as $k => $v) {
			$tenpay_notify[$k] = $v;
		}
		foreach($_POST as $k => $v) {
			$tenpay_notify[$k] = $v;
		}
		$mysign = $this->paraFilter($tenpay_notify);
		$mysign = $this->getMysign($mysign);
		$responseTxt = $this->getResponse($tenpay_notify["notify_id"]);
		if ($responseTxt && $mysign == strtolower($tenpay_notify['sign']) && $tenpay_notify['trade_state']==0) {
			return $tenpay_notify;
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
		foreach($p as $k=>$v){$g.=$k.'='.$v.'&';}
		return strtolower(md5($g.'key='.$this->keys));
	}
	public function getResponse($notify_id) {
		$pd=array('input_charset'=>'utf-8','notify_id'=>$notify_id,'partner'=>$this->partner);
		$sign=$this->getMysign($pd);
		$url='https://gw.tenpay.com/gateway/simpleverifynotifyid.xml?input_charset=utf-8&partner='.$this->partner.'&notify_id='.$notify_id.'&sign='.$sign;
		if(function_exists('curl_init')){
			$responseText=curl_get($url);
			$x=xml_array($responseText);
		}else{
			$x=xml_array('',$url);
		}
		if($x){
			if($x['retcode']==0){return true;}else{return false;}
		}else{
			if($responseText){
				preg_match_all('/<retcode>(.*)<\/retcode>/i',$responseText,$d);
				if(strpos($responseText,'<retcode>0</retcode>')!==FALSE&&$d[1][0]=='0'){
					return true;
				}else{
					return false;
				}
			}else{
				exit('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">无法与财付通进行数据验证，请检查：<br />1. 建议安装CURL函数,或开启php的simplexml函数。<br />2. 检查是否防火墙阻止了APACHE/PHP访问网络。');
			}
		}
	}
	public function success($db) {
		$extra=explode(',',$db['attach']);
		$row=array(
			'uid'=>$extra[0],
			'paymenttype'=>$extra[1],
			'orderid'=>$db['out_trade_no'],
			'money'=>$db['total_fee']/100,
			'custom'=>'',
			'payment'=>'tenpay',
			'paymentno'=>$db['transaction_id'],
			'molds'=>'',
			'aid'=>'',
			'addtime'=>strtotime($db['time_end']),
			'auser'=>'',
		);
		if(substr($db['out_trade_no'],0,2)=='cz'){$row['type']=1;}else{$row['type']=3;}
		$a=syClass('syaccount',array($row))->payment();
		return $a;
	}
}