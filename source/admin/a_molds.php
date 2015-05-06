<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_molds extends syController
{
	function __construct(){
		parent::__construct();
		$this->Get_c='a_molds';
		$this->a=$this->syArgs('a',1);
		$this->db=$GLOBALS['G_DY']['db']['prefix'];
		$this->Class=syClass('c_molds');
		$this->newrow = array(
			'moldname' => $this->syArgs('moldname',1),
			'isshow' => $this->syArgs('isshow'),
			'orders' => $this->syArgs('orders'),
			't_index' => $this->syArgs('t_index',1),
			't_list' => $this->syArgs('t_list',1),
			't_listimg' => $this->syArgs('t_listimg',1),
			't_listb' => $this->syArgs('t_listb',1),
			't_content' => $this->syArgs('t_content',1),
			'molddb' => '',
			'config' => serialize($this->syArgs('config',2)),
		);
	}
	function index(){
		$this->toptxt='频道管理';
		$this->lists = syDB('molds')->findAll(array('isshow'=>1));
		$this->lists_no = syDB('molds')->findAll(array('isshow'=>0));
		$this->display("molds.html");
	}
	function add(){
		if ($this->syArgs('run')==1){
			$this->newrow=array_merge($this->newrow,array('molds' => $this->syArgs('molds',1),));
			$m=syDB('molds')->find(array('molds'=>$this->newrow['molds']));
			if($m){message_a("频道标识已存在，请重新输入");}
			$v=$this->Class->syVerifier($this->newrow);
			if(false == $v){
				$f1=$this->Class->findSql('SHOW TABLES LIKE "'.$this->db.$this->newrow['molds'].'"');
				$f2=$this->Class->findSql('SHOW TABLES LIKE "'.$this->db.$this->newrow['molds'].'_field"');
				if($f1 || $f2)message_a("频道标识数据表已存在，请重新输入");
				$txt='<?php
class c_'.$this->newrow['molds'].' extends syModel{	var $pk = "id";	var $table = "'.$this->newrow['molds'].'";	var $verifier = array("rules" => array("tid" => array("notnull" => TRUE,),"mgold" => array("isgold" => TRUE,),"htmlfile" => array("isfile" => TRUE,),),"messages" => array("tid" => array("notnull" => "请选择栏目",),"mgold" => array("isgold" => "请输入正确的价格，只能包含0-9数字及小数点",	),"htmlfile" => array("isfile" => "文件名只能为英文、数字、下划线、中划线组成",),));}';
				if(!write_file($txt,'include/class/c_'.$this->newrow['molds'].'.php'))message_a("频道标识数据表已存在，请重新输入");
				$db1=$this->Class->runSql("CREATE TABLE IF NOT EXISTS `".$this->db.$this->newrow['molds']."` (
				`id` mediumint(8) unsigned NOT NULL auto_increment,
				`tid` smallint(5) unsigned NOT NULL default '0',
				`sid` smallint(5) unsigned NOT NULL default '0',
				`isshow` tinyint(1) unsigned NOT NULL default '0',
				`title` char(100) NOT NULL,
				`style` char(60) NOT NULL,
				`trait` char(50) NOT NULL,
				`gourl` char(255) NOT NULL,
				`htmlfile` char(100) NOT NULL,
				`htmlurl` char(255) NOT NULL,
				`addtime` int(10) unsigned NOT NULL default '0',
				`hits` int(10) unsigned NOT NULL default '0',
				`orders` int(10) NOT NULL default '0',
				`mrank` smallint(5) NOT NULL default '0',
				`mgold` int(10) unsigned NOT NULL default '0',
				`keywords` char(200) NOT NULL,
				`description` char(255) NOT NULL,
				`user` char(30) NOT NULL,
				`usertype` tinyint(2) unsigned NOT NULL default '0',
				
				PRIMARY 
				KEY  (`id`),
				KEY `orbye` (`orders`,`addtime`),
				KEY `".$this->newrow['molds']."` (`isshow`,`tid`,`trait`,`sid`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
				
				$db2=$this->Class->runSql("CREATE TABLE IF NOT EXISTS `".$this->db.$this->newrow['molds']."_field` (
				`aid` mediumint(8) unsigned NOT NULL default '0',
				
				PRIMARY KEY  (`aid`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
				
				if(!db1||!db2)message_a("频道数据库创建失败，请重新提交");
				$pr=syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'], 'name'=>'管理','molds'=>$this->newrow['molds']));
				syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_add', 'name'=>'添加','molds'=>'','up'=>$pr));
				syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_edit', 'name'=>'编辑','molds'=>'','up'=>$pr));
				syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_del', 'name'=>'删除','molds'=>'','up'=>$pr));
				syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_audit', 'name'=>'审核','molds'=>'','up'=>$pr));
				syDB('admin_per')->create(array('action'=>'channel_'.$this->newrow['molds'].'_index', 'name'=>'列表','molds'=>'','no'=>1,'up'=>$pr));
				if(syDB('molds')->create($this->newrow)){
					$themes=syExt('view_themes');$themes='template/'.$themes.'/';
					if(is_dir($themes.$this->newrow['molds'])!=true){recurse_copy($themes.'channel',$themes.$this->newrow['molds']);}
					message_a("频道创建成功","?c=".$this->Get_c);
				}else{message_a("频道创建失败，请重新提交");}
			}else{message_a($v);}
		}
		$this->toptxt='添加频道';
		$this->postgo='add';
		$this->display("molds.html");
	}
	function edit(){
		$this->d=syDB('molds')->find(array('mid'=>$this->syArgs('mid')));
		if ($this->syArgs('run')==1){
			if(syDB('molds')->update(array('mid'=>$this->d['mid']),$this->newrow)){
				message_a("频道修改成功","?c=".$this->Get_c);
			}else{message_a("频道修改失败,请重新提交");}
		}
		$this->toptxt='修改频道';
		$this->postgo='edit';
		$this->display("molds.html");
	}
	function del(){
		$this->toptxt='删除频道';
		$this->d=syDB('molds')->find(array('mid'=>$this->syArgs('mid')));
		if($this->d['sys']==1)message_a("系统频道,禁止删除");
		if ($this->syArgs('run')==1){
			syDB('molds')->runSql("DROP TABLE IF EXISTS `".$this->db.$this->d['molds']."`");
			syDB('molds')->runSql("DROP TABLE IF EXISTS `".$this->db.$this->d['molds']."_field`");
			@unlink('include/class/c_'.$this->d['molds'].'.php');
			syDB('admin_per')->delete(' action like "channel_'.$this->d['molds'].'_%" or action="channel_'.$this->d['molds'].'" ');
			syDB('fields')->delete(array('molds'=>$this->d['molds']));
			syDB('comment')->delete(array('molds'=>$this->d['molds']));
			syDB('traits')->delete(array('molds'=>$this->d['molds']));
			if(syDB('molds')->delete(array('mid'=>$this->syArgs('mid')))&&syDB('admin_per')->delete(" action like '%a_".$this->d['molds']."%' "))
			{message_a("频道删除成功","?c=".$this->Get_c);}else{message_a("频道删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除频道 <strong>['.$this->d['moldname'].']</strong> 吗？';
		$this->msg='警告：删除频道，将自动删除本频道下的所有已发布内容，不可恢复，建议删除前备份数据库。';
		$this->msggo='<a href="?c='.$this->Get_c.'&a=del&run=1&mid='.$this->d['mid'].'">确定删除</a><a href="?c='.$this->Get_c.'">取消操作</a>';
		$this->display("msg.html");
	}
}	