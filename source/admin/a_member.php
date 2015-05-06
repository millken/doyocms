<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_member extends syController
{
	function __construct(){
		parent::__construct();
		$this->cu=syClass('c_member');
		
		$this->glista = syDB('member_group')->findAll(null,' weight,gid ');
		if(($this->syArgs('a',1)=='add'||$this->syArgs('a',1)=='edit')&&$this->syArgs('run')==1){
			$this->newrow1 = array(
				'user' => $this->syArgs('user',1),
				'pass' => md5(md5($this->syArgs("pass",1)).$this->syArgs("user",1)),
				'email' => $this->syArgs('email',1),
				'gid' => $this->syArgs('gid'),
				//'money' => $this->syArgs('money',3,0.00),
				'regtime' => time(),
				'token' => '',
			);
			$this->newrow2 = array();
			$v=syDB('fields')->findAll(" molds='member' ");
			foreach($v as $f){
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
		if(($this->syArgs('a',1)=='gadd'||$this->syArgs('a',1)=='gedit')&&$this->syArgs('run')==1){
			$this->newrow = array(
				'name' => $this->syArgs('name',1),
				'weight' => $this->syArgs('weight'),
				'submit' => $this->syArgs('submit'),
				'audit' => $this->syArgs('audit'),
				'filetype' => strtolower($this->syArgs('filetype',1)),
				'filesize' => $this->syArgs('filesize'),
				'fileallsize' => $this->syArgs('fileallsize'),
				'discount_type' => $this->syArgs('discount_type'),
				'discount' => $this->syArgs('discount',3,0.00),
			);
		}
	}
	function index(){
		$this->toptxt='会员管理';
		$this->lists = $this->cu->findAll(null,null,'id,user,gid');
		$this->display("member.html");
	}
	function add(){
		if ($this->syArgs('run')==1){
			if(!$this->syArgs('pass',1)){message_a("密码不能为空");}
			$newVerifier=$this->cu->syVerifier($this->newrow1);
				if(false == $newVerifier){
					$addnewrow=$this->cu->create($this->newrow1);
					if($addnewrow==FALSE){message_a("会员主表数据写入失败，请重新提交");}
					$arrays = array(
						'aid' => $addnewrow,
					);
					$this->newrow2=array_merge($this->newrow2,$arrays);
					if(syDB('member_field')->create($this->newrow2)){
						message_a("会员添加成功","?c=a_member");
					}else{message_a("会员附表数据写入失败，请重新提交");}
				}else{message_b($newVerifier);}
		}
		$this->toptxt='添加会员';
		$this->postgo='add';
		$this->display("member.html");
	}
	function edit(){
		$this->d=$this->cu->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			$a=$this->newrow1;
			if($this->syArgs('pass',1)==''){unset($a['pass']);}
				if($this->cu->update(array('id'=>$this->syArgs('id')),$a)==FALSE)
				{message_a("会员主表数据修改失败，请重新提交");}
				if(!$this->newrow2){message_a("会员修改成功","?c=a_member");}
				if(syDB('member_field')->update(array('aid'=>$this->syArgs('id')),$this->newrow2)){
					message_a("会员修改成功","?c=a_member");
				}else{message_a("会员附表数据修改失败，请重新提交");}
		}
		$this->toptxt='修改会员';
		$this->postgo='edit';
		$this->display("member.html");
	}
	function del(){
		$this->toptxt='删除会员';
		$this->delarray=$this->cu->find(array('id'=>$this->syArgs('id')));
		$id=$this->delarray['id'];
		if ($this->syArgs('run')==1){
			if($this->cu->delete(array('id'=>$id))&&syDB('member_field')->delete(array('aid'=>$id))){
				message_a("会员删除成功","?c=a_member");
			}else{message_a("会员删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除会员 <strong>['.$this->delarray['user'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c=a_member&a=del&run=1&id='.$id.'">确定删除</a><a href="?c=a_member">取消操作</a>';
		$this->display("msg.html");
	}
	function glist(){
		$this->toptxt='会员分组管理';
		$this->lists = syDB('member_group')->findAll();
		$this->postgo='glist';
		$this->display("member.html");
	}
	function gadd(){
		if ($this->syArgs('run')==1){
			if(!$this->syArgs('name',1)){message_a("分组名称不能为空");}
			if(syDB('member_group')->create($this->newrow)){
				message_a("会员分组添加成功","?c=a_member&a=glist");
			}else{message_a("会员分组添加失败，请重新提交");}
		}
		$this->toptxt='添加会员分组';
		$this->postgo='gadd';
		$this->display("member.html");
	}
	function gedit(){
		$this->d=syDB('member_group')->find(array('gid'=>$this->syArgs('gid')));
		if ($this->syArgs('run')==1){
			if(!$this->syArgs('name',1)){message_a("分组名称不能为空");}
			if(syDB('member_group')->update(array('gid'=>$this->syArgs('gid')),$this->newrow)){
				message_a("会员分组修改成功","?c=a_member&a=glist");
			}else{message_a("会员分组修改失败，请重新提交");}
		}
		$this->toptxt='修改会员分组';
		$this->postgo='gedit';
		$this->display("member.html");
	}
	function gdel(){
		$this->toptxt='删除会员分组';
		$this->delarray=syDB('member_group')->find(array('gid'=>$this->syArgs('gid')));
		if($this->delarray('sys')==1)message_a("系统分组，禁止删除");
		$gid=$this->delarray['gid'];
		if ($this->syArgs('run')==1){
			if(syDB('member_group')->delete(array('gid'=>$gid))){
				syDB('member')->update(array('gid'=>$gid),array('gid'=>2));
				syDB('product_discount')->delete(array('mgid'=>$gid));
				message_a("会员分组删除成功","?c=a_member&a=glist");
			}else{message_a("会员分组删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除会员分组 <strong>['.$this->delarray['name'].']</strong> 吗？';
		$this->msg='删除此分组后，此分组下所有会员自动转移自系统初级分组';
		$this->msggo='<a href="?c=a_member&a=gdel&run=1&gid='.$gid.'">确定删除</a><a href="?c=a_member&a=glist">取消操作</a>';
		$this->display("msg.html");
	}
}	