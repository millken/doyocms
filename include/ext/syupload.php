<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class syupload {
 	public $max_size = '';
 	public $file_name = '';
 	public $allow_types;
 	public $errmsg = '';
 	public $uploaded = '';
 	public $save_path;
	public $img_water = '';
	public $img_water_t = '';
	public $img_w = '';
	public $img_h = '';
	public $imagesizes = '';
	public $img_caling = '';
 	private $files;
 	private $file_type = array();
 	private $ext = '';

 	public function __construct($allow_types,$max_size,$img_water,$img_caling,$img_w,$img_h) {
 		$this->file_name   = 'date';
		$this->save_path   = 'uploads/'.date('Y').'/'.date('m').'/';
		$this->allow_types = $allow_types ? $allow_types : syExt('filetype');
		$this->max_size = $max_size ? $max_size : syExt('filesize');
		$this->img_water = $img_water ? $img_water : syExt('imgwater');
		$this->img_caling = $img_caling ? $img_caling : syExt('imgcaling');
		$this->img_w = $img_w ? $img_w : syExt('img_w');
		$this->img_h = $img_h ? $img_h : syExt('img_h');
		$this->img_water_t = syExt('imgwater_t');
 	}

	public function upload_file($files) {
		$name = $files['name'];
		$type = $files['type'];
		$size = $files['size'];
		$tmp_name = $files['tmp_name'];
		$error = $files['error'];
		switch ($error) {
			case 0 : $this->errmsg = '';
				break;
			case 1 : $this->errmsg = '超过了php.ini中文件大小';
				break;
			case 2 : $this->errmsg = '超过了MAX_FILE_SIZE 选项指定的文件大小';
				break;
	 	    case 3 : $this->errmsg = '文件只有部分被上传';
				break;
			case 4 : $this->errmsg = '没有文件被上传';
				break;
			case 5 : $this->errmsg = '上传文件大小为0';
				break;
		    default : $this->errmsg = '上传文件失败！';
				break;
			}
		if(!extension_loaded('gd')){
			$error=1;
			$this->errmsg = '图片上传要求空间支持GD库！';
		}
		if($error == 0 && is_uploaded_file($tmp_name)) {
			if($this->check_file_type($name,$tmp_name) == FALSE){
				return FALSE;
			}
			if($size > $this->max_size){
				$this->errmsg = '文件超过<font color=red>'.ceil($this->max_size/1024).'</font>kb';
				return FALSE;
			}
			$this->set_save_path();
			$new_name = date('dHis').mt_rand(1000,9999).'.'.$this->ext;
			$this->uploaded = $this->save_path.$new_name;
			if(move_uploaded_file($tmp_name,$this->uploaded)){
				$extaq = $this->get_file_type($this->uploaded);
				if(in_array($extaq,array('jpg', 'jpeg', 'gif', 'png', 'bmp'))){
					$imagesizes=@getimagesize($this->uploaded);
					if (!$imagesizes){@unlink($this->uploaded);$this->errmsg = '请上传真实图片';return FALSE;}
				}
				//if(extension_loaded('gd')){
					if(in_array($this->ext,array('jpg','jpeg','gif'))){
						if($this->img_caling==1&&($imagesizes[1]>$this->img_h || $imagesizes[0]>$this->img_w)){
							syClass('syimage')->img_resized($this->img_w,$this->img_h,$this->uploaded);//生成小图
						}else{
							syClass('syimage')->img_resized($imagesizes[0],$imagesizes[1],$this->uploaded);//生成安全图片
						}
						if($this->img_water==1){
							if(($imagesizes[0]>300 || $imagesizes[1]>300) && ($this->img_h>300 || $this->img_w>300)){
								syClass('syimage')->img_mark($this->uploaded,3);//添加水印
							}
						}
					}
				//}
				return array('fn'=>$this->uploaded,'ft'=>$type,'si'=>@filesize($this->uploaded));
			}else{
				$this->errmsg = '文件<font color=red>'.$this->uploaded.'</font>上传失败！';
				return FALSE;
			}

		}
	}

    public function check_file_type($filename,$tmpname){
		$ext = $this->get_file_type($filename);
		$this->ext = $ext;
  		$allow_types = explode(',',$this->allow_types);
  		if(in_array($ext,$allow_types)){
			return TRUE;
  		}else{
  			$this->errmsg = '只支持上传<font color=red>'.str_replace('|',',',$this->allow_types).'</font>等文件类型!';
  			return FALSE;
  		}
    }

    public function get_file_type($filename){
    	$info = pathinfo($filename);
    	$ext = strtolower($info['extension']);
    	return $ext;
    }

	public function set_save_path(){
		$this->save_path = (preg_match('/\/$/',$this->save_path)) ? $this->save_path : $this->save_path . '/';
		if(!is_dir($this->save_path)){
			$this->set_dir();
		}
	}

	public function set_dir($dir = null){
		if(!$dir){
			$dir = $this->save_path;
		}
		__mkdirs($dir);
		@fclose(fopen($dir.'/index.htm', 'w'));
		return true;
	}
	public function saveRemoteImg($sUrl){
		$reExt='(jpg|jpeg|gif|png)';
		if(substr($sUrl,0,10)=='data:image'){
			if(!preg_match('/^data:image\/'.$reExt.'/i',$sUrl,$sExt))return false;
			$sExt=$sExt[1];
			$imgContent=base64_decode(substr($sUrl,strpos($sUrl,'base64,')+7));
		}
		else{//url图片
			if(!preg_match('/\.'.$reExt.'$/i',$sUrl,$sExt))return false;
			$sExt=$sExt[1];
			$imgContent=$this->getUrl($sUrl);
		}
		if(strlen($imgContent)>$this->max_size)return false;
		$this->set_dir($this->save_path);
		$newFilename=date('dHis').mt_rand(1000,9999).'.'.$sExt;
		$sLocalFile = $this->save_path.$newFilename;
		file_put_contents($sLocalFile,$imgContent);
		$fileinfo= @getimagesize($sLocalFile);
		if(!$fileinfo){
			@unlink($sLocalFile);
			return false;
		}
		if($this->img_caling==1){
			if($fileinfo[1]>$this->img_h || $fileinfo[0]>$this->img_w){
				syClass('syimage')->img_resized($this->img_w,$this->img_h,$sLocalFile);
			}
		}
		if($this->img_water==1){
			if(($fileinfo[0]>300 || $fileinfo[1]>300) && ($this->img_h>300 || $this->img_w>300)){
				syClass('syimage')->img_mark($sLocalFile,3);
			}
		}
		return $sLocalFile;
	}
	private function getUrl($sUrl,$jumpNums=0){
		$arrUrl = parse_url(trim($sUrl));
		if(!$arrUrl)return false;
		$host=$arrUrl['host'];
		$port=isset($arrUrl['port'])?$arrUrl['port']:80;
		$path=$arrUrl['path'].(isset($arrUrl['query'])?"?".$arrUrl['query']:"");
		$fp = @fsockopen($host,$port,$errno, $errstr, 30);
		if(!$fp)return false;
		$output="GET $path HTTP/1.0\r\nHost: $host\r\nReferer: $sUrl\r\nConnection: close\r\n\r\n";
		stream_set_timeout($fp, 60);
		@fputs($fp,$output);
		$Content='';
		while(!feof($fp))
		{
			$buffer = fgets($fp, 4096);
			$info = stream_get_meta_data($fp);
			if($info['timed_out'])return false;
			$Content.=$buffer;
		}
		@fclose($fp);
		if(preg_match("/^HTTP\/\d.\d (301|302)/is",$Content)&&$jumpNums<5)
		{
			if(preg_match("/Location:(.*?)\r\n/is",$Content,$murl))return $this->getUrl($murl[1],$jumpNums+1);
		}
		if(!preg_match("/^HTTP\/\d.\d 200/is", $Content))return false;
		$Content=explode("\r\n\r\n",$Content,2);
		$Content=$Content[1];
		if($Content)return $Content;
		else return false;
	}

}