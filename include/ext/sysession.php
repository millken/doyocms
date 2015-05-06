<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class sysession{
	private $session_Overdue;
	private $session_dir;
	public function __construct(){
		$this->session_Overdue=3600;
		$this->session_dir=$GLOBALS['G_DY']['sp_session'].'/';
		ini_set('session.save_handler','user');
		session_set_save_handler ( 
			array ($this, 'open' ), 
			array ($this, 'close' ), 
			array ($this, 'read' ), 
			array ($this, 'write' ), 
			array ($this, 'destroy' ), 
			array ($this, 'gc' )
		);
		if($_REQUEST['session_id']){if(preg_match("/^[a-zA-Z0-9]*$/", $_REQUEST['session_id']) != 0){session_id($_REQUEST['session_id']);}}
		session_start();
    }
	public function open(){
		return true;
	}
	public function close(){
		return true;
	}
	public function read($id){
		$value = $this->sessdb('r',$id);
		if($value){
			return $value['datas'];
		}else{
			return '';
		}
	}
	public function write($id,$datas){
		$ses=array('datas'=>$datas);
		$this->sessdb('w',$id,$ses,$this->session_Overdue);
		return true;
	}
	public function destroy($id){
		$this->sessdel();
		return $this->sessdb('c',$id);
	}
	public function gc($max){
		$this->sessdel();
		return true;
	}
    public function __destruct(){  
		$this->sessdel(); 
	} 

	private function sessdb($method, $name, $value = NULL, $life_time = -1){
		$filedir=$this->session_dir;
		if(!is_dir($filedir))__mkdirs($filedir);
		$sfile = $filedir.'/'.$name.".php";
		if('w' == $method){ 
			$life_time = ( -1 == $life_time ) ? '300000000' : $life_time;
			$value = '<?php die();?>'.( time() + $life_time ).serialize($value);
			return file_put_contents($sfile, $value);
		}elseif('c' == $method){
			return @unlink($sfile);
		}else{
			return $this->sesstime($sfile);
		}
	}
	private function sessdel() { 
		$dirName=@opendir($this->session_dir);
		while(($file = @readdir($dirName)) !== false){
			if($file!='.' && $file!='..'){
				$this->sesstime($this->session_dir.'/'.$file);
			}
		}
		closedir($dirName);
	}
	private function sesstime($sfile){
		if( !is_readable($sfile) )return false;
		$arg_data = file_get_contents($sfile);
		if( substr($arg_data, 14, 10) < time() ){
			@unlink($sfile); 
			return false;
		}
		return unserialize(substr($arg_data, 24));
	}
}