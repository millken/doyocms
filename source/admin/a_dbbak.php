<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_dbbak extends syController
{
	function __construct(){
		parent::__construct();
		$this->classa=syClass('syModel');
		$this->a=$this->syArgs('a',1);
		$this->db=$GLOBALS['G_DY']['db']['database'];
		$this->bakdir='include/backup/';
	}
	function index(){
		$p=$GLOBALS['G_DY']['db']['prefix'];
		$pl=strlen($p);
		$dbs=array();$i=$ii=0;
		$ald=$this->classa->findSql('show table status from `'.$this->db.'`');
		foreach($ald as $v){
			if(substr($v['Name'], 0, $pl)==$p){
				$dbs['doyo'][$i]=$v['Name'];$i++;
			}else{
				$dbs['other'][$ii]=$v['Name'];$ii++;
			}
		}
		$this->dbdoyo=$dbs['doyo'];
		$this->dbother=$dbs['other'];
		$this->handle=opendir($this->bakdir);
		$this->display("dbbak.html");
	}
	function bak(){
		if($this->syArgs('filesize')<=0)message_a("未填写分卷文件大小");
		if(!$this->syArgs('tables',2))message_a("请选择需要备份的数据表");
		set_time_limit(99999999);
		ob_implicit_flush(1);
		ob_end_flush();
		ini_set('memory_limit',-1);
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link href="source/admin/template/style/admin.css" rel="stylesheet" type="text/css" />';
		$tables=$this->syArgs('tables',2);
		$sql="<?php die();?>";$p=1;$dir=$this->bakdir.date("Y-m-d-H-i-s",time());
		$filename=$dir;
		foreach($tables as $t){
			$c=$this->classa->findSql('show create table '.$t);
			$sql.='DROP TABLE IF EXISTS `'.$t."`\r\n".preg_replace("/\n/","",$c[0]['Create Table'])."\r\n";
		}
		foreach($tables as $t){
			$num_fields=$this->classa->findSql('select * from '.$t);
			foreach($num_fields as $v){
				$tt='';
				$sql.= 'INSERT INTO `'.$t.'` VALUES(';
				foreach($v as $f){$tt.= "'".mysql_real_escape_string($f)."'".",";}
				$sql.= rtrim($tt,',').')'."\r\n";
				if(strlen($sql)>=$this->syArgs('filesize')*1024){
					if($p==1){$filename.=".php";}else{$filename.="_v".$p.".php";}
					if(write_file($sql,$filename)){
						echo '<div class="flush">生成数据表卷：'.$filename.'</div>';
					}else{set_time_limit(30);message_a("写入备份文件-".$filename."-失败");}
					$p++;
					$filename=$dir;
					$sql="<?php die();?>";
				}
			}
		}
		if($sql!="<?php die();?>"){
			if($p==1){$filename.=".php";}else{$filename.="_v".$p.".php";}
			if(write_file($sql,$filename))
			echo '<div class="flush">生成数据表卷：'.$filename.'</div>';
		}
		set_time_limit(30);
		message_a('数据备份全部完成');
	}
	function optimize(){
		if(!$this->syArgs('tables',2))message_a("请选择需要优化的数据表");
		set_time_limit(99999999);
		ob_implicit_flush(1);
		ob_end_flush();
		ini_set('memory_limit',-1);
		$tables=$this->syArgs('tables',2);
		foreach($tables as $t){
			if($this->classa->findSql('OPTIMIZE TABLE '.$t))echo '数据表：'.$t.'优化完成<br>';
		}
		set_time_limit(30);
		message_a('数据表优化全部完成');
	}
	function restore(){
		if(!$this->syArgs('serverfile',1))message_a("请选择需要恢复的备份");
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link href="source/admin/template/style/admin.css" rel="stylesheet" type="text/css" />';
		set_time_limit(99999999);
		ob_implicit_flush(1);
		ob_end_flush();
		ini_set('memory_limit',-1);
		$serverfile=$this->syArgs('serverfile',1);
		$filename=$this->bakdir.$serverfile;
		$volnum=explode(".ph",$serverfile);
		$this->rfiles=array($filename);
		$this->dbbak_file($this->bakdir.$volnum[0].'_v',2);
		foreach($this->rfiles as $v){
			foreach(file($v) as $rsql){
				$sql=str_replace('<?php die();?>','',$rsql);
				$rgo=$this->classa->runSql($sql);
				if(!$rgo){echo '<div class="flush">'.$v."导入失败".$rgo."</div>";}
			}
			echo '<div class="flush">'.$v."导入完成</div>";
		}
		set_time_limit(30);
		message_a('数据还原全部完成');
	}
	private function dbbak_file($filename,$p){
		$file=$filename.$p.'.php';
		if(file_exists($file)){
			$this->rfiles=array_merge($this->rfiles,array($file));
			$p=$p+1;$this->dbbak_file($filename,$p);
		}
	}
}	