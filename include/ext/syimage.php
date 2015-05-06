<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class syimage extends syupload {

	public $mark_logo = 'include/water.png';
	public $resize_h;
	public $resize_w;
	public $source_img;
	public $dst_path = '';
	public function img_resized($w,$h,$source_img = NULL){
		$source_img = $source_img == NULL ? $this->uploaded : $source_img;
		if(!is_file($source_img)) {
			$this->errmsg = '文件'.$source_img.'不存在';
			return FALSE;
		}
		$this->source_img = $source_img;
		$img_info = getimagesize($source_img);
			if($img_info[1]>$img_info[0]){
				$w=round(($img_info[0]/$img_info[1])*$h);
			}else{
				$h=round(($img_info[1]/$img_info[0])*$w);
			}
			$source = $this->img_create($source_img);
			if(function_exists("imagecopyresampled")){
				$thumb = imagecreatetruecolor($w,$h);
				imagecopyresampled($thumb,$source,0,0,0,0,$w,$h,$img_info[0],$img_info[1]);
			}else{
				$newim = imagecreate($w,$h);
				imagecopyresized($thumb,$source,0,0,0,0,$w,$h,$img_info[0],$img_info[1]);
			}
			
			$dst_path = $this->dst_path == '' ? $this->save_path : $this->dst_path;
			$dst_path = (preg_match('/\/$/',$dst_path)) ? $dst_path : $dst_path . '/';
			$dst_name = $source_img;
			$this->img_output($thumb,$dst_name);
			imagedestroy($source);
			imagedestroy($thumb);
	}

	public function img_mark($source_img = NULL,$mark_type = 3) {
		$source_img = $source_img == NULL ? $this->uploaded : $source_img;
		if(!is_file($source_img)) {
			$this->errmsg = '文件'.$source_img.'不存在';
			return FALSE;
		}
		$this->source_img = $source_img;
		$img_info = getimagesize($source_img);
		$source = $this->img_create($source_img);
		$mark_xy = $this->get_mark_xy(syExt('imgwater_t'));
		$mark_color = imagecolorallocate($source,$this->str_r,$this->str_g,$this->str_b);

			if(is_file($this->mark_logo)){
				$logo_info = getimagesize($this->mark_logo);
			}else{
				$this->errmsg = '打水印文件'.$this->mark_logo.'不存在';
				return FALSE;
			}
			if($logo_info[0]>$img_info[0] || $logo_info[1]>$img_info[1]) {
				return FALSE;
			}

			$logo = $this->img_create($this->mark_logo);
			imagecopy ( $source, $logo, $mark_xy[4], $mark_xy[5], 0, 0, $logo_info[0], $logo_info[1]);
			$this->img_output($source,$source_img);

		imagedestroy($source);
	}

	private function get_mark_xy($mark_postion){
		$img_info = getimagesize($this->source_img);
		if(is_file($this->mark_logo)){
			$logo_info = getimagesize($this->mark_logo);
		}
		switch($mark_postion){
			case 1: //位置左下角
			$mark_xy[4] = 5;
			$mark_xy[5] = $img_info[1]-$logo_info[1]-5;
			break;
			case 2: //位置右下角
			$mark_xy[4] = $img_info[0]-$logo_info[0]-5;
			$mark_xy[5] = $img_info[1]-$logo_info[1]-5;
			break;
			case 3: //位置左上角
			$mark_xy[4] = 5;
			$mark_xy[5] = 5;
			break;
			case 4: //位置右上角
			$mark_xy[4] = $img_info[0]-$logo_info[0]-5;
			$mark_xy[5] = 5;
			break;
			default : //默认为右下角
			$mark_xy[4] = $img_info[0]-$logo_info[0]-5;
			$mark_xy[5] = $img_info[1]-$logo_info[1]-5;
			break;
		}
		return $mark_xy;
	}

	private function img_create($source_img) {
		$info = getimagesize($source_img);
		switch ($info[2]){
            case 1:
            if(!function_exists('imagecreatefromgif')){
            	$source = @imagecreatefromjpeg($source_img);
            }else{
            	$source = @imagecreatefromgif($source_img);
            }
            break;
            case 2:
            $source = @imagecreatefromjpeg($source_img);
            break;
            case 3:
            $source = @imagecreatefrompng($source_img);
            break;
            case 6:
            $source = @imagecreatefromwbmp($source_img);
            break;
            default:
			$source = FALSE;
			break;
        }
		return $source;
	}

	 private function set_newname($sourse_img) {
		$info = pathinfo($sourse_img);
		$new_name = $this->resize_w.'_'.$this->resize_h.'_'.$info['basename'];
		if($this->dst_path == ''){
			$dst_name = str_replace($info['basename'],$new_name,$sourse_img);
		}else{
			$dst_name = $this->dst_path.$new_name;
		}
		return $dst_name;
	 }

	 public function img_output($im,$dst_name) {
		$info = getimagesize($this->source_img);
		switch ($info[2]){
				case 1:
				if(!function_exists('imagegif')){
					imagejpeg($im,$dst_name,95);
				}else{
					imagegif($im, $dst_name,95);
				}
				break;
				case 2:
				imagejpeg($im,$dst_name,95);
				break;
				case 3:
				imagepng($im,$dst_name,95);
				break;
				case 6:
				imagewbmp($im,$dst_name,95);
				break;
			}
	 }
}