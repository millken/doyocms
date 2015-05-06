<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
function spRun(){
	GLOBAL $__controller, $__action;
	if($__controller=='syaccount'){
		syError('route Error');
		exit;
	}
	syClass('sysession');
	spLaunch("router_prefilter");
	$handle_controller = syClass($__controller, null, $GLOBALS['G_DY']["controller_path"].'/'.$__controller.".php");
	if(!is_object($handle_controller) || !method_exists($handle_controller, $__action)){
		syError('route Error');
		exit;
	}
	$handle_controller->$__action();
	if(FALSE != $GLOBALS['G_DY']['view']['auto_display']){
		$__tplname = $__controller.$GLOBALS['G_DY']['view']['auto_display_sep'].
				$__action.$GLOBALS['G_DY']['view']['auto_display_suffix']; 
		$handle_controller->auto_display($__tplname);
	}
	spLaunch("router_postfilter");
}

function dump($vars, $output = TRUE, $show_trace = FALSE){
	if(TRUE != SP_DEBUG && TRUE != $GLOBALS['G_DY']['allow_trace_onrelease'])return;
	if( TRUE == $show_trace ){ 
		$content = syError(htmlspecialchars(print_r($vars, true)), TRUE, FALSE);
	}else{
		$content = "<div align=left><pre>\n" . htmlspecialchars(print_r($vars, true)) . "\n</pre></div>\n";
	}
    if(TRUE != $output) { return $content; } 
       echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></head><body>{$content}</body></html>"; 
	   return;
}

function import($sfilename, $auto_search = TRUE, $auto_error = FALSE){
	if(isset($GLOBALS['G_DY']["import_file"][md5($sfilename)]))return TRUE;
	if( TRUE == @is_readable($sfilename) ){
		require($sfilename); 
		$GLOBALS['G_DY']['import_file'][md5($sfilename)] = TRUE; 
		return TRUE;
	}else{
		if(TRUE == $auto_search){
			foreach(array_merge( $GLOBALS['G_DY']['include_path'], array($GLOBALS['G_DY']['model_path']), $GLOBALS['G_DY']['sp_include_path'] ) as $sp_include_path){
				if(isset($GLOBALS['G_DY']["import_file"][md5($sp_include_path.'/'.$sfilename)]))return TRUE;
				if( is_readable( $sp_include_path.'/'.$sfilename ) ){
					require($sp_include_path.'/'.$sfilename);
					$GLOBALS['G_DY']['import_file'][md5($sp_include_path.'/'.$sfilename)] = TRUE;
					return TRUE;
				}
			}
		}
	}
	if( TRUE == $auto_error )syError("未能找到名为：{$sfilename}的文件");
	return FALSE;
}

function syAccess($method, $name, $value = NULL, $life_time = -1){
	if( $launch = spLaunch("function_access", array('method'=>$method, 'name'=>$name, 'value'=>$value, 'life_time'=>$life_time), TRUE) )return $launch;
	if(!is_dir($GLOBALS['G_DY']['sp_cache']))__mkdirs($GLOBALS['G_DY']['sp_cache']);
	$sfile = $GLOBALS['G_DY']['sp_cache'].'/'.$GLOBALS['G_DY']['sp_app_id'].md5($name).".php";
	if('w' == $method){ 
		$life_time = ( -1 == $life_time ) ? '300000000' : $life_time;
		$value = '<?php die();?>'.( time() + $life_time ).serialize($value);
		return file_put_contents($sfile, $value);
	}elseif('c' == $method){
		return @unlink($sfile);
	}else{
		if( !is_readable($sfile) )return FALSE;
		$arg_data = file_get_contents($sfile);
		if( substr($arg_data, 14, 10) < time() ){
			@unlink($sfile); 
			return FALSE;
		}
		return unserialize(substr($arg_data, 24)); 
	}
}

function syClass($class_name, $args = null, $sdir = null, $force_inst = FALSE){
	if(preg_match("/^[a-zA-Z0-9_\-]*$/",$class_name)==0)syError("类定义不存在，请检查。");
	if(TRUE != $force_inst)if(isset($GLOBALS['G_DY']["inst_class"][$class_name]))return $GLOBALS['G_DY']["inst_class"][$class_name];
	if(null != $sdir && !import($sdir) && !import($sdir.'/'.$class_name.'.php'))return FALSE;
	$has_define = FALSE;
	if(class_exists($class_name, false) || interface_exists($class_name, false)){
		$has_define = TRUE;
	}else{
		if( TRUE == import($class_name.'.php')){
			$has_define = TRUE;
		}
	}
	if(FALSE != $has_define){
		$argString = '';$comma = ''; 
		if(null != $args)for ($i = 0; $i < count($args); $i ++) { $argString .= $comma . "\$args[$i]"; $comma = ', ';}
		eval("\$GLOBALS['G_DY']['inst_class'][\$class_name]= new \$class_name($argString);"); 
		return $GLOBALS['G_DY']["inst_class"][$class_name];
	}
	syError($class_name."类定义不存在，请检查。");
}
function curl_get($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	$arr = explode("?", $url);
	if(count($arr) >= 2) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $arr[0]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $arr[1]);
	}else{
		curl_setopt($ch, CURLOPT_URL, $url);
	}
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}
function xml_array($content,$url='') {
	if($url){$xml = simplexml_load_file($url);}else{$xml = simplexml_load_string($content);}
	$x=array();
	if($xml && $xml->children()) {
		foreach ($xml->children() as $node){
			if($node->children()) {
				$k = $node->getName();
				$nodeXml = $node->asXML();
				$v = substr($nodeXml, strlen($k)+2, strlen($nodeXml)-2*strlen($k)-5);
			} else {
				$k = $node->getName();
				$v = (string)$node;
			}
			$x[$k]=$v;		
		}
	}
	return $x;
}
function syError($msg, $output = TRUE, $stop = TRUE){
	if($GLOBALS['G_DY']['sp_error_throw_exception'])throw new Exception($msg);
	if(TRUE != SP_DEBUG){
		//error_log($msg);
		echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
		echo "<body>程序出错！<br><br>出现此情况可能为：1、模板标签书写错误。2、修改过程序代码，代码修改有误。<br><br>请在后台系统设置中开启PHP错误调试，或者修改include\inc.php第一行'mode' => 'debug'后查看信息错误记录<br><br>您可以购买官方商业服务，为您提供全程服务http://wdoyo.com</body></html>";
		if(TRUE == $stop)exit;
	}
	$traces = debug_backtrace();
	$bufferabove = ob_get_clean();
	require_once($GLOBALS['G_DY']['notice_php']);
	if(TRUE == $stop)exit;
}


