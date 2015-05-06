<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class syclasstype {
        private $result;
        private $tmp;
        private $arr;
		private $arrp;
		private $arrt;
		private $n;
		public function __construct($result='') {
			if($result==''){
				if(syAccess('r','classtype')==FALSE){syAccess('w','classtype',syDB('classtype')->findAll(null,null,'tid,classname,pid,molds'));}
				$this->result = syAccess('r','classtype');
			}else{$this->result=$result;}
        }
		private function find($tid){
			foreach ($this->result as $k => $v){
				if($v['pid']== $tid){
					$childs[]=$v;
				}
			}
			return $childs;  
		}
        private function type_tree($tid=0,$pid=0) {
			$childs=$this->find($tid);
			if(empty($childs)){
				return null;
			}
			foreach ($childs as $k => $v){
				$this->n= null;
				$this->n=count($this->navi($childs[$k]['tid'], $pid));
				$childs[$k]['n']=$this->n;
				$rescurTree=$this->type_tree($v['tid'],$pid);
				if( null !=   $rescurTree){ 
				$childs[$k]['child']=$rescurTree;
				}
			}
            $this->tmp = $childs;
			return $this->tmp;
        }

        private function recur_n($arr, $tid, $pid) {
			foreach ($arr as $v) {
				if ($v['tid'] == $tid) {
					$this->arr[] = $v;
					if ($v['pid'] != $pid) $this->recur_n($arr, $v['pid'], $pid);
				}
			}
        }
        private function recur_p($arr) {
			foreach ($arr as $v) {
				$this->arrp = $this->arrp.','.$v['tid'];
				if ($v['child']) $this->recur_p($v['child']);
			}
        }
		private function type_txt_for($arr) {
			foreach ($arr as $v){
				$txt=array('tid'=>$v['tid'],'name'=>$v['classname'],'n'=>$v['n']-1,'molds'=>$v['molds']);
				$this->arrt[]=$txt;
				if(is_array($v['child'])){
					$this->type_txt_for($v['child']);
				}
			}
		}
        public function navi($tid, $pid=0) {
			$this->arr = null;
			$this->recur_n($this->result, $tid, $pid);
			return array_reverse($this->arr);
        }
        public function leafid($tid=0) {
			$this->arrp = $tid;
			$this->recur_p($this->type_tree($tid,$tid));
			return rtrim($this->arrp,",");
        }
		public function type_txt($tid=0) {
			$this->arrt = null;
			$this->type_txt_for($this->type_tree($tid,$tid));
			return $this->arrt;
		}
}
