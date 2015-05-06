<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class article extends syController
{
	function __construct(){
		parent::__construct();
		$this->molds = 'article';
		$this->moldname=moldsinfo('article','moldname');
		$this->sy_class_type=syClass('syclasstype');
		$this->Class=syClass('c_article');
		$this->db=$GLOBALS['G_DY']['db']['prefix'].'article';
	}
	function index(){
		if($this->syArgs('file',1)!=''){
			$this->article=syDB('article')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where (htmlfile="'.$this->syArgs('file',1).'" or id='.$this->syArgs('file').') and isshow=1 limit 1');
			$id = $this->article['id'];
		}else{
			$id = $this->syArgs('id');
			if(!$id){message("请指定内容id");}
			$this->article=syDB('article')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where id='.$id.' and isshow=1 limit 1');
		}
		if(!$this->article){message("指定内容不存在或未审核");}
		$this->article=$this->article[0];
		$this->article=array_merge($this->article,array('tid_leafid'=>$this->sy_class_type->leafid($this->article['tid'])));
		if($this->article['mrank']>0||$this->article['mgold']>0){
			syClass('symember')->p_v($this->article['mrank'],$this->article['mgold'],'article',$this->article['id']);
		}
		$prev_next_w=' and tid in('.$this->sy_class_type->leafid($this->article['tid']).') ';
		$prev_next_f='id,litpic,style,mrank,mgold,title,htmlurl,htmlfile';
		$prev=syDB('article')->find(' id<'.$this->article['id'].$prev_next_w,'id desc',$prev_next_f);
		if($prev){
			$prev['url']=html_url('article',$prev);
			$this->aprev=$prev;
		}
		$next=syDB('article')->find(' id>'.$this->article['id'].$prev_next_w,'id',$prev_next_f);
		if($next){
			$next['url']=html_url('article',$next);
			$this->anext=$next;
		}
		
		$body=array_filter(explode("[doyo|page]",$this->article['body']));
		if(count($body)>1){
			$pages=array(
						'total_page' => count($body),    // 总页数
						'prev_page' => $this->syArgs('page',0,1)-1,     // 上一页的页码
						'next_page' => $this->syArgs('page',0,1)+1,     // 下一页的页码
						'last_page' => count($body),      // 最后一页的页码
						'current_page' => $this->syArgs('page',0,1),   // 当前页码
					);
			$this->article=array_merge($this->article,array('body'=>$body[$this->syArgs('page',0,1)-1]));
			if($this->syArgs('page')>1){
				$this->article=array_merge($this->article,array('title'=>$this->article['title'].'&nbsp;&nbsp;('.$this->syArgs('page').')'));
			}
			$this->pages=html_url('article',$this->article,$pages,$this->syArgs('page',0,1));
		}
		$this->type=syDB('classtype')->find(" molds='article' and tid=".$this->article['tid']." ",null,'tid,classname,litpic,keywords,description,t_content,htmldir,htmlfile,mrank,msubmit');
		if($this->type['mrank']>0){
			syClass('symember')->p_v($this->type['mrank']);
		}
		$this->type=array_merge($this->type,array('tid_leafid'=>$this->sy_class_type->leafid($this->article['tid'])));
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>';
		foreach($this->sy_class_type->navi($this->article['tid']) as $v){
			$d_pos=syDB('classtype')->find(array('tid'=>$v['tid']),null,'tid,molds,htmldir,htmlfile,mrank');
			$this->positions.='  &gt;  <a href="'.html_url('classtype',$d_pos).'">'.$v['classname'].'</a>';
		}
		$this->display('article/'.$this->type['t_content']);
	}
	function type(){
		if($this->syArgs('file',1)!=''){
			$this->type=syDB('classtype')->find(' htmlfile="'.$this->syArgs('file',1).'" or tid='.$this->syArgs('file').' ');
			$tid = $this->type['tid'];
		}else{
			$tid = $this->syArgs('tid');
			$this->type=syDB('classtype')->find(" molds='article' and tid=".$tid." ");
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
			$order=' order by orders desc,addtime desc,id desc';
			$f=syDB('fields')->findAll(" molds='article' and types like '%|".$tid."|%' and lists=1 ");
			if($f){
				foreach($f as $v){$fields.=','.$v['fields'];}
				$sql='select id,tid,title,style,trait,gourl,addtime,hits,litpic,orders,mrank,mgold,isshow,description,htmlurl,htmlfile,user'.$fields.' from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
			}else{
				$sql='select id,tid,title,style,trait,gourl,addtime,hits,litpic,orders,mrank,mgold,isshow,description,htmlurl,htmlfile,user from '.$this->db.$w.$order;
			}
			$total_page=total_page($this->db.$w);
			$this->lists = $this->Class->syPager($this->syArgs('page',0,1),$this->type['listnum'],$total_page)->findSql($sql);
			$pages=$this->Class->syPager()->getPager();
			$this->pages=html_url('classtype',$this->type,$pages,$this->syArgs('page',0,1));
			$list_c=$this->lists;
			foreach($list_c as $k=>$v){
				$list_c[$k]['url']=html_url('article',$v);
			}
			$this->lists=$list_c;
		}
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>';
		$type_pos=$this->sy_class_type->navi($tid);
		foreach($type_pos as $v){
			$d_pos=syDB('classtype')->find(array('tid'=>$v['tid']),null,'tid,molds,htmldir,htmlfile,mrank');
			$this->positions.='  &gt;  <a href="'.html_url('classtype',$d_pos).'">'.$v['classname'].'</a>';
		}
		$this->display('article/'.$t);
	}
	function hits(){
		if($this->syArgs('id')){
			syDB('article')->incrField(array('id'=>$this->syArgs('id')), 'hits');
			$hits=syDB('article')->find(array('id'=>$this->syArgs('id')),null,'hits');
			echo 'document.write("'.$hits['hits'].'");';
		}
	}
	function search(){
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
		$sql='select id,tid,title,style,trait,gourl,addtime,hits,litpic,orders,mrank,mgold,isshow,htmlurl,htmlfile,description,user,body from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
		$total_page=total_page($this->db.$w);
		$this->lists = $this->Class->syPager($this->syArgs('page',0,1),30,$total_page)->findSql($sql); 
		$pages=$this->Class->syPager()->getPager();
		$this->pages=pagetxt($pages);
		$list_c=$this->lists;
		foreach($list_c as $k=>$v){
			$list_c[$k]['title']=str_ireplace($this->syArgs('word',1),'<b style="color:red;">'.$this->syArgs('word',1).'</b>',$v['title']);
			$list_c[$k]['url']=html_url('article',$v);
		}		
		$this->lists=$list_c;
		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>  &gt;  '.$this->moldname.'搜索“'.$this->syArgs('word',1).'”';
		$this->display('article/search.html');
	}
}	