function spLaunch($configname, $launchargs = null, $returns = FALSE ){
	if( isset($GLOBALS['G_DY']['launch'][$configname]) && is_array($GLOBALS['G_DY']['launch'][$configname]) ){
		foreach( $GLOBALS['G_DY']['launch'][$configname] as $launch ){
			if( is_array($launch) ){
				$reval = syClass($launch[0])->{$launch[1]}($launchargs);
			}else{
				$reval = call_user_func_array($launch, $launchargs);
			}
			if( TRUE == $returns )return $reval;
		}
	}
	return false;
}

function spUrl($geturl = null, $controller = null, $action = null, $args = null, $anchor = null, $no_sphtml = FALSE) {
	if(TRUE == $GLOBALS['G_DY']['html']["enabled"] && TRUE != $no_sphtml){
		$realhtml = syhtml::getUrl($geturl, $controller, $action, $args, $anchor);if(isset($realhtml[0]))return $realhtml[0];
	}
	$geturl = ( null != $geturl ) ? $geturl :  basename(__FILE__);
	$controller = ( null != $controller ) ? $controller : $GLOBALS['G_DY']["default_controller"];
	$action = ( null != $action ) ? $action : $GLOBALS['G_DY']["default_action"];
	if( $launch = spLaunch("function_url", array('controller'=>$controller, 'action'=>$action, 'args'=>$args, 'anchor'=>$anchor, 'no_sphtml'=>$no_sphtml), TRUE ))return $launch;
	if( TRUE == $GLOBALS['G_DY']['url']["url_path_info"] ){
		$url = "{$controller}/{$controller}/{$action}";
		if(null != $args)foreach($args as $key => $arg) $url .= "/{$key}/{$arg}";
	}else{
		$url = $geturl."?". $GLOBALS['G_DY']["url_controller"]. "={$controller}&";
		$url .= $GLOBALS['G_DY']["url_action"]. "={$action}";
		if(null != $args)foreach($args as $key => $arg) $url .= "&{$key}={$arg}";
	}
	if(null != $anchor) $url .= "#".$anchor;
	return $url;
}

function __mkdirs($dir, $mode = 0755)
{
	if (!is_dir($dir)) {
		__mkdirs(dirname($dir), $mode);
		return @mkdir($dir, $mode);
	}
	return true;
}
function syExt($ext_node_name)
{
	return (empty($GLOBALS['G_DY']['ext'][$ext_node_name])) ? FALSE : $GLOBALS['G_DY']['ext'][$ext_node_name];
}
function syCus($ext_node_name)
{
	return (empty($GLOBALS['G_DY']['cus'][$ext_node_name])) ? FALSE : $GLOBALS['G_DY']['cus'][$ext_node_name];
}
function spAddViewFunction($alias, $callback_function)
{
	return $GLOBALS['G_DY']["view_registered_functions"][$alias] = $callback_function;
}

function syDB($tbl_name, $pk = null){
	$modelObj = syClass("syModel");
	$modelObj->tbl_name = (TRUE == $GLOBALS['G_DY']["db_spdb_full_tblname"]) ? $tbl_name :	$GLOBALS['G_DY']['db']['prefix'] . $tbl_name;
	if( !$pk ){
		@list($pk) = $modelObj->_db->getTable($modelObj->tbl_name);$pk = $pk['Field'];
	}
	$modelObj->pk = $pk;
	return $modelObj;
}

