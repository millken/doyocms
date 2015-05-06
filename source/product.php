<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class product extends syController
{
	function __construct(){
		parent::__construct();
		$this->molds = 'product';
		$this->moldname=moldsinfo('product','moldname');
		$this->sy_class_type=syClass('syclasstype');
		$this->Class=syClass('c_product');
		$this->db=$GLOBALS['G_DY']['db']['prefix'].'product';
	}
	function index(){
		if($this->syArgs('file',1)!=''){
			$this->product=syDB('product')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where (htmlfile="'.$this->syArgs('file',1).'" or id='.$this->syArgs('file').') and isshow=1 limit 1');
			$id = $this->product['id'];
		}else{
			$id = $this->syArgs('id');
			if(!$id){message("请指定内容id");}
			$this->product=syDB('product')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where id='.$id.' and isshow=1 limit 1');
		}
		if(!$this->product){message("指定内容不存在或未审核");}
		
		$this->product=$this->product[0];
		$this->product=array_merge($this->product,array('tid_leafid'=>$this->sy_class_type->leafid($this->product['tid'])));
		$backurl=html_url('product',$this->product);
		$this->backurl=urlencode($backurl);
		
		$this->attribute_type=syDB('product')->findSql('select distinct a.tid,a.aid,b.tid,b.isshow,b.orders,b.name from '.$this->db.'_attribute a left join '.$GLOBALS['G_DY']['db']['prefix'].'attribute_type b on (a.tid=b.tid and a.aid='.$this->product['id'].') where b.isshow=1 order by b.orders desc,b.tid desc');
		
		if($this->product['mrank']>0||$this->product['mgold']>0){
			syClass('symember')->p_v($this->product['mrank'],$this->product['mgold'],'product',$this->product['id']);
		}
		
		$prev_next_w=' and tid in('.$this->sy_class_type->leafid($this->product['tid']).') ';
		$prev_next_f='id,litpic,style,mrank,mgold,title,htmlurl,htmlfile';
		$prev=syDB('product')->find(' id<'.$this->product['id'].$prev_next_w,'id desc',$prev_next_f);
		if($prev){
			$prev['url']=html_url('product',$prev);
			$this->aprev=$prev;
		}
		$next=syDB('product')->find(' id>'.$this->product['id'].$prev_next_w,'id',$prev_next_f);
		if($next){
			$next['url']=html_url('product',$next);
			$this->anext=$next;
		}
		$this->record=total_page($GLOBALS['G_DY']['db']['prefix'].'sales_record where aid='.$this->product['id']);
		$body=array_filter(explode("[doyo|page]",$this->product['body']));
		if(count($body)>1){
			$pages=array(
						'total_page' => count($body),    // 总页数
						'prev_page' => $this->syArgs('page',0,1)-1,     // 上一页的页码
						'next_page' => $this->syArgs('page',0,1)+1,     // 下一页的页码
						'last_page' => count($body),      // 最后一页的页码
						'current_page' => $this->syArgs('page',0,1),   // 当前页码
					);
			$this->product=array_merge($this->product,array('body'=>$body[$this->syArgs('page',0,1)-1]));
			if($this->syArgs('page')>1){
				$this->product=array_merge($this->product,array('title'=>$this->product['title'].'&nbsp;&nbsp;('.$this->syArgs('page').')'));
			}
			$this->pages=html_url('product',$this->product,$pages,$this->syArgs('page',0,1));
		}
		$this->type=syDB('classtype')->find(" molds='product' and tid=".$this->product['tid']." ",null,'tid,classname,litpic,keywords,description,t_content,htmldir,htmlfile,mrank,msubmit,unit');
		if($this->type['mrank']>0){
			syClass('symember')->p_v($this->type['mrank']);
		}
		$this->type=array_merge($this->type,array('tid_leafid'=>$this->sy_class_type->leafid($this->product['tid'])));
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>';
		foreach($this->sy_class_type->navi($this->product['tid']) as $v){
			$d_pos=syDB('classtype')->find(array('tid'=>$v['tid']),null,'tid,molds,htmldir,htmlfile,mrank');
			$this->positions.='  &gt;  <a href="'.html_url('classtype',$d_pos).'">'.$v['classname'].'</a>';
		}
		$this->display('product/'.$this->type['t_content']);
	}
	function type(){
		if($this->syArgs('file',1)!=''){
			$this->type=syDB('classtype')->find(' htmlfile="'.$this->syArgs('file',1).'" or tid='.$this->syArgs('file').' ');
			$tid = $this->type['tid'];
		}else{
			$tid = $this->syArgs('tid');
			$this->type=syDB('classtype')->find(" molds='product' and tid=".$tid." ");
		}
		if(!$this->type){message("指定栏目不存在");}
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
			$this->orders = $this->syArgs('orders');
			$by = ($this->syArgs('acs')!=1) ? '' : 'desc';
			$this->acs = ($this->syArgs('acs')!=1) ? 1 : 0;
			$this->url='c=product&a=type&tid='.$tid.'&orders='.$orders.'&acs='.$this->acs;
			if($this->orders==1){
				$order=' order by addtime '.$by.',orders desc,id desc';
			}else if($this->orders==2){
				$order=' order by record '.$by.',orders desc,id desc';
			}else if($this->orders==3){
				$order=' order by hits '.$by.',orders desc,id desc';	
			}else{
				$order=' order by orders desc,addtime desc,id desc';
			}
			
			$f=syDB('fields')->findAll(" molds='product' and types like '%|".$tid."|%' and lists=1 ");
			$sql='select id,tid,title,style,trait,gourl,addtime,record,hits,litpic,orders,price,mrank,mgold,isshow,description,htmlurl,htmlfile,user';
			if($f){
				foreach($f as $v){$fields.=','.$v['fields'];}
				$sql=$sql.$fields.' from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
			}else{
				$sql=$sql.' from '.$this->db.$w.$order;
			}
			$total_page=total_page($this->db.$w);
			$this->lists = $this->Class->syPager($this->syArgs('page',0,1),$this->type['listnum'],$total_page)->findSql($sql);
			$pages=$this->Class->syPager()->getPager();
			$this->pages=html_url('classtype',$this->type,$pages,$this->syArgs('page',0,1));
			$list_c=$this->lists;
			foreach($list_c as $k=>$v){
				$list_c[$k]['url']=html_url('product',$v);
			}
			$this->lists=$list_c;
		}
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>';
		$type_pos=$this->sy_class_type->navi($tid);
		foreach($type_pos as $v){
			$d_pos=syDB('classtype')->find(array('tid'=>$v['tid']),null,'tid,molds,htmldir,htmlfile,mrank');
			$this->positions.='  &gt;  <a href="'.html_url('classtype',$d_pos).'">'.$v['classname'].'</a>';
		}
		$this->display('product/'.$t);
	}
	function hits(){
		if($this->syArgs('id')){
			syDB('product')->incrField(array('id'=>$this->syArgs('id')), 'hits');
			$hits=syDB('product')->find(array('id'=>$this->syArgs('id')),null,'hits');
			echo 'document.write("'.$hits['hits'].'");';
		}
	}
	function search(){
		$this->type=array('title'=>'站内搜索','keywords'=>$GLOBALS['S']['keywords'],'description'=>$GLOBALS['S']['description'],'classname'=>'全部',);
		$this->type=array_merge($this->type,array('tid_leafid'=>$this->sy_class_type->leafid()));
		$w.=" where isshow=1 ";
		$word=$this->syArgs('word',1);
		if($word){
			$w.="and (";
			$str = explode(' ',$word);
			foreach($str as $s){
				if($s)$w.=" title like '%".$s."%' or keywords like '%".$s."%' or";
			}
			$w=rtrim($w,'or').") ";
		}
		$order=' order by orders desc,addtime desc,id desc';
		$sql='select id,tid,title,style,trait,gourl,addtime,hits,litpic,orders,price,mrank,mgold,isshow,htmlurl,htmlfile,description,user,body from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
		$total_page=total_page($this->db.$w);
		$this->lists = $this->Class->syPager($this->syArgs('page',0,1),30,$total_page)->findSql($sql); 
		$pages=$this->Class->syPager()->getPager();
		$this->pages=pagetxt($pages);
		$list_c=$this->lists;
		foreach($list_c as $k=>$v){
			$list_c[$k]['title']=str_ireplace($this->syArgs('word',1),'<b style="color:red;">'.$this->syArgs('word',1).'</b>',$v['title']);
			$list_c[$k]['url']=html_url('product',$v);
		}		
		$this->lists=$list_c;
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>  &gt;  '.$this->moldname.'搜索“'.$this->syArgs('word',1).'”';
		$this->display('product/search.html');
	}
}	