<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_message extends syController
{
	function __construct(){
		parent::__construct();
		$this->gopage=$this->syArgs('page',0,1);
		$this->molds = 'message';
		$this->sqldb=$GLOBALS['G_DY']['db']['prefix'].$this->molds;
		$this->moldname=moldsinfo($this->molds,'moldname');
		$this->ClassADD = syClass('c_'.$this->molds);
		$this->ClassT=syClass('c_classtype');
		$this->ClassF=syClass('c_fields');
		$this->auser=syClass('syauser');
		$classtype=syDB('classtype')->findAll(array('molds'=>$this->molds),null,'tid,classname,pid,molds');
		$this->types=syClass('syclasstype');
		$this->typesdb=$this->types->type_txt();		
		if($this->syArgs('a',1)=='edit'&&$this->syArgs('run')==1){
			$retime=strtotime($this->syArgs('retime',1));
			$this->newrow1 = array(
				'tid' => $this->syArgs('tid'),
				'faid' => $this->syArgs('faid'),
				'fmolds' => $this->syArgs('fmolds',1),
				'user' => $this->syArgs('user',1),
				'title' => $this->syArgs('title',1),
				'retime' => $retime,
				'orders' => $this->syArgs('orders'),
				'isshow' => $this->syArgs('isshow'),
				'body' => $this->syArgs('body',1),
				'reply' => $this->syArgs('reply',1),
			);
			$this->newrow2 = array();
			$v=$this->ClassF->findAll(" molds='".$this->molds."' and types like '%|".$this->syArgs('tid')."|%' ");
			foreach($v as $f){
				$ns='';$n=array();
				if($f['fieldstype']=='varchar' || $f['fieldstype']=='files' || $f['fieldstype']=='select'){$ns=$this->syArgs($f['fields'],1);}
				if($f['fieldstype']=='int'){$ns=$this->syArgs($f['fields']);}
				if($f['fieldstype']=='contingency'&&$this->syArgs('contingency_'.$f['fields'].'_word',1)){$ns=$this->syArgs($f['fields']);}
				if($f['fieldstype']=='decimal'){$ns=$this->syArgs($f['fields'],3);}
				if($f['fieldstype']=='text'){$ns=$this->syArgs($f['fields'],4);}
				if($f['fieldstype']=='time'){$ns=strtotime($this->syArgs($f['fields'],1));}
				if($f['fieldstype']=='fileall'){
					$fieldsall=$this->syArgs($f['fields'].'file',2);
					if($fieldsall){
						$num=$this->syArgs($f['fields'].'num',2);
						$txt=$this->syArgs($f['fields'].'txt',2);$ns='';
						natsort($num);
						foreach($num as $k=>$v){
							$ns.='|-|'.$fieldsall[$k].'|,|'.$txt[$k];
						}
						$ns=substr($ns,3);
					}
				}
				if($f['fieldstype']=='checkbox'){if($this->syArgs($f['fields'],2)){$ns='|'.implode('|',$this->syArgs($f['fields'],2)).'|';}else{$ns='';}}
				$n=array($f['fields'] => $ns);
				$this->newrow2=array_merge($this->newrow2,$n);
			}
		}
	}
	function index(){
		$this->toptxt=$this->moldname.'列表';
		if($this->syArgs('isshow')){
			if($this->syArgs('isshow')==1){$this->toptxt='已审'.$this->toptxt;}else{$this->toptxt='待审'.$this->toptxt;}
			$conditions.="and isshow=".$this->syArgs('isshow')." ";
		}
		if($_SESSION['auser']['level']==1){
			if($this->syArgs("tid")){$conditions.= "and tid in(".$this->types->leafid($this->syArgs('tid')).") ";}
		}else{
			if($this->syArgs("tid") && $this->auser->checkclass($this->syArgs("tid"))){$conditions.= "and tid in(".$this->types->leafid($this->syArgs('tid')).") and tid in(".trim($_SESSION['auser']['pclasstype'],',').") ";}
			else{if($this->auser->checkclass()==false)message_a("您没有栏目权限");$conditions.= "and tid in(".trim($_SESSION['auser']['pclasstype'],',').") ";}
		}
		if($this->syArgs("title",1)){$conditions.= "and title like '%".$this->syArgs('title',1)."%' ";}
		if($conditions!=''){$conditions=' where '.substr($conditions,3);}
		$sql='select * from '.$this->sqldb.$conditions.' order by orders desc,addtime desc,id desc';
		$total_page=total_page($this->sqldb.$conditions);
		$this->listarray = $this->ClassADD->syPager($this->gopage,15,$total_page)->findSql($sql); 
		$this->pages = pagetxt($this->ClassADD->syPager()->getPager());
		$this->display($this->molds.".html");
	}
	function edit(){
		$this->carray=$this->ClassADD->findSql('select * from '.$this->sqldb.' a left join '.$this->sqldb.'_field b on (a.id=b.aid) where id='.$this->syArgs('id').' limit 0,1');
		$this->carray=$this->carray[0];
		if(!$this->auser->checkclass($this->carray['tid'])){message_a("无权操作本栏目内容");}
		if ($this->syArgs('run')==1){
			$newVerifier=$this->ClassADD->syVerifier($this->newrow1);
				if(false == $newVerifier){
					if($this->ClassADD->update(array('id'=>$this->syArgs('id')),$this->newrow1)==FALSE)
					{message_a("主表数据修改失败，请重新提交");}
					if(!$this->newrow2){message_a("修改成功","?c=a_".$this->molds);}
					if(syDB($this->molds.'_field')->update(array('aid'=>$this->syArgs('id')),$this->newrow2)){
						message_a($this->moldname."修改成功","?c=a_".$this->molds);
					}else{message_a("附表数据修改失败，请重新提交");}
				}else{message_b($newVerifier);}
		}
		$this->toptxt=$this->moldname.'修改内容';
		$this->postgo='edit';
		$this->display($this->molds."_edit.html");
	}
	function del(){
		$this->toptxt=$this->moldname.'删除内容';
		$this->del=$this->ClassADD->find(array('id'=>$this->syArgs('id')));
		if(!$this->auser->checkclass($this->del['tid'])){message_a("无权操作本栏目内容");}
		$id=$this->del['id'];
		if ($this->syArgs('run')==1){
			if(syDB($this->molds)->delete(array('id'=>$id))&&syDB($this->molds.'_field')->delete(array('aid'=>$id)))
			{message_a("删除成功","?c=a_".$this->molds);}else{message_a("删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除 <strong>['.$this->del['title'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c=a_'.$this->molds.'&a=del&run=1&id='.$id.'">确定删除</a><a href="?c=a_'.$this->molds.'">取消操作</a>';
		$this->display("msg.html");
	}
	function alledit(){
		if($this->syArgs('ids',2)===NULL || $this->syArgs('go')===NULL){message_a("请选择操作对象");}
		if($this->syArgs('run')==1){
			$ids=explode(',',$this->syArgs('ids',1));
			switch ($this->syArgs('go')) {
				case 0:
					foreach($ids as $tp){
						$a=$this->ClassADD->find(array('id'=>$tp), null, " id,tid ");
						if($this->auser->checkclass($a['tid']))$this->ClassADD->update(array('id'=>$tp),array('isshow'=>1));
					}
					jump("?c=a_".$this->molds);
					break;
				case 1:
					foreach($ids as $tp){
						$a=$this->ClassADD->find(array('id'=>$tp), null, " id,tid ");
						if($this->auser->checkclass($a['tid']))$this->ClassADD->update(array('id'=>$tp),array('isshow'=>0));
					}
					jump("?c=a_".$this->molds);
					break;
				case 2:
					foreach($ids as $tp){
						$a=$this->ClassADD->find(array('id'=>$tp), null, " id,tid ");
						if($this->auser->checkclass($a['tid'])){
							syDB($this->molds)->delete(array('id'=>$tp));
							syDB($this->molds.'_field')->delete(array('aid'=>$tp));
						}
					}
					jump("?c=a_".$this->molds);
					break;
			}
		}
		if($this->syArgs('go')==0)$txt='审核';
		if($this->syArgs('go')==1)$txt='取消审核';
		if($this->syArgs('go')==2)$txt='删除';
		$this->toptxt='批量'.$txt.$this->moldname.'内容';
		$ids=implode(',',$this->syArgs('ids',2));
		$this->msgtitle='确定要'.$txt.'这些'.$this->moldname.'内容吗？';
		$this->msg='本操作将批量'.$txt.'ID为['.$ids.']的内容<br>';
		$this->msggo='<a href="?c=a_'.$this->molds.'&a=alledit&run=1&ids='.$ids.'&go='.$this->syArgs('go').'&tid='.$this->syArgs('tid').'">确定'.$toptxt.'</a><a href="?c=a_'.$this->molds.'">取消操作</a>';
		$this->display("msg.html");
	}
}	