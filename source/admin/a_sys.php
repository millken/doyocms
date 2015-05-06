<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_sys extends syController
{
	function __construct(){
		parent::__construct();
		$this->a=$this->syArgs('a',1);
	}
	function index(){
		if($this->syArgs('run')==1){
			$configfile='config.php';
			$fp_tp=@fopen($configfile,"r");
			$fp_txt=@fread($fp_tp,filesize($configfile));
			@fclose($fp_tp);
			$config=array('http_path','site_title','site_keywords','site_description','view_themes','site_html','site_html_dir','site_html_rules','site_html_index','cache_auto','cache_time','filetype','filesize','imgwater','imgwater_t','imgcaling','img_w','img_h','comment_audit','comment_user','rewrite_open','enable_gzip','enable_gzip_level');
			foreach($config as $v){
				if (strpos(',site_html,cache_auto,cache_time,filesize,imgwater,imgwater_t,imgcaling,img_w,img_h,comment_audit,comment_user,site_html_index,enable_gzip,enable_gzip_level,',$v)){
					$txt=$this->syArgs($v);if($v=='filesize'){$txt=$txt*1024;}
					if($v=='site_html'){
						if($GLOBALS['G_DY']['ext']['site_html']==1&&$txt==0)@unlink('index.html');
					}
					$fp_txt=preg_replace("/'".$v."' => .*?,/","'".$v."' => ".$txt.",",$fp_txt);
				}else if($v=='filetype'){
					$fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".strtolower($this->syArgs($v,1))."',",$fp_txt);
				}else if($v=='site_html_rules'){
					if(stripos($this->syArgs($v,1),'[id]')===false&&stripos($this->syArgs($v,1),'[file]')===false){
					message_a("静态规则中[id]与[file]必须至少包含一个");
					}
					if(stripos($this->syArgs($v,1),'.')===false){
					message_a("静态规则中必须包含“.”和后缀名");
					}
					$fp_txt=preg_replace("/'site_html_rules' => '.*?',/","'site_html_rules' => '".$this->syArgs($v,1)."',",$fp_txt);
				}else if($v=='site_html_dir'){
					if(preg_match("/^[a-zA-Z0-9_\/]*$/",$this->syArgs($v,1))==0)message_a("生成目录只能为英文、数字、下划线和/组成");
					if($this->syArgs($v,1)=='/'){
						$dir=$this->syArgs($v,1);
					}else if($this->syArgs($v,1)==''){
						$dir='html';
					}else{
						$dir=trim($this->syArgs($v,1),'/');
					}
					$fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".strtolower($dir)."',",$fp_txt);
					
				}else if($v=='site_html_rules'){
					if(preg_match("/^[a-zA-Z0-9_\.\[\]\/]*$/",$this->syArgs($v,1))==0)message_a("生成规则只能为英文、数字、下划线、点和/组成");
					if($this->syArgs($v,1)==''){
						$dir='[y]/[m]/[id].html';
					}else{
						$dir=trim($this->syArgs($v,1),'/');
					}
					$fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".strtolower(trim($this->syArgs($v,1),'/'))."',",$fp_txt);
				}else{
					$fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".str_replace(array("\r\n","\n","\r"),'',$this->syArgs($v,1))."',",$fp_txt);
				}
			}
			$fpt_tpl=@fopen($configfile,"w");
			@fwrite($fpt_tpl,$fp_txt);
			@fclose($fpt_tpl);
			
			$incfile='include/inc.php';
			$inc_tp=@fopen($incfile,"r");
			$inc_txt=@fread($inc_tp,filesize($incfile));
			@fclose($inc_tp);
			$inc=array('rewrite_open','rewrite_article','rewrite_article_type','rewrite_product','rewrite_product_type','rewrite_channel','rewrite_channel_type','rewrite_message_type','rewrite_special','rewrite_labelcus_custom','rewrite_dir');
			foreach($inc as $v){
				$vt=strtolower(trim($this->syArgs($v,1),'/'));
				if(preg_match("/^[a-zA-Z0-9_\{\}\.\-\/]*$/",$vt)==0)message_a("伪静态规则只能包含英文、数字、下划线、点、中划线、{、}、/");
				if($v=='rewrite_article'){
					if((stripos($vt,'{id}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{article}')===false){
					message_a("article规则中{id}与{file}必须至少包含一个，并且必须包含{article}");
					}
				}
				if($v=='rewrite_article_type'){
					if((stripos($vt,'{tid}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{page}')===false||stripos($vt,'{article}')===false||stripos($vt,'{type}')===false){
					message_a("article栏目规则中{tid}与{file}必须至少包含一个，并且必须包含{page}与{article}与{type}");
					}
				}
				if($v=='rewrite_product'){
					if((stripos($vt,'{id}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{product}')===false){
					message_a("product规则中{id}与{file}必须至少包含一个，并且必须包含{product}");
					}
				}
				if($v=='rewrite_product_type'){
					if((stripos($vt,'{tid}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{page}')===false||stripos($vt,'{product}')===false||stripos($vt,'{type}')===false){
					message_a("product栏目规则中{tid}与{file}必须至少包含一个，并且必须包含{page}与{product}与{type}");
					}
				}
				if($v=='rewrite_channel'){
					if((stripos($vt,'{id}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{channel}')===false||stripos($vt,'{molds}')===false){
					message_a("自定义频道规则中{id}与{file}必须至少包含一个，并且必须包含{molds}与{channel}");
					}
				}
				if($v=='rewrite_channel_type'){
					if((stripos($vt,'{tid}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{page}')===false||stripos($vt,'{channel}')===false||stripos($vt,'{type}')===false){
					message_a("自定义频道栏目规则中{tid}与{file}必须至少包含一个，并且必须包含{page}与{channel}与{type}");
					}
				}
				if($v=='rewrite_message_type'){
					if((stripos($vt,'{tid}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{page}')===false||stripos($vt,'{message}')===false||stripos($vt,'{type}')===false){
					message_a("message规则中{tid}与{file}必须至少包含一个，并且必须包含{page}与{message}与{type}");
					}
				}
				if($v=='rewrite_special'){
					if((stripos($vt,'{sid}')===false&&stripos($vt,'{file}')===false)||stripos($vt,'{page}')===false||stripos($vt,'{special}')===false){
					message_a("专题规则中{sid}与{file}必须至少包含一个，并且必须包含{page}与{special}");
					}
				}
				if($v=='rewrite_labelcus_custom'){
					if(stripos($vt,'{file}')===false){
					message_a("自定义页面规则中必须含有{file}");
					}
				}
				if($v=='rewrite_open'){
					$inc_txt=preg_replace("/'".$v."' => .*?,/","'".$v."' => ".$vt.",",$inc_txt);
				}else{
					$inc_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".$vt."',",$inc_txt);
				}
				if($v=='rewrite_dir'){
					if($vt==''){$vt='/';}else{$vt='/'.$vt.'/';}
					$inc_txt=preg_replace("/'".$v."' => .*?,/","'".$v."' => '".$vt."',",$inc_txt);
				}
			}
			$inc_txt=preg_replace("/'mode' => '.*?',/","'mode' => '".$this->syArgs('mode',1)."',",$inc_txt);
			$inc_txt=preg_replace("/'vercode' => .*?,/","'vercode' => ".$this->syArgs('vercode',1).",",$inc_txt);
			$inc_tpl=@fopen($incfile,"w");
			@fwrite($inc_tpl,$inc_txt);
			@fclose($inc_tpl);
			syDB('sysconfig')->update(array('name'=>'sendmail'),array('sets'=>serialize($this->syArgs('sendmail',2))));
			message_a("系统设置修改成功",'?c=a_sys');
		}
		$this->toptxt='系统设置';
		$this->d=$GLOBALS['G_DY']['ext'];
		$this->r=$GLOBALS['G_DY']['rewrite'];
		$this->inc=$GLOBALS['G_DY'];
		$s=syDB('sysconfig')->findAll();
		foreach($s as $v){$sysconfig[$v['name']]=$v['sets'];}
		$this->sysconfig=$sysconfig;
		$this->display("sys.html");
	}
	function rewrite(){
		$url_article=preg_match_all('/\{(.*?)\}/si',$this->syArgs('article',1),$r_article);
		$url_article_type=preg_match_all('/\{(.*?)\}/si',$this->syArgs('article_type',1),$r_article_type);
		$url_product=preg_match_all('/\{(.*?)\}/si',$this->syArgs('product',1),$r_product);
		$url_product_type=preg_match_all('/\{(.*?)\}/si',$this->syArgs('product_type',1),$r_product_type);
		$url_channel=preg_match_all('/\{(.*?)\}/si',$this->syArgs('channel',1),$r_channel);
		$url_channel_type=preg_match_all('/\{(.*?)\}/si',$this->syArgs('channel_type',1),$r_channel_type);
		$url_message_type=preg_match_all('/\{(.*?)\}/si',$this->syArgs('message_type',1),$r_message_type);
		$url_special=preg_match_all('/\{(.*?)\}/si',$this->syArgs('special',1),$r_special);
		$url_labelcus_custom=preg_match_all('/\{(.*?)\}/si',$this->syArgs('labelcus_custom',1),$r_labelcus_custom);
		
		$r_article=$this->rewrite_for($r_article,$this->syArgs('article',1));
		$r_article_type=$this->rewrite_for($r_article_type,$this->syArgs('article_type',1));
		$r_product=$this->rewrite_for($r_product,$this->syArgs('product',1));
		$r_product_type=$this->rewrite_for($r_product_type,$this->syArgs('product_type',1));
		$r_channel=$this->rewrite_for($r_channel,$this->syArgs('channel',1));
		$r_channel_type=$this->rewrite_for($r_channel_type,$this->syArgs('channel_type',1));
		$r_message_type=$this->rewrite_for($r_message_type,$this->syArgs('message_type',1));
		$r_special=$this->rewrite_for($r_special,$this->syArgs('special',1));
		$r_labelcus_custom=$this->rewrite_for($r_labelcus_custom,$this->syArgs('labelcus_custom',1));
				
		//apache独立主机规则
		$at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article[1]));
		$this->apache='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_article[0]).'$ $1/index.php?'.$at.'&%1'."\r\n";
		
		$at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article_type[1]));
		$this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_article_type[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
		
		$at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product[1]));
		$this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_product[0]).'$ $1/index.php?'.$at.'&%1'."\r\n";
		
		$at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product_type[1]));
		$this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_product_type[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
		
		$at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_channel[1]));
		$this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_channel[0]).'$ $1/index.php?'.$at.'&%1'."\r\n";
		
		$at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_channel_type[1]));
		$this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_channel_type[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
		
		$at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_message_type[1]));
		$this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_message_type[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
		
		$at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_special[1]));
		$this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_special[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
		
		$at=str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_labelcus_custom[1]));
		$this->apache.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_labelcus_custom[0]).'$ $1/index.php?'.$at.'%1'."\r\n";
		
		//apache虚拟主机规则
		$this->apache1='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_article[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_article[1]))).'&%1'."\r\n";
		$this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_article_type[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_article_type[1]))).'%1'."\r\n";
		$this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_product[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_product[1]))).'&%1'."\r\n";
		$this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_product_type[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_product_type[1]))).'%1'."\r\n";
		$this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_channel[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_channel[1]))).'&%1'."\r\n";
		$this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_channel_type[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_channel_type[1]))).'%1'."\r\n";
		$this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_message_type[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_message_type[1]))).'%1'."\r\n";
		$this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_special[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_special[1]))).'%1'."\r\n";
		$this->apache1.='RewriteCond %{QUERY_STRING} ^(.*)$'."\r\n";
		$this->apache1.='RewriteRule ^'.str_ireplace('.','\.',$r_labelcus_custom[0]).'$ index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$this->thisnumjian($r_labelcus_custom[1]))).'%1'."\r\n";
		
		
		//iis规则
		$n=$r_article[2]+1;
		$this->iis='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_article[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article[1])).'&$'.$n."\r\n";
		$n=$r_article_type[2]+1;
		$this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_article_type[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article_type[1])).'&$'.$n."\r\n";
		$n=$r_product[2]+1;
		$this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_product[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product[1])).'&$'.$n."\r\n";
		$n=$r_product_type[2]+1;
		$this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_product_type[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product_type[1])).'&$'.$n."\r\n";
		
		$n=$r_channel[2]+1;
		$this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_channel[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_channel[1])).'&$'.$n."\r\n";
		$n=$r_channel_type[2]+1;
		$this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_channel_type[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_channel_type[1])).'&$'.$n."\r\n";
		
		$n=$r_message_type[2]+1;
		$this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_message_type[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_message_type[1])).'&$'.$n."\r\n";
		$n=$r_special[2]+1;
		$this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_special[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_special[1])).'&$'.$n."\r\n";
		$n=$r_labelcus_custom[2]+1;
		$this->iis.='RewriteRule ^(.*)/'.str_ireplace('.','\.',$r_labelcus_custom[0]).'(\?(.*))*$ $1/index\.php\?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_labelcus_custom[1])).'&$'.$n."\r\n";
		
		//iis7规则
		$this->iis7='&lt;rule name="article"&gt;'."\r\n";
		$this->iis7.='	&lt;match url="^(.*/)*'.$r_article[0].'\?*(.*)$" /&gt;'."\r\n";
		$this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_article[1])).'&{R:'.$r_article[2].'}" /&gt;'."\r\n";
		$this->iis7.='&lt;/rule&gt;'."\r\n";
		
		$this->iis7.='&lt;rule name="article_type"&gt;'."\r\n";
		$this->iis7.='	&lt;match url="^(.*/)*'.$r_article_type[0].'\?*(.*)$" /&gt;'."\r\n";
		$this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_article_type[1])).'&a=type&{R:'.$r_article_type[2].'}" /&gt;'."\r\n";
		$this->iis7.='&lt;/rule&gt;'."\r\n";
		
		$this->iis7.='&lt;rule name="product"&gt;'."\r\n";
		$this->iis7.='	&lt;match url="^(.*/)*'.$r_product[0].'\?*(.*)$" /&gt;'."\r\n";
		$this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_product[1])).'&{R:'.$r_product[2].'}" /&gt;'."\r\n";
		$this->iis7.='&lt;/rule&gt;'."\r\n";
		
		$this->iis7.='&lt;rule name="product_type"&gt;'."\r\n";
		$this->iis7.='	&lt;match url="^(.*/)*'.$r_product_type[0].'\?*(.*)$" /&gt;'."\r\n";
		$this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_product_type[1])).'&a=type&{R:'.$r_product_type[2].'}" /&gt;'."\r\n";
		$this->iis7.='&lt;/rule&gt;'."\r\n";
		
		$this->iis7.='&lt;rule name="channel"&gt;'."\r\n";
		$this->iis7.='	&lt;match url="^(.*/)*'.$r_channel[0].'\?*(.*)$" /&gt;'."\r\n";
		$this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_channel[1])).'&{R:'.$r_channel[2].'}" /&gt;'."\r\n";
		$this->iis7.='&lt;/rule&gt;'."\r\n";
		
		$this->iis7.='&lt;rule name="channel_type"&gt;'."\r\n";
		$this->iis7.='	&lt;match url="^(.*/)*'.$r_channel_type[0].'\?*(.*)$" /&gt;'."\r\n";
		$this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_channel_type[1])).'&a=type&{R:'.$r_channel_type[2].'}" /&gt;'."\r\n";
		$this->iis7.='&lt;/rule&gt;'."\r\n";
		
		$this->iis7.='&lt;rule name="message_type"&gt;'."\r\n";
		$this->iis7.='	&lt;match url="^(.*/)*'.$r_message_type[0].'\?*(.*)$" /&gt;'."\r\n";
		$this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_message_type[1])).'&a=type&{R:'.$r_message_type[2].'}" /&gt;'."\r\n";
		$this->iis7.='&lt;/rule&gt;'."\r\n";
		
		$this->iis7.='&lt;rule name="special"&gt;'."\r\n";
		$this->iis7.='	&lt;match url="^(.*/)*'.$r_special[0].'\?*(.*)$" /&gt;'."\r\n";
		$this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_special[1])).'&{R:'.$r_special[2].'}" /&gt;'."\r\n";
		$this->iis7.='&lt;/rule&gt;'."\r\n";
		
		$this->iis7.='&lt;rule name="special"&gt;'."\r\n";
		$this->iis7.='	&lt;match url="^(.*/)*'.$r_labelcus_custom[0].'\?*(.*)$" /&gt;'."\r\n";
		$this->iis7.='	&lt;action type="Rewrite" url="{R:1}/index.php\?'.str_ireplace('[-|', '{R:',str_ireplace('|-]', '}',$r_labelcus_custom[1])).'&{R:'.$r_labelcus_custom[2].'}" /&gt;'."\r\n";
		$this->iis7.='&lt;/rule&gt;'."\r\n";
		
		
		//nginx规则
		$this->nginx='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_article[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article[1])).' last;'."\r\n";
		$this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_article_type[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_article_type[1])).' last;'."\r\n";
		$this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_product[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product[1])).' last;'."\r\n";
		$this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_product_type[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_product_type[1])).' last;'."\r\n";
		
		$this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_channel[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_channel[1])).' last;'."\r\n";
		$this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_channel_type[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_channel_type[1])).' last;'."\r\n";
		
		$this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_message_type[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_message_type[1])).' last;'."\r\n";
		$this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_special[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_special[1])).' last;'."\r\n";
		$this->nginx.='rewrite ^([^\.]*)/'.str_ireplace('.','\.',$r_labelcus_custom[0]).'$ $1/index.php?'.str_ireplace('[-|', '$',str_ireplace('|-]', '',$r_labelcus_custom[1])).' last;'."\r\n";
		
		$this->display("rewrite.html");
	}
	function ecache(){
		$this->toptxt='清空缓存';
		if($this->syArgs('run')==1){
			if($this->syArgs("tmp")==1)deleteDir($GLOBALS['G_DY']['sp_cache']);
			if($this->syArgs("tpl")==1)deleteDir($GLOBALS['G_DY']['view']['config']['template_tpl']);
			$this->checkdir('./template');
			$this->checkdir('config.php');
			message_a("缓存清理成功",'?c=a_sys&a=ecache');
		}
		$this->display("sys.html");
	}
	function toemail() {
		$http=get_domain();
		$subject='DOYO_'.$http.'测试邮件';
		$body=$GLOBALS['S']['title'];
		$send=syClass('syphpmailer');
		$retrieve=$send->Send('sydna@163.com','',$subject,$body);
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		if(!$retrieve){
			echo '邮件发送失败，错误信息：'.$send->ErrorInfo;
		}else{
			echo '恭喜您，邮件发送成功，邮件发送功能正常。';
		}
	}
	function template_cache() {
		$y=$this->syArgs('y',1);
		if(!$y){
			$d='include/cache/log/';;
			$w=$this->syArgs('w');
			$f=date('Ym').'.txt';
			if($w==1){
				deleteDir($d);__mkdirs($d);
				$wt=@fopen($d.$f,"w");if($wt===false){echo 'false';}@fclose($wt);
			}else{
				if(!file_exists($d.$f)){echo 'false';}else{echo 'true';}
			}
		}else{
			if(function_exists('curl_init')){
				echo curl_get($y);
			}else{
				$data=file_get_contents($y);
				if( FALSE !== $data ){echo $data;}
			}
		}
	}
	private function thisnumjian($d){
		$d=str_ireplace(array('[-|2|-]','[-|3|-]','[-|4|-]','[-|5|-]','[-|6|-]'),array('[-|1|-]','[-|2|-]','[-|3|-]','[-|4|-]','[-|5|-]'),$d);
		return $d;
	}
	private function rewrite_for($d,$r){
		$num=1;
		foreach($d[1] as $k=>$v){
			if(stripos(',,type,special,article,product,channel,message,,',','.$v.',')){
				if(stripos(',,special,article,product,channel,message,,',','.$v.',')){
					$u.='&c='.$v;
				}else{
					$u.='&a='.$v;
				}
				$r=str_ireplace(array('{'.$v.'}'),$v,$r);
			}else{
				$num++;
				$u.='&'.$v.'=[-|'.$num.'|-]';
				if(stripos(',,id,tid,sid,page,,',','.$v.',')){
					$r=str_ireplace($d[0][$k],'([0-9]+)',$r);
				}else{
					$r=str_ireplace($d[0][$k],'(\w+)',$r);
				}
			}
		}
		return array($r,ltrim($u,'&'),$num+1);
	}
	private function checkdir($basedir){
		if (is_file($basedir)) {
			$this->checkBOM($basedir);
		}else{
			if ($dh = opendir($basedir)) {
				while (($file = readdir($dh)) !== false) {
					if ($file != '.' && $file != '..'){
						if (!is_dir($basedir."/".$file)) {
							$this->checkBOM("$basedir/$file");
						}else{
							$dirname = $basedir.'/'.$file;
							$this->checkdir($dirname);
						}
					}
				}
			closedir($dh);
			}
		}
	}
	private function checkBOM ($filename) {
		$contents = file_get_contents($filename);
		$charset[1] = substr($contents, 0, 1); 
		$charset[2] = substr($contents, 1, 1); 
		$charset[3] = substr($contents, 2, 1); 
		if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
			$rest = substr($contents, 3);
			$this->rewr ($filename, $rest);
		} 
	}
	private function rewr ($filename, $data) {
		$filenum = fopen($filename, "w");
		flock($filenum, LOCK_EX);
		fwrite($filenum, $data);
		fclose($filenum);
	}
}	