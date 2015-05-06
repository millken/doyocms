<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_adminuser extends syController
{
	function __construct(){
		parent::__construct();
		$this->cu=syClass('c_admin_user');
		$this->glista = syDB('admin_group')->findAll();
		$this->per0=syDB('admin_per')->findAll(array('up'=>0,'no'=>0),' orders desc,pid ');
		$this->per1=syDB('admin_per')->findAll(array('no'=>1),' orders,pid ');
		$this->types=syClass('syclasstype');
		$this->typesdb=$this->types->type_txt();
		if(($this->syArgs('a',1)=='add'||$this->syArgs('a',1)=='edit'||$this->syArgs('a',1)=='edituser')&&$this->syArgs('run')==1){
			if($this->syArgs('pclasstype',2)){$pclasstype=','.implode(',',$this->syArgs('pclasstype',2)).',';}else{$pclasstype='';}
			$this->newrow = array(
				'auser' => $this->syArgs('auser',1),
				'apass' => md5(md5($this->syArgs("apass",1)).$this->syArgs("auser",1)),
				'aname' => $this->syArgs('aname',1),
				'amail' => $this->syArgs('amail',1),
				'atel' => $this->syArgs('atel',1),
				'level' => $this->syArgs('level'),
				'gid' => $this->syArgs('gid'),
				'pclasstype' => $pclasstype,
			);
		}
		if(($this->syArgs('a',1)=='gadd'||$this->syArgs('a',1)=='gedit')&&$this->syArgs('run')==1){
			$paction=$this->syArgs('paction',2);
			$paction=array_filter($paction);
			if($paction){$paction=','.implode(',',$this->syArgs('paction',2)).',';}else{$paction='';}
			$this->newrow = array(
				'name' => $this->syArgs('name',1),
				'audit' => $this->syArgs('audit'),
				'oneself' => $this->syArgs('oneself'),
				'paction' => $paction,
			);
		}
	}
	function index(){
		$this->toptxt='管理员管理';
		if($_SESSION['auser']['level']==1){
			$this->lists = $this->cu->findAll(null,null,'auid,auser,level,gid');
		}else{
			$this->lists = $this->cu->findAll(array('level'=>0),null,'auid,auser,level');
		}
		$this->display("adminuser.html");
	}
	function add(){
		if ($this->syArgs('run')==1){
			if(!$this->syArgs('apass',1)){message_a("密码不能为空");}
			$newVerifier=$this->cu->syVerifier($this->newrow);
				if(false == $newVerifier){	
					if($this->cu->find(array('auser'=>$this->newrow['auser']))){message_a("用户名名称已经存在");}			
					if($this->cu->create($this->newrow)){
						message_a("管理员创建成功","?c=a_adminuser");
					}else{message_a("管理员创建失败，请重新提交");}
				}else{message_b($newVerifier);}
		}
		$this->toptxt='添加管理员';
		$this->postgo='add';
		$this->display("adminuser.html");
	}
	function edit(){
		$this->d=$this->cu->find(array('auid'=>$this->syArgs('auid')));
		if($_SESSION['auser']['level']==0 && $this->d['level']==1){message_a("无权操作超级管理员");}
		if ($this->syArgs('run')==1){
			$a=$this->newrow;
			$b=$this->cu->find(' level=1 and auid!='.$this->syArgs('auid').' ');
			if(!$b&&$a['level']!=1)message_a("本管理为系统中唯一超级管理员，因此本管理员级别必须为超级管理员。");
			if($this->syArgs('apass',1)==''){unset($a['apass']);}
				if($this->cu->update(array('auid'=>$this->d['auid']),$a)){
					message_a("管理员修改成功","?c=a_adminuser");
				}else{message_a("管理员修改失败,请重新提交");}
		}
		$this->toptxt='修改管理员';
		$this->postgo='edit';
		$this->display("adminuser.html");
	}
	function edituser(){
		$this->d=$this->cu->find(array('auid'=>$_SESSION['auser']['auid']));
		if ($this->syArgs('run')==1){
			$a=$this->newrow;unset($a['auser'],$a['level'],$a['gid'],$a['pclasstype']);
			if($this->syArgs('apass',1)==''){unset($a['apass']);}
				if($this->cu->update(array('auid'=>$this->d['auid']),$a)){
					message_a("会员信息修改成功");
				}else{message_a("会员信息修改失败,请重新提交");}
		}
		$this->toptxt='修改会员信息';
		$this->postgo='edituser';
		$this->display("adminuser.html");
	}
	function del(){
		$this->toptxt='删除管理员';
		$this->delarray=$this->cu->find(array('auid'=>$this->syArgs('auid')));
		if($_SESSION['auser']['level']==0 && $this->delarray['level']==1){message_a("无权操作超级会员");}
		$b=$this->cu->find(' level=1 and auid!='.$this->syArgs('auid').' ');
		if(!$b&&$a['level']!=1)message_a("本管理为系统中唯一超级管理员，不能删除。");
		$auid=$this->delarray['auid'];
		if ($this->syArgs('run')==1){
			if($this->cu->delete(array('auid'=>$this->syArgs('auid'))))
			{message_a("管理员删除成功","?c=a_adminuser");}else{message_a("管理员删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除管理员 <strong>['.$this->delarray['auser'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c=a_adminuser&a=del&run=1&auid='.$auid.'">确定删除</a><a href="?c=a_adminuser">取消操作</a>';
		$this->display("msg.html");
	}
	function glist(){
		$this->toptxt='管理员分组管理';
		$this->lists = syDB('admin_group')->findAll();
		$this->postgo='glist';
		$this->display("adminuser.html");
	}
	function gadd(){
		if ($this->syArgs('run')==1){
			if(!$this->syArgs('name',1)){message_a("分组名称不能为空");}
			if(syDB('admin_group')->create($this->newrow)){
				message_a("管理员分组添加成功","?c=a_adminuser&a=glist");
			}else{message_a("管理员分组添加失败，请重新提交");}
		}
		$this->toptxt='添加管理员分组';
		$this->postgo='gadd';
		$this->display("adminuser.html");
	}
	function gedit(){
		$this->d=syDB('admin_group')->find(array('gid'=>$this->syArgs('gid')));
		if ($this->syArgs('run')==1){
			if(!$this->syArgs('name',1)){message_a("分组名称不能为空");}
			if(syDB('admin_group')->update(array('gid'=>$this->syArgs('gid')),$this->newrow)){
				message_a("管理员分组修改成功","?c=a_adminuser&a=glist");
			}else{message_a("管理员分组修改失败，请重新提交");}
		}
		$this->toptxt='修改管理员分组';
		$this->postgo='gedit';
		$this->display("adminuser.html");
	}
	function gdel(){
		$this->toptxt='删除管理员分组';
		$this->delarray=syDB('admin_group')->find(array('gid'=>$this->syArgs('gid')));
		$gid=$this->delarray['gid'];
		if ($this->syArgs('run')==1){
			if(syDB('admin_group')->delete(array('gid'=>$gid)))
			{message_a("管理员分组删除成功","?c=a_adminuser&a=glist");}else{message_a("管理员分组删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除管理员分组 <strong>['.$this->delarray['name'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c=a_adminuser&a=gdel&run=1&gid='.$gid.'">确定删除</a><a href="?c=a_adminuser&a=glist">取消操作</a>';
		$this->display("msg.html");
	}
}	