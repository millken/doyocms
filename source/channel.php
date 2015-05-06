<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class channel extends syController
{
	function __construct(){
		parent::__construct();
		$this->molds = $this->syArgs('molds',1);
		$this->moldname=moldsinfo($this->molds,'moldname');
		$this->sy_class_type=syClass('syclasstype');
		$this->db=$GLOBALS['G_DY']['db']['prefix'].$this->molds;
	}
	function index(){
		$this->Class=syClass('c_'.$this->molds);
		if($this->syArgs('file',1)!=''){
			$this->channel=syDB($this->molds)->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where (htmlfile="'.$this->syArgs('file',1).'" or id='.$this->syArgs('file').') and isshow=1 limit 1');
			$id = $this->channel['id'];
		}else{
			$id = $this->syArgs('id');
			if(!$id){message("请指定内容id");}
			$this->channel=syDB($this->molds)->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where id='.$id.' and isshow=1 limit 1');
		}
		if(!$this->channel){message("指定内容不存在或未审核");}
		$this->channel=$this->channel[0];
		$this->channel=array_merge($this->channel,array('tid_leafid'=>$this->sy_class_type->leafid($this->channel['tid'])));
		if($this->channel['mrank']>0||$this->channel['mgold']>0){
			syClass('symember')->p_v($this->channel['mrank'],$this->channel['mgold'],$this->molds,$this->channel['id']);
		}
		$this->fields=syDB('fields')->findAll('molds="'.$this->molds.'" and types like "%|'.$this->channel['tid'].'|%"',' fieldorder DESC,fid ');
		$prev_next_w=' and tid in('.$this->sy_class_type->leafid($this->channel['tid']).') ';
		$prev_next_f='id,style,mrank,mgold,title,htmlurl,htmlfile';
		$prev=syDB($this->molds)->find(' id<'.$this->channel['id'].$prev_next_w,'id desc',$prev_next_f);
		if($prev){
			$prev['url']=html_url('channel',$prev,0,'',$this->molds);
			$this->aprev=$prev;
		}
		$next=syDB($this->molds)->find(' id>'.$this->channel['id'].$prev_next_w,'id',$prev_next_f);
		if($next){
			$next['url']=html_url('channel',$next,0,'',$this->molds);
			$this->anext=$next;
		}
		
		$this->type=syDB('classtype')->find(" molds='".$this->molds."' and tid=".$this->channel['tid']." ",null,'tid,classname,keywords,description,t_content,htmldir,htmlfile,mrank,msubmit');
		if($this->type['mrank']>0){
			syClass('symember')->p_v($this->type['mrank']);
		}
		$this->type=array_merge($this->type,array('tid_leafid'=>$this->sy_class_type->leafid($this->channel['tid'])));
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>';
		foreach($this->sy_class_type->navi($this->channel['tid']) as $v){
			$d_pos=syDB('classtype')->find(array('tid'=>$v['tid']),null,'tid,molds,htmldir,htmlfile,mrank');
			$this->positions.='  &gt;  <a href="'.html_url('classtype',$d_pos).'">'.$v['classname'].'</a>';
		}
		$this->display($this->molds.'/'.$this->type['t_content']);
	}
	function type(){
		$this->Class=syClass('c_classtype');
		if($this->syArgs('file',1)!=''){
			$this->type=syDB('classtype')->find(' htmlfile="'.$this->syArgs('file',1).'" or tid='.$this->syArgs('file').' ');
			$tid = $this->type['tid'];
		}else{
			$tid = $this->syArgs('tid');
			$this->type=syDB('classtype')->find(" tid=$tid ");
		}
		if(!$this->type){message("指定栏目不存在");}
		$this->db=$GLOBALS['G_DY']['db']['prefix'].$this->type['molds'];
		if($this->type['mrank']>0){
			syClass('symember')->p_v($this->type['mrank']);
		}
		$this->type=array_merge($this->type,array('tid_leafid'=>$this->sy_class_type->leafid($tid)));
		if($this->type['isindex']==0)$t=$this->type['t_index'];
		if($this->type['isindex']==3)$t=$this->type['t_listb'];
		if($this->type['isindex']==2)$t=$this->type['t_listimg'];
		if($this->type['isindex']==1)$t=$this->type['t_list'];
		if($this->type['isindex']==1||$this->type['isindex']==2){
			$w.=" where isshow=1 ";
			$w.="and tid in(".$this->type['tid_leafid'].") ";
			if($this->syArgs('trait'))$w.="and trait like '%,".$this->syArgs('trait').",%' ";
			$order=' order by orders desc,addtime desc,id desc';
			$f=syDB('fields')->findAll(" molds='".$this->type['molds']."' and types like '%|".$tid."|%' and lists=1 ");
			if($f){
				foreach($f as $v){$fields.=','.$v['fields'];}
				$sql='select id,tid,title,style,trait,gourl,addtime,hits,orders,mrank,mgold,isshow,description,htmlurl,htmlfile,user'.$fields.' from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
			}else{
				$sql='select id,tid,title,style,trait,gourl,addtime,hits,orders,mrank,mgold,isshow,description,htmlurl,htmlfile,user from '.$this->db.$w.$order;
			}
			$total_page=total_page($this->db.$w);
			$this->lists = $this->Class->syPager($this->syArgs('page',0,1),$this->type['listnum'],$total_page)->findSql($sql);
			$pages=$this->Class->syPager()->getPager();
			$this->pages=html_url('classtype',$this->type,$pages,$this->syArgs('page',0,1));
			$list_c=$this->lists;
			foreach($list_c as $k=>$v){
				$list_c[$k]['url']=html_url('channel',$v,0,'',$this->type['molds']);
			}
			$this->lists=$list_c;
		}
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>';
		$type_pos=$this->sy_class_type->navi($tid);
		foreach($type_pos as $v){
			$d_pos=syDB('classtype')->find(array('tid'=>$v['tid']),null,'tid,molds,htmldir,htmlfile,mrank');
			$this->positions.='  &gt;  <a href="'.html_url('classtype',$d_pos).'">'.$v['classname'].'</a>';
		}
		$this->display($this->type['molds'].'/'.$t);
	}
	function hits(){
		if($this->syArgs('id')){
			syDB($this->molds)->incrField(array('id'=>$this->syArgs('id')), 'hits');
			$hits=syDB($this->molds)->find(array('id'=>$this->syArgs('id')),null,'hits');
			echo 'document.write("'.$hits['hits'].'");';
		}
	}
	function search(){
		$this->Class=syClass('c_'.$this->molds);
		$this->type=array('title'=>'站内搜索','keywords'=>$GLOBALS['S']['keywords'],'description'=>$GLOBALS['S']['description'],'classname'=>'全部',);
		$this->type=array_merge($this->type,array('tid_leafid'=>$this->sy_class_type->leafid()));
		$w.=" where isshow=1 ";
		$word=$this->syArgs('word',1);
		if($word){
			$w.=" and (";
			$str = explode(' ',$word);
			foreach($str as $s){
				if($s)$w.=" title like '%".$s."%' or keywords like '%".$s."%' or";
			}
			$w=rtrim($w,'or').") ";
		}
		$order=' order by orders desc,addtime desc,id desc';
		$sql='select id,tid,title,style,trait,gourl,addtime,hits,orders,mrank,mgold,isshow,htmlurl,htmlfile,description,user from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
		$total_page=total_page($this->db.$w);
		$this->lists = $this->Class->syPager($this->syArgs('page',0,1),30,$total_page)->findSql($sql); 
		$pages=$this->Class->syPager()->getPager();
		$this->pages=pagetxt($pages);
		$list_c=$this->lists;
		foreach($list_c as $k=>$v){
			$list_c[$k]['title']=str_ireplace($this->syArgs('word',1),'<b style="color:red;">'.$this->syArgs('word',1).'</b>',$v['title']);
			$list_c[$k]['url']=html_url('channel',$v,0,'',$this->molds);
		}
		$this->lists=$list_c;
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>  &gt;  '.$this->moldname.'搜索“'.$this->syArgs('word',1).'”';
		$this->display($this->molds.'/search.html');
	}
}	