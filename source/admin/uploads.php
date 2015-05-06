<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class uploads extends syController
{
	function __construct(){
		parent::__construct();
	}
	function index(){
		$allow = $this->syArgs('allow',1)!='' ? $this->syArgs('allow',1) : syExt('filetype');
		$size = $this->syArgs('size',1)!='' ? $this->syArgs('size',1) : syExt('filesize');
		$water = $this->syArgs('water',1)!='' ? $this->syArgs('water',1) : syExt('imgwater');
		$caling = $this->syArgs('caling',1)!='' ? $this->syArgs('caling',1) : syExt('imgcaling');
		$w = $this->syArgs('w',1)!='' ? $this->syArgs('w',1) : syExt('img_w');
		$h = $this->syArgs('h',1)!='' ? $this->syArgs('h',1) : syExt('img_h');
		$fileClass=syClass('syupload',array($allow,$size,$water,$caling,$w,$h));
		if (!empty($_FILES)){
			if($this->syArgs('isfiles',1)=='editor_KindEditor'){
				header('Content-type: text/html; charset=UTF-8');
				$fileinfos = $fileClass->upload_file($_FILES[$this->syArgs('isfiles',1)]);
				if (is_array($fileinfos)){
					echo '{"error" : 0,"url" : "'.$fileinfos['fn'].'"}';
				}else{
					echo '{"error" : 1,"message" : "'.$fileClass->errmsg.'"}';
				}
			}else{
				$fileinfos = $fileClass->upload_file($_FILES[$this->syArgs('isfiles',1)]);
				if (is_array($fileinfos)){
					echo '0';
						$f=explode('.',$fileinfos['fn']);
						echo ','.$fileinfos['fn'];
						echo ','.preg_replace('/.*\/.*\//si','',$f[0]);
						if(stripos($fileinfos['fn'],'jpg') || stripos($fileinfos['fn'],'gif') || stripos($fileinfos['fn'],'png') || stripos($fileinfos['fn'],'jpeg')){
							echo ',1';
						}else{
							echo ','.$f[1];
						}
				}else{
					echo $fileClass->errmsg;
				}
			}
		}else{echo '未上传任何文件';}
	}
	function loadup(){
		$this->inputid=$this->syArgs('inputid',1);
		$this->multi=$this->syArgs('multi') ? 'true':'false';
		if($this->syArgs('tid')){
			$t=syDB('classtype')->find(array('tid'=>$this->syArgs('tid')),null.'imgw,imgh');
			if($t['imgw']&&$t['imgh']){
				$this->w = $t['imgw'];
				$this->h = $t['imgh'];
			}
		}
		if($this->syArgs('imgw')&&$this->syArgs('imgh')){
			$this->w = $this->syArgs('imgw');
			$this->h = $this->syArgs('imgh');
		}
		if($this->syArgs('fileExt',1)){$this->fileExt=$this->syArgs('fileExt',1);}else{
			foreach(explode(',',syExt('filetype')) as $v){
				$fileExt.=';*.'.$v;
			}$this->fileExt=substr($fileExt,1);
		}
		$this->sizeLimit=$this->syArgs('sizeLimit') ? $this->syArgs('sizeLimit'):syExt('filesize');
		$this->fileover=$this->syArgs('fileover') ? $this->syArgs('fileover'):1;
		$this->display("uploads.php");
	}
	function filemanager(){
		$root_path = APP_PATH . '/uploads/';
		$root_url = $GLOBALS["WWW"] . 'uploads/';
		$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'swf', 'flv', 'wmv', 'mp3', 'mp4', '3gp', 'wma', 'mpeg', 'rm', 'avi');
		$dir_name = '';
		$g_path=$this->syArgs('path',1);
		if ($dir_name !== '') {
			$root_path .= $dir_name . "/";
			$root_url .= $dir_name . "/";
		}
		if (empty($g_path)) {
			$current_path = realpath($root_path) . '/';
			$current_url = $root_url;
			$current_dir_path = '';
			$moveup_dir_path = '';
		} else {
			$current_path = realpath($root_path) . '/' . $g_path;
			$current_url = $root_url . $g_path;
			$current_dir_path = $g_path;
			$moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
		}
		echo realpath($root_path);
		if (preg_match('/\.\./', $current_path)) {
			echo 'Access is not allowed.';
			exit;
		}
		if (!preg_match('/\/$/', $current_path)) {
			echo 'Parameter is not valid.';
			exit;
		}
		if (!file_exists($current_path) || !is_dir($current_path)) {
			echo 'Directory does not exist.';
			exit;
		}
		$file_list = array();
		if ($handle = opendir($current_path)) {
			$i = 0;
			while (false !== ($filename = readdir($handle))) {
				if ($filename{0} == '.') continue;
				$file = $current_path . $filename;
				if (is_dir($file)) {
					$file_list[$i]['is_dir'] = true;
					$file_list[$i]['has_file'] = (count(scandir($file)) > 2);
					$file_list[$i]['filesize'] = 0;
					$file_list[$i]['is_photo'] = false;
					$file_list[$i]['filetype'] = '';
				} else {
					$file_list[$i]['is_dir'] = false;
					$file_list[$i]['has_file'] = false;
					$file_list[$i]['filesize'] = filesize($file);
					$file_list[$i]['dir_path'] = '';
					$file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					$file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
					$file_list[$i]['filetype'] = $file_ext;
				}
				$file_list[$i]['filename'] = $filename;
				$file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file));
				$i++;
			}
			closedir($handle);
		}
		usort($file_list, 'filemanager_list');
		
		$result = array();
		$result['moveup_dir_path'] = $moveup_dir_path;
		$result['current_dir_path'] = $current_dir_path;
		$result['current_url'] = $current_url;
		$result['total_count'] = count($file_list);
		$result['file_list'] = $file_list;
		header('Content-type: application/json; charset=UTF-8');
		echo syClass('syjson')->encode($result);
	}
	
}