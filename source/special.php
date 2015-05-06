<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class special extends syController
{
	function __construct(){
		parent::__construct();
		$this->sy_class_type=syClass('syclasstype');
		$this->Class=syClass('c_special');
	}
	function index(){
		if($this->syArgs('file',1)!=''){
			$this->special=syDB('special')->find(' htmlfile="'.$this->syArgs('file',1).'" or sid='.$this->syArgs('file').' ');
			$tid = $this->special['sid'];
		}else{
			$sid = $this->syArgs('sid');
			$this->special=syDB('special')->find(" sid=".$sid." ");
		}
		if(!$this->special){message("指定专题不存在");}

		$this->db=$GLOBALS['G_DY']['db']['prefix'].$this->special['molds'];
		
		if($this->special['isindex']==0)$t=$this->special['t_index'];
		if($this->special['isindex']==2)$t=$this->special['t_listb'];
		if($this->special['isindex']==1)$t=$this->special['t_list'];
		if($this->special['isindex']==1){
			$w.=' where isshow=1 ';
			$w.='and sid='.$sid.' ';
			$order=' order by orders desc,addtime desc,id desc';
			$f=syDB('fields')->findAll(" molds='".$this->special['molds']."' and lists=1 ");
			if($f){
				foreach($f as $v){$fields.=','.$v['fields'];}
				$sql='select id,tid,sid,title,style,trait,gourl,addtime,hits,litpic,orders,mrank,mgold,isshow,description,htmlurl,htmlfile,user'.$fields.' from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
			}else{
				$sql='select id,tid,sid,title,style,trait,gourl,addtime,hits,litpic,orders,mrank,mgold,isshow,description,htmlurl,htmlfile,user from '.$this->db.$w.$order;
			}
			$total_page=total_page($this->db.$w);
			$this->lists = $this->Class->syPager($this->syArgs('page',0,1),$this->special['listnum'],$total_page)->findSql($sql);
			$pages=$this->Class->syPager()->getPager();
			$this->pages=html_url('special',$this->special,$pages,$this->syArgs('page',0,1));
			$i=1;
			$list_c=$this->lists;
			if($this->special['molds']!='article'&&$this->special['molds']!='product'&&$this->special['molds']!='message'){
				foreach($list_c as $k=>$v){
					$list_c[$k]['url']=html_url('channel',$v,0,'',$this->special['molds']);
				}
			}else{
				foreach($list_c as $k=>$v){
					$list_c[$k]['url']=html_url($this->special['molds'],$v);
				}
			}
			$this->lists=$list_c;
		}
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>  &gt;  专题  &gt;  '.$this->special['name'];
		$this->display('special/'.$t);
	}
}	