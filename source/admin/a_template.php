<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_template extends syController
{
	function __construct(){
		parent::__construct();
		$this->Get_c='a_template';
		$this->classa=syClass('syModel');
		$this->db=$GLOBALS['G_DY']['db']['prefix'];
		$this->indir='template/';
		$this->themes=syExt('view_themes');
	}
	function index(){
		$this->type=$this->syArgs('type');
		if(!$this->type){
			$this->toptxt='模板管理';
			$lists=array();
			$i=0;
			if($dp=@opendir($this->indir)){
				while(false!==($file = readdir($dp))) {
					if($file!='.' && $file!='..' && is_dir($this->indir.$file)) {
						$lists[$i]['dir']=$file;
						if(file_exists($this->indir.$file.'/thumb.jpg'))$lists[$i]['thumb']=1;
						if(file_exists($this->indir.$file.'/sql.txt'))$lists[$i]['sql']=1;
						$i=$i+1;
					}
				}
				@closedir($dp);
			}
			$this->lists=$lists;
		}
		$this->display('template.html');
	}
	function add(){
		$t=$this->syArgs('template',1);
		$db=file_exists($this->indir.$t.'/sql.txt');
		if($this->syArgs('run')==1){
			if($db){
				if(file_exists($this->indir.$this->themes.'/sql.txt')===false){$this->dbexport($this->themes);}
				$this->dbadd($this->indir.$t.'/sql.txt');
				deleteDir($GLOBALS['G_DY']['sp_cache']);
				deleteDir($GLOBALS['G_DY']['view']['config']['template_tpl']);
			}
			$configfile='config.php';
			$fp_tp=@fopen($configfile,"r");
			$fp_txt=@fread($fp_tp,filesize($configfile));
			@fclose($fp_tp);
			$fp_txt=preg_replace("/'view_themes' => '.*?',/","'view_themes' => '".$t."',",$fp_txt);
			$fpt_tpl=@fopen($configfile,"w");
			@fwrite($fpt_tpl,$fp_txt);
			@fclose($fpt_tpl);
			
			message_a('模板设置成功，请刷新前台页面查看。','?c='.$this->Get_c);
		}else{
			if($db){
				$this->msgtitle='确定要安装模板 <strong>['.$t.']</strong> 吗？';
				$this->msg='提示:本模板需要覆盖数据，强烈建议先进行系统数据库备份操作，否则将造成现有数据丢失。';
				$this->msggo='<a href="?c='.$this->Get_c.'&a=add&run=1&template='.$t.'" onclick="click_go();">开始安装</a><a href="?c='.$this->Get_c.'">取消操作</a>';
				$this->msgclick='正在安装模板，请稍后...';
				$this->display("msg.html");
			}else{
				jump('?c='.$this->Get_c.'&a=add&run=1&template='.$t);
			}
		}
	}
	function export(){
		$this->toptxt='备份模板数据库';
		$t=$this->syArgs('template',1);
		if($this->syArgs('run')==1){
			$this->dbexport($t);
			message_a('备份成功，您可以将template/'.$t.'及skin/'.$t.'拷贝至其它DOYO系统使用','?c='.$this->Get_c);
		}
		$this->msgtitle='确定要备份模板 <strong>['.$t.']</strong> 数据文件吗？';
		$this->msg='提示:将导出当前系统数据，并生成模板安装数据库文件,便于在其他系统进行安装，此操作不会导出涉及系统隐私的数据(如管理员、会员信息等)，请放心使用。';
		$this->msggo='<a href="?c='.$this->Get_c.'&a=export&run=1&template='.$t.'" onclick="click_go();">开始备份</a><a href="?c='.$this->Get_c.'">取消操作</a>';
		$this->msgclick='正在备份模板数据，请稍后...';
		$this->display("msg.html");
	}
	private function dbexport($template){
		set_time_limit(99999999);
		ini_set('memory_limit',-1);
		$table=array('ads','adstype','attribute','attribute_type','classtype','custom','fields','funs','labelcus','linkstype','molds','special','traits','member_field');
		$molds = syDB('molds')->findAll(array('isshow'=>1),null,'molds');
		foreach($molds as $m){
			$table=array_merge($table,array($m['molds']));
			$table=array_merge($table,array($m['molds'].'_field'));
		}
		$sql='';
		foreach($table as $s){
			$c=$this->classa->findSql('show create table '.$this->db.$s);
			$CreateTable=preg_replace("/\n/","",$c[0]['Create Table']);
			$CreateTable=str_replace('`'.$this->db,'`[_pre_]',$CreateTable);
			$sql.='DROP TABLE IF EXISTS [_pre_]'.$s."\r\n".$CreateTable."\r\n";
		}
		foreach($table as $s1){
			$num_fields=$this->classa->findSql('select * from '.$this->db.$s1);
			foreach($num_fields as $v){
				$tt='';
				$sql.= 'INSERT INTO [_pre_]'.$s1.' VALUES(';
				foreach($v as $f){$tt.= "'".mysql_real_escape_string($f)."'".",";}
				$sql.= rtrim($tt,',').')'."\r\n";
			}
		}
		write_file($sql,$this->indir.$template.'/sql.txt');
		set_time_limit(30);
		return true;
	}
	private function dbadd($sql){
		set_time_limit(99999999);
		ini_set('memory_limit',-1);
		if(file_exists($sql)){
			foreach(file($sql) as $rsql){
				$sql=str_replace('[_pre_]',$this->db,$rsql);
				$rgo=$this->classa->runSql($sql);
			}
			set_time_limit(30);
			return true;
		}
	}

}	