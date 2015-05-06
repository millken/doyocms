<?php if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');} ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script src="include/js/jsmain.js" type="text/javascript"></script>
<script src="include/js/adminuser.js" type="text/javascript"></script>
<script type="text/javascript">
function doyocms_upload_config(){
	var multi=<?php echo $multi ?>;
	var fileSize=<?php echo $sizeLimit ?>;
	var fileTypeExts='<?php echo $fileExt ?>';
	var bgimage='include/js/doyoupload/add.gif';
	var siteurl='<?php echo $GLOBALS['WWW'] ?>';
	var upurl='<?php echo $GLOBALS['G_DY']['url']["url_path_base"]  ?>';
	var updata='?c=uploads&w=<?php echo $w;?>&h=<?php echo $w;?>&isfiles=Filedata&session_id=<?php echo session_id();?>';
	return [multi,fileSize,fileTypeExts,bgimage,siteurl,upurl,updata];
}
function doyocms_upload_onOPEN(date){
<?php if($fileover==1){
	if($multi=='false'){ ?>
	$(".info").empty();
	<?php } ?>
	$(".info").append('<li><p class="loading"><img src="include/js/doyoupload/loading.gif" /></p><p class="file"><span>'+date+'</span><br /><span class="progress"></span></p><p class="cancel"><a onclick="doyocms_upload_gocancel('+"'"+date+"'"+',1);"></a></p></li>');
<?php } ?>
};
function doyocms_upload_listfile(info){
	$(".list").append('<li><p class="cancel"><a onclick="doyocms_upload_gocancel('+"'"+info+"'"+',1);"></a></p><span>'+info+'</span> <span class="progress"></span></li>');
}
function doyocms_upload_gocancel(file,cancel){
	if(cancel==1){
		if(navigator.appName.indexOf("Microsoft") != -1){
			window['upload_swf'].gocancel('cancel');
		}else{
			document['upload_swf'].gocancel('cancel');
		}
	}
	$("span:contains('"+file+"')").parents("li").remove();
	<?php if($fileover==2){ ?>
	var num=$("span:contains('"+file+"')").parents("li").prevAll().length;
	$("#onAll img:eq("+num+")").nextUntil('img').remove();
	$("#onAll img:eq("+num+")").remove();
	<?php } ?>
}
function doyocms_upload_onProgress(info){
	var html;
	if(info[0]==100){
		html="正在处理图片...";
		$("span:contains('"+info[2]+"')").next().html("正在处理图片...")
	}else{
		html="进度：<strong>"+info[0]+"%</strong> ("+info[1]+")";
	}
<?php if($fileover==2){ ?>
	$("span:contains('"+info[2]+"')").next().html(html);
<?php }else{ ?>
	$(".name").html(info[2]);
	$(".progress").html(html);
<?php } ?>
}
function doyocms_upload_onCompleteData(info){
<?php if($fileover==2){ ?>
	var gocancel=$("span:contains('"+info[1]+"')").parents("li").children(".cancel");
	var html=gocancel.html();
	html = html.replace(',1);"></a>',',0);"></a>');
	gocancel.html(html);
	$("span:contains('"+info[1]+"')").next().html("完成");
	var strs=info[0].split(",");
	var tta=info[0].substring(0,3);
	if(tta.indexOf('0,')==-1){
		$("span:contains('"+info[1]+"')").next().html(strs[0]);
	}else{
		$("#onAll").append("<img src='<?php echo $GLOBALS['WWW'] ?>"+strs[1]+"' /><br><br>");
	}
<?php }else{ ?>
	var strs=info[0].split(",");
	var tt=info[0].substring(0,3);
	<?php if($multi=='false'){ ?>
	$(".info").empty();
	<?php }else{ ?>
	$("span:contains('"+info[1]+"')").parents("li").remove();
	<?php } ?>
	if(tt.indexOf('0,')==-1){
		$(".info").append(strs[0]);
	}else{
		<?php if($multi=='false'){ ?>
		$(window.parent.document).find("#<?php echo $inputid ?>").attr("value",'<?php echo $GLOBALS['WWW'];?>'+strs[1]);
		$(window.parent.document).find("#<?php echo $inputid ?>over dd").remove();
		$(window.parent.document).find("#<?php echo $inputid ?>over").css("display","block");
		$(window.parent.document).find("#<?php echo $inputid ?>over").append("<dd><img src='"+strs[1]+"' height='50' /></dd>");
		<?php }else{ ?>
		$(window.parent.document).find("#<?php echo $inputid ?>over").css("display","block");
		if(strs[3]==1){
		$(window.parent.document).find("#<?php echo $inputid ?>over").append('<dd id="f_'+strs[2]+'"><img src="'+strs[1]+'" height="50" width="60" /><input name="<?php echo $inputid ?>file[]" type="hidden" value="'+'<?php echo $GLOBALS['WWW'];?>'+strs[1]+'" /><br /><input name="<?php echo $inputid ?>txt[]" type="text" class="int" style="width:52px;height:12px;" /><br />排序 <input name="<?php echo $inputid ?>num[]" type="text" class="int" style="width:22px;height:12px;" /><br /><a onclick=delfieldall("f_'+strs[2]+'") style="width:43;padding-left:17px;cursor:pointer;">删除</a></dd>');
		}else{
		$(window.parent.document).find("#<?php echo $inputid ?>over").append('<dd id="f_'+strs[2]+'"><a href="'+strs[1]+'" target="_blank" class="f">'+strs[3]+'文件</a><br /><input name="<?php echo $inputid ?>file[]" type="hidden" value="'+'<?php echo $GLOBALS['WWW'];?>'+strs[1]+'" /><br /><input name="<?php echo $inputid ?>txt[]" type="text" class="int" style="width:52px;height:12px;" /><br />排序 <input name="<?php echo $inputid ?>num[]" type="text" class="int" style="width:22px;height:12px;" /><br /><a onclick=delfieldall("f_'+strs[2]+'") style="width:43;padding-left:17px;cursor:pointer;">删除</a></dd>');
		}
		<?php } ?>
	}
<?php } ?>
}
function doyocms_upload_err(err){
	alert(err);
}
</script>
<style type="text/css">
body{ margin:0; font-size:12px;}
ul,li,p{margin: 0; padding: 0; border:0;list-style: none;}
<?php if($fileover==2){ ?>
.swf{ padding:3px; border-bottom:1px solid #CCC;}
.list{overflow-x:auto; height:200px;overflow-x:hidden;overflow-y:auto;}
.list li{height:25px; line-height:25px;padding:5px 10px;border-bottom:1px solid #CCC; background-color:#F6F6F6}
.list li .cancel{float:right; width:28px; height:25px;}
.list li .cancel a{float:left; width:28px; height:25px; cursor:pointer; background:url(include/js/doyoupload/cancel.gif) no-repeat 0 5px;}
.list li .progress{color:#06C;font-size:10px; line-height:13px; font-family:Verdana, Geneva, sans-serif;}
<?php }else{ ?>
.swf{ float:left;}
.info{ float:left;font-size:12px; line-height:28px; padding-left:10px;}
.info li{height:28px; clear:both}
.info p{ float:left;height:28px; overflow:hidden;}
.info .loading{padding:3px 5px 5px 0;}
.info .file{padding-right:5px;font-size:10px; line-height:13px; font-family:Verdana, Geneva, sans-serif;}
.info .cancel{width:28px;}
.info .cancel a{float:left;width:28px;height:28px; cursor:pointer; background:url(include/js/doyoupload/cancel.gif) no-repeat 0 6px;}
<?php } ?>
</style>
</head>

<body>
<div class="swf">
<object id="upload_swf" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="80" height="26"><param name="movie" value="include/js/doyoupload/doyo_upload.swf?date=<?php echo time(); ?>" /><param name="quality" value="high" /><param name="wmode" value="opaque" /><param name="swfversion" value="9.0.0" /><!--[if !IE]>--><embed src="include/js/doyoupload/doyo_upload.swf?date=<?php echo time(); ?>" name="upload_swf" quality="high" wmode="opaque"  swfversion="9.0.0" type="application/x-shockwave-flash"  width="80" height="26"></embed><!--<![endif]--></object>
</div>
<?php if($fileover==2){ ?>
<ul class="list"></ul>
<div id="onAll" style="display:none;"></div>
<?php }else{ ?>
<ul class="info"></ul>
<?php } ?>
<script type="text/javascript">
swfobject.registerObject("upload_swf");
</script>
</body>
</html>