function spConfigReady( $preconfig, $useconfig = null){
	$nowconfig = $preconfig;
	if (is_array($useconfig)){
		foreach ($useconfig as $key => $val){
			if (is_array($useconfig[$key])){
				@$nowconfig[$key] = is_array($nowconfig[$key]) ? spConfigReady($nowconfig[$key], $useconfig[$key]) : $useconfig[$key];
			}else{
				@$nowconfig[$key] = $val;
			}
		}
	}
	return $nowconfig;
}
function jump($url, $delay = 0){
	echo '<html><head><meta http-equiv="refresh" content="'.$delay.';url='.$url.'"></head><body><script type=text/javascript>window.location.href='.$url.'"</script></body></html>';
	exit;
}
function dykeycode($string, $operation = 'DECODE'){
	$ckey_length = 4;
	$key = md5($GLOBALS['G_DY']['ext']['secret_key'] != '' ? $GLOBALS['G_DY']['ext']['secret_key'] : 'doyo_secret_key');
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d',0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}
function filemanager_list($a, $b){
	$order=is_escape($_GET['order']);
	$order=strtolower($order);
	if ($a['is_dir'] && !$b['is_dir']) {
		return -1;
	} else if (!$a['is_dir'] && $b['is_dir']) {
		return 1;
	} else {
		if ($order == 'size') {
			if ($a['filesize'] > $b['filesize']) {
				return 1;
			} else if ($a['filesize'] < $b['filesize']) {
				return -1;
			} else {
				return 0;
			}
		} else if ($order == 'type') {
			return strcmp($a['filetype'], $b['filetype']);
		} else {
			return strcmp($a['filename'], $b['filename']);
		}
	}
}
//提示信息
function message($info,$gurl){
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	if ($gurl==''){echo "<script>alert('".$info."');javascript:history.go(-1);</script>";}
	else{echo "<script>alert('".$info."');window.location.href='".$gurl."';</script>";}
	exit;
}
function message_err($newerrors){
	foreach($newerrors as $errortxt){
		$error_txt1=$errortxt;
		foreach($error_txt1 as $msg){ 
			$error_txt=$msg;
		}
	}
	message($error_txt);
}
//获取IP
function GetIP(){ 
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) 
	$ip = getenv("HTTP_CLIENT_IP"); 
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) 
	$ip = getenv("HTTP_X_FORWARDED_FOR"); 
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) 
	$ip = getenv("REMOTE_ADDR"); 
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) 
	$ip = $_SERVER['REMOTE_ADDR']; 
	else 
	$ip = "unknown"; 
	$ip=htmlspecialchars($ip, ENT_QUOTES);
	if(!get_magic_quotes_gpc())$ip = addslashes($ip);
	return($ip); 
}
//获取域名
function get_domain(){
    $protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
    if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }elseif (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    }else{
        if(isset($_SERVER['SERVER_PORT'])) {
            $port = ':' . $_SERVER['SERVER_PORT'];
            if((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
                $port = '';
            }
        }else{
            $port = '';
        }
        if(isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'].$port;
        }else if(isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'].$port;
        }
    }
    return $protocol.$host;
}
//字符截断,中文算2个字符
function newstr($string, $length, $dot="...") {
	if(strlen($string) <= $length) {return $string;}
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&','"','<','>'), $string);
	$strcut = '';$n = $tn = $noc = $noct = $nc = $tnc =0;
	while($n < strlen($string)) {
		$t = ord($string[$n]);
		if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
			$tn = 1; $n++; $noct++;
		} elseif(194 <= $t && $t <= 223) {
			$tn = 2; $n += 2; $noct += 2;
		} elseif(224 <= $t && $t <= 239) {
			$tn = 3; $n += 3; $noct += 2;
		} elseif(240 <= $t && $t <= 247) {
			$tn = 4; $n += 4; $noct += 2;
		} elseif(248 <= $t && $t <= 251) {
			$tn = 5; $n += 5; $noct += 2;
		} elseif($t == 252 || $t == 253) {
			$tn = 6; $n += 6; $noct += 2;
		} else {$n++;}
		if($noct >= $length){if($noct==0)$noc=$noct;if($nc==0)$nc=$n;if($tnc==0)$tnc=$tn;}
	}
	if($noct<=$length){return str_replace(array('&','"','<','>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);}
	if($noc > $length) {$nc -= $tnc;}
	$strcut = substr($string, 0, $nc);
	$strcut = str_replace(array('&','"','<','>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
	return $strcut.$dot;
}
//编辑器代码
function code_body($value,$type=1){
	if($type!=1){
		$ma='/<pre class=\"prettyprint\s.*?\">(.*?)<\/pre>/si';
	}else{
		$ma='/<pre class=\\\"prettyprint\s.*?\\\">(.*?)<\/pre>/si';
	}
	preg_match_all($ma,$value,$newbody);
	$newbody=array_unique($newbody[1]);
	foreach($newbody as $v){
		if($type!=1){
			$s=str_replace(array('&lt;','&gt;','&quot;'),array('&amp;lt;','&amp;gt;','&amp;quot;'),$v);
		}else{
			$s=str_replace(array('&amp;lt;','&amp;gt;','&amp;quot;'),array('&lt;','&gt;','&quot;'),$v);
		}
		if($s){
			$value=str_ireplace($v,$s,$value);
		}
	}
	return $value;
}
//数据操作过滤
function is_escape($value) {
	if(is_null($value))return 'NULL';
	if(is_bool($value))return $value ? 1 : 0;
	if(is_int($value))return (int)$value;
	if(is_float($value))return (float)$value;
	$value=htmlspecialchars(trim($value));
	if(!get_magic_quotes_gpc())$value = addslashes($value);
	return $value;
}
//替换url参数
function url_set_value($url,$key,$value) { 
	parse_str($url,$arr); 
	$arr[$key]=$value;
	return '?'.http_build_query($arr); 
}
//价格计算 1+,2-
function calculate($v1,$v2,$type=1) {
	if($type==1){
		$value=$v1+$v2;
	}else{
		$value=$v1-$v2;
	}
	$value=floor($value*100);
	return $value/100;
}
//内容推荐名称
function traitinfo($traitid){
	$traitid=trim($traitid, ',');
	$traitinfo=syDB('traits')->findAll(' id in ('.$traitid.') ',null,'id,name');
	foreach($traitinfo as $v){
		$trait.=' '.$v['name'];
	}
	return $trait;
}
//栏目名称
function typename($tid){
	$t=syDB('classtype')->find(array('tid' => $tid),null,'classname');
	return $t['classname'];
}
//栏目信息
function typeinfo($tid,$q){
	$t=syDB('classtype')->find(array('tid' => $tid),null,$q);
	return $t[$q];
}
//自定义标签
function labelcus($id,$q){
	$l=syClass('c_labelcus')->syCache(3600*240)->find(array('id' => $id));
	return $l[$q];
}
//自定义页面
function custom_html($file){
	$c=syDB('custom')->find(array('file' => $file));
	return $c;
}
//频道信息获取
function moldsinfo($molds,$q){
	$m=syDB('molds')->find(array('molds' => $molds),null,$q);
	return $m[$q];
}
//内容信息获取
function contentinfo($molds,$id,$q){
	$c=syDB($molds)->find(array('id' => $id),null,$q);
	return $c[$q];
}
//管理员信息获取
function adminuser_info($auser,$q){
	$m=syDB('admin_user')->find(array('auser' => $auser),null,$q);
	echo $m[$q];
}
//会员信息获取
function memberinfo($id,$q){
	$m=syDB('member')->find(array('id' => $id),null,$q);
	return $m[$q];
}
//会员组信息获取
function membergroup($gid,$q){
	$m=syDB('member_group')->find(array('gid ' => $gid),null,$q);
	return $m[$q];
}
//插件信息获取
function funsinfo($funs,$q){
	$f=syDB('funs')->find(array('funs' => $funs),null,$q);
	return $f[$q];
}
//规格获取
function attributetype($tid,$v=''){
	$type=syDB('attribute_type')->find(array('tid' => $tid));
	if($v==''){return $type;}else{return $type[$v];}
}
//规格选项获取
function attribute($id,$is=0,$v=''){
	if($is==0){$a=array('tid' => $id);}else{$a=array('sid' => $id);}
	$attribute=syDB('attribute')->find($a);
	if($v==''){return $attribute;}else{return $attribute[$v];}
}
//规格选项列表获取
function product_attribute($tid,$aid){
	$db=$GLOBALS['G_DY']['db']['prefix'];
	return syDB('product_attribute')->findSql('select * from '.$db.'product_attribute a left join '.$db.'attribute b on (a.sid=b.sid and a.aid='.$aid.') where a.tid='.$tid.' and b.isshow=1 order by b.orders desc,b.sid desc');
}
//自定义字段，单选多选项名获取
function fieldsinfo($fields,$key,$molds='article'){
	$f=syDB('fields')->find(array('fields' => $fields,'molds' => $molds),null,'selects');
	$fields=explode(',',$f['selects']);
	$k=array_search('='.$key, $fields);
	$fields=explode('=',$fields[$k]);
	return $fields[0];
}
//返回多附件字段数组
function fileall($fileall){
	if($fileall!=''){
		$fileall=explode('|-|',$fileall);
		$f=array();
		foreach($fileall as $v){
			$v=explode('|,|',$v);
			$f=array_merge($f,array(array($v[0],$v[1])));
		}
		return $f;
	}
}
//自定义字段列表
function fields_info($tid,$molds='',$lists=0,$c=array()){
	GLOBAL $__controller;
	$hand=date('His').mt_rand(1000,9999);
	$allfields=array();
	$fieldswhere=" fieldshow=1 and issubmit=1 ";
	if($molds){$fieldswhere.=" and molds='".$molds."'";}
	if($tid){$fieldswhere.=" and types like '%|".$tid."|%' ";}
	if($lists){$fieldswhere.=" and lists=1 ";}
	$v=syDB('fields')->findAll($fieldswhere,' fieldorder DESC,fid ');
	foreach($v as $f){
		$m='';
		switch ($f['fieldstype']){
			case 'varchar':
				if($f['fieldslong']>255){
					$t='<textarea name="'.$f['fields'].'" id="'.$f['fields'].'" style="width:'.$f['imgw'].'px; height:'.$f['imgh'].'px;" class="inp">'.$c[$f['fields']].'</textarea>';
				}else{
					$t='<input name="'.$f['fields'].'" id="'.$f['fields'].'" type="text" class="inp" value="'.$c[$f['fields']].'" />';
				}
				$m='最多'.$f['fieldslong'].'个字';
			break;
			case 'text':
				$t='<script type="text/javascript">$(function(){KindEditor.create("#'.$f['fields'].'",{resizeType : 1,allowPreviewEmoticons : false,allowImageUpload : false,items : ["fontname", "fontsize", "|", "forecolor", "hilitecolor", "bold", "italic", "underline","removeformat", "|", "justifyleft", "justifycenter", "justifyright", "insertorderedlist","insertunorderedlist", "|", "emoticons", "image", "link"]})});</script>';
				$t.='<textarea name="'.$f['fields'].'" id="'.$f['fields'].'" style="width:'.$f['imgw'].'px;height:'.$f['imgh'].'px;">'.$c[$f['fields']].'</textarea>';
			break;
			case 'int':
				$t='<input name="'.$f['fields'].'" id="'.$f['fields'].'" type="text" class="inp" value="'.$c[$f['fields']].'" />';
				$m='请输入整数格式，可为负数';
			break;
			case 'decimal':
				$t='<input name="'.$f['fields'].'" id="'.$f['fields'].'" type="text" class="inp" value="'.$c[$f['fields']].'" />';
				$m='请输入货币格式，如2.03';
			break;
			case 'time':
				if($c[$f['fields']]!=''){$time=date('Y-m-d H:i',$c[$f['fields']]);}else{$time=date('Y-m-d H:i');}
				$t='<input name="'.$f['fields'].'" id="'.$f['fields'].'" type="text" class="inp" value="'.$time.'" onClick="WdatePicker({dateFmt:';$t.="'yyyy-MM-dd HH:mm'";$t.='})" />';
			break;
			case 'files':
			$t='<table border="0" cellspacing="0" cellpadding="0"><tr><td><input name="'.$f['fields'].'" id="'.$f['fields'].'" type="text" class="inp" value="'.$c[$f['fields']].'" /></td><td width="5"></td><td><iframe frameborder="0" width="300" height="26" scrolling="No" src="'.$GLOBALS["WWW"].'index.php?c='.$__controller.'&a=m_upload_load&inputid='.$f['fields'].'&hand='.$hand.'&molds='.$molds.'&tid='.$tid.'&aid='.$c['id'].'" style="float:left;"></iframe><input name="hand" type="hidden" value="'.$hand.'"></td></tr></table>';
			break;
			case 'fileall':
			$t='<table border="0" cellspacing="0" cellpadding="0"><tr><td><input name="'.$f['fields'].'" id="'.$f['fields'].'" type="text" class="inp" value="'.$c[$f['fields']].'" /></td><td width="5"></td><td><iframe frameborder="0" width="300" height="26" scrolling="No" src="'.$GLOBALS["WWW"].'index.php?c='.$__controller.'&a=m_upload_load&inputid='.$f['fields'].'&hand='.$hand.'&molds='.$molds.'&tid='.$tid.'&aid='.$c['id'].'" style="float:left;"></iframe><input name="hand" type="hidden" value="'.$hand.'"></td></tr></table>';
			break;
			case 'select':
				$t='<select name="'.$f['fields'].'" id="'.$f['fields'].'">';
				foreach(explode(',',$f['selects']) as $v){
					$s=explode('=',$v);
					$t.='<option value="'.$s[1].'" ';
					if($c[$f['fields']]==$s[1])$t.='selected="selected"';
					$t.='>'.$s[0].'</option>';
				}
				$t.='</select>';
			break;
			case 'checkbox':
				$t='';
				foreach(explode(',',$f['selects']) as $v){
					$s=explode('=',$v);
					$t.='<input type="checkbox" id="'.$f['fields'].'" name="'.$f['fields'].'[]" value="'.$s[1].'" ';
					if(stristr($c[$f['fields']],'|'.$s[1].'|')!=FALSE)$t.='checked="checked"';
					$t.='>'.$s[0];
				}
			break;
			case 'contingency':
				$t='<script type="text/javascript">$(function(){$("input[name=contingency_'.$f['fields'].'_word]").bind({keyup: function() {$.get("'.$GLOBALS["WWW"].'index.php?c=ajax&a=fields_contingency&molds='.$f['contingency'].'&fields='.$f['fields'].'&word="+$(this).attr("value"), function(data){
$("#contingency_'.$f['fields'].'").removeClass("none");$("#contingency_'.$f['fields'].'").html(data);});},focusout: function() {$("#contingency_'.$f['fields'].'").addClass("none");}});});function contingency_id_'.$f['fields'].'(value,title){$("#'.$f['fields'].'").attr("value",value);$("input[name=contingency_'.$f['fields'].'_word]").attr("value",title);}</script><div style="position:relative"><input name="contingency_'.$f['fields'].'_word" type="text" class="int" value="'.contentinfo($f['contingency'],$c[$f['fields']],'title').'" /><input name="'.$f['fields'].'" id="'.$f['fields'].'" type="hidden" value="'.$c[$f['fields']].'" /><ul class="contingency none" id="contingency_'.$f['fields'].'"></ul></div>';
				$m='输入<strong>['.moldsinfo($f['contingency'],'moldname').']标题</strong>，可输入标题关键词搜索。';
		}
		$allfields=array_merge($allfields,array(array('name'=>$f['fieldsname'],'input'=>$t,'fields'=>$f['fields'],'m'=>$m)));
	}
	return $allfields;
}
//统计
function  total_page($sql,$v='doyo_total_page'){
	$a=syDB('molds')->findSql('select count(*) as '.$v.' from '.$sql);
	return $a[0][$v];
}
//支付平台
function payment($pay){
	$payment=syDB('payment')->find(array('pay' => $pay),null,'name');
	return $payment['name'];
}
//订单状态
function order_state($state,$type){
	$a=array(
		0=>'未支付',
		1=>'已支付待发货',
		2=>'已发货待确认',
		9=>'已完成',
		3=>'换货',
		4=>'退货',
	);
	switch($type){
		case 1:
			foreach($a as $k=>$v){
				$t.='<input name="state" type="radio" value="'.$k.'"';
				if($state==$k){$t.=' checked="checked"';$v='<strong>'.$v.'</strong>';}
				$t.=' />'.$v.'&nbsp;';
			}
		break;
		case 2:
			foreach($a as $k=>$v){
				$t.='<option value="'.$k.'"';
				if($state==$k)$t.=' selected="selected"';
				$t.='>'.$v.'</option>';
			}
		break;
		default:$t=$a[$state];break;
	}
	echo $t;
}
//获取订单内容
function order_goods($d,$logistics){
	foreach($d as $k=>$v){
		$va=syDB('product')->find(array('id'=>$v['aid'],'isshow'=>1),null,'title,tid,price,logistics');
		$goods[$k]['aid']=$v['aid'];
		$goods[$k]['attribute']=$v['attribute'];
		$goods[$k]['quantity']=$v['quantity'];
		$goods[$k]['title']=$va['title'];
		$goods[$k]['tid']=$va['tid'];
		$logistics_price=unserialize($va['logistics']);
		$goods[$k]['logistics_price']=$logistics_price[$logistics]*$v['quantity'];
		$p_type=syDB('attribute_type')->findSql('select distinct a.tid,a.aid,b.tid,b.isshow,b.orders,b.name from '.$GLOBALS['G_DY']['db']['prefix'].'product_attribute a left join '.$GLOBALS['G_DY']['db']['prefix'].'attribute_type b on (a.tid=b.tid) where a.aid='.$v['aid'].' and b.isshow=1 order by b.orders desc,b.tid desc');
		$ov['price']=$va['price'];$ov['txt']='';
		foreach($p_type as $s){
			$p=syDB('product_attribute')->find(array('aid' => $v['aid'],'tid' => $s['tid'],'sid' => $v['attribute'][$s['tid']]),null,'price');
			$ov['price']=$ov['price']+$p['price'];
			$a=syDB('attribute')->find(array('sid' => $v['attribute'][$s['tid']]),null,'name');
			$ov['txt'].=$s['name'].'('.$a['name'].') ';
		}			
		$goods[$k]['attribute_txt']=$ov['txt'];
		$goods[$k]['price']=$ov['price'];
		$goods[$k]['total']=$ov['price']*$v['quantity'];
		$aggregate+=$goods[$k]['total']+$goods[$k]['logistics_price'];
	}
	$t[0]=$goods;$t[1]=$aggregate;
	return $t;
}
//时间比较
function newest($t,$h){
	$t=(time()-$t)/3600;
	if($t < $h){return true;}else{return false;}
}
//替换内容静态规则
function html_rules($mold,$tid,$d,$id='',$f=''){
	if($f=='')$f=$id;
	if(strpos(','.syExt('site_html_rules'),'[type]')!==FALSE){
		$type=syDB('classtype')->find(array('tid'=>$tid),null,'htmldir');
		if($type['htmldir']!=''){
			$typedir=$type['htmldir'];
		}else{$typedir='c/'.$tid;}
	}
	$u=syExt('site_html_dir').'/'.str_replace(array('[y]','[m]','[d]','[id]','[file]','[mold]','[type]'),array(date('Y',$d),date('m',$d),date('d',$d),$id,$f,$mold,$typedir),syExt('site_html_rules'));
	return str_replace(array('///','//'),'/',$u);
}
//页面URL判断
function html_url($type,$c,$pages=0,$ispage,$molds){
	if($c['gourl']!='')return $c['gourl'];
	$sh=syExt("site_html");
	$sr=$GLOBALS['G_DY']['url']["url_path_base"];
	$sg=$GLOBALS["WWW"];
	$re=$GLOBALS['G_DY']['rewrite']["rewrite_open"];
	switch($type){
		case 'channel':
			if($re==1){
				$re_url=$sg.$GLOBALS['G_DY']['rewrite']["rewrite_channel"];
				if($c['htmlfile']=='')$c['htmlfile']=$c['id'];
				$go_url=str_replace(array('{id}','{file}','{molds}'),array($c['id'],$c['htmlfile'],$molds),$re_url);
				$go_url=str_replace(array('{channel}'),'channel',$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0&&$c['mgold']==0&&$c['htmlurl']!=''){
				$go_url=$sg.$c['htmlurl'];
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url=$sr.'?c=channel&molds='.$molds.'&id='.$c['id'];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		case 'article':
			if($re==1){
				$re_url=$sg.$GLOBALS['G_DY']['rewrite']["rewrite_article"];
				if($c['htmlfile']=='')$c['htmlfile']=$c['id'];
				$go_url=str_replace(array('{id}','{file}'),array($c['id'],$c['htmlfile']),$re_url);
				$go_url=str_replace(array('{article}'),'article',$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0&&$c['mgold']==0&&$c['htmlurl']!=''){
				$go_url=$sg.$c['htmlurl'];
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url=$sr.'?c=article&id='.$c['id'];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		case 'product':
			if($re==1){
				$re_url=$sg.$GLOBALS['G_DY']['rewrite']["rewrite_product"];
				if($c['htmlfile']=='')$c['htmlfile']=$c['id'];
				$go_url=str_replace(array('{id}','{file}'),array($c['id'],$c['htmlfile']),$re_url);
				$go_url=str_replace(array('{product}'),'product',$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0&&$c['mgold']==0&&$c['htmlurl']!=''){
				$go_url=$sg.$c['htmlurl'];
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url=$sr.'?c=product&id='.$c['id'];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		case 'message':
		break;
		case 'classtype':
			if($re==1){
				if($c["molds"]!='article'&&$c["molds"]!='product'&&$c["molds"]!='message'){
					$re_url=$sg.$GLOBALS['G_DY']['rewrite']['rewrite_channel_type'];
				}else{
					$re_url=$sg.$GLOBALS['G_DY']['rewrite']['rewrite_'.$c['molds'].'_type'];
				}
				$go_url=str_replace(array('{tid}','{file}'),array($c['tid'],$c['htmlfile']),$re_url);
				$go_url=str_replace(array('{'.$c['molds'].'}','{type}','{channel}'),array($c['molds'],type,'channel'),$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0){
				$noindex=syExt("site_html_index");
				if($noindex==1&&$pages==0){$html_file=='';}else{
					if($c["htmlfile"]!=''){$html_file=$c["htmlfile"].syExt("site_html_suffix");}
					else{$html_file='index'.syExt("site_html_suffix");}
				}
				if($c["htmldir"]==''){
					$go_url=$sg.syExt("site_html_dir")."/c/".$c["tid"]."/".$html_file;
				}else{ 
					$go_url=$sg.$c["htmldir"]."/".$html_file;
				}
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{ 
				if($c["molds"]!='article'&&$c["molds"]!='product'&&$c["molds"]!='message'){
					$go_url=$sr."?c=channel&a=type&tid=".$c["tid"];
				}else{
					$go_url=$sr."?c=".$c["molds"]."&a=type&tid=".$c["tid"];
				}
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		case 'special':
			if($re==1){
				$re_url=$sg.$GLOBALS['G_DY']['rewrite']["rewrite_special"];
				$go_url=str_replace(array('{sid}','{file}'),array($c['sid'],$c['htmlfile']),$re_url);
				$go_url=str_replace(array('{special}'),'special',$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1){ 
				if($c["htmlfile"]!=''){$html_file=$c["htmlfile"].syExt("site_html_suffix");}
				else{$html_file='index'.syExt("site_html_suffix");}
				if($c["htmldir"]==''){ 
					$go_url=$sg.syExt("site_html_dir")."/s/".$c["sid"]."/".$html_file;
				}else{
					$go_url=$sg.$c["htmldir"]."/".$html_file;
				}
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url=$sr."?c=special&sid=".$c["sid"];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		case 'labelcus_custom':
			if($re==1){
				$re_url=$sg.$GLOBALS['G_DY']['rewrite']["rewrite_labelcus_custom"];
				$go_url=str_replace(array('{file}'),array($c['file']),$re_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($c["html"]==1){ 
				$html_file=$c["file"];
				if($c["dir"]==''){ 
					$go_url=$sg.syExt("site_html_dir")."/".$html_file;
				}else{
					$go_url=$sg.$c["dir"]."/".$html_file;
				}
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url="index.php?file=".$c["file"];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
		default:
			if($re==1){
				$re_url=$sg.$GLOBALS['G_DY']['rewrite']["rewrite_channel"];
				if($c['htmlfile']=='')$c['htmlfile']=$c['id'];
				$go_url=str_replace(array('{id}','{file}','{molds}'),array($c['id'],$c['htmlfile'],$type),$re_url);
				$go_url=str_replace(array('{channel}'),'channel',$go_url);
				if($pages!==0){
					$go_url=str_replace('{page}','[p]',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}else{$go_url=str_replace('{page}',1,$go_url);}
			}else if($sh==1&&$c['mrank']==0&&$c['mgold']==0&&$c['htmlurl']!=''){
				$go_url=$sg.$c['htmlurl'];
				$go_url=str_replace(array("///","//"),"/",$go_url);
				if($pages!==0){
					$go_url=str_replace('.','[p].',$go_url);
					$go_url=pagetxt_html($go_url,$pages['total_page'],$ispage);
				}
			}else{
				$go_url=$sr.'?c=channel&molds='.$type.'&id='.$c['id'];
				if($pages!==0)$go_url=pagetxt($pages);
			}
		break;
	}
	return $go_url;
}
//分页代码
function pagetxt($pagearray,$pageno=3,$vp='page'){
	$pagetxt='';
	if($pagearray['total_count']>0)$pagetxt.='<li><a>共'.$pagearray['total_count'].'篇</a></li>';
	$pageurl=$_SERVER["QUERY_STRING"];
	if($pagearray['current_page']>1){
		$pagetxt.='<li><a href="'.url_set_value($pageurl,$vp,1).'">首页</a></li><li><a href="'.url_set_value($pageurl,$vp,$pagearray['prev_page']).'">上一页</a></li>';
	}
	$pageno1=$pagearray['current_page']-$pageno;if($pageno1<1)$pageno1=1;
	$pageno2=$pagearray['current_page']+$pageno;if($pageno2>$pagearray['total_page']){$pageno2=$pagearray['total_page'];}
	while($pageno1<=$pageno2){
		if($pagearray['current_page']==$pageno1){$pagetxt.='<li class="c">'.$pageno1.'</li>';}else{$pagetxt.='<li><a href="'.url_set_value($pageurl,$vp,$pageno1).'">'.$pageno1.'</a></li>';}
		$pageno1++;
	}
	if($pagearray['current_page'] < $pagearray['last_page']){
		$pagetxt.='<li><a href="'.url_set_value($pageurl,$vp,$pagearray['next_page']).'">下一页</a></li><li><a href="'.url_set_value($pageurl,$vp,$pagearray['last_page']).'">尾页</a></li>';
	}
	return $pagetxt;
}
//静态html分页代码
function pagetxt_html($url,$total_page,$current_page,$pageno=3){
	if($GLOBALS['G_DY']['rewrite']["rewrite_open"]==1){$is_p=1;}else{$is_p='';}
	$pagetxt='';
	if($total_page>0)$pagetxt.='<li><a>共'.$total_page.'篇</a></li>';
	$n=$current_page+1;$p=$current_page-1;
	if($current_page>1){
		$pagetxt.='<li><a href="'.str_replace('[p]',$is_p,$url).'">首页</a></li>';
		if($current_page==2){$pagetxt.='<li><a href="'.str_replace('[p]',$is_p,$url).'">上一页</a></li>';
		}else{$pagetxt.='<li><a href="'.str_replace('[p]',$p,$url).'">上一页</a></li>';}
	}
	$pageno1=$current_page-$pageno;if($pageno1<1)$pageno1=1;
	$pageno2=$current_page+$pageno;if($pageno2>$total_page)$pageno2=$total_page;
	while($pageno1<=$pageno2){
		if($current_page==$pageno1){$pagetxt.='<li class="c">'.$pageno1.'</li>';}else{
			if($pageno1==1){$pagetxt.='<li><a href="'.str_replace('[p]',$is_p,$url).'">'.$pageno1.'</a></li>';
			}else{$pagetxt.='<li><a href="'.str_replace('[p]',$pageno1,$url).'">'.$pageno1.'</a></li>';}
		}
		$pageno1++;
	}
	if($current_page < $total_page){
		$pagetxt.='<li><a href="'.str_replace('[p]',$n,$url).'">下一页</a></li><li><a href="'.str_replace('[p]',$total_page,$url).'">尾页</a></li>';
	}
	return $pagetxt;
}
//其他分页
function pagetxt_other($pagearray,$url,$syarg,$pageno=3){
	$pagetxt='';
	if($pagearray['total_count']>0)$pagetxt.='<li><a>共'.$pagearray['total_count'].'条</a></li>';
	if($pagearray['current_page']>1){
		$pagetxt.='<li><a href="'.$url.'&'.$syarg.'=1">首页</a></li><li><a href="'.$url.'&'.$syarg.'='.$pagearray['prev_page'].'">上一页</a></li>';
	}
	$pageno1=$pagearray['current_page']-$pageno;if($pageno1<1){$pageno1=1;}
	$pageno2=$pagearray['current_page']+$pageno;if($pageno2>$pagearray['total_page']){$pageno2=$pagearray['total_page'];}
	while($pageno1<=$pageno2){
		if($pagearray['current_page']==$pageno1){$pagetxt.='<li class="c">'.$pageno1.'</li>';}else{$pagetxt.='<li><a href="'.$url.'&'.$syarg.'='.$pageno1.'">'.$pageno1.'</a></li>';}
		$pageno1++;
	}
	if($pagearray['current_page'] < $pagearray['last_page']){
		$pagetxt.='<li><a href="'.$url.'&'.$syarg.'='.$pagearray['next_page'].'">下一页</a></li><li><a href="'.$url.'&'.$syarg.'='.$pagearray['last_page'].'">尾页</a></li>';
	}
	return $pagetxt;
}
//ajax分页
function pagetxt_ajax($pagearray,$url,$ajax,$pageno=3){
	$pagetxt='';
	if($pagearray['total_count']>0)$pagetxt.='<li><a>共'.$pagearray['total_count'].'条</a></li>';
	if($pagearray['current_page']>1){
		$pagetxt.='<li><a onClick="'.str_replace('[_page_]',1,$ajax).'">首页</a></li><li><a onClick="'.str_replace('[_page_]',$pagearray['prev_page'],$ajax).'">上一页</a></li>';
	}
	$pageno1=$pagearray['current_page']-$pageno;if($pageno1<1){$pageno1=1;}
	$pageno2=$pagearray['current_page']+$pageno;if($pageno2>$pagearray['total_page']){$pageno2=$pagearray['total_page'];}
	while($pageno1<=$pageno2){
		if($pagearray['current_page']==$pageno1){
			$pagetxt.='<li class="c">'.$pageno1.'</li>';
		}else{
			$pagetxt.='<li><a onClick="'.str_replace('[_page_]',$pageno1,$ajax).'">'.$pageno1.'</a></li>';
		}
		$pageno1++;
	}
	if($pagearray['current_page'] < $pagearray['last_page']){
		$pagetxt.='<li><a onClick="'.str_replace('[_page_]',$pagearray['next_page'],$ajax).'">下一页</a></li><li><a onClick="'.str_replace('[_page_]',$pagearray['last_page'],$ajax).'">尾页</a></li>';
	}
	return $pagetxt;
}