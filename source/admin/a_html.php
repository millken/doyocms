<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_html extends syController
{
	function __construct(){	
		parent::__construct();
		set_time_limit(99999999);
		if(syExt('site_html')!=1)message_a("系统静态生成已关闭，请先在系统设置——其他设置——开启静态生成");		
		$this->types=syClass('syclasstype');
		$this->typesdb=$this->types->type_txt();
		$this->specials=syDB('special')->findAll(array('isshow'=>1));
		$this->chtml=syClass('syhtml');
		$this->html_dir=syExt("site_html_dir");
		$this->html_rules=syExt("site_html_rules");
		$this->html_suffix=syExt("site_html_suffix");
	}
	function index(){
		$this->channel=syDB('molds')->findAll(array('isshow'=>1,'sys'=>0));
		$this->display("html.html");
	}
	function clear(){
		ob_implicit_flush(1);
		ob_end_flush();
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link href="source/admin/template/style/admin.css" rel="stylesheet" type="text/css" /><script src="include/js/jsmain.js" type="text/javascript"></script><script type="text/javascript">function goclear(ctxt){$("#clear").html(ctxt);}</script><div class="main"><div class="progress" id="clear">正在统计需更新数量...</div></div>';
		deleteDir($GLOBALS['G_DY']['sp_cache']);
		deleteDir($GLOBALS['G_DY']['view']['config']['template_tpl']);
		$ww='';$t=$this->syArgs('t',1);
		if($t==1||$t==0){
			if($t==0){
				$m=array('article','product');
				$channel=syDB('molds')->findAll(array('isshow'=>1,'sys'=>0));
				foreach($channel as $cc){
					$m=array_merge($m,array($cc['molds']));
				}
			}else{$m=array($this->syArgs('molds',1));}
			foreach($m as $mv){
				$this->t=moldsinfo($mv,'moldname');;
				if($this->syArgs('tid',1)!='')$ww.=' and tid in('.$this->types->leafid($this->syArgs('tid')).')';
				if($this->syArgs('id1',1)!=''&&$this->syArgs('id2',1)!='')$ww.=' and id>'.$this->syArgs('id1').' and  id<'.$this->syArgs('id2').'';
				if($mv=='article'||$mv=='product'){
					$sql='select id,tid,addtime,htmlfile,body from '.$GLOBALS["G_DY"]["db"]["prefix"].$mv.' a left join '.$GLOBALS["G_DY"]["db"]["prefix"].$mv.'_field b on (a.id=b.aid) where isshow=1'.$ww.' and mrank=0 and mgold=0';
				}else{
					$sql='select id,tid,addtime,htmlfile from '.$GLOBALS["G_DY"]["db"]["prefix"].$mv.' a left join '.$GLOBALS["G_DY"]["db"]["prefix"].$mv.'_field b on (a.id=b.aid) where isshow=1'.$ww.' and mrank=0 and mgold=0';
				}
				$numall=syDB($mv)->findSql('select count(id) as ct from '.$GLOBALS["G_DY"]["db"]["prefix"].$mv.' where isshow=1'.$ww.' and mrank=0 and mgold=0');
				$i=0;$ii=1;$all=ceil($numall[0]['ct']/20);
				while($ii<= $all){
					$tosql=$sql.' limit '.$i.',20';$a='';
					$a=syDB($mv)->findSql($tosql);
					$this->chtml_molds($mv,$a,$numall[0]['ct'],$i);
					$i=$i+20;
					$ii=$ii+1;
					$ii++;
				}
				$this->chtml_echo('['.$this->t.']更新完成');
			}
		}
		if($t==2||$t==0){
			$this->t='栏目';
			if($this->syArgs('tid',1)!='')$ww=' and tid in('.$this->types->leafid($this->syArgs('tid')).') ';
			$a=syDB('classtype')->findAll(' mrank=0'.$ww,null,'tid,htmldir,htmlfile,molds,listnum,mrank');
			$this->chtml_classtype($a);
		}
		if($t==3||$t==0){
			$this->t='专题';
			if($this->syArgs('sid',1)!='')$ww=' sid='.$this->syArgs('sid').' ';
			$a=syDB('special')->findAll($ww,null,'sid,htmldir,htmlfile,molds,listnum');
			$this->chtml_special($a);
		}
		if($t==4||$t==0){
			$this->t='自定义页面';
			$a=syDB('custom')->findAll();
			$this->chtml_labelcus_custom($a);
		}
		if($t==99||$t==0){
			$this->t='首页';
			$this->chtml_index();
		}
		$this->chtml_echo(' ');
		set_time_limit(30);
		message_a('['.$this->t.']静态html更新全部完成');
	}
	private function chtml_molds($molds,$a,$anum,$isnum) {
		foreach($a as $v){
			$this->chtml_echo('正在更新：'.$this->t.'<br>总共需更新：<span>'.$anum.'</span><br>当前更新：<span>'.$isnum.'</span><br>总进度：<span>'.floor($isnum/$anum*100).'%</span>');
			$c_html_f=html_rules($molds,$v['tid'],$v['addtime'],$v['id'],$v['htmlfile']);
			syDB($molds)->updateField(array('id'=>$v['id']),'htmlurl',$c_html_f);
			$ms=syDB('molds')->find(array('molds'=>$molds),null,'sys');
			if($ms['sys']!=1){
				$this->chtml->c_channel(array('id'=>$v['id'],'page'=>$i,'molds'=>$molds),$c_html_f);
			}else{
				$this->chtml->c_molds($molds,array('id'=>$v['id']),$c_html_f);
				$body=array_filter(explode("[doyo|page]",$v['body']));
				$allb=count($body);
				if($allb>1){
					for ($i = 1; $i <= $allb; $i++) {
						if($i>1){
						$this->chtml->c_molds($molds,array('id'=>$v['id'],'page'=>$i),str_replace('.',$i.'.',$c_html_f));
						}
					}
				}
			}
			
			$isnum++;
		}
	}
	private function chtml_classtype($a) {
		$anum=count($a);
		$isnum=1;
		foreach($a as $v){
			$this->chtml_echo('正在更新：'.$this->t.'<br>总共需更新：<span>'.$anum.'</span><br>当前更新：<span>'.$isnum.'</span><br>总进度：<span>'.floor($isnum/$anum*100).'%</span>');
			if($v['htmldir']==''){
				$c_html_f=$this->html_dir.'/c/'.$v['tid'].'/';
			}else{
				$c_html_f=$v['htmldir'].'/';
			}
			if($v['htmlfile']==''){
				$c_html_f.='index'.syExt('site_html_suffix');
			}else{
				$c_html_f.=$v['htmlfile'].syExt('site_html_suffix');
			}
			$ms=syDB('molds')->find(array('molds'=>$v['molds']),null,'sys');
			if($ms['sys']==1){
				$this->chtml->c_classtype($v['molds'],array('tid'=>$v['tid']),$c_html_f);
			}else{
				$this->chtml->c_classtype('channel',array('tid'=>$v['tid']),$c_html_f);
			}
			$cl=syClass('c_'.$v['molds']);
			$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].$v['molds'].' where isshow=1 and tid in('.$this->types->leafid($v['tid']).')');
			$alls=$cl->syPager(1,$v['listnum'],$total_page)->findAll(' isshow=1 and tid in('.$this->types->leafid($v['tid']).') ',null,'tid,isshow');
			$pages=$cl->syPager()->getPager();
			if($pages['total_page']>1){
				for ($i = 2; $i <= $pages['total_page']; $i++) {
					if($ms['sys']==1){
						$this->chtml->c_classtype($v['molds'],array('tid'=>$v['tid'],'page'=>$i),str_replace('.',$i.'.',$c_html_f));
					}else{
						$this->chtml->c_classtype('channel',array('tid'=>$v['tid'],'page'=>$i),str_replace('.',$i.'.',$c_html_f));
					}
				}	
			}		
			$isnum++;
		}
		$this->chtml_echo('['.$this->t.']更新完成');
	}
	private function chtml_special($a) {
		$anum=count($a);
		$isnum=1;
		foreach($a as $v){
			$this->chtml_echo('正在更新：'.$this->t.'<br>总共需更新：<span>'.$anum.'</span><br>当前更新：<span>'.$isnum.'</span><br>总进度：<span>'.floor($isnum/$anum*100).'%</span>');
			if($v['htmldir']==''){
				$c_html_f=$this->html_dir.'/s/'.$v['sid'].'/';
			}else{
				$c_html_f=$v['htmldir'].'/';
			}
			if($v['htmlfile']==''){
				$c_html_f.='index'.syExt('site_html_suffix');
			}else{
				$c_html_f.=$v['htmlfile'].syExt('site_html_suffix');
			}
			$this->chtml->c_special(array('sid'=>$v['sid']),$c_html_f);
			$cl=syClass('c_'.$v['molds']);
			$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].$v['molds']);
			$alls=$cl->syPager(1,$v['listnum'],$total_page)->findAll(array('sid'=>$v['sid'],'isshow'=>1),null,'sid,isshow');
			$pages=$cl->syPager()->getPager();
			if($pages['total_page']>1){
				for ($i = 2; $i <= $pages['total_page']; $i++) {
					$this->chtml->c_special(array('sid'=>$v['sid'],'page'=>$i),str_replace('.',$i.'.',$c_html_f));
				}
			}
			$isnum++;
		}
		$this->chtml_echo('['.$this->t.']更新完成');
	}
	private function chtml_labelcus_custom($a) {
		$anum=count($a);
		$isnum=1;
		foreach($a as $v){
			$this->chtml_echo('正在更新：'.$this->t.'<br>总共需更新：<span>'.$anum.'</span><br>当前更新：<span>'.$isnum.'</span><br>总进度：<span>'.floor($isnum/$anum*100).'%</span>');
			if($v['dir']==''){
				$c_html_f=$this->html_dir.'/';
			}else{
				$c_html_f=$v['dir'].'/';
			}
			$c_html_f.=$v['file'];
			$this->chtml->c_labelcus_custom(array('file'=>$v['file']),$c_html_f);
		}
		$this->chtml_echo('['.$this->t.']更新完成');
	}
	private function chtml_index() {
		$this->chtml->c_index();
		$this->chtml_echo('[首页]更新完成');
	}
	private function chtml_echo($msg) {
		echo '<script type="text/javascript">goclear("'.$msg.'");</script>';
	}
}	