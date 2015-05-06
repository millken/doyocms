<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class message extends syController
{	
	function __construct(){
		parent::__construct();
		if(moldsinfo('message','isshow')!=1)message("留言功能已关闭");
		$this->Class=syClass('c_message');
		$this->dbl=$GLOBALS['G_DY']['db']['prefix'];
		$this->db=$this->dbl.'message';
		$this->sy_class_type=syClass('syclasstype');
		$this->member=syClass('symember');
		$this->my=$this->member->islogin(0);
		$this->uploadfile=$this->syArgs('c',1);
	}
	function type(){
		if($this->syArgs('file',1)!=''){
			$this->type=syDB('classtype')->find(' htmlfile="'.$this->syArgs('file',1).'" or tid='.$this->syArgs('file').' ');
			$tid = $this->type['tid'];
		}else{
			$tid = $this->syArgs('tid');
			$this->type=syDB('classtype')->find(" molds='message' and tid=".$tid." ");
		}
		if(!$this->type){message("指定栏目不存在");}

		if($this->type['mrank']>0){
			$this->member->p_v($this->type['mrank']);
		}
		$this->faid=$this->syArgs('faid');
		$this->fmolds=$this->syArgs('fmolds',1);
		$this->fields=fields_info($tid,'message');
		
		$t=$this->type['t_index'];
		$w.=" where isshow=1 ";
		$w.="and tid=$tid ";
		$order=' order by orders desc,addtime desc,id desc';
		$this->fieldinfo=syDB('fields')->findAll(" molds='message' and types like '%|".$tid."|%' and lists=1 ");
		if($this->fieldinfo){
			foreach($this->fieldinfo as $v){$fields.=','.$v['fields'];}
			$sql='select id,tid,title,addtime,orders,isshow,user,body,retime,reply'.$fields.' from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid)'.$w.$order;
		}else{
			$sql='select id,tid,title,addtime,orders,isshow,user,body,retime,reply from '.$this->db.$w.$order;
		}
		$total_page=total_page($this->db.$w);
		$this->lists = $this->Class->syPager($this->syArgs('page',0,1),$this->type['listnum'],$total_page)->findSql($sql);
		$pages=$this->Class->syPager()->getPager();
		$this->pages=html_url('classtype',$this->type,$pages,$this->syArgs('page',0,1));

		$this->positions='<a href="'.$GLOBALS["WWW"].'">首页</a>';
		foreach($this->sy_class_type->navi($this->syArgs('tid')) as $v){
			$this->positions.='  &gt;  '.$v['classname'];
		}
		$this->display('message/'.$t);
	}
	function add(){
		if($GLOBALS['G_DY']['vercode']==1){
		if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
		}
		if(!$this->syArgs('tid'))message("请选择栏目");
		$tid=$this->syArgs('tid');
		$this->type=syDB('classtype')->find(array('tid'=>$tid),null,'molds,classname,msubmit');
		if($this->type['msubmit']!=1){
			$this->member->p_r($this->type['msubmit']);
		}
		$isshow = ($this->my['group']['audit']==1) ? 1 : 0;
		$user = ($this->my['id']!=0) ? $this->my['user'] : '游客';
		$fmolds = ($this->syArgs('fmolds',1)!='') ? $this->syArgs('fmolds',1) : '';
		$title = ($this->syArgs('title',1)!='') ? $this->syArgs('title',1) : $this->type['classname'];
		$body = ($this->syArgs('body',1)!='') ? $this->syArgs('body',1) : '';
		$row1 = array('tid' => $tid,'fmolds' => $fmolds,'faid' => $this->syArgs('faid'),'title' => $title,'addtime' => time(),'orders' => 0,'isshow' => $isshow,'user' => $user,'body' => $body,'reply'=>'');
		$row2=$this->fields_args('message',$tid);
		$add = syClass('c_message');$newv=$add->syVerifier($row1);
		if(false == $newv){
			$a=$add->create($row1);$row2=array_merge($row2,array('aid' => $a));
			syDB('message_field')->create($row2);
			if($this->my['id']!=0){
				syDB('member_file')->update(array('hand'=>$this->syArgs('hand'),'uid'=>$this->my['id']),array('hand'=>0,'aid'=>$a,'molds' => 'message'));
			}else{
				syDB('member_file')->update(array('hand'=>$this->syArgs('hand'),'ip'=>GetIP()),array('hand'=>0,'aid'=>$a,'molds' => 'message'));
			}
			message('发布成功',$GLOBALS["WWW"]);
		}else{message_err($newv);}
	}
	function m_upload(){
		$aid=$this->syArgs('aid');
		$tid=$this->syArgs('tid');
		$molds=$this->syArgs('molds',1);
		$t=syDB('classtype')->find(array('tid'=>$tid),null,'msubmit');
		if($t['msubmit']!=1){$this->member->p_r($t['msubmit']);}
		if($this->my['id']!=0){
			$ufm=syDB('member_file')->findSql('SELECT sum(size) FROM '.$GLOBALS['G_DY']['db']['prefix'].'member_file where uid='.$this->my['id']);
			if($ufm[0]['sum(size)']>$this->my['group']['fileallsize']*1024){echo '您的上传空间已满';exit;}
			$fileClass=syClass('syupload',array($this->my['group']['filetype'],$this->my['group']['filesize']*1024));
			$w=' (hand='.$this->syArgs('hand').' and uid='.$this->my['id'].' and fields="'.$this->syArgs('inputid',1).'") or (hand!='.$this->syArgs('hand').' and hand!=0 and uid='.$this->my['id'].') ';
			if($aid&&$molds)$w.=' or (aid='.$aid.' and molds="'.$molds.'") ';
		}else{
			//游客
			$ip=GetIP();
			$group=syDB('member_group')->find(array('sys'=>1));
			if($group['filesize']<=0||$group['fileallsize']<=0){echo $group['name'].'不能上传文件';exit;}
			$ufm=syDB('member_file')->findSql('SELECT sum(size) FROM '.$GLOBALS['G_DY']['db']['prefix'].'member_file where ip="'.$ip.'"');
			if($ufm[0]['sum(size)']>$group['fileallsize']*1024){echo '您的上传空间已满';exit;}
			$fileClass=syClass('syupload',array($group['filetype'],$group['filesize']*1024));
			$w=' (hand='.$this->syArgs('hand').' and ip="'.$ip.'" and fields="'.$this->syArgs('inputid',1).'") or (hand!='.$this->syArgs('hand').' and hand!=0 and ip="'.$ip.'") ';
			if($aid&&$molds)$w.=' or (aid='.$aid.' and molds="'.$molds.'") ';
		}
		if (!empty($_FILES)){
			$fileinfos = $fileClass->upload_file($_FILES[$this->syArgs('isfiles',1)]);
			if(is_array($fileinfos)){
				$finfo=array(
					'uid' => $this->my['id'],
					'ip' => $ip,
					'url' => $fileinfos['fn'],
					'size' => $fileinfos['si'],
					'fields' => $this->syArgs('inputid',1),
					'hand' => $this->syArgs('hand'),
					'molds' => ''
				);
				foreach(syDB('member_file')->findAll($w,null,'url') as $v){@unlink($v['url']);}
				syDB('member_file')->delete($w);
				syDB('member_file')->create($finfo);
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
	}
	function m_upload_load(){
		$this->hand=$this->syArgs('hand',1);
		$this->molds=$this->syArgs('molds',1);
		$this->aid=$this->syArgs('aid');
		$this->tid=$this->syArgs('tid');
		$this->inputid=$this->syArgs('inputid',1);
		if(!$this->hand||$this->inputid=='')message("no hand or inputid");
		$this->multi=$this->syArgs('multi') ? 'true':'false';
		if($this->syArgs('fileExt',1)){$this->fileExt=$this->syArgs('fileExt',1);}else{
			foreach(explode(',',$this->my['group']['filetype']) as $v){
				$fileExt.=';*.'.$v;
			}$this->fileExt=substr($fileExt,1);
		}
		$this->sizeLimit=$this->syArgs('sizeLimit') ? $this->syArgs('sizeLimit'):$this->my['group']['filesize']*1024;
		$this->fileover=$this->syArgs('fileover') ? $this->syArgs('fileover'):1;
		$this->display('include/uploads.php');
	}
	private function fields_args($molds,$tid=0,$lists=0){
		$fa=array();
		$fieldswhere=" fieldshow=1 and issubmit=1 and molds='".$molds."'";
		if($tid){$fieldswhere.=" and types like '%|".$tid."|%' ";}
		if($lists){$fieldswhere.=" and lists=1 ";}
		$v=syDB('fields')->findAll($fieldswhere,' fieldorder DESC,fid ');
		foreach($v as $f){
			$ns='';$n=array();
			if($f['fieldstype']=='varchar' || $f['fieldstype']=='files' || $f['fieldstype']=='fileall' || $f['fieldstype']=='select' || $f['fieldstype']=='text'){$ns=$this->syArgs($f['fields'],1);}
			if($f['fieldstype']=='int'){$ns=$this->syArgs($f['fields']);}
			if($f['fieldstype']=='contingency'&&$this->syArgs('contingency_'.$f['fields'].'_word',1)){$ns=$this->syArgs($f['fields']);}
			if($f['fieldstype']=='decimal'){$ns=$this->syArgs($f['fields'],3);}
			if($f['fieldstype']=='time'){$ns=strtotime($this->syArgs($f['fields'],1));}
			if($f['fieldstype']=='checkbox'){if($this->syArgs($f['fields'],2)){$ns='|'.implode('|',$this->syArgs($f['fields'],2)).'|';}else{$ns='';}}
			if($molds=='member'&&$lists==1){if($ns=='')message("请输入".$f['fieldsname']);}
			$n=array($f['fields'] => $ns);
			$fa=array_merge($fa,$n);
		}
		return $fa;
	}
}	