<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DOYO通用建站程序安装</title>
<link href="install.css" rel="stylesheet" type="text/css" />
<script src="../include/js/jsmain.js" type="text/javascript"></script>
<script language="JavaScript">
function formChk(){
	if(document.f.host.value==""){alert("请填写Mysql地址");return false;}
	if(document.f.port.value==""){alert("请填写Mysql端口");return false;}
	if(document.f.database.value==""){alert("请填写数据库名");return false;}
	if(document.f.login.value==""){alert("请填写数据库帐号");return false;}
	if(document.f.prefix.value==""){alert("请填写表前缀");return false;}
	if(document.f.auser.value==""){alert("请填写管理员帐号");return false;}
	if(document.f.apass.value==""){alert("请填写管理员密码");return false;}
	if(/^[A-Za-z0-9_]+$/.test(document.f.database.value)==0){alert("数据库名只能为英文、数字、下划线");return false;}
	$("#install_go").html('<strong style="color:#F00; font-size:14px;">正在执行安装，请稍后，安装完成前请勿关闭本页面...</strong>');
}
</script>
</head>
<body>
<div class="top"><span>系统安装</span><img src="logo.gif" /></div>
<div class="wp">
<div class="main">
<?php
error_reporting(0);
if(is_file("install.txt")){echo '系统已经安装，如需要重新安装请删除install目录下的install.txt文件';exit;}
if((int)$_GET['backup']!=1){
	$backup_db=array();
	$fdir='../include/backup/';
	if($dp=@opendir($fdir)){
		while(false!==($file = readdir($dp))) {
			if($file!='.' && $file!='..' && is_file($fdir.$file)) {
				$backup_db=array_merge($backup_db,array($fdir.$file));
			}
		}
		@closedir($dp);
	}
}
$go=(int)$_GET['go'];
if(!$go){
function file_info($file){
	if (DIRECTORY_SEPARATOR == '/' and @ini_get("safe_mode") == FALSE){
		return is_writable($file);
	}
	if (is_dir($file)){
		$file = rtrim($file, '/').'/is_writable.html';
		if (($fp = @fopen($file,'w+')) === FALSE){
			return FALSE;
		}
  		fclose($fp);
  		@chmod($file,0755);
  		@unlink($file);
		return TRUE;
	}else if ( ! is_file($file) or ($fp = @fopen($file, 'r+')) === FALSE){
		return FALSE;
	}
	fclose($fp);
	return TRUE;
}
if (substr(PHP_VERSION, 0, 1) != '5'){
	$php="<em>检测未通过</em>运行环境要求PHP版本5！";
}
if (!extension_loaded('gd')){
	$gd="<em>检测未通过</em>运行环境要求安装GD库！";
}else{
	$gd_info=gd_info();
	$gd_info=substr($gd_info['GD Version'], 9, 1);
	if ((int)$gd_info<'2'){
		$gd="<em>提示</em>GD库版本要求2以上，如您确认已安装GD2以上版本，可忽略本提示。";
	}
}
if (!file_info('../uploads')){
	$uploads="<em>检测未通过</em>此目录要求可读写";
}
if (!file_info('../include/backup')){
	$backup="<em>检测未通过</em>此目录要求可读写";
}
if (!file_info('../include/cache')){
	$cache="<em>检测未通过</em>此目录要求可读写";
}
if (!file_info('../config.php')){
	$config="<em>检测未通过</em>此文件要求可读写";
}
if (!file_info('../skin')){
	$skin="<em>检测未通过</em>此目录要求可读写";
}
if (!file_info('../template')){
	$template="<em>检测未通过</em>此目录要求可读写";
}
if (!file_info('../include/inc.php')){
	$inc="<em>检测未通过</em>此文件要求可读写";
}
?>
<form action="index.php?go=2" method="post" name="f" onsubmit="return formChk()">
	<div class="switchs">&nbsp;请填写数据库信息</div>
    <div class="info">
    	<dl><dt>Mysql地址：</dt><dd><input name="host" type="text" class="int" value="127.0.0.1" /></dd><dd class="t">一般无需修改</dd></dl>
    	<dl><dt>Mysql端口：</dt><dd><input name="port" type="text" class="int" value="3306" /></dd><dd class="t">一般无需修改</dd></dl>
        <dl><dt>数据库名：</dt><dd><input name="database" type="text" class="int" /></dd><dd class="t">请输入</dd></dl>
        <dl><dt>数据库帐号：</dt><dd><input name="login" type="text" class="int" /></dd><dd class="t">请输入</dd></dl>
        <dl><dt>数据库密码：</dt><dd><input name="password" type="text" class="int" /></dd><dd class="t">请输入</dd></dl>
<?php if(empty($backup_db)){?>
        <dl><dt>表前缀：</dt><dd><input name="prefix" type="text" class="int" value="dy_" /></dd><dd class="t">一般无需修改，同数据库安装多个请务必修改</dd></dl>
        <dl><dt>后台管理员帐号：</dt><dd><input name="auser" type="text" class="int" /></dd><dd class="t">请输入</dd></dl>
        <dl><dt>后台管理员密码：</dt><dd><input name="apass" type="text" class="int" /></dd><dd class="t">请输入</dd></dl>
        <dl><dt>验证码：</dt><dd><input type="radio" name="vercode" value="1" checked="checked" />开启</label>&nbsp;<label><input type="radio" name="vercode" value="0" />关闭</label></dd><dd><img src="../include/vercode.php" width="58" height="25"/></dd><dd class="t">如无法看到左图验证码，请关闭验证码功能</dd></dl>
<?php }?>
    </div>
<?php if(count($backup_db)>0){?>
	<input name="go_backup" type="hidden" value="1" />
    <div class="switchs">&nbsp;检测到您的系统存在数据库备份</div>
    <div class="info">
    	<dl><dt>选择数据备份：</dt><dd><select name="backup_db">
        <?php foreach($backup_db as $v){
			$v=str_replace('../include/backup/','',$v);
			if(false===strpos($v,'_v')){echo "<option value='".$v."'>".$v."</option>";}
		}?>
    	</select></dd></dl>
        <dl><dt>&nbsp;</dt><dd><a href="?backup=1">不恢复数据备份，执行全新安装</a></dd></dl>
    </div>
<?php }?>
	<div class="switchs" style="margin-top:10px;">&nbsp;环境检测</div>
    <div class="info">
    	<dl><dt>PHP版本：</dt><dd><?php if ($php){echo $php;}else{echo '<span>检测通过</span>';}?></dd></dl>
    	<dl><dt>GD库：</dt><dd><?php if ($gd){echo $gd;}else{echo '<span>检测通过</span>';}?></dd></dl>
        <dl><dt>目录权限：</dt><dd>uploads <?php if ($uploads){echo $uploads;}else{echo ' <span>检测通过</span>';}?><br />include/backup <?php if ($backup){echo $backup;}else{echo ' <span>检测通过</span>';}?><br />include/cache <?php if ($cache){echo $cache;}else{echo ' <span>检测通过</span>';}?><br />config.php <?php if ($config){echo $config;}else{echo ' <span>检测通过</span>';}?><br />skin/ <?php if ($skin){echo $skin;}else{echo ' <span>检测通过</span>';}?><br />template/ <?php if ($template){echo $template;}else{echo ' <span>检测通过</span>';}?><br />include/inc.php <?php if ($inc){echo $inc;}else{echo ' <span>检测通过</span>';}?></dd></dl>
        <dl style="margin-bottom:20px;"><dt>&nbsp;</dt><dd id="install_go"><input type="submit" id="submit" value="开始安装" class="btnbig" /></dd></dl>
    </div>
</form>
<?php }

