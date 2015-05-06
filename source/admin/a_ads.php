<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_ads extends syController
{
	function __construct(){
		parent::__construct();
		$this->Gets=$this->syArgs('a',1);
		$this->Class=syClass('c_ads');
		$this->opers='<a href="?c=a_ads">广告位管理</a><a href="?c=a_ads&a=tadd">添加广告位</a><a href="?c=a_ads&a=adlist">广告管理</a><a href="?c=a_ads&a=add">添加广告</a>';
		
		if($this->Gets=='add' || $this->Gets=='edit'){
			$this->newrow = array(
				'taid' => $this->syArgs('taid'),
				'name' => $this->syArgs('name',1),
				'adsw' => $this->syArgs('adsw'),
				'adsh' => $this->syArgs('adsh'),
				'type' => $this->syArgs('type'),
				'adfile' => $this->syArgs('adfile',1),
				'gourl' => $this->syArgs('gourl',1),
				'target' => $this->syArgs('target',1),
				'body' => $this->syArgs('body',4),
				'isshow' => $this->syArgs('isshow'),
				'orders' => $this->syArgs('orders'),
			);
		}
		if($this->Gets=='tadd' || $this->Gets=='tedit'){
			$this->newrow = array(
				'name' => $this->syArgs('name',1),
				'adsw' => $this->syArgs('adsw'),
				'adsh' => $this->syArgs('adsh'),
			);
		}
	}
	function index(){
		$this->toptxt='广告位管理';
		$this->lists = syDB('adstype')->findAll();
		$this->display("ads.html");
	}
	function adlist(){
		$this->toptxt='广告管理';
		if($this->syArgs('taid')){$c=array('taid'=>$this->syArgs('taid'));}
		$this->lists = $this->Class->findAll($c,' orders desc,id desc ');
		$this->display("ads.html");
	}
	function tadd(){
		if ($this->syArgs('run')==1){
			if($this->newrow['name']==''){message_a("广告位名称不能为空");}
			if($this->newrow['adsw']<=0 || $this->newrow['adsh']<=0){
				$this->newrow=array_merge($this->newrow,array('adsw' => 100,'adsh' => 100,));
			}
			if(syDB('adstype')->create($this->newrow)){
				message_a("广告位创建成功","?c=a_ads");
			}else{message_a("广告位创建失败，请重新提交");}
		}
		$this->toptxt='添加广告位';
		$this->postgo='tadd';
		$this->display("ads.html");
	}
	function tedit(){
		$this->d=syDB('adstype')->find(array('taid'=>$this->syArgs('taid')));
		if ($this->syArgs('run')==1){
			if($this->newrow['name']==''){message_a("广告位名称不能为空");}
			if(syDB('adstype')->update(array('taid'=>$this->d['taid']),$this->newrow)){
				message_a("广告位修改成功","?c=a_ads");
			}else{message_a("广告位修改失败,请重新提交");}
		}
		$this->toptxt='修改广告位';
		$this->postgo='tedit';
		$this->display("ads.html");
	}
	function tdel(){
		$this->toptxt='删除广告位';
		$this->d=syDB('adstype')->find(array('taid'=>$this->syArgs('taid')));
		if ($this->syArgs('run')==1){
			syDB('ads')->delete(array('taid'=>$this->syArgs('taid')));
			if(syDB('adstype')->delete(array('taid'=>$this->syArgs('taid'))))
			{message_a("广告位删除成功","?c=a_ads");}else{message_a("广告位删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除广告位 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='删除广告位，将自动删除本广告位下的所有广告，删除后不可恢复';
		$this->msggo='<a href="?c=a_ads&a=tdel&run=1&taid='.$this->d['taid'].'">确定删除</a><a href="?c=a_ads">取消操作</a>';
		$this->display("msg.html");
	}
	function add(){
		$this->adstype=syDB('adstype')->findAll();
		if($this->syArgs('taid')){$this->ctaid=$this->syArgs('taid');}
		if ($this->syArgs('run')==1){
			$newVerifier=$this->Class->syVerifier($this->newrow);
			if(false == $newVerifier){
				if($this->newrow['adsw']<=0 || $this->newrow['adsh']<=0){
					if($this->newrow['taid']){
						$t=syDB('adstype')->find(array('taid'=>$this->newrow['taid']),null,'adsw,adsh');
						$w=$t['adsw'];$h=$t['adsh'];
					}else{
						$w=100;$h=100;
					}
					$this->newrow=array_merge($this->newrow,array('adsw' => $w,'adsh' => $h,));
				}
				switch($this->newrow['type']){
					case 1:
					$body='<a href="'.$this->newrow['gourl'].'" target="_'.$this->newrow['target'].'"><img src="'.$this->newrow['adfile'].'" width="'.$this->newrow['adsw'].'" height="'.$this->newrow['adsh'].'" /></a>';
					break;
					case 2:
					$body='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="'.$this->newrow['adsw'].'" height="'.$this->newrow['adsh'].'"><param name="movie" value="'.$this->newrow['adfile'].'" /><param name="quality" value="high" /><embed src="'.$this->newrow['adfile'].'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$this->newrow['adsw'].'" height="'.$this->newrow['adsh'].'"></embed></object>';
					break;
					case 3:
					$body='<a href="'.$this->newrow['gourl'].'" target="_'.$this->newrow['target'].'">'.$this->newrow['name'].'</a>';
					break;
					case 4:
					$body='<embed height="'.$this->newrow['adsh'].'" type="application/x-mplayer2" width="'.$this->newrow['adsw'].'" src="'.$this->newrow['adfile'].'" autostart="false" enablecontextmenu="false" classid="clsid:6bf52a52-394a-11d3-b153-00c04f79faa6" />';
					break;
					case 5:
					$body=$this->newrow['body'];
					break;
				}
				$this->newrow=array_merge($this->newrow,array('body' => $body));
				if(syDB('ads')->create($this->newrow)){
					deleteDir($GLOBALS['G_DY']['sp_cache']);
					message_a("广告创建成功","?c=a_ads&a=adlist");
				}else{message_a("广告创建失败，请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='添加广告';
		$this->postgo='add';
		$this->display("ads.html");
	}
	function edit(){
		$this->adstype=syDB('adstype')->findAll();
		$this->d=syDB('ads')->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			$newVerifier=$this->Class->syVerifier($this->newrow);
				if(false == $newVerifier){
				switch($this->newrow['type']){
					case 1:
					$body='<a href="'.$this->newrow['gourl'].'" target="_'.$this->newrow['target'].'"><img src="'.$this->newrow['adfile'].'" width="'.$this->newrow['adsw'].'" height="'.$this->newrow['adsh'].'" /></a>';
					break;
					case 2:
					$body='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="'.$this->newrow['adsw'].'" height="'.$this->newrow['adsh'].'"><param name="movie" value="'.$this->newrow['adfile'].'" /><param name="quality" value="high" /><embed src="'.$this->newrow['adfile'].'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$this->newrow['adsw'].'" height="'.$this->newrow['adsh'].'"></embed></object>';
					break;
					case 3:
					$body='<a href="'.$this->newrow['gourl'].'" target="_'.$this->newrow['target'].'">'.$this->newrow['name'].'</a>';
					break;
					case 4:
					$body='<embed height="'.$this->newrow['adsh'].'" type="application/x-mplayer2" width="'.$this->newrow['adsw'].'" src="'.$this->newrow['adfile'].'" autostart="false" enablecontextmenu="false" classid="clsid:6bf52a52-394a-11d3-b153-00c04f79faa6" />';
					break;
					case 5:
					$body=$this->newrow['body'];
					break;
				}
				$this->newrow=array_merge($this->newrow,array('body' => $body,));
				if($this->newrow['adsw']<=0 || $this->newrow['adsh']<=0){
					$t=syDB('adstype')->find(array('taid'=>$this->newrow['taid']),null,'adsw,adsh');
					if($this->newrow['adsw']<=0){$w=$t['adsw'];}else{$w=$this->newrow['adsw'];}
					if($this->newrow['adsh']<=0){$h=$t['adsh'];}else{$h=$this->newrow['adsh'];}
					$this->newrow=array_merge($this->newrow,array('adsw' => $w,'adsh' => $h,));
				}
				if(syDB('ads')->update(array('id'=>$this->d['id']),$this->newrow)){
					deleteDir($GLOBALS['G_DY']['sp_cache']);
					message_a("广告修改成功","?c=a_ads&a=adlist");
				}else{message_a("广告修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->toptxt='修改广告';
		$this->postgo='edit';
		$this->display("ads.html");
	}
	function del(){
		$this->toptxt=$this->moldname.'删除广告';
		$this->d=syDB('ads')->find(array('id'=>$this->syArgs('id')));
		if ($this->syArgs('run')==1){
			if(syDB('ads')->delete(array('id'=>$this->syArgs('id')))){
				deleteDir($GLOBALS['G_DY']['sp_cache']);
				message_a("广告删除成功","?c=a_ads&a=adlist");
			}else{message_a("广告删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删广告 <strong>['.$this->d['name'].']</strong> 吗？';
		$this->msg='';
		$this->msggo='<a href="?c=a_ads&a=del&run=1&id='.$this->d['id'].'">确定删除</a><a href="?c=a_ads&a=adlist">取消操作</a>';
		$this->display("msg.html");
	}

}	