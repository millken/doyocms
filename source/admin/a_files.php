<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_files extends syController
{
	function __construct(){
		parent::__construct();
		$this->db=$GLOBALS['G_DY']['db']['database'];
	}
	function index(){
		$this->display("files.html");
	}
	function clear(){
		set_time_limit(99999999);
		ob_implicit_flush(1);
		ob_end_flush();
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link href="source/admin/template/style/admin.css" rel="stylesheet" type="text/css" /><script src="include/js/jsmain.js" type="text/javascript"></script><script type="text/javascript">function goclear(ctxt){$("#clear").html(ctxt);}</script><div class="main"><div class="progress" id="clear">开始检测附件...</div></div>';
		$this->filesnum=0;
		$this->allfiles=array();
		$this->clear_file('uploads');
		$tables=syDB('molds')->findSql('SHOW TABLES');
		$tablesf=array();
		foreach($tables as $v){
			$tablesf=array_merge($tablesf,array($v['Tables_in_'.$this->db]));
		}
		$dbl=$GLOBALS['G_DY']['db']['prefix'];
		$tablesf=array_diff($tablesf,array($dbl.'admin_per',$dbl.'admin_user',$dbl.'admin_group',$dbl.'adstype',$dbl.'comment',$dbl.'fields',$dbl.'funs',$dbl.'linkstype',$dbl.'member_group',$dbl.'molds',$dbl.'traits',$dbl.'member_file',$dbl.'molds'));
		$delnum=0;$isnum=1;
		$allfilesnum=count($this->allfiles); 
		echo '<script type="text/javascript">goclear("检测到附件'.$allfilesnum.'个");</script>';
		foreach($this->allfiles as $a){
			echo '<script type="text/javascript">goclear("总共附件：<span>'.$allfilesnum.'</span><br>当前检测：<span>'.$isnum.'</span><br>清理数量：<span>'.$delnum.'</span><br>检测进度：<span>'.floor($isnum/$allfilesnum*100).'%</span>");</script>';
			$counts=0;
			foreach($tablesf as $v){
				if(strpos($v,$dbl)!== false){
					$fields=syDB('molds')->findSql('desc '.$this->db.'.'.$v);
					$serachs=array();
					$where='';
					foreach($fields as $f){
						if($this->fieldtype($f['Type']))$where.=" `".$f['Field']."` like '%".$a."%' or";;
					}
					if($where!=''){$count=syDB('molds')->findSql('SELECT COUNT(*) FROM `'.$v.'` where '.rtrim($where,'or'));$count=$count[0]['COUNT(*)'];}else{$count=0;}
					$counts=$counts+$count;
				}
			}
			if($counts==0){@unlink($a);$delnum++;}
			$isnum++;
		}
		echo '<script type="text/javascript">goclear("");</script>';
		set_time_limit(30);
		message_a('清理完成，共清理多余附件'.$delnum.'个');
	}
	private function fieldtype($field) {
		if(strpos($field,'char')!== false||strpos($field,'text')!== false||strpos($field,'varchar')!== false||strpos($field,'mediumtext')!== false)
		{return true;}else{return false;}
	}
	private function clear_file($dir) { 
		$dirs=@opendir($dir);
		while(($file = @readdir($dirs)) !== false){
			if($file!='.' && $file!='..'){
				if(is_file($dir.'/'.$file)){
					$this->allfiles=array_merge($this->allfiles,array($dir.'/'.$file));
					$this->filesnum=$this->filesnum+1;
				}else{
					$this->clear_file($dir.'/'.$file);
				}
			}
		}
		closedir($dirs);
	}

}	