<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
$__template_compression_level = 6;
class templatedoyo{
	public $template_dir = null;
	public $template_tpl = null;
	public $no_compile_dir = true;
	private $_vars = array();
	public $sycache='';
	public function assign($key, $value = null){
		if (is_array($key)){
			foreach($key as $var => $val)if($var != "")$this->_vars[$var] = $val;
		}else{
			if ($key != "")$this->_vars[$key] = $value;
		}
	}
	public function templateExists($tplname){
		if (is_readable(realpath($this->template_dir).'/'.$tplname))return TRUE;
		if (is_readable($tplname))return TRUE;
		return FALSE;
	}
	public function template_err($msg){
		exit('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.$msg);
	}
	public function template_err_check($a,$b,$msg){
		if($a!=$b)$this->template_err($this->template_err_tpl.'模板中存在不完整'.$msg.'标签，请检查是否遗漏{'.$msg.'}开始或结束符');
	}
	public function template_exists($tplname){return $this->templateExists($tplname);}
	public function registerPlugin(){}
	public function display($tplname){
		//$tplname=str_replace('..','',$tplname);
		if(is_readable(realpath($this->template_dir.'/'.$tplname))){
			$tplpath = realpath($this->template_dir.'/'.$tplname);
		//}elseif(is_readable(APP_PATH.'/'.$tplname)){
			//$tplpath = APP_PATH.'/'.$tplname;
		}else{
			$this->template_err('无法找到模板 '.$tplname);
		}
		$templateverify=stripos($tplpath,realpath($this->template_dir));
		if($templateverify===false||$templateverify!==0)$this->template_err('路径超出模板文件夹');
		$this->template_err_tpl=$tplname;
		extract($this->_vars);
		if(syExt("view_admin")=='admin'||$tplname==$GLOBALS['WWW'].'include/uploads.php'){
			$template_tpl=$tplpath;
		}else{
			$cache_time=syExt("cache_time");
			if($cache_time>0)$this->sycache='syCache('.$cache_time.')->';
			$template_tpl=str_replace('/','_',$tplname);
			$template_tpl=str_replace('.html','.php',$template_tpl);
			$template_tpl=realpath($this->template_tpl).'/'.$template_tpl;
			if(syExt("cache_auto")==1 || !is_readable($template_tpl) || filemtime($tplpath)>filemtime($template_tpl)){
				if(!is_dir($GLOBALS['G_DY']['view']['config']['template_tpl'].'/'))__mkdirs($GLOBALS['G_DY']['view']['config']['template_tpl'].'/');
				$fp_tp=@fopen($tplpath,"r");
				$fp_txt=@fread($fp_tp,filesize($tplpath));
				@fclose($fp_tp);
				$fp_txt=$this->template_html($fp_txt);
				$fpt_tpl=@fopen($template_tpl,"w");
				@fwrite($fpt_tpl,$fp_txt);
				@fclose($fpt_tpl);
				if(is_readable($template_tpl)!==true)$this->template_err('无法找到模板缓存，请刷新后重试，或者检查系统文件夹权限');
			}
		}
		$enable_gzip=syExt('enable_gzip');
		if( $enable_gzip==1 ){
			GLOBAL $__template_compression_level;
			$__template_compression_level=syExt('enable_gzip_level');
			ob_start('template_ob_gzip');
		}
		include $template_tpl;
	}
	
	private function template_html($content){
		preg_match_all('/\{include=\"(.*?)\"\}/si',$content,$i);
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,$this->template_html_include(strtolower($i[1][$k])),$content);
		}
		preg_match_all('/\{loop (.*?)\}/si',$content,$i);
		$this->template_err_check(substr_count($content, '{/loop}'),count($i[0]),'loop');
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,$this->template_html_loop(strtolower($i[1][$k])),$content);
		}
		$content=str_ireplace('{/loop}','<?php } ?>',$content);
		
		preg_match_all('/\{sql (.*?)\}/si',$content,$i);
		$this->template_err_check(substr_count($content, '{/sql}'),count($i[0]),'sql');
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,$this->template_html_sql(strtolower($i[1][$k])),$content);
		}
		$content=str_ireplace('{/sql}','<?php } ?>',$content);
		
		preg_match_all('/\{if(.*?)\}/si',$content,$i);
		$this->template_err_check(substr_count($content, '{/if}'),count($i[0]),'if');
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php if'.$i[1][$k].'{ ?>',$content);
		}	
		$content=str_ireplace('{else}','<?php }else{ ?>',$content);
		$content=str_ireplace('{/if}','<?php } ?>',$content);
		
		preg_match_all('/\{foreach(.*?)\}/si',$content,$i);
		$this->template_err_check(substr_count($content, '{/foreach}'),count($i[0]),'foreach');
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php foreach('.$i[1][$k].'){ ?>',$content);
		}	
		$content=str_ireplace('{/foreach}','<?php } ?>',$content);
		
		preg_match_all('/\{\$(.*?)\}/si',$content,$i);
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php echo $'.$i[1][$k].' ?>',$content);
		}
		preg_match_all('/\{fun (.*?)\}/si',$content,$i);
		foreach($i[0] as $k=>$v){
			$content=str_ireplace($v,'<?php echo '.$i[1][$k].' ?>',$content);
		}
		return $content;
	}
	private function template_html_include($filename){
		$filename=realpath($this->template_dir).'/'.trim($filename);
		$txt=@fread(@fopen($filename,"r"),filesize($filename));
		$txt=$this->template_html($txt);
		@fclose($fp_tpl);
		return $txt;
	}
	private function template_html_loop($f){
		preg_match_all('/.*?(\s*.*?=.*?[\"|\'].*?[\"|\']\s).*?/si',' '.$f.' ',$aa);
		$a=array();foreach($aa[1] as $v){$t=explode('=',trim(str_replace(array('"',"'"),'',$v)));$a=array_merge($a,array(trim($t[0]) => trim($t[1])));}
		$dbleft=$GLOBALS['G_DY']['db']['prefix'];
		if(strpos($a['table'],'$')!==FALSE){$a['table']='".'.$a['table'].'."';}
		if($a['table']=='channel'){$db='channel';$molds=$a['molds'];}else{$db=$dbleft.$a['table'];}
		if($a['limit']!=''){$limit=' limit '.$a['limit'];}else{$limit='';}
		if($a['as']!=''){$as=$a['as'];}else{$as='v';}
		if($a['orderby']!=''){
			$order='order by '.$a['orderby'];
			$order=' '.str_replace('|',' ',$order).' ';
		}else{$order='';}
		unset($a['table']);unset($a['molds']);unset($a['orderby']);unset($a['orderway']);unset($a['limit']);unset($a['as']);
		$pages='';
		switch($db){
			case $dbleft.'article':
				$fielddb=syDB('fields')->findAll(array('molds'=>'article','lists'=>1),' fieldorder DESC,fid ','fields');
				foreach($fielddb as $v){$fields.=','.$v['fields'];}
				$field_all='id,tid,sid,title,style,trait,gourl,addtime,hits,htmlurl,htmlfile,litpic,orders,mrank,mgold,isshow,keywords,description'.$fields;
				foreach($a as $k=>$v){
					if($k=='tid'){
						foreach(explode(',',$v) as $t){
							if(strpos($t,'$')!==FALSE){
								$t=preg_replace('/\[.*?\]/', '["tid_leafid"]', $t);
								$ts.=',".'.$t.'."';
							}else{
								$ts.=','.syClass("syclasstype",array("article"))->leafid($t);
							}
						}
						$w.='and tid in('.substr($ts,1).') ';
					}else if($k=='trait'){$w.="and ";
						if(strpos($v,',')!==FALSE){
							foreach(explode(',',$v) as $tt){$trait.="or trait like '%,".$tt.",%' ";}
							$w.="(".substr($trait,3).")";
						}else{$w.="trait like '%,".$v.",%' ";}
					}else if($k=='notrait'){$w.="and ";
						if(strpos($v,',')!==FALSE){
							foreach(explode(',',$v) as $tt){$trait.="or trait not like '%,".$tt.",%' ";}
							$w.="(".substr($trait,3).")";
						}else{$w.="trait not like '%,".$v.",%' ";}
					}else if($k=='image'){$w.="and ";
						if($v==1){$w.="litpic!='' ";}if($v==2){$w.="litpic='' ";}
					}else if($k=='keywords'){
						$w.="and (title like '%".$v."%' or keywords like '%".$v."%')";
					}else if($k=='page'){
						$page=explode(',',$v);
						$limit='';
					}else{
						if(strpos($field_all,$k)!==FALSE){
							if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
							$w.="and ".$k."='".$v."' ";
						}
					}
				}$w=' where isshow=1 '.$w;
				if($order==''){$order=' order by orders desc,addtime desc,id desc';}
				if($fielddb){
					$sql='select '.$field_all.' from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order.$limit;
				}else{
					$sql='select * from '.$db.$w.$order.$limit;
				}
				$txt='<?php $'.$as.'n=0;';
				if($page){
					$total_page=total_page($db.$w);
					$txt.='$'.$page[0].'_class=syClass("c_article");$table'.$as.'= $'.$page[0].'_class->syPager(syClass("syController")->syArgs("'.$page[0].'_page",0,1),'.$page[1].','.$total_page.')->findSql("'.$sql.'");$'.$page[0].'=pagetxt($'.$page[0].'_class->syPager()->getPager(),3,"'.$page[0].'_page");';
				}else{
					$txt.='$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
				}
				$txt.='foreach($table'.$as.' as $'.$as.'){ $'.$as.'["tid_leafid"]=$sy_class_type->leafid($'.$as.'["tid"]);$'.$as.'["n"]=$'.$as.'n=$'.$as.'n+1; $'.$as.'["url"]=html_url("article",$'.$as.'); $'.$as.'["title"]=stripslashes($'.$as.'["title"]); $'.$as.'["description"]=stripslashes($'.$as.'["description"]); ?>';
			break;
			case $dbleft.'product':
				$fielddb=syDB('fields')->findAll(array('molds'=>'product','lists'=>1),' fieldorder DESC,fid ','fields');
				foreach($fielddb as $v){$fields.=','.$v['fields'];}
				$field_all='id,tid,sid,title,style,trait,gourl,addtime,record,hits,htmlurl,htmlfile,litpic,orders,price,mrank,mgold,isshow,keywords,description'.$fields;
				foreach($a as $k=>$v){
					if($k=='tid'){
						foreach(explode(',',$v) as $t){
							if(strpos($t,'$')!==FALSE){
								$t=preg_replace('/\[.*?\]/', '["tid_leafid"]', $t);
								$ts.=',".'.$t.'."';
							}else{
								$ts.=','.syClass("syclasstype",array("product"))->leafid($t);
							}
						}
						$w.='and tid in('.substr($ts,1).') ';
					}else if($k=='trait'){$w.="and ";
						if(strpos($v,',')!==FALSE){
							foreach(explode(',',$v) as $tt){$trait.="or trait like '%,".$tt.",%' ";}
							$w.="(".substr($trait,3).")";
						}else{$w.="trait like '%,".$v.",%' ";}
					}else if($k=='notrait'){$w.="and ";
						if(strpos($v,',')!==FALSE){
							foreach(explode(',',$v) as $tt){$trait.="or trait not like '%,".$tt.",%' ";}
							$w.="(".substr($trait,3).")";
						}else{$w.="trait not like '%,".$v.",%' ";}
					}else if($k=='image'){$w.="and ";
						if($v==1){$w.="litpic!='' ";}if($v==2){$w.="litpic='' ";}
					}else if($k=='keywords'){
						$w.="and (title like '%".$v."%' or keywords like '%".$v."%')";
					}else if($k=='page'){
						$page=explode(',',$v);
						$limit='';
					}else{
						if(strpos($field_all,$k)!==FALSE){
							if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
							$w.="and ".$k."='".$v."' ";
						}
					}
				}$w=' where isshow=1 '.$w;
				if($order==''){$order=' order by orders desc,addtime desc,id desc';}
				if($fielddb){
					$sql='select '.$field_all.' from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order.$limit;
				}else{
					$sql='select * from '.$db.$w.$order.$limit;
				}
				$txt='<?php $'.$as.'n=0;';
				if($page){
					$total_page=total_page($db.$w);
					$txt.='$'.$page[0].'_class=syClass("c_product");$table'.$as.'= $'.$page[0].'_class->syPager(syClass("syController")->syArgs("'.$page[0].'_page",0,1),'.$page[1].','.$total_page.')->findSql("'.$sql.'");$'.$page[0].'=pagetxt($'.$page[0].'_class->syPager()->getPager(),3,"'.$page[0].'_page");';
				}else{
					$txt.='$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
				}
				$txt.='foreach($table'.$as.' as $'.$as.'){ $'.$as.'["tid_leafid"]=$sy_class_type->leafid($'.$as.'["tid"]);$'.$as.'["n"]=$'.$as.'n=$'.$as.'n+1; $'.$as.'["url"]=html_url("product",$'.$as.'); $'.$as.'["title"]=stripslashes($'.$as.'["title"]); $'.$as.'["description"]=stripslashes($'.$as.'["description"]); ?>';
			break;
			case $dbleft.'message':
				$fielddb=syDB('fields')->findAll(array('molds'=>'message','lists'=>1),' fieldorder DESC,fid ','fields');
				$field_all='id,tid,isshow,title,addtime,retime,orders,user,body,reply'.$fields;
				foreach($a as $k=>$v){
					if($k=='tid'){
						foreach(explode(',',$v) as $t){
							if(strpos($t,'$')!==FALSE){
								$t=preg_replace('/\[.*?\]/', '["tid_leafid"]', $t);
								$ts.=',".'.$t.'."';
							}else{
								$ts.=','.syClass("syclasstype",array("article"))->leafid($t);
							}
						}
						$w.='and tid in('.substr($ts,1).') ';
					}else if($k=='reply'){
						$w.="and ";
						if($v==1){$w.="reply!='' ";}if($v==2){$w.="reply='' ";}
					}else if($k=='page'){
						$page=explode(',',$v);
						$limit='';
					}else{
						if(strpos($field_all,$k)!==FALSE){
							if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
							$w.="and ".$k."='".$v."' ";
						}
					}
				}
				if($w!=''){$w=' where '.substr($w,3);}
				if($order==''){$order=' order by orders desc,addtime desc,id desc';}
				if($fielddb){
					foreach($fielddb as $v){$fields.=','.$v['fields'];}
					$sql='select '.$field_all.' from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order.$limit;
				}else{
					$sql='select * from '.$db.$w.$order.$limit;
				}
				$txt='<?php ';
				if($page){
					$total_page=total_page($db.$w);
					$txt.='$'.$page[0].'_class=syClass("c_message");$table'.$as.'= $'.$page[0].'_class->syPager(syClass("syController")->syArgs("'.$page[0].'_page",0,1),'.$page[1].','.$total_page.')->findSql("'.$sql.'");$'.$page[0].'=pagetxt($'.$page[0].'_class->syPager()->getPager(),3,"'.$page[0].'_page");';
				}else{
					$txt.='$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
				}
				$txt.='foreach($table'.$as.' as $'.$as.'){?>';
			break;
			case 'channel':
				if(!syDB('molds')->find(array('molds'=>$molds),null,'molds'))return '';
				$db=$dbleft.$molds;
				$fielddb=syDB('fields')->findAll(array('molds'=>$molds,'lists'=>1),' fieldorder DESC,fid ','fields');
				foreach($fielddb as $v){$fields.=','.$v['fields'];}
				$field_all='id,tid,sid,title,style,trait,gourl,addtime,hits,htmlurl,htmlfile,orders,mrank,mgold,isshow,keywords,description'.$fields;
				foreach($a as $k=>$v){
					if($k=='tid'){
						foreach(explode(',',$v) as $t){
							if(strpos($t,'$')!==FALSE){
								$t=preg_replace('/\[.*?\]/', '["tid_leafid"]', $t);
								$ts.=',".'.$t.'."';
							}else{
								$ts.=','.syClass("syclasstype",array("article"))->leafid($t);
							}
						}
						$w.='and tid in('.substr($ts,1).') ';
					}else if($k=='trait'){$w.="and ";
						if(strpos($v,',')!==FALSE){
							foreach(explode(',',$v) as $tt){$trait.="or trait like '%,".$tt.",%' ";}
							$w.="(".substr($trait,3).")";
						}else{$w.="trait like '%,".$v.",%' ";}
					}else if($k=='notrait'){$w.="and ";
						if(strpos($v,',')!==FALSE){
							foreach(explode(',',$v) as $tt){$trait.="or trait not like '%,".$tt.",%' ";}
							$w.="(".substr($trait,3).")";
						}else{$w.="trait not like '%,".$v.",%' ";}
					}else if($k=='keywords'){
						$w.="and (title like '%".$v."%' or keywords like '%".$v."%')";
					}else if($k=='page'){
						$page=explode(',',$v);
						$limit='';
					}else{
						if(strpos($field_all,$k)!==FALSE){
							if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
							$w.="and ".$k."='".$v."' ";
						}
					}
				}$w=' where isshow=1 '.$w;
				if($order==''){$order=' order by orders desc,addtime desc,id desc';}
				if($fielddb){
					$sql='select '.$field_all.' from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order.$limit;
				}else{
					$sql='select * from '.$db.$w.$order.$limit;
				}
				$txt='<?php $'.$as.'n=0;';
				if($page){
					$total_page=total_page($db.$w);
					$txt.='$'.$page[0].'_class=syClass("c_'.$molds.'");$table'.$as.'= $'.$page[0].'_class->syPager(syClass("syController")->syArgs("'.$page[0].'_page",0,1),'.$page[1].','.$total_page.')->findSql("'.$sql.'");$'.$page[0].'=pagetxt($'.$page[0].'_class->syPager()->getPager(),3,"'.$page[0].'_page");';
				}else{
					$txt.='$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
				}
				$txt.='foreach($table'.$as.' as $'.$as.'){ $'.$as.'["tid_leafid"]=$sy_class_type->leafid($'.$as.'["tid"]);$'.$as.'["n"]=$'.$as.'n=$'.$as.'n+1; $'.$as.'["url"]=html_url("channel",$'.$as.',0,0,'.$molds.'); $'.$as.'["title"]=stripslashes($'.$as.'["title"]); $'.$as.'["description"]=stripslashes($'.$as.'["description"]); ?>';
			break;
			case $dbleft.'classtype':
				$field_all='tid,molds,pid,classname,gourl,litpic,title,keywords,description,orders,mrank,htmldir,htmlfile,mshow';
				foreach($a as $k=>$v){
					if($k=='not'&&$a['pid']){
						$not=1;
					}else{
						if($k!='not' && strpos($field_all,$k)!==FALSE){
							if($k=='pid'){$p=$v;}
							if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
							$w.="and ".$k."='".$v."' ";
						}else if($k=='body'){
							if($v==1)$field_all=$field_all.',body';
						}
					}
				}
				if(!$a['pid'] && !$a['tid']){$w.='and pid=0 ';}
				if($w)$w=" where ".substr($w,3);
				if($order=='')$order=' order by orders desc,tid';
				$sql='select '.$field_all.' from '.$db.$w.$order.$limit;
				if($not==1){
					if($a['mshow'])$notarr=',"mshow"=>'.$v;
					$txt='<?php $ytid=$type[tid];if(!syDB("classtype")->find(array("pid"=>'.$p.$notarr.'),null,"tid")){ $ypid=syDB("classtype")->find(array("tid"=>'.$p.$notarr.'),null,"pid");$type[tid]=$ypid[pid];} ?>';
				}
				$txt.='<?php $'.$as.'n=0;$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
				if($not==1){$txt.='$type[tid]=$ytid;';}
				$txt.='foreach($table'.$as.' as $'.$as.'){ $'.$as.'["tid_leafid"]=$sy_class_type->leafid($'.$as.'["tid"]);$'.$as.'["n"]=$'.$as.'n=$'.$as.'n+1; $'.$as.'["classname"]=stripslashes($'.$as.'["classname"]);$'.$as.'["description"]=stripslashes($'.$as.'["description"]); $'.$as.'["url"]=html_url("classtype",$'.$as.'); ?>';
			break;
			case $dbleft.'special':
				$field_all='sid,molds,name,gourl,litpic,title,keywords,description,orders,htmldir,htmlfile,isshow';
				foreach($a as $k=>$v){
					if(strpos($field_all,$k)!==FALSE){
						if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
						$w.="and ".$k."='".$v."' ";
					}else if($k=='body'){
						if($v==1)$field_all=$field_all.',body';
					}
				}
				if($w)$w=" where isshow=1 ".$w;
				if($order=='')$order=' order by orders desc,sid';
				$sql='select '.$field_all.' from '.$db.$w.$order.$limit;
				$txt.='<?php $'.$as.'n=0;$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");foreach($table'.$as.' as $'.$as.'){ $'.$as.'["n"]=$'.$as.'n=$'.$as.'n+1; $'.$as.'["name"]=stripslashes($'.$as.'["name"]);$'.$as.'["description"]=stripslashes($'.$as.'["description"]); $'.$as.'["url"]=html_url("special",$'.$as.'); ?>';
			break;
			case $dbleft.'ads':
				$field_all='id,taid,orders,name,type,adsw,adsh,adfile,body,gourl,target,isshow';
				foreach($a as $k=>$v){
					if(strpos($field_all,$k)!==FALSE){
						if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
						$w.="and ".$k."='".$v."' ";
					}
				}
				$w=" where isshow=1 ".$w;
				if($order=='')$order=' order by orders desc,id desc';
				$sql='select * from '.$db.$w.$order.$limit;
				$txt='<?php $'.$as.'n=0;$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");foreach($table'.$as.' as $'.$as.'){ $'.$as.'["n"]=$'.$as.'n=$'.$as.'n+1; $'.$as.'["name"]=stripslashes($'.$as.'["name"]); ?>';
			break;
			case $dbleft.'links':
				$field_all='id,taid,orders,name,image,gourl,isshow';
				foreach($a as $k=>$v){
					if($k=='type'){
						if($v=='image'){
							$w.="and image!='' ";
						}
						if($v=='text'){
							$w.="and image='' ";
						}
					}else{
						if(strpos($field_all,$k)!==FALSE){
							if(strpos($v,'$')!==FALSE){$v='".'.$v.'."';}
							$w.="and ".$k."='".$v."' ";
						}
					}
				}
				$w=" where isshow=1 ".$w;
				if($order=='')$order=' order by orders desc,id desc';
				$sql='select * from '.$db.$w.$order.$limit;
				$txt='<?php $table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");foreach($table'.$as.' as $'.$as.'){ $'.$as.'["name"]=stripslashes($'.$as.'["name"]); ?>';
			break;
			default:
				foreach($a as $k=>$v){
					if($k=='tid'){
						$leafid='$leafid=syClass("syclasstype")->leafid('.$v.');';
						$w.='and tid in(".$leafid.") ';
					}else{
						if(strpos($v,'$')!==FALSE)$v='".'.$v.'."';
						$w.="and ".$k."='".$v."' ";
					}
				}
				if($w!=''){$w=' where '.substr($w,3);}
				$sql='select * from '.$db.$w.$order.$limit;
				$txt='<?php '.$leafid.'$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");foreach($table'.$as.' as $'.$as.'){ ';
				if(strpos($db,'[molds]')!==FALSE){
					$molds=explode('.',$db);
					$txt.='$'.$as.'["url"]=html_url('.$molds[1].',$'.$as.');';
				}
				$txt.='?>';
			break;
		}
		return $txt;
	}
	private function template_html_sql($aa){
		preg_match_all('/sql=\"(.*?)\"/si',$aa,$sql);
		preg_match_all('/\sas=\"(.*?)\"/si',$aa,$as);
		preg_match_all('/\spage=\"(.*?)\"/si',$aa,$pg);
		
		if($as[1][0]!=''){$as=$as[1][0];}else{$as='v';}
		if($pg[1][0]!=''){$page=explode(',',$pg[1][0]);}
		if($sql[1][0]!=''){
			$sql=$sql[1][0];
			preg_match_all('/\$(.*?)\]/',$sql,$f);
			foreach($f[0] as $k=>$v){
				$sql=str_replace($v,'".$'.$f[1][$k].']."',$sql);
			}
			$sql=str_replace('[pre]',$GLOBALS['G_DY']['db']['prefix'],$sql);
			$txt='<?php ';
			if($page){
				$txt.='$total_count = syClass("syModel")->findSql("SELECT count(*) from ('.$sql.') '.$page[0].'_total_count");$total_page = ceil( $total_count[0]["count(*)"] / '.$page[1].' );$'.$page[0].'_page_on=(int)$_GET["'.$page[0].'_page"];if($'.$page[0].'_page_on<=1){ $'.$page[0].'_page_on=1;}if($'.$page[0].'_page_on>$total_page){ $'.$page[0].'_page_on=$total_page;}if($'.$page[0].'_page_on<=1){ $'.$page[0].'_limit_left=0;}else{ $'.$page[0].'_limit_left='.$page[1].'*($'.$page[0].'_page_on-1);}$'.$page[0].'=pagetxt(array("total_count" => $total_count[0]["count(*)"],"total_page"  => $total_page,"prev_page"   => $'.$page[0].'_page_on - 1,"next_page"   => $'.$page[0].'_page_on + 1,"last_page"   => $total_page,"current_page"=> $'.$page[0].'_page_on,),3,"'.$page[0].'_page");';
				$txt.='$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("SELECT * FROM ('.$sql.') '.$page[0].'_all_sql limit ".$'.$page[0].'_limit_left.",'.$page[1].'");';
			}else{
				$txt.='$table'.$as.'=syClass("syModel")->'.$this->sycache.'findSql("'.$sql.'");';
			}
			$txt.='foreach($table'.$as.' as $'.$as.'){?>';
		}else{$txt='';}
		return $txt;
	}
	
} 

function template_ob_gzip($content){ 
	if( !headers_sent() && extension_loaded("zlib") && strstr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip") ){
		GLOBAL $__template_compression_level;
		$content = gzencode($content,$__template_compression_level); 
		header("Content-Encoding: gzip"); 
		header("Vary: Accept-Encoding"); 
		header("Content-Length: ".strlen($content)); 
	} 
	return $content; 
}