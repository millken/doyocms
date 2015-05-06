<?php
if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');}

class a_fields extends syController
{
	function __construct(){
		parent::__construct();
		$this->moldname=moldsinfo($this->syArgs('molds',1),'moldname');
		if($this->moldname==''){
			$this->moldname=funsinfo($this->syArgs('molds',1),'name');
			$this->moldtype=1;
		}
		$this->molds=$this->syArgs('molds',1);
		$this->ClassA=syClass('c_'.$this->molds);
		$this->ClassT=syClass('c_classtype');
		$classtype=syDB('classtype')->findAll(array('molds'=>$this->molds),' orders desc,tid ','tid,classname,pid,molds');
		$this->types=syClass('syclasstype');
		$this->typesdb=$this->types->type_txt();
		$this->ClassF=syClass('c_fields');
		$this->auser=syClass('syauser');
		if($this->syArgs('tid')){
			$this->tid=$this->syArgs('tid');
			$this->typename='['.typename($this->tid).']';
		}
		$this->Get_c='a_fields&molds='.$this->molds.'&tid='.$this->tid;
		$this->sqldb=$GLOBALS['G_DY']['db']['prefix'].$this->molds;
		if($this->syArgs('types',2)){$types='|'.implode('|',$this->syArgs('types',2)).'|';}else{$types='';}
		$fieldstype=$this->syArgs('fieldstype',1);
		if($fieldstype=='varchar'){$imgw=$this->syArgs('varchar_imgw');$imgh=$this->syArgs('varchar_imgh');}
		if($fieldstype=='text'){$imgw=$this->syArgs('text_imgw');$imgh=$this->syArgs('text_imgh');}
		if($fieldstype=='files'||$fieldstype=='fileall'){$imgw=$this->syArgs('imgw');$imgh=$this->syArgs('imgh');}
		$this->newrow = array(
			'molds' => $this->molds,
			'fieldsname' => $this->syArgs('fieldsname',1),
			'fields' => $this->syArgs('fields',1),
			'fieldstype' => $this->syArgs('fieldstype',1),
			'fieldslong' => $this->syArgs('fieldslong'),
			'selects' => $this->syArgs('selects',1),
			'fieldshow' => $this->syArgs('fieldshow'),
			'fieldorder' => $this->syArgs('fieldorder'),
			'issubmit' => $this->syArgs('issubmit'),
			'lists' => $this->syArgs('lists'),
			'types' => $types,
			'contingency' => $this->syArgs('contingency',1),
			'imgw' => $imgw,
			'imgh' => $imgh,
		);
	}
	function index(){
		$w=" molds='".$this->molds."' ";
		if($this->tid){
			$w.="and types like '%|".$this->tid."|%' ";
		}
		$total_page=total_page($GLOBALS['G_DY']['db']['prefix'].'fields where'.$w);
		$this->lists=$this->ClassF->syPager($this->gopage,15,$total_page)->findAll($w);
		$this->pages = pagetxt($this->ClassF->syPager()->getPager());
		$this->toptxt=$this->moldname.$this->typename.'-字段管理';
		$this->display("fields.html");
	}
	function add(){
		if ($this->syArgs('run')==1){
			$thenewfields = $this->newrow;
			$newVerifier=$this->ClassF->syVerifier($thenewfields);
			if(false == $newVerifier){
				$fieldsture1=$this->ClassF->findSql('describe '.$this->sqldb.' '.$thenewfields['fields']);
				$fieldsture2=$this->ClassF->findSql('describe '.$this->sqldb.'_field '.$thenewfields['fields']);
				if(!empty($fieldsture1) || !empty($fieldsture2)){message_a("字段标识已存在，请重新输入，可在字段后加数字区别");}
				$fsql="ALTER TABLE ".$this->sqldb."_field ADD ".$thenewfields['fields']." ";
				switch ($thenewfields['fieldstype']) {
					case 'varchar':
						$fsql.="VARCHAR(".$thenewfields['fieldslong'].") CHARACTER SET utf8 default NULL";
						break;
					case 'text':
						$fsql.="TEXT CHARACTER SET utf8 default NULL";
						break;
					case 'int':
						$fsql.="INT(10) DEFAULT '0' NOT NULL";
						break;
					case 'decimal':
						$fsql.="DECIMAL(10,2) UNSIGNED DEFAULT '0.00' NOT NULL";
						break;
					case 'time':
						$fsql.="INT(10) DEFAULT '0' NOT NULL";
						break;
					case 'files':
						$fsql.="CHAR(255) CHARACTER SET utf8 default NULL";
						break;
					case 'fileall':
						$fsql.="TEXT CHARACTER SET utf8 default NULL";
						break;
					case 'select':
						$fsql.="CHAR(30) CHARACTER SET utf8 default NULL";
						break;
					case 'checkbox':
						$fsql.="CHAR(200) CHARACTER SET utf8 default NULL";
						break;
					case 'contingency':
						$fsql.="INT(10) DEFAULT '0' NOT NULL";
						break;
				}
				if($thenewfields['fieldstype']!="varchar"){$thenewfields['fieldslong']=0;}
				if($thenewfields['fieldstype']!="select" && $thenewfields['fieldstype']!="checkbox"){$thenewfields['selects']='';}
				if(!$this->ClassF->runSql($fsql))message_a("字段创建失败，请重新提交");
				if($this->ClassF->find(array('fields'=>$thenewfields['fields'],'molds'=>$thenewfields['molds']))==FALSE){
					if($this->ClassF->create($thenewfields)==FALSE)
					{message_a("字段入库失败，请手工删除".$this->dbleft."_field 表下的".$thenewfields['fields']."字段后重新提交");}
				}
				message_a("字段创建成功","?c=".$this->Get_c);
			}else{message_b($newVerifier);}
		}
		$this->toptxt=$this->moldname.$this->typename.'-添加字段';
		$this->postgo='add';
		$this->display("fields_edit.html");
	}
	function edit(){
		$this->fields=$this->ClassF->find(array('fid'=>$this->syArgs('fid')));
		$this->toptxt=$this->moldname.$this->typename.'-修改字段';
		if ($this->syArgs('run')==1){
			$thenewfields = $this->newrow;
			$newVerifier=$this->ClassF->syVerifier($thenewfields);
			if(false == $newVerifier){
				if($this->fields['fields']!=$thenewfields['fields'] || $this->fields['fieldstype']!=$thenewfields['fieldstype'] || $this->fields['fieldslong']!=$thenewfields['fieldslong']){
					if($this->fields['fields']!=$thenewfields['fields']){
						$fieldsture1=$this->ClassF->findSql('describe '.$this->sqldb.' '.$thenewfields['fields']);
						$fieldsture2=$this->ClassF->findSql('describe '.$this->sqldb.'_field '.$thenewfields['fields']);
						if($fieldsture1['0']!='' && $fieldsture2['0']!=''){message_a("字段标识已存在，请重新输入，可在字段后加数字区别");}
					}
					$fsql='ALTER TABLE '.$this->sqldb.'_field change '.$this->fields['fields'].' '.$thenewfields['fields'].' ';
					switch ($thenewfields['fieldstype']) {
						case 'varchar':
							$fsql.="VARCHAR(".$thenewfields['fieldslong'].") CHARACTER SET utf8 default NULL";
							break;
						case 'text':
							$fsql.="TEXT CHARACTER SET utf8 default NULL";
							break;
						case 'int':
							$fsql.="INT(10) DEFAULT '0' NOT NULL";
							break;
						case 'decimal':
							$fsql.="DECIMAL(10,2) UNSIGNED DEFAULT '0.00' NOT NULL";
							break;
						case 'time':
							$fsql.="INT(10) DEFAULT '0' NOT NULL";
							break;
						case 'files':
							$fsql.="CHAR(255) CHARACTER SET utf8 default NULL";
							break;
						case 'fileall':
							$fsql.="TEXT CHARACTER SET utf8 default NULL";
							break;
						case 'select':
							$fsql.="CHAR(30) CHARACTER SET utf8 default NULL";
							break;
						case 'checkbox':
							$fsql.="CHAR(200) CHARACTER SET utf8 default NULL";
							break;
						case 'contingency':
							$fsql.="INT(10) DEFAULT '0' NOT NULL";
							break;
					}
					if(!$this->ClassF->runSql($fsql))message_a("数据库字段类型失败，请重新提交");
				}
				if($thenewfields['fieldstype']!="varchar"){$thenewfields['fieldslong']=0;}
				if($thenewfields['fieldstype']!="select" && $thenewfields['fieldstype']!="checkbox"){$thenewfields['selects']='';}
				if($this->ClassF->update(array('fid'=>$this->syArgs('fid')),$thenewfields))
				{message_a("字段修改成功","?c=".$this->Get_c);}else{message_a("字段修改失败,请重新提交");}
			}else{message_b($newVerifier);}
		}
		$this->postgo='edit';
		$this->display("fields_edit.html");
	}
	function del(){
		$this->toptxt='删除字段';
		$delfields=$this->ClassF->find(array('fid'=>$this->syArgs('fid')));
		if ($this->syArgs('run')==1){
			if(!$this->ClassF->runSql("ALTER TABLE ".$this->sqldb."_field DROP COLUMN ".$delfields['fields']))
			{message_a("字段删除失败，请重新提交");}
			if($this->ClassF->delete(array('fid'=>$delfields['fid'])))
			{message_a("字段删除成功","?c=".$this->Get_c);}else{message_a("字段删除失败,请重新提交");}
		}
		$this->msgtitle='确定要删除字段 <strong>['.$delfields['fieldsname'].']</strong> 字段吗？';
		$this->msg='警告：本操作将删除所有已发布内容的['.$delfields['fieldsname'].']字段<br>本操作不可逆！建议删除前备份数据库！';
		$this->msggo='<a href="?c='.$this->Get_c.'&a=del&run=1&fid='.$delfields['fid'].'">确定删除</a><a href="?c='.$this->Get_c.'">取消操作</a>';
		$this->display("msg.html");
		
	}
	function info(){
		$fieldswhere=" fieldshow=1 and molds='".$this->syArgs('molds',1)."'";
		if($this->moldtype!=1){$fieldswhere.="  and types like '%|".$this->tid."|%' ";}
		$v=$this->ClassF->findAll($fieldswhere,' fieldorder DESC,fid ');
		if($this->syArgs('id'))$c=syDB($this->molds.'_field')->find(array('aid'=>$this->syArgs('id')));
		foreach($v as $f){
			switch ($f['fieldstype']){
				case 'varchar':
					if($f['fieldslong']>255){
						$t='<dd><textarea name="'.$f['fields'].'" style="width:'.$f['imgw'].'px; height:'.$f['imgh'].'px;" class="int">'.$c[$f['fields']].'</textarea></dd>';
					}else{
						$t='<dd><input name="'.$f['fields'].'" type="text" class="int" value="'.$c[$f['fields']].'" /></dd><dd class="t">最多'.$f['fieldslong'].'个字</dd>';
					}
				break;
				case 'text':
					$t='<script type="text/javascript">$(function(){KindEditor.create("#'.$f['fields'].'",{cssPath : ["include/js/prettify.css"],fileManagerJson : "'.$GLOBALS['G_DY']['url']["url_path_base"].'?c=uploads&a=filemanager",allowFileManager : true,filePostName : "editor_KindEditor",filterMode : false,uploadJson : "'.$GLOBALS['G_DY']['url']["url_path_base"].'?c=uploads&tid='.$this->tid.'&isfiles=editor_KindEditor"})});</script>';
					$t.='<dd><textarea name="'.$f['fields'].'" id="'.$f['fields'].'" style="width:'.$f['imgw'].'px;height:'.$f['imgh'].'px;">'.code_body($c[$f['fields']],0).'</textarea></dd>';
				break;
				case 'int':
					$t='<dd><input name="'.$f['fields'].'" type="text" class="int" value="'.$c[$f['fields']].'" /></dd><dd class="t">请输入整数格式，可为负数</dd>';
				break;
				case 'decimal':
					$t='<dd><input name="'.$f['fields'].'" type="text" class="int" value="'.$c[$f['fields']].'" /></dd><dd class="t">请输入货币格式，如2.03</dd>';
				break;
				case 'time':
					if($c[$f['fields']]!=''){$time=date('Y-m-d H:i',$c[$f['fields']]);}else{$time=date('Y-m-d H:i');}
					$t='<dd><input name="'.$f['fields'].'" type="text" class="int" value="'.$time.'" onClick="WdatePicker({dateFmt:';$t.="'yyyy-MM-dd HH:mm'";$t.='})" /></dd>';
				break;
				case 'files':
				$t='<dd><input name="'.$f['fields'].'" id="'.$f['fields'].'" type="text" class="int" value="'.$c[$f['fields']].'" /></dd><dd><iframe frameborder="0" width="300" height="26" scrolling="No" id="flitpic" name="flitpic" src="?c=uploads&a=loadup&inputid='.$f['fields'].'&imgw='.$f['imgw'].'&imgh='.$f['imgh'].'"></iframe></dd>';
				break;
				case 'fileall':
				if($c[$f['fields']]!='')$style='display:block;';
				$t='<dl id="'.$f['fields'].'over" class="fileover fall" style="'.$style.'">';
				if($c[$f['fields']]){
					$n=1;
					foreach(explode('|-|',$c[$f['fields']]) as $v){
						$s=explode('|,|',$v);
						$fname=explode('.',$s[0]);$fnames=preg_replace('/.*\/.*\//si', '',$fname[0]);
						if(stripos($s[0],'jpg') || stripos($s[0],'gif') || stripos($s[0],'png') || stripos($s[0],'jpeg')){
							$t.='<dd id="f_'.$fnames.'"><img src="'.$s[0].'" height="50" width="60" />';
						}else{
							$t.='<dd id="f_'.$fnames.'"><a href="'.$s[0].'" target="_blank">'.$fname[1].'文件</a><br />';
						}
						$t.='<input name="'.$f['fields'].'file[]" type="hidden" value="'.$s[0].'" /><br /><input name="'.$f['fields'].'txt[]" type="text" value="'.$s[1].'" class="int" style="width:52px;height:12px;" /><br />排序 <input name="'.$f['fields'].'num[]" type="text" value="'.$n.'" class="int" style="width:22px;height:12px;" /><br /><a onclick=delfieldall("f_'.$fnames.'") style="width:43;padding-left:17px;cursor:pointer;">删除</a></dd>';
						$n++;
					}
				}
				$t.='</dl>';
				$t.='<dl><dt>'.$f['fieldsname'].'：</dt><dd><iframe frameborder="0" width="300" height="26" scrolling="No" id="flitpic" name="flitpic" src="?c=uploads&a=loadup&inputid='.$f['fields'].'&multi=1&fileover=1&imgw='.$f['imgw'].'&imgh='.$f['imgh'].'"></iframe></dd></dl>';
				break;
				case 'select':
					$t='<dd><select name="'.$f['fields'].'">';
					foreach(explode(',',$f['selects']) as $v){
						$s=explode('=',$v);
						$t.='<option value="'.$s[1].'" ';
						if($c[$f['fields']]==$s[1])$t.='selected="selected"';
						$t.='>'.$s[0].'</option>';
					}
					$t.='</select></dd>';
				break;
				case 'checkbox':
					$t='<dd>';
					foreach(explode(',',$f['selects']) as $v){
						$s=explode('=',$v);
						$t.='<input type="checkbox" name="'.$f['fields'].'[]" value="'.$s[1].'" ';
						if(stristr($c[$f['fields']],'|'.$s[1].'|')!=FALSE)$t.='checked="checked"';
						$t.='>'.$s[0];
					}
					$t.='</dd>';
				break;
				case 'contingency':
					$t='<script type="text/javascript">$(function(){$("input[name=contingency_'.$f['fields'].'_word]").bind({keyup: function() {$.get("'.$GLOBALS["WWW"].'index.php?c=ajax&a=fields_contingency&molds='.$f['contingency'].'&fields='.$f['fields'].'&word="+$(this).attr("value"), function(data){
$("#contingency_'.$f['fields'].'").removeClass("none");$("#contingency_'.$f['fields'].'").html(data);});},focusout: function() {$("#contingency_'.$f['fields'].'").addClass("none");}});});function contingency_id_'.$f['fields'].'(value,title){$("#'.$f['fields'].'").attr("value",value);$("input[name=contingency_'.$f['fields'].'_word]").attr("value",title);}</script><dd><div style="position:relative"><input name="contingency_'.$f['fields'].'_word" type="text" class="int" value="'.contentinfo($f['contingency'],$c[$f['fields']],'title').'" /><input name="'.$f['fields'].'" id="'.$f['fields'].'" type="hidden" value="'.$c[$f['fields']].'" /><ul class="contingency none" id="contingency_'.$f['fields'].'"></ul></div></dd><dd class="t">请输入需要关联的<strong>['.moldsinfo($f['contingency'],'moldname').']内容标题</strong>，可输入标题关键词搜索。</dd>';
				break;
			}
			if($f['fieldstype']!='fileall'){echo '<dl><dt>'.$f['fieldsname'].'：</dt>'.$t.'</dl>';}else{echo $t;}
		}
	}

}	