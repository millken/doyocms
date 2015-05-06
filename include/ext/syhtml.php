<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class syhtml
{
	private $spurls = null;
	public function make($spurl, $alias_url = null, $todir = 1)
	{
		if(1 == syAccess('r','sp_html_making')){$this->spurls[] = array($spurl, $alias_url); return;}
		@list($geturl, $controller, $action, $args, $anchor) = $spurl;
			if($todir!=0){
				$file_root_name = ( '' == $GLOBALS['G_DY']['ext']['site_html_dir'] ) ? '' : $GLOBALS['G_DY']['ext']['site_html_dir'].'/';
			}
			if( null == $alias_url ){
				$filedir = $file_root_name .date('Y/m/').'/';
				$filename = substr(time(),3,10).substr(mt_rand(100000, substr(time(),3,10)),4).".html";
			}else{
				$filedir = $file_root_name.dirname($alias_url) . '/';
				$filename = basename($alias_url);
			}
			$baseuri = rtrim(dirname($GLOBALS['G_DY']['url']["url_path_base"]), '/\\')."/".$filedir.$filename;
			$realfile = APP_PATH."/".$filedir.$filename;
			$remoteurl = '/'.$GLOBALS['WWW'].ltrim(spUrl($geturl, $controller, $action, $args, $anchor, TRUE), '/\\');
			$remoteurl = str_replace(array('///','//'),'/',$remoteurl);
			$remoteurl = 'http://'.$_SERVER["SERVER_NAME"].':'.$_SERVER['SERVER_PORT'].$remoteurl;
			if(function_exists('curl_init')){
				$cachedata = $this->curl_get_file_contents($remoteurl);
			}else{
				$cachedata=file_get_contents($remoteurl);
				if( FALSE === $cachedata ){
					syError("无法从网络获取页面数据，请检查：<br />1. Url生成地址是否正确！<a href='{$remoteurl}' target='_blank'>点击这里测试</a>。<br />2. 设置php.ini的allow_url_fopen为On。<br />3. 检查是否防火墙阻止了APACHE/PHP访问网络。<br />4. 建议安装CURL函数库。");
				}
			}
			__mkdirs(dirname($realfile));
			$write=@fopen($realfile,"w");
			@fwrite($write,$cachedata);
			@fclose($write);
	}

	function curl_get_file_contents($url)
    {
    	if(!function_exists('curl_init'))return FALSE;
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        curl_close($c);
        if (FALSE === $contents)return FALSE;
        return $contents;
    }

	public function makeAll($spurls)
	{
		foreach( $spurls as $single ){
			list($spurl, $alias_url) = $single;
			$this->make($spurl, $alias_url, 0);
		}
		foreach( $spurls as $single ){
			list($spurl, $alias_url) = $single;
			$this->make($spurl, $alias_url, 1);
		}
	}

	public function getUrl($geturl=null,$controller = null, $action = null, $args = null, $anchor = null, $force_no_check = FALSE)
	{
		if( $url_list = syAccess('r', 'sp_url_list') ){
			$url_list = explode("\n",$url_list);
			$args_en = !empty($args) ? serialize($args) : "";
			$url_input = "{$geturl}|{$controller}|{$action}|{$args_en}|$anchor|";
			foreach( $url_list as $url ){
				if( substr($url,0,strlen($url_input)) == $url_input ){
					$url_item = explode("|",substr($url,strlen($url_input)));
					if( TRUE == $GLOBALS['G_DY']['html']['safe_check_file_exists'] && TRUE != $force_no_check ){
						if( !is_readable($url_item[1]) )return FALSE;
					}
					return $url_item;
				}
			}
		}
		return FALSE;
	}

	public function setUrl($spurl, $baseuri, $realfile)
	{
		@list($geturl, $controller, $action, $args, $anchor) = $spurl;
		$args = !empty($args) ? serialize($args) : '';
		$url_input = "{$geturl}|{$controller}|{$action}|{$args}|{$anchor}|{$baseuri}|{$realfile}";
		if( $url_list = syAccess('r', 'sp_url_list') ){
			syAccess('w', 'sp_url_list', $url_list."\n".$url_input);
		}else{
			syAccess('w', 'sp_url_list', $url_input);
		}
	}

	public function clear($geturl, $controller, $action = null, $args = FALSE, $anchor = '', $delete_file = TRUE)
	{
		if( $url_list = syAccess('r', 'sp_url_list') ){
			$url_list = explode("\n",$url_list);$re_url_list = array();
			if( null == $action ){
				$prep = "{$geturl}|{$controller}|";
			}elseif( FALSE === $args ){
				$prep = "{$geturl}|{$controller}|{$action}|";
			}else{
				$args = !empty($args) ? serialize($args) : '';
				$prep = "{$geturl}|{$controller}|{$action}|{$args}|{$anchor}|";
			}
			foreach( $url_list as $url ){
				if( substr($url,0,strlen($prep)) == $prep ){
					$url_tmp = explode("|",$url);$realfile = $url_tmp[5];
					echo $realfile;exit;
					if( TRUE == $delete_file )@unlink($realfile);
				}else{
					$re_url_list[] = $url;
				}
			}
			syAccess('w', 'sp_url_list', join("\n", $re_url_list));
		}
	}
	public function c_molds($molds,$date,$file){
		$this->make(array('index.php',$molds,'index', $date),$file,0);
	}
	public function c_channel($date,$file){
		$this->make(array('index.php','channel','index', $date),$file,0);
	}
	public function c_classtype($molds,$date,$file){
		$this->make(array('index.php',$molds,'type', $date),$file,0);
	}
	public function c_special($date,$file){
		$this->make(array('index.php','special','index', $date),$file,0);
	}
	public function c_labelcus_custom($date,$file){
		$this->make(array('index.php','index','index', $date),$file,0);
	}
	public function c_index(){
		$this->make(array('index.php','index','index'),'index.html',0);
	}
}