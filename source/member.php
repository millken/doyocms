<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}
class member extends syController
{	
	function __construct(){
		parent::__construct();
		if(funsinfo('member','isshow')!=1)message("会员功能已关闭");
		$this->member=syClass('symember');
		$this->my=$this->member->islogin(1);
		$this->cu=syClass('c_member');
		$this->dbl=$GLOBALS['G_DY']['db']['prefix'];
		$this->db=$this->dbl.'member';
		if($this->syArgs('url',1)){
			$this->backurl=urlencode($this->syArgs('url',1));
			$this->gourl=str_replace('&amp;', '&', $this->syArgs('url',1));
		}else{
			$this->backurl='?c=member';
			$this->gourl='?c=member';
		}
		$this->sy_class_type=syClass('syclasstype');
		$this->molds_message=syDB('molds')->find(array('molds'=>'message'));
		$this->fun_comment=syDB('funs')->find(array('funs'=>'comment'));
		if($this->my['id']!=0){
			if($this->my['group']['submit']==1){
				$weight=syDB('member_group')->findAll(' weight<'.$this->my['group']['weight'].' ',null,'gid');
				foreach($weight as $v){$w.=$v['gid'].',';}
				$w.=$this->my['gid'];
				$this->typemenu=syDB('classtype')->findAll(' msubmit>0 and msubmit in('.$w.') and molds!="message" ',' orders desc,tid ','tid,molds,classname,orders,msubmit');
			}
			$money=syDB('member')->find(array('id'=>$this->my['id']),null,'money');
			$this->mymoney=$money['money'];
		}
		$this->uploadfile=$this->syArgs('c',1);
	}
	function index(){
		$this->display("member/index.html");
	}
	function login(){
		if($this->syArgs("go")==1){
			if($this->syArgs("user",1) && $this->syArgs("pass",1)){
				if($GLOBALS['G_DY']['vercode']==1){
				if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
				}
				$conditions = array('user' => $this->syArgs("user",1),'pass' => md5(md5($this->syArgs("pass",1)).$this->syArgs("user",1)));
				$r = syDB('member')->find($conditions);
				if(!$r){
					message("用户名或密码错误");
				}else{
					$weight=syDB('member_group')->find(array('gid'=>$r['gid']));
					$_SESSION['member'] = array(
						'user' => $r['user'],
						'id' => $r['id'],
					);
					jump($this->gourl);
				}
			}else{
				message("请输入用户名和密码");
			}
		}
		$this->display("member/login.html");
	}
	function retrieve_password(){
		if($this->syArgs("go")){
			if($GLOBALS['G_DY']['vercode']==1){
				if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
			}
			$ok=false;
			$user=$this->syArgs("user",1);
			$email=$this->syArgs("email",1);
			if($email){
				if($user){
					$conditions = array('user' => $user,'email' => $email);
					$m = syDB('member')->find($conditions,null,'id,user,email');
					if(!$m){message("没有找到匹配的用户和邮箱，请确认用户名及邮箱输入正确。");}else{$ok=true;}
				}else{
					$conditions = array('email' => $email);
					$num = syDB('member')->findCount($conditions);
					if($num<1)message("没有找到匹配的用户和邮箱，请确认用户名及邮箱输入正确。");
					if($num>1)message("此邮箱注册了多个账号，必须填写用户名才可找回密码。");
					if($num==1){$m = syDB('member')->find($conditions,null,'id,user,email');$ok=true;}
				}
			}else{
				message("请输入email地址");
			}
			if($ok){
				$http=get_domain();
				$subject=$http.'密码找回邮件';
				$token=md5($this->syArgs("vercode",1).md5(substr($m['pass'],mt_rand(1,10),mt_rand(10,20)).mt_rand(10000,99999)).$email.time());
				$url=$GLOBALS['WWW'].'index.php?c=member&a=reset_password&id='.$m['id'].'&token='.$token;
				$body='您在'.$GLOBALS['S']['title'].'提交了密码找回邮件，点击下面的链接进行密码重置：<a href="'.$http.$url.'" target="_blank">【点击此处进行密码重置】</a>，如果本次找回密码不是您亲自操作，请忽略本邮件。';
				$send=syClass('syphpmailer');
				$retrieve=$send->Send($email,$m['user'],$subject,$body);
				if(!$retrieve){
					message('邮件发送失败，请联系管理员。');
				}else{
					syDB('member')->update(array('id'=>$m['id']),array('token'=>$token,'tokentime'=>time()));
					message('密码已成功发送至您的邮箱，请点击邮件内容中的链接设置新密码，有效期3天。','?c=member');
				}
			}
		}
		$this->display("member/password.html");
	}
	function reset_password(){
		$id=$this->syArgs("id");
		$token=$this->syArgs("token",1);
		if($id&&$token){
			$conditions = array('id' => $id,'token' => $token);
			$m = syDB('member')->find($conditions,null,'id,user,token,tokentime');
			if(!$m)message('找回密码参数有误');
			$t=time()-$m['tokentime'];
			if($t>259200){
				syDB('member')->update(array('id'=>$id),array('token'=>'','tokentime'=>0));
				message('找回密码链接已过期，请重新申请找回密码。');
			}else{
				if($this->syArgs("go")){
					if($GLOBALS['G_DY']['vercode']==1){
						if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
					}
					if(!$this->syArgs('pass1',1))message("请输入密码");
					if(!$this->syArgs('pass2',1))message("请输入确认密码");
					if($this->syArgs('pass1',1)!=$this->syArgs('pass2',1))message("两次密码输入不一致");
					$newpass=md5(md5($this->syArgs("pass1",1)).$m['user']);
					syDB('member')->update(array('id'=>$id),array('pass'=>$newpass,'token'=>'','tokentime'=>0));
					message('恭喜您，密码已重置成功。',$GLOBALS['WWW'].'index.php?c=member&a=login');
				}else{
					$this->password=$m;
					$this->display("member/password.html");
				}
			}
		}else{
			message('找回密码参数有误');
		}
	}
	function out(){
		$_SESSION['member'] = array();
		if (isset($_COOKIE[session_name()])) {setcookie(session_name(), '', time()-42000, '/');}
		session_destroy();
		jump($GLOBALS['WWW']);
	}
	function rules(){
		if(syDB('member')->find(array('user'=>$this->syArgs('user',1)))){echo 'false';}else{echo 'true';}
	}
	function reg(){
		$this->fields=fields_info(0,'member',1);
		if($this->syArgs("go")==1){
			if(!$this->syArgs('user',1))message("请输入用户名");
			if(syDB('member')->find(array('user'=>$this->syArgs('user',1))))message("用户名已被注册，请更换");
			if(!$this->syArgs('pass1',1))message("请输入密码");
			if(!$this->syArgs('pass2',1))message("请输入确认密码");
			if(!$this->syArgs('email',1))message("请输入Email");
			if($this->syArgs('pass1',1)!=$this->syArgs('pass2',1))message("两次密码输入不一致");
			if($GLOBALS['G_DY']['vercode']==1){
			if(md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify']){message("验证码错误");}
			}
			$newrow1 = array(
				'user' => $this->syArgs('user',1),
				'pass' => md5(md5($this->syArgs("pass1",1)).$this->syArgs("user",1)),
				'email' => $this->syArgs('email',1),
				'gid' => 2,
				'money' => 0,
				'regtime' => time(),
				'token' => '',
			);
			$newrow2 = array();
			$newrow2=array_merge($newrow2,$this->fields_args('member',0,1));
			$newVerifier=$this->cu->syVerifier($newrow1);
			if(false == $newVerifier){
				$addnewrow=$this->cu->create($newrow1);
				if($addnewrow==FALSE){message("注册失败，请重新注册");}
				$arrays = array(
					'aid' => $addnewrow,
				);
				$newrow2=array_merge($newrow2,$arrays);
				syDB('member_field')->create($newrow2);
				$_SESSION['member'] = array(
					'user' => $newrow1['user'],
					'id' => $addnewrow,
				);
				message("恭喜您，注册成功",$this->gourl);
			}else{message_err($newVerifier);}
		}
		$this->display("member/reg.html");
	}
	function myinfo(){
		if($this->syArgs("go")==1){
			$newrow1 = array();
			if($this->syArgs('pass',1)||$this->syArgs('pass1',1)||$this->syArgs('pass2',1)){
				if(!$this->syArgs('pass',1))message("请输入原密码");
				if(!syDB('member')->find(array('user'=>$this->my['user'],'pass'=>md5(md5($this->syArgs("pass",1)).$this->my['user']))))message("原密码错误");
				if(!$this->syArgs('pass1',1))message("请输入新密码");
				if(!$this->syArgs('pass2',1))message("请输入确认新密码");
				if($this->syArgs('pass1',1)!=$this->syArgs('pass2',1))message("两次密码输入不一致");
				$newrow1=array_merge($newrow1,array('pass' => md5(md5($this->syArgs("pass1",1)).$this->my['user'])));
			}
			$newrow2=array();
			$newrow2=array_merge($newrow2,$this->fields_args('member'));
			syDB('member')->update(array('id'=>$this->my['id']),$newrow1);
			syDB('member_field')->update(array('aid'=>$this->my['id']),$newrow2);
			message("资料修改成功");
		}
		$user=syDB('member')->findSql('select * from '.$this->db.' a left join '.$this->db.'_field b on (a.id=b.aid) where id='.$this->my['id']);
		$this->myinfo=$user[0];
		$this->fields=fields_info(0,'member',0,$this->myinfo);
		$this->display("member/myinfo.html");
	}
	function mylist(){
		if(!$this->syArgs('tid'))message("请指定内容tid","?c=member");
		$tid = $this->syArgs('tid');
		$this->type=syDB('classtype')->find(array('tid'=>$tid),null,'tid,molds,mrank,classname,msubmit');
		$c=syClass('c_'.$this->type['molds']);
		$this->member->p_v($this->type['mrank']);
		$db=$GLOBALS['G_DY']['db']['prefix'].$this->type['molds'];
		$tid_leafid=$this->sy_class_type->leafid($tid);
			$w=" where tid in(".$tid_leafid.") and user='".$this->my['user']."' and usertype=1 ";
			$order=' order by orders desc,id desc';
			$f=syDB('fields')->findAll(" molds='".$this->type['molds']."' and types like '%|".$tid."|%' and lists=1 ");
			if($f){
				foreach($f as $v){$fields.=','.$v['fields'];}
				$sql='select a.*'.$fields.' from '.$db.' a left join '.$db.'_field b on (a.id=b.aid)'.$w.$order;
			}else{
				$sql='select * from '.$db.$w.$order;
			}
		$total_page=total_page($db.$w);
		$this->lists = $c->syPager($this->syArgs('page',0,1),20,$total_page)->findSql($sql);
		$pages=$c->syPager()->getPager();
		$this->pages=pagetxt($pages,$GLOBALS['G_DY']['url']["url_path_base"].'?c=member&a=mylist&tid='.$tid);
		$this->display("member/mylist.html");
	}
	function mymessage(){
		$c=syClass('c_message');
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'message where user="'.$this->my['user'].'"');
		$this->lists=$c->syPager($this->syArgs('page',0,1),10,$total_page)->findAll(array('user'=>$this->my['user']),' addtime desc ');
		$c_page=$c->syPager()->getPager();
		$this->pages=pagetxt($c_page,$GLOBALS['G_DY']['url']["url_path_base"].'?c=member&a=mymessage');
		$this->display("member/mymessage.html");
	}
	function mycomment(){
		$c=syClass('c_comment');
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'comment where user="'.$this->my['user'].'"');
		$this->lists=$c->syPager($this->syArgs('page',0,1),10,$total_page)->findAll(array('user'=>$this->my['user']),' addtime desc ');
		$c_page=$c->syPager()->getPager();
		$this->pages=pagetxt($c_page,$GLOBALS['G_DY']['url']["url_path_base"].'?c=member&a=mycomment');
		$this->display("member/mycomment.html");
	}
	function mymolds(){
		$c=syClass('c_account');
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'account where uid='.$this->my['id'].' and type=4');
		$this->lists = $c->syPager($this->syArgs('page',0,1),20,$total_page)->findAll(array('uid'=>$this->my['id'],'type'=>4),' addtime desc ');
		$pages=$c->syPager()->getPager();
		$this->pages=pagetxt($pages,$GLOBALS['G_DY']['url']["url_path_base"].'?c=member&a=mymolds');
		$this->display("member/mymolds.html");
	}
	function myorder(){
		if($this->syArgs('oid')||$this->syArgs('orderid',1)!=''){
			if($this->syArgs('oid')){$r=array('id'=>$this->syArgs('oid'));}else{$r=array('orderid'=>$this->syArgs('orderid',1));}
			$this->order=syDB('order')->find($r);
			if($this->order['state']>0&&$this->order['virtual']==1)$this->virtuals=syDB('product_virtual')->findAll(array('oid'=>$this->order['id'],'state'=>1));
			$this->goods=order_goods(unserialize($this->order['goods']),$this->order['logistics']);
			$this->info=unserialize($this->order['info']);
			$this->sendgoods=unserialize($this->order['sendgoods']);
			$total=0;
			foreach($this->goods[0] as $v){
				$total=calculate($total,$v['total']);
				$total=calculate($total,$v['logistics_price']);
			}
			$this->aggregate=calculate($total, $this->order['favorable'],2);
			$this->display("member/myorderinfo.html");
		}else{
			$c=syClass('c_order');
			$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'order where uid='.$this->my['id']);
			$this->lists=$c->syPager($this->syArgs('page',0,1),10,$total_page)->findAll(array('uid'=>$this->my['id']),' addtime desc ');
			$c_page=$c->syPager()->getPager();
			$this->pages=pagetxt($c_page,$GLOBALS['G_DY']['url']["url_path_base"].'?c=member&a=myorder');
			$this->display("member/myorder.html");
		}
	}
	function account(){
		$a=syClass('syaccount');
		$c=syClass('c_account');
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'account where uid='.$this->my['id']);
		$this->lists = $c->syPager($this->syArgs('page',0,1),20,$total_page)->findAll(array('uid'=>$this->my['id']),' addtime desc ');
		$lists = $this->lists;
		foreach($lists as $k=>$v){
			$lists[$k]['info']=$a->userinfo($v);
			$lists[$k]['pn']=$a->pn($v['type']);
		}
		$this->lists = $lists;
		$pages=$c->syPager()->getPager();
		$this->pages=pagetxt($pages,$GLOBALS['G_DY']['url']["url_path_base"].'?c=member&a=account');
		$this->display("member/account.html");
	}
	function recharge(){
		$p=syDB('payment')->findall(array('isshow'=>1),'orders desc,id desc');
		foreach($p as $k=>$v){
			$service=unserialize($v['keyv']);
			if($service['service']==2)unset($p[$k]);
			if($v['pay']=='cashbalance')unset($p[$k]);
			if($v['pay']=='offline')unset($p[$k]);
		}
		if($p){$p[0]['n']=1;$this->payment=$p;}
		$this->display("member/recharge.html");
	}
	function mydel(){
		$molds=$this->syArgs('molds',1);
		$id=$this->syArgs('id');
		switch ($molds){
			case 'comment':
				if(!syDB('comment')->delete(array('cid'=>$id,'user'=>$this->my['user']))){
					message("删除失败,请重新提交");
				}
			break;
			case 'message':
				if(!syDB('message')->delete(array('id'=>$id,'user'=>$this->my['user']))){
					message("删除失败,请重新提交");
				}
				syDB('message_field')->delete(array('aid'=>$id));
			break;
			default:
				$c=syDB($molds)->find(array('id'=>$id,'user'=>$this->my['user'],'usertype'=>1),null,'id,isshow');
				if(!$c||$c['isshow']==1)message("此内容已经审核或不是您发布的内容，不能删除。");
				if(!syDB($molds)->delete(array('id'=>$id,'user'=>$this->my['user'],'usertype'=>1))){
					message("删除失败,请重新提交");
				}
				syDB($molds.'_field')->delete(array('aid'=>$id));
			break;
		}
		$w=array('aid'=>$id,'molds'=>$molds);
		foreach(syDB('member_file')->findAll($w,null,'url') as $v){@unlink($v['url']);}
		syDB('member_file')->delete($w);
		message("删除成功");
	}
	function release(){
		if(!$this->syArgs('tid'))message("请选择栏目","?c=member");
		$this->id=$this->syArgs('id');
		$tid=$this->syArgs('tid');
		$this->type=syDB('classtype')->find(array('tid'=>$tid),null,'tid,molds,classname,msubmit');
		if($this->type['msubmit']!=1){
			$this->member->p_r($this->type['msubmit']);
		}
		if($this->syArgs("go")==1){
			if($GLOBALS['G_DY']['vercode']==1){
			if(!$this->syArgs("vercode",1)||md5(strtolower($this->syArgs("vercode",1)))!=$_SESSION['doyo_verify'])message("验证码错误");
			}
			$isshow = ($this->my['group']['audit']==1) ? 1 : 0;
			//按频道投稿入库
			  $row1 = array('tid' => $tid,'sid' => 0,'title' => $this->syArgs('title',1),'style' => '','trait' => '','gourl' => '','htmlfile' => '','htmlurl' => '','addtime' => time(),'hits' => 0,'litpic' => '','orders' => 0,'mrank' => 0,'mgold' => 0,'isshow' => $isshow,'keywords' => '','description' => '','user' => $this->my['user'],'usertype' => 1);
			  if($this->type['molds']=='product')$row1=array_merge(array('price' => $this->syArgs('price',3),'photo' => ''),$row1);
			  $row2=array_merge(array('body' => $this->syArgs('body',1)),$this->fields_args($this->type['molds'],$tid));
			  $add = syClass('c_'.$this->type['molds']);$newv=$add->syVerifier($row1);
			  if(false == $newv){
				  if($this->id){
					  if(syDB($this->type['molds'])->find(array('tid'=>$tid,'id'=>$this->id,'user'=>$this->my['user'],'usertype'=>1))){
						  syDB($this->type['molds'])->update(array('id' => $this->id),$row1);
						  syDB($this->type['molds'].'_field')->update(array('aid' => $this->id),$row2);
					  }else{message('无权操作');}
				  }else{
					  $a=$add->create($row1);$row2=array_merge($row2,array('aid' => $a));
					  syDB($this->type['molds'].'_field')->create($row2);
				  }
				  syDB('member_file')->update(array('hand'=>$this->syArgs('hand'),'uid'=>$this->my['id']),array('hand'=>0,'aid'=>$a,'molds' => $this->type['molds']));
				  message('内容更新成功','?c=member&a=mylist&tid='.$tid);
			  }else{message_err($newv);}
			//--------------
		}
		$this->hand=date('His').mt_rand(100,999);
		if($this->id){
		$c=syDB($this->type['molds'])->findSql('select * from '.$this->dbl.$this->type['molds'].' a left join '.$this->dbl.$this->type['molds'].'_field b on (a.id=b.aid) where user="'.$this->my['user'].'" and usertype=1 and id='.$this->id);
		$c=$c[0];
		}
		
		$this->fields=array();
		//按频道显示投稿字段
		switch ($this->type['molds']){
			case 'article':
				$a=array(
					array('name'=>'标题','input'=>'<input name="title" id="title" type="text" class="inp" value="'.$c['title'].'" style="width:300px;" />','fields'=>'title'),
					array('name'=>'内容','input'=>'<script type="text/javascript">$(function(){KindEditor.create("#body",{resizeType : 1,allowPreviewEmoticons : false,allowImageUpload : false,items : ["fontname", "fontsize", "|", "forecolor", "hilitecolor", "bold", "italic", "underline","removeformat", "|", "justifyleft", "justifycenter", "justifyright", "insertorderedlist","insertunorderedlist", "|", "emoticons", "image", "link"]})});</script><textarea name="body" id="body" class="inp" style="width:550px;height:300px;">'.$c['body'].'</textarea>','fields'=>'body'),
				);
				$this->fields=array_merge($this->fields,$a);
			break;
			case 'product':
				$a=array(
					array('name'=>'标题','input'=>'<input name="title" id="title" type="text" class="inp" value="'.$c['title'].'" style="width:300px;" />','fields'=>'title'),
					array('name'=>'价格','input'=>'<input name="price" id="price" type="text" class="inp" value="'.$c['price'].'" style="width:300px;" />','fields'=>'price'),
					array('name'=>'内容','input'=>'<script type="text/javascript">$(function(){KindEditor.create("#body",{resizeType : 1,allowPreviewEmoticons : false,allowImageUpload : false,items : ["fontname", "fontsize", "|", "forecolor", "hilitecolor", "bold", "italic", "underline","removeformat", "|", "justifyleft", "justifycenter", "justifyright", "insertorderedlist","insertunorderedlist", "|", "emoticons", "image", "link"]})});</script><textarea name="body" id="body" class="inp" style="width:550px;height:300px;">'.$c['body'].'</textarea>','fields'=>'body'),
				);
				$this->fields=array_merge($this->fields,$a);
			break;
			default:
			$a=array(
					array('name'=>'标题','input'=>'<input name="title" id="title" type="text" class="inp" value="'.$c['title'].'" style="width:300px;" />','fields'=>'title'),
				);
			break;
			
		}
		//--------------
		if($c){$this->fields=array_merge($this->fields,fields_info($tid,$this->type['molds'],0,$c));}
		else{$this->fields=array_merge($this->fields,fields_info($tid,$this->type['molds']));}
		$this->display("member/release.html");
	}
	function m_upload(){
		$aid=$this->syArgs('aid');
		$tid=$this->syArgs('tid');
		$molds=$this->syArgs('molds',1);
		$t=syDB('classtype')->find(array('tid'=>$tid),null,'msubmit');
		if($t['msubmit']!=1){$this->member->p_r($t['msubmit']);}
		if($this->my['id']!=0){
			$ufm=syDB('member_file')->findSql('SELECT sum(size) FROM '.$this->db.'_file where uid='.$this->my['id']);
			if($ufm[0]['sum(size)']>$this->my['group']['fileallsize']*1024){echo '您的上传空间已满';exit;}
			$fileClass=syClass('syupload',array($this->my['group']['filetype'],$this->my['group']['filesize']*1024));
			$w=' (hand='.$this->syArgs('hand').' and uid='.$this->my['id'].' and fields="'.$this->syArgs('inputid',1).'") or (hand!='.$this->syArgs('hand').' and hand!=0 and uid='.$this->my['id'].') ';
			if($aid&&$molds)$w.=' or (aid='.$aid.' and molds="'.$molds.'") ';
		}else{
			//游客
			$ip=GetIP();
			$group=syDB('member_group')->find(array('sys'=>1));
			if($group['filesize']<=0||$group['fileallsize']<=0){echo $group['name'].'不能上传文件';exit;}
			$ufm=syDB('member_file')->findSql('SELECT sum(size) FROM '.$this->db.'_file where ip="'.$ip.'"');
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
		$this->hand=$this->syArgs('hand');
		$this->molds=$this->syArgs('molds');
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
			//if($molds=='member'&&$lists==1){if($ns=='')message("请输入".$f['fieldsname']);}
			$n=array($f['fields'] => $ns);
			$fa=array_merge($fa,$n);
		}
		return $fa;
	}
}	