if($go==2){
	$con = mysql_connect($_POST['host'].':'.$_POST['port'],$_POST['login'],$_POST['password']);
	if(!$con){
		echo '<script>alert("数据库连接失败，请检查数据库帐号输入是否正确：'.mysql_error().'");javascript:history.go(-1);</script>';exit;
	}
	mysql_query('CREATE DATABASE IF NOT EXISTS '.$_POST['database'].' default charset utf8',$con);
	if(!mysql_select_db($_POST['database'], $con)){
		echo '<script>alert("数据库连接失败，请检查数据库信息输入是否正确：'.mysql_error().'");javascript:history.go(-1);</script>';exit;
	}
	$mysqlv=mysql_get_server_info();
	if(substr($mysqlv,0,1)<5){
		echo '<script>alert("您的数据库版本过低，Mysql版本要求大于等于5");javascript:history.go(-1);</script>';exit;
	};
	mysql_query('SET NAMES UTF8',$con);
	mysql_query('set sql_mode=""',$con);

	$configfile='../config.php';
	$fp_tp=@fopen($configfile,"r");
	$fp_txt=@fread($fp_tp,filesize($configfile));
	@fclose($fp_tp);
	if((int)$_POST['go_backup']==1){
		$db=array('db','host','port','database','login','password');
	}else{
		$db=array('db','host','port','database','login','password','prefix','secret_key');
	}
	foreach($db as $v){
		if ($v=='port'){
			$fp_txt=preg_replace("/'port' => .*?,/","'port' => ".$_POST[$v].",",$fp_txt);
		}else if($v=='secret_key'){
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$key = '';
			for($i=0;$i<12;$i++){
				$key .= $chars[ mt_rand(0, strlen($chars) - 1) ];
			}
			$secret_key=md5(md5(time()).md5($key));
			$fp_txt=preg_replace("/'secret_key' => .*?,/","'secret_key' => '".$secret_key."',",$fp_txt);
		}else{
			$fp_txt=preg_replace("/'".$v."' => '.*?',/","'".$v."' => '".$_POST[$v]."',",$fp_txt);
		}
	}
	$fpt_tpl=@fopen($configfile,"w");
	@fwrite($fpt_tpl,$fp_txt);
	@fclose($fpt_tpl);
	if((int)$_POST['vercode']==0){
		$incfile='../include/inc.php';
		$inc_tp=@fopen($incfile,"r");
		$inc_txt=@fread($inc_tp,filesize($incfile));
		@fclose($inc_tp);
		$inc_txt=preg_replace("/'vercode' => .*?,/","'vercode' => 0,",$inc_txt);
		$inct_tpl=@fopen($incfile,"w");
		@fwrite($inct_tpl,$inc_txt);
		@fclose($inct_tpl);
	}
	
	ob_implicit_flush(1);
	ob_end_flush();
?>
<div class="switchs">&nbsp;执行安装</div>
<div class="info">
<dl><dt>建立数据库：</dt><dd>
<?php 
echo '安装数据库...';

function dbbak_file($fname,$p,$rf){
	$filed=$fname.$p.'.php';
	if(file_exists($filed)){
		$rft=array_merge($rf,array($filed));
		$p=$p+1;dbbak_file($fname,$p,$rft);
	}else{
		$GLOBALS["rfiles"]=$rf;
	}
}

$i=0;
if((int)$_POST['go_backup']==1){
	set_time_limit(99999999);
	$volnum=explode(".ph",$_POST['backup_db']);
	$backups=array('../include/backup/'.$_POST['backup_db']);
	dbbak_file('../include/backup/'.$volnum[0].'_v',2,$backups);
	foreach($GLOBALS["rfiles"] as $v){
		foreach(file($v) as $rsql){
			$sql=str_replace('<?php die();?>','',$rsql);
			$rgo=mysql_query($sql,$con);
			if(!$rgo){$i++;}
		}
	}
	set_time_limit(30);
}else{
	$db = file('doyo.sql');
	$db[6]=str_ireplace('|-auser-|',$_POST['auser'],$db[6]);
	$db[6]=str_ireplace('|-apass-|',md5(md5($_POST['apass']).$_POST['auser']),$db[6]);
	foreach ($db as  $num =>$v) {
		$v=trim($v);
		if((int)$_POST['go_backup']!=1){$v=str_ireplace('`dy_','`'.$_POST['prefix'],$v);}
		if (!mysql_query($v,$con)){$i++;}
	}
}

if($i>0){echo '<script>alert("数据库安装有'.$i.'条失败！检查数据库中是否存在同名表，或之前是否已安装过本系统，请删除表或更改表前缀重新执行安装");javascript:history.go(-1);</script>';exit;}else{echo '<span>数据库安装成功</span>';}
?>
</dd></dl>
<?php 
$filename="install.txt";
$fp=@fopen("$filename", "w");
@fclose($fp);
echo '<dl><dt></dt><dd class="oka">安装全部完成！[请删除install文件夹] <a href="../"><span>[浏览网站]</span></a><a href="../admin.php"><span>[进入后台]</span></a></dd></dl>'; ?>
</div>
<?php }?>
<div style="clear: both"></div>
</div>
</div>
<div class="bottom"><a href="http://wdoyo.com" target="_blank">DoYo通用建站程序</a> Powered by DoYo! 2.3 © 2010-2012 <a href="http://wdoyo.com" target="_blank">wDoYo.com</a></div>
</body>
</html>