<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_label extends syController
{
	function __construct(){
		parent::__construct();
		$this->types=syClass('syclasstype')->type_txt();
		
		$this->article=syDB('molds')->find(array('molds'=>'article'),null,' molds,moldname ');
		$this->article_type=$this->types;
		$this->article_trait=syDB('traits')->findAll(array('molds'=>'article'));
		
		$this->product=syDB('molds')->find(array('molds'=>'product'),null,' molds,moldname ');
		$this->product_type=$this->types;
		$this->product_trait=syDB('traits')->findAll(array('molds'=>'product'));
		
		$this->channel=syDB('molds')->findAll(array('isshow'=>1,'sys'=>0),null,' molds,moldname ');
		
		$this->message=syDB('molds')->find(array('molds'=>'message'),null,' molds,moldname ');
		$this->message_type=$this->types;
	}
	function index(){
		$this->toptxt='模板调用生成器';
		$this->display("label.html");
	}
	function channels(){
		$molds=$this->syArgs('molds',1);
		$t=syDB('classtype')->findAll(array('molds'=>$molds),' orders desc,tid desc ',' tid,classname,orders ');
		$s=syDB('special')->findAll(array('molds'=>$molds),' orders desc,sid desc ',' sid,name,orders ');
		$r=syDB('traits')->findAll(array('molds'=>$molds));
		$c='<select name="tid"><option value="">栏目</option><option value="">全部</option>';
		foreach($t as $v){
			$c.='<option value="'.$v['tid'].'">'.$v['classname'].'</option>';
		}
		$c.='</select>';
		$c.='<select name="sid"><option value="">专题</option><option value="">全部</option>';
		foreach($s as $v){
			$c.='<option value="'.$v['sid'].'">'.$v['name'].'</option>';
		}
		$c.='</select>';
		$c.='<select name="trait"><option value="">包含推荐</option><option value="">不指定</option>';
		foreach($r as $v){
			$c.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
		}
		$c.='</select>';
		$c.='<select name="notrait"><option value="">不包含推荐</option><option value="">不指定</option>';
		foreach($r as $v){
			$c.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
		}
		$c.='</select>';
		echo $c;
	}
	function output(){
		if($this->syArgs('as',1)){
			if(preg_match("/^[a-zA-Z][a-zA-Z0-9]*$/",$this->syArgs('as',1))!=0){
				$as=$this->syArgs('as',1);$asv=' as="'.$this->syArgs('as',1).'"';
			}else{echo '调用标识必须为英文或数字，并且以英文开头';exit;}
		}else{$as='v';}
		if($this->syArgs('page',1)&&preg_match("/^[a-zA-Z][a-zA-Z0-9]*$/",$this->syArgs('page',1))==0){echo '分页标识必须为英文或数字，并且以英文开头';exit;}
		switch($this->syArgs('m',1)){
			case 'article':
				$w='table="article"';
				if($this->syArgs('tid'))$w.=' tid="'.$this->syArgs('tid').'"';
				if($this->syArgs('sid'))$w.=' sid="'.$this->syArgs('sid').'"';
				if($this->syArgs('trait',1))$w.=' trait="'.$this->syArgs('trait',1).'"';
				if($this->syArgs('notrait',1))$w.=' notrait="'.$this->syArgs('notrait',1).'"';
				if($this->syArgs('image'))$w.=' image="'.$this->syArgs('image').'"';
				if($this->syArgs('keywords',1))$w.=' keywords="'.$this->syArgs('keywords',1).'"';
				if($this->syArgs('orderby',1))$w.=' orderby="orders|desc,'.$this->syArgs('orderby',1).'"';
				if($this->syArgs('page',1)){
					$w.=' page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$asv;
				$l='本循环会自动调用指定栏目及所有下级栏目的内容，栏目tid也可以指定多个，如tid="1,2,3,4"，多个栏目用英文逗号分割<br />';
				$l.='<span>{loop '.$w.'}</span> <br /><table width="100%" class="tablabel">';
				$l.='<tr><td><strong>内容ID:</strong> <span>{$'.$as.'[';$l.="'id'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>顺序:</strong> <span>{$'.$as.'[';$l.="'n'";$l.=']}</span> 顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><strong>链接地址:</strong> <span>{$'.$as.'[';$l.="'url'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>标题:</strong> <span>{$'.$as.'[';$l.="'title'";$l.=']}</span><br />';
				$l.='<em></em>说明：限定字数调用: {fun newstr($'.$as.'[';$l.="'title'";$l.='],20)}其中"20"为限制多少个字</td></tr>';
				$l.='<tr><td><strong>标题样式:</strong> <span>{$'.$as.'[';$l.="'style'";$l.=']}</span> 样式直接输出style代码，可直接使用，如: style="<span>{$'.$as.'[';$l.="'style'";$l.=']}</span>"</td></tr>';
				$l.='<tr><td><strong>缩略图:</strong> <span>{$'.$as.'[';$l.="'litpic'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>点击次数:</strong> <span>{$'.$as.'[';$l.="'hits'";$l.=']}</span></td></tr>';
				if(funsinfo('pay','isshow')==1){
				$l.='<tr><td><strong>消耗现金：</strong> <span>{$'.$as.'[';$l.="'mgold'";$l.=']}</span></td></tr>';
				}
				$l.='<tr><td><strong>简介:</strong> <span>{$'.$as.'[';$l.="'description'";$l.=']}</span> 限定字数调用: <span>{fun newstr($'.$as.'[';$l.="'description'";$l.='],20)}</span></td></tr>';
				$l.='<tr><td><strong>所属栏目ID:</strong> <span>{$'.$as.'[';$l.="'tid'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>所属栏目名:</strong> <span>{fun typename($'.$as.'[';$l.="'tid'";$l.='])}</span></td></tr>';
				$l.='<tr><td><strong>发布时间:</strong> <span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='说明：时间调用中大写Y为4位年份，小写y为两位年份，m为月，d为日，H为小时，i为分钟，s为秒<br />如需要调用2012-12-23 15:25:59样式的日期，即为<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='另外可使用if标签进行最新时间内的特殊显示，如<br /><em></em>{if(newest($'.$as.'[';$l.="'addtime'";$l.='],24))}<br><em></em>&lt;span style=&quot;color:red&quot;&gt;{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}&lt;/span&gt;<br><em></em>{else}<br><em></em>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}<br><em></em>{/if}<br>以上代码使用if判断的方式执行：如果内容是24小时内发布，显示红色的日期，否则正常显示日期</td></tr>';
				if($this->syArgs('tid')){
foreach(syDB('fields')->findAll(' fieldshow=1 and molds="article" and types like "%|'.$this->syArgs('tid').'|%" ',' fieldorder desc,fid ','fieldsname,fields,fieldstype') as $v){
if($v['fieldstype']=='fileall'){
	$l.='<tr><td><strong>'.$v['fieldsname'].':</strong> 说明：本字段为多个附件，使用循环调用<br />';
	$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$as.'[';$l.="'".$v['fields']."'";$l.=']) as $'.$v['fields'].'}</span><br />';
	$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$'.$v['fields'].'[0]}</span> 附件文字说明：<span>{$'.$v['fields'].'[1]}</span><br />';
	$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';
}else{
	$l.='<tr><td><strong>'.$v['fieldsname'].':</strong> <span>{$'.$as.'[';$l.="'".$v['fields']."'";$l.=']}</span></td></tr>';
}
					}
				}
				$l.='</table><span>{/loop}</span><br />';
				if($this->syArgs('page',1)){$l.='<strong>分页代码：</strong><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
			case 'product':
				$w='table="product"';
				if($this->syArgs('tid'))$w.=' tid="'.$this->syArgs('tid').'"';
				if($this->syArgs('sid'))$w.=' sid="'.$this->syArgs('sid').'"';
				if($this->syArgs('trait',1))$w.=' trait="'.$this->syArgs('trait',1).'"';
				if($this->syArgs('notrait',1))$w.=' notrait="'.$this->syArgs('notrait',1).'"';
				if($this->syArgs('image'))$w.=' image="'.$this->syArgs('image').'"';
				if($this->syArgs('keywords',1))$w.=' keywords="'.$this->syArgs('keywords',1).'"';
				if($this->syArgs('orderby',1))$w.=' orderby="orders|desc,'.$this->syArgs('orderby',1).'"';
				if($this->syArgs('page',1)){
					$w.=' page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$asv;
				$l='本循环会自动调用指定栏目及所有下级栏目的内容，栏目tid也可以指定多个，如tid="1,2,3,4"，多个栏目用英文逗号分割<br />';
				$l.='<span>{loop '.$w.'}</span> <br /><table width="100%" class="tablabel">';
				$l.='<tr><td><strong>内容ID:</strong> <span>{$'.$as.'[';$l.="'id'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>顺序:</strong> <span>{$'.$as.'[';$l.="'n'";$l.=']}</span> 顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><strong>链接地址:</strong> <span>{$'.$as.'[';$l.="'url'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>标题:</strong> <span>{$'.$as.'[';$l.="'title'";$l.=']}</span><br />';
				$l.='<em></em>说明：限定字数调用: {fun newstr($'.$as.'[';$l.="'title'";$l.='],20)}其中"20"为限制多少个字</td></tr>';
				$l.='<tr><td><strong>标题样式:</strong> <span>{$'.$as.'[';$l.="'style'";$l.=']}</span> 样式直接输出style代码，可直接使用，如: style="<span>{$'.$as.'[';$l.="'style'";$l.=']}</span>"</td></tr>';
				$l.='<tr><td><strong>缩略图:</strong> <span>{$'.$as.'[';$l.="'litpic'";$l.=']}</span></td></tr>';

				$l.='<tr><td><strong>图集:</strong> 使用循环调用<br />';
				$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$as.'[';$l.="'photo'";$l.=']) as $pk=>$ps}</span><br />';
				$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$ps[0]}</span> 图集文字说明：<span>{$ps[1]}</span> 序号(0开始)：<span>{$pk}</span><br />';
				$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';

				$l.='<tr><td><strong>点击次数:</strong> <span>{$'.$as.'[';$l.="'hits'";$l.=']}</span></td></tr>';
				if(funsinfo('pay','isshow')==1){
				$l.='<tr><td><strong>售价：</strong> <span>{$'.$as.'[';$l.="'mgold'";$l.=']}</span></td></tr>';
				}
				$l.='<tr><td><strong>简介:</strong> <span>{$'.$as.'[';$l.="'description'";$l.=']}</span> 限定字数调用: <span>{fun newstr($'.$as.'[';$l.="'description'";$l.='],20)}</span></td></tr>';
				$l.='<tr><td><strong>所属栏目ID:</strong> <span>{$'.$as.'[';$l.="'tid'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>所属栏目名:</strong> <span>{fun typename($'.$as.'[';$l.="'tid'";$l.='])}</span></td></tr>';
				$l.='<tr><td><strong>发布时间:</strong> <span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='说明：时间调用中大写Y为4位年份，小写y为两位年份，m为月，d为日，H为小时，i为分钟，s为秒<br />如需要调用2012-12-23 15:25:59样式的日期，即为<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='另外可使用if标签进行最新时间内的特殊显示，如<br /><em></em>{if(newest($'.$as.'[';$l.="'addtime'";$l.='],24))}<br><em></em>&lt;span style=&quot;color:red&quot;&gt;{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}&lt;/span&gt;<br><em></em>{else}<br><em></em>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}<br><em></em>{/if}<br>以上代码使用if判断的方式执行：如果内容是24小时内发布，显示红色的日期，否则正常显示日期</td></tr>';
				if($this->syArgs('tid')){
foreach(syDB('fields')->findAll(' fieldshow=1 and molds="product" and types like "%|'.$this->syArgs('tid').'|%" ',' fieldorder desc,fid ','fieldsname,fields,fieldstype') as $v){
if($v['fieldstype']=='fileall'){
	$l.='<tr><td><strong>'.$v['fieldsname'].':</strong> 说明：本字段为多个附件，使用循环调用<br />';
	$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$as.'[';$l.="'".$v['fields']."'";$l.=']) as $'.$v['fields'].'}</span><br />';
	$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$'.$v['fields'].'[0]}</span> 附件文字说明：<span>{$'.$v['fields'].'[1]}</span><br />';
	$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';
}else{
	$l.='<tr><td><strong>'.$v['fieldsname'].':</strong> <span>{$'.$as.'[';$l.="'".$v['fields']."'";$l.=']}</span></td></tr>';
}
					}
				}
				$l.='</table><span>{/loop}</span><br />';
				if($this->syArgs('page',1)){$l.='<strong>分页代码：</strong><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
			case 'message':
				$w='table="message"';
				if($this->syArgs('tid'))$w.=' tid="'.$this->syArgs('tid').'"';
				if($this->syArgs('isshow',1))$w.=' isshow="'.$this->syArgs('isshow',1).'"';
				if($this->syArgs('reply'))$w.=' reply="'.$this->syArgs('reply').'"';
				if($this->syArgs('orderby',1))$w.=' orderby="orders|desc,'.$this->syArgs('orderby',1).'"';
				if($this->syArgs('page',1)){
					$w.=' page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$asv;
				$l='本循环会自动调用指定栏目及所有下级栏目的内容，栏目tid也可以指定多个，如tid="1,2,3,4"，多个栏目用英文逗号分割<br />';
				$l.='<span>{loop '.$w.'}</span> <br /><table width="100%" class="tablabel">';
				$l.='<tr><td><strong>内容ID:</strong> <span>{$'.$as.'[';$l.="'id'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>顺序:</strong> <span>{$'.$as.'[';$l.="'n'";$l.=']}</span> 顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><strong>所属栏目ID:</strong> <span>{$'.$as.'[';$l.="'tid'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>所属栏目名:</strong> <span>{fun typename($'.$as.'[';$l.="'tid'";$l.='])}</span></td></tr>';
				$l.='<tr><td><strong>标题:</strong> <span>{$'.$as.'[';$l.="'title'";$l.=']}</span><br />';
				$l.='<em></em>说明：限定字数调用: {fun newstr($'.$as.'[';$l.="'title'";$l.='],20)}其中"20"为限制多少个字</td></tr>';
				$l.='<tr><td><strong>留言内容:</strong> <span>{$'.$as.'[';$l.="'body'";$l.=']}</span> 限定字数调用: <span>{fun newstr($'.$as.'[';$l.="'body'";$l.='],200)}</span></td></tr>';
				$l.='<tr><td><strong>回复内容:</strong> <span>{$'.$as.'[';$l.="'reply'";$l.=']}</span> 限定字数调用: <span>{fun newstr($'.$as.'[';$l.="'reply'";$l.='],200)}</span></td></tr>';
				$l.='<tr><td><strong>留言时间:</strong> <span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='<tr><td><strong>回复时间:</strong> <span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'retime'";$l.='])}</span><br />';
				if($this->syArgs('tid')){
foreach(syDB('fields')->findAll(' fieldshow=1 and molds="article" and types like "%|'.$this->syArgs('tid').'|%" ',' fieldorder desc,fid ','fieldsname,fields,fieldstype') as $v){
if($v['fieldstype']=='fileall'){
	$l.='<tr><td><strong>'.$v['fieldsname'].':</strong> 说明：本字段为多个附件，使用循环调用<br />';
	$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$as.'[';$l.="'".$v['fields']."'";$l.=']) as $'.$v['fields'].'}</span><br />';
	$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$'.$v['fields'].'[0]}</span> 附件文字说明：<span>{$'.$v['fields'].'[1]}</span><br />';
	$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';
}else{
	$l.='<tr><td><strong>'.$v['fieldsname'].':</strong> <span>{$'.$as.'[';$l.="'".$v['fields']."'";$l.=']}</span></td></tr>';
}
					}
				}
				$l.='</table><span>{/loop}</span><br />';
				if($this->syArgs('page',1)){$l.='<strong>分页代码：</strong><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
			case 'channel':
				$w='table="channel" molds="'.$this->syArgs('molds',1).'"';
				if($this->syArgs('tid'))$w.=' tid="'.$this->syArgs('tid').'"';
				if($this->syArgs('sid'))$w.=' sid="'.$this->syArgs('sid').'"';
				if($this->syArgs('trait',1))$w.=' trait="'.$this->syArgs('trait',1).'"';
				if($this->syArgs('notrait',1))$w.=' notrait="'.$this->syArgs('notrait',1).'"';
				if($this->syArgs('keywords',1))$w.=' keywords="'.$this->syArgs('keywords',1).'"';
				if($this->syArgs('orderby',1))$w.=' orderby="orders|desc,'.$this->syArgs('orderby',1).'"';
				if($this->syArgs('page',1)){
					$w.=' page="'.$this->syArgs('page',1).','.$this->syArgs('limit',1).'"';
				}else{
					if($this->syArgs('limit',1))$w.=' limit="'.$this->syArgs('limit',1).'"';
				}
				$w.=$asv;
				$l='本循环会自动调用指定栏目及所有下级栏目的内容，栏目tid也可以指定多个，如tid="1,2,3,4"，多个栏目用英文逗号分割<br />';
				$l.='<span>{loop '.$w.'}</span> <br /><table width="100%" class="tablabel">';
				$l.='<tr><td><strong>内容ID:</strong> <span>{$'.$as.'[';$l.="'id'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>顺序:</strong> <span>{$'.$as.'[';$l.="'n'";$l.=']}</span> 顺序是调用条数的排序，如调用10条，即为1—10</td></tr>';
				$l.='<tr><td><strong>链接地址:</strong> <span>{$'.$as.'[';$l.="'url'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>标题:</strong> <span>{$'.$as.'[';$l.="'title'";$l.=']}</span><br />';
				$l.='<em></em>说明：限定字数调用: {fun newstr($'.$as.'[';$l.="'title'";$l.='],20)}其中"20"为限制多少个字</td></tr>';
				$l.='<tr><td><strong>标题样式:</strong> <span>{$'.$as.'[';$l.="'style'";$l.=']}</span> 样式直接输出style代码，可直接使用，如: style="<span>{$'.$as.'[';$l.="'style'";$l.=']}</span>"</td></tr>';
				$l.='<tr><td><strong>点击次数:</strong> <span>{$'.$as.'[';$l.="'hits'";$l.=']}</span></td></tr>';
				if(funsinfo('pay','isshow')==1){
				$l.='<tr><td><strong>消耗现金：</strong> <span>{$'.$as.'[';$l.="'mgold'";$l.=']}</span></td></tr>';
				}
				$l.='<tr><td><strong>简介:</strong> <span>{$'.$as.'[';$l.="'description'";$l.=']}</span> 限定字数调用: <span>{fun newstr($'.$as.'[';$l.="'description'";$l.='],20)}</span></td></tr>';
				$l.='<tr><td><strong>所属栏目ID:</strong> <span>{$'.$as.'[';$l.="'tid'";$l.=']}</span></td></tr>';
				$l.='<tr><td><strong>所属栏目名:</strong> <span>{fun typename($'.$as.'[';$l.="'tid'";$l.='])}</span></td></tr>';
				$l.='<tr><td><strong>发布时间:</strong> <span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='说明：时间调用中大写Y为4位年份，小写y为两位年份，m为月，d为日，H为小时，i为分钟，s为秒<br />如需要调用2012-12-23 15:25:59样式的日期，即为<span>{fun date(';$l.="'Y-m-d H:i:s'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='另外可使用if标签进行最新时间内的特殊显示，如<br /><em></em>{if(newest($'.$as.'[';$l.="'addtime'";$l.='],24))}<br><em></em>&lt;span style=&quot;color:red&quot;&gt;{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}&lt;/span&gt;<br><em></em>{else}<br><em></em>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}<br><em></em>{/if}<br>以上代码使用if判断的方式执行：如果内容是24小时内发布，显示红色的日期，否则正常显示日期</td></tr>';
				if($this->syArgs('tid')){
foreach(syDB('fields')->findAll(' fieldshow=1 and molds="'.$this->syArgs('molds',1).'" and types like "%|'.$this->syArgs('tid').'|%" ',' fieldorder desc,fid ','fieldsname,fields,fieldstype') as $v){
if($v['fieldstype']=='fileall'){
	$l.='<tr><td><strong>'.$v['fieldsname'].':</strong> 说明：本字段为多个附件，使用循环调用<br />';
	$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{foreach fileall($'.$as.'[';$l.="'".$v['fields']."'";$l.=']) as $'.$v['fields'].'}</span><br />';
	$l.='<em class="e3"></em>&nbsp;&nbsp;附件地址：<span>{$'.$v['fields'].'[0]}</span> 附件文字说明：<span>{$'.$v['fields'].'[1]}</span><br />';
	$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{/foreach}</span></td></tr>';
}else if($v['fieldstype']=='select'||$v['fieldstype']=='checkbox'){
	$l.='<tr><td><strong>'.$v['fieldsname'].':</strong> 说明：本字段为选择字段，使用函数调用<br />';
	$l.='<em class="e2"></em>&nbsp;&nbsp;&nbsp;<span>{fun fieldsinfo("'.$v['fields'].'",$'.$as.'[';$l.="'".$v['fields']."'";$l.='],"'.$this->syArgs('molds',1).'")}</span><br />';
	$l.='</td></tr>';
}else{
	$l.='<tr><td><strong>'.$v['fieldsname'].':</strong> <span>{$'.$as.'[';$l.="'".$v['fields']."'";$l.=']}</span></td></tr>';
}
					}
				}
				$l.='</table><span>{/loop}</span><br />';
				if($this->syArgs('page',1)){$l.='<strong>分页代码：</strong><span>{$'.$this->syArgs('page',1).'}</span>';}
				$l.='<br /><br /><br />';
			break;
			case 'message_form':
				if(!$this->syArgs('tid'))exit('请选择栏目');
				$l.='<div style=" line-height:180%">';
				$l.='<span>&lt;form action="'.$GLOBALS['WWW'].'index.php?c=message&a=add&tid='.$this->syArgs('tid').'" method="post" &gt;<br />';
				$l.='<em class="e2"></em>标题：&lt;input name="title" id="title"&gt;<br />';
				$l.='<em class="e2"></em>内容：&lt;textarea name="body" id="body"&gt;&lt;/textarea><br /><br /></span>';
				$l.='<em class="e2"></em>循环输出自定义表单字段：<br />';
				$l.='<span><em class="e2"></em>{foreach fields_info('.$this->syArgs('tid').') as $v}<br /></span>';
				$l.='<em class="e2"></em><em class="e2"></em>字段名称：<span>{$v["name"]}</span> 字段输入框：<span>{$v["input"]}</span><br />';
				$l.='<span><em class="e2"></em>{/foreach}<br /></span>';
				$l.='<em class="e2"></em>字段输出结束---------<br /><br />';
				$l.='<em class="e2"></em>验证码输出：<br />';
				$l.='<span>';
				$l.='<em class="e2"></em>{if($GLOBALS["G_DY"]["vercode"]==1)}<br />';
				$l.='<em class="e2"></em><em class="e2"></em>验证码输入框：&lt;input type="text" name="vercode" id="vercode"&gt;<br />';
				$l.='<em class="e2"></em><em class="e2"></em>验证码图片：&lt;img src="'.$GLOBALS["WWW"].'include/vercode.php" id="vercodeimg" width="60" height="25" onclick="newvercode();" style="cursor:pointer;" &gt;<br />';
				$l.='<em class="e2"></em><em class="e2"></em>&lt;script type="text/javascript"&gt;function newvercode(){document.getElementById("vercodeimg").src="'.$GLOBALS['WWW'].'include/vercode.php?a="+Math.random();}&lt;/script&gt;<br />';
				$l.='<em class="e2"></em>{/if}<br />';
				$l.='</span>';
				$l.='<em class="e2"></em>验证码输出结束---------<br /><br />';
				$l.='<span><em class="e2"></em>&lt;input type="submit" value="提交留言" &gt;<br /></span>';
				$l.='<span>&lt;/form&gt;</span>';
				$l.='<dl><dt>&nbsp;</dt><dd id="loading"></dd></dl>';
				$l.='</div>';
			break;
			case 'classtypes':
				$w='table="classtype"';
				if($this->syArgs('pid'))$w.=' pid="'.$this->syArgs('pid').'"';
				if($this->syArgs('not'))$w.=' not="1"';
				if($this->syArgs('body'))$w.=' body="1"';
				if($this->syArgs('mshow'))$w.=' mshow="1"';
				$w.=$asv;
				$l='<span>{loop '.$w.'}</span><br />';
				$l.='<em></em><strong>栏目ID:</strong> <span>{$'.$as.'[';$l.="'tid'";$l.=']}</span> ';
				$l.='<strong>栏目名称:</strong> <span>{$'.$as.'[';$l.="'classname'";$l.=']}</span> <strong>顺序:</strong> <span>{$'.$as.'[';$l.="'n'";$l.=']}</span> <br />';
				$l.='<em></em><strong>栏目缩略图:</strong> <span>{$'.$as.'[';$l.="'litpic'";$l.=']}</span> ';
				$l.='<strong>栏目简介:</strong> <span>{$'.$as.'[';$l.="'description'";$l.=']}</span>';
				if($this->syArgs('body'))$l.='<strong>栏目介绍:</strong> <span>{$'.$as.'[';$l.="'body'";$l.=']}</span>';
				$l.='<strong>链接:</strong> <span>{$'.$as.'[';$l.="'url'";$l.=']}</span><br />';
				$l.='<em></em>当前循环栏目下级栏目调用方法----------------------<br />';
				$l.='<em class="e2"></em><span>{loop table="classtype" pid="$'.$as.'[';$l.="'tid'";$l.=']"';if($this->syArgs('body')){$l.=' body="1"';}$l.=' as="v1"}</span><br />';
				$l.='<em class="e3"></em><strong>栏目ID:</strong> <span>{$v1[';$l.="'tid'";$l.=']}</span> ';
				$l.='<strong>栏目名称:</strong> <span>{$v1[';$l.="'classname'";$l.=']}</span><br />';
				$l.='<em class="e3"></em><strong>栏目缩略图:</strong> <span>{$v1[';$l.="'litpic'";$l.=']}</span> ';
				$l.='<strong>栏目简介:</strong> <span>{$v1[';$l.="'description'";$l.=']}</span> ';
				if($this->syArgs('body'))$l.='<strong>栏目介绍:</strong> <span>{$'.$as.'[';$l.="'body'";$l.=']}</span>';
				$l.='<strong>链接:</strong> <span>{$v1[';$l.="'url'";$l.=']}</span><br />';
				$l.='<em class="e2"></em><span>{/loop}</span><br />';
				$l.='<em></em>--------------------------------------------------<br />';
				$l.='<em></em>当前循环栏目下内容调用方法------------------------<br />';
				$l.='<em class="e2"></em>"频道标签"与内容"标签"请参照对应频道调用代码<br />';
				$l.='<em class="e2"></em><span>{loop table="频道标签" tid="$'.$as.'[';$l.="'tid'";$l.=']" as="a"}</span><br />';
				$l.='<em class="e3"></em><span>{$a[';$l.="'标签'";$l.=']}</span> <br />';
				$l.='<em class="e2"></em><span>{/loop}</span><br />';
				$l.='<em></em>--------------------------------------------------<br />';
				$l.='<span>{/loop}</span><br />';
				$l.='说明：本调用标签包含多级循环嵌套示例，注意在多级循环嵌套时，必须区分每个循环的调用标识"as",否则会造成嵌套下的数据调用混乱<br />';
				$l.='使用无下级调用同级not=1标识时，适用于栏目页当前栏目下级调用，在当前栏目无下级栏目时，将调用当前同级栏目<br /><br /><br /><br /><br />';
			break;
			case 'classinfo':
				$w='table="classtype"';
				if($this->syArgs('tid')){$w.=' tid="'.$this->syArgs('tid').'" limit="1"';}else{echo '请选择调用栏目';exit;}
				if($this->syArgs('body'))$w.=' body="1"';
				$w.=$asv;
				$l='<span>{loop '.$w.'}</span><br />';
				$l.='<strong>栏目ID:</strong> <span>{$'.$as.'[';$l.="'tid'";$l.=']}</span> <br />';
				$l.='<strong>栏目名称:</strong> <span>{$'.$as.'[';$l.="'classname'";$l.=']}</span> <br />';
				$l.='<strong>栏目缩略图:</strong> <span>{$'.$as.'[';$l.="'litpic'";$l.=']}</span> <br />';
				$l.='<strong>栏目简介:</strong> <span>{$'.$as.'[';$l.="'description'";$l.=']}</span> 限定字数调用: <span>{fun newstr($'.$as.'[';$l.="'description'";$l.='],80)}</span>其中"80"为限制多少个字<br />';
				$l.='<strong>链接:</strong> <span>{$'.$as.'[';$l.="'url'";$l.=']}</span><br />';
				$l.='<em></em>下级栏目调用方法----------------------<br />';
				$l.='<em class="e2"></em><span>{loop table="classtype" pid="$'.$as.'[';$l.="'tid'";$l.=']" as="v1"}</span><br />';
				$l.='<em class="e3"></em><strong>栏目ID:</strong> <span>{$v1[';$l.="'tid'";$l.=']}</span> ';
				$l.='<strong>栏目名称:</strong> <span>{$v1[';$l.="'classname'";$l.=']}</span><br />';
				$l.='<em class="e3"></em><strong>栏目缩略图:</strong> <span>{$v1[';$l.="'litpic'";$l.=']}</span> ';
				$l.='<strong>栏目简介:</strong> <span>{$v1[';$l.="'description'";$l.=']}</span> ';
				$l.='<strong>链接:</strong> <span>{$v1[';$l.="'url'";$l.=']}</span><br />';
				$l.='<em class="e2"></em><span>{/loop}</span><br />';
				$l.='<em></em>--------------------------------------------------<br />';
				$l.='<em></em>当前栏目内容调用方法------------------------<br />';
				$l.='<em class="e2"></em>"频道标签"与内容"标签"请参照对应频道调用代码<br />';
				$l.='<em class="e2"></em><span>{loop table="频道标签" tid="$'.$as.'[';$l.="'tid'";$l.=']" as="a"}</span><br />';
				$l.='<em class="e3"></em><span>{$a[';$l.="'标签'";$l.=']}</span> <br />';
				$l.='<em class="e2"></em><span>{/loop}</span><br />';
				$l.='<em></em>--------------------------------------------------<br />';
				$l.='<span>{/loop}</span><br />';
				$l.='说明：本调用标签包含多级循环嵌套示例，注意在多级循环嵌套时，必须区分每个循环的调用标识"as",否则会造成嵌套下的数据调用混乱<br /><br /><br /><br /><br />';
			break;
			case 'special':
				$w='table="special"';
				if($this->syArgs('body'))$w.=' body="1"';
				$w.=$asv;
				$l='<span>{loop '.$w.'}</span><br />';
				$l.='<em></em><strong>专题ID:</strong> <span>{$'.$as.'[';$l.="'sid'";$l.=']}</span> ';
				$l.='<strong>专题名称:</strong> <span>{$'.$as.'[';$l.="'name'";$l.=']}</span> <strong>顺序:</strong> <span>{$'.$as.'[';$l.="'n'";$l.=']}</span> <br />';
				$l.='<em></em><strong>专题缩略图:</strong> <span>{$'.$as.'[';$l.="'litpic'";$l.=']}</span> ';
				$l.='<strong>专题简介:</strong> <span>{$'.$as.'[';$l.="'description'";$l.=']}</span>';
				$l.='<strong>链接:</strong> <span>{$'.$as.'[';$l.="'url'";$l.=']}</span><br />';
				$l.='<em></em>当前专题内容调用方法------------------------<br />';
				$l.='<em class="e2"></em>"频道标签"与内容"标签"请参照对应频道调用代码<br />';
				$l.='<em class="e2"></em><span>{loop table="频道标签" sid="$'.$as.'[';$l.="'sid'";$l.=']" as="a"}</span><br />';
				$l.='<em class="e3"></em><span>{$a[';$l.="'标签'";$l.=']}</span> <br />';
				$l.='<em class="e2"></em><span>{/loop}</span><br />';
				$l.='<em></em>--------------------------------------------------<br />';
				$l.='<span>{/loop}</span><br />';
			break;
			case 'specialinfo':
				$w='table="special"';
				if($this->syArgs('sid')){$w.=' sid="'.$this->syArgs('sid').'" limit="1"';}else{echo '请选择调用专题';exit;}
				if($this->syArgs('body'))$w.=' body="1"';
				$w.=$asv;
				$l='<span>{loop '.$w.'}</span><br />';
				$l.='<strong>专题ID:</strong> <span>{$'.$as.'[';$l.="'sid'";$l.=']}</span> <br />';
				$l.='<strong>专题名称:</strong> <span>{$'.$as.'[';$l.="'name'";$l.=']}</span> <br />';
				$l.='<strong>专题缩略图:</strong> <span>{$'.$as.'[';$l.="'litpic'";$l.=']}</span> <br />';
				$l.='<strong>专题简介:</strong> <span>{$'.$as.'[';$l.="'description'";$l.=']}</span> 限定字数调用: <span>{fun newstr($'.$as.'[';$l.="'description'";$l.='],80)}</span>其中"80"为限制多少个字<br />';
				$l.='<strong>链接:</strong> <span>{$'.$as.'[';$l.="'url'";$l.=']}</span><br />';
				$l.='<em></em>当前专题内容调用方法------------------------<br />';
				$l.='<em class="e2"></em>"频道标签"与内容"标签"请参照对应频道调用代码<br />';
				$l.='<em class="e2"></em><span>{loop table="频道标签" sid="$'.$as.'[';$l.="'sid'";$l.=']" as="a"}</span><br />';
				$l.='<em class="e3"></em><span>{$a[';$l.="'标签'";$l.=']}</span> <br />';
				$l.='<em class="e2"></em><span>{/loop}</span><br />';
				$l.='<em></em>--------------------------------------------------<br />';
				$l.='<span>{/loop}</span><br />';
				$l.='说明：本调用标签包含多级循环嵌套示例，注意在多级循环嵌套时，必须区分每个循环的调用标识"as",否则会造成嵌套下的数据调用混乱<br /><br /><br /><br /><br />';
			break;
			case 'ads':
				$w='table="ads"';
				if($this->syArgs('taid'))$w.=' taid="'.$this->syArgs('taid').'"';
				if($this->syArgs('type'))$w.=' type="'.$this->syArgs('type').'"';
				if($this->syArgs('limit',1))$w.=' limit="'.$this->syArgs('limit',1).'"';
				$w.=$asv;
				$l='<span>{loop '.$w.'}</span><br />';
				$l.='<em></em><strong>广告内容:</strong> <span>{$'.$as.'[';$l.="'body'";$l.=']}</span><br />';
				$l.='<em></em>说明：广告内容为系统根据广告类型，自动生成的广告显示代码，并已包含链接等信息<br />';
				$l.='<em></em><strong>顺序:</strong> <span>{$'.$as.'[';$l.="'n'";$l.=']}</span> 顺序是调用条数的排序，如调用10条，即为1—10<br />';
				$l.='<em></em><strong>广告名称:</strong> <span>{$'.$as.'[';$l.="'name'";$l.=']}</span><br />';
				$l.='<em></em><strong>链接地址:</strong> <span>{$'.$as.'[';$l.="'gourl'";$l.=']}</span><br />';
				$l.='<em></em><strong>广告上传文件:</strong> <span>{$'.$as.'[';$l.="'adfile'";$l.=']}</span><br />';
				$l.='<span>{/loop}</span><br /><br /><br /><br />';
			break;
			case 'links':
				$w='table="links"';
				if($this->syArgs('taid'))$w.=' taid="'.$this->syArgs('taid').'"';
				if($this->syArgs('type',1))$w.=' type="'.$this->syArgs('type',1).'"';
				if($this->syArgs('limit',1))$w.=' limit="'.$this->syArgs('limit',1).'"';
				$w.=$asv;
				$l='<span>{loop '.$w.'}</span><br />';
				$l.='<em></em><strong>链接名称:</strong> <span>{$'.$as.'[';$l.="'name'";$l.=']}</span><br />';
				$l.='<em></em><strong>链接地址:</strong> <span>{$'.$as.'[';$l.="'gourl'";$l.=']}</span><br />';
				if($this->syArgs('type',1)!='text'){
				$l.='<em></em><strong>图片:</strong> <span>{$'.$as.'[';$l.="'image'";$l.=']}</span><br />';
				}
				$l.='<span>{/loop}</span><br /><br /><br /><br />';
			break;
			case 'comment':
				$w='table="comment"';
				if($this->syArgs('isshow'))$w.=' isshow="'.$this->syArgs('isshow').'"';
				if($this->syArgs('limit',1))$w.=' limit="'.$this->syArgs('limit',1).'"';
				$w.=$asv;
				$l='<span>{loop '.$w.'}</span><br />';
				$l.='<em></em><strong>评论用户:</strong> <span>{$'.$as.'[';$l.="'user'";$l.=']}</span><br />';
				$l.='<em></em><strong>评论内容:</strong> <span>{$'.$as.'[';$l.="'body'";$l.=']}</span> 限定字数调用: <span>{fun newstr($'.$as.'[';$l.="'body'";$l.='],200)}</span><br >';
				$l.='<em></em><strong>回复内容:</strong> <span>{$'.$as.'[';$l.="'reply'";$l.=']}</span> 限定字数调用: <span>{fun newstr($'.$as.'[';$l.="'reply'";$l.='],200)}</span><br >';
				$l.='<em></em><strong>评论时间:</strong> <span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'addtime'";$l.='])}</span><br />';
				$l.='<em></em><strong>回复时间:</strong> <span>{fun date(';$l.="'Y-m-d'";$l.=',$'.$as.'[';$l.="'retime'";$l.='])}</span><br />';
				$l.='<span>{/loop}</span><br /><br /><br /><br />';
			break;
		}
		echo $l;
	}

}	