<?php if(!defined('APP_PATH')||!defined('DOYO_PATH')){exit('Access Denied');} ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script src="<?php echo $GLOBALS['WWW'] ?>include/js/jsmain.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['WWW'] ?>include/js/doyoupload/swfobject.js" type="text/javascript"></script>
<script type="text/javascript">
function doyocms_upload_config(){
	var multi=false;
	var fileSize=<?php echo $sizeLimit ?>;
	var fileTypeExts='<?php echo $fileExt ?>';
	var bgimage='<?php echo $GLOBALS['WWW'] ?>include/js/doyoupload/add.gif';
	var siteurl='<?php echo $GLOBALS['WWW'] ?>';
	var upurl='<?php echo $GLOBALS['G_DY']['url']["url_path_base"]  ?>';
	var updata='?c=<?php echo $uploadfile;?>&a=m_upload&molds=<?php echo $molds;?>&aid=<?php echo $aid;?>&tid=<?php echo $tid;?>&isfiles=Filedata&inputid=<?php echo $inputid;?>&hand=<?php echo $hand;?>&session_id=<?php echo session_id();?>';
	return [multi,fileSize,fileTypeExts,bgimage,siteurl,upurl,updata];
}
function doyocms_upload_onOPEN(date){
	$(".info").empty();
	$(".info").append('<li><p class="loading"><img src="include/js/doyoupload/loading.gif" /></p><p class="file"><span>'+date+'</span><br /><span class="progress"></span></p><p class="cancel"><a onclick="doyocms_upload_gocancel('+"'"+date+"'"+',1);"></a></p></li>');
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
}
function doyocms_upload_onProgress(info){
	var html;
	if(info[0]==100){
		html="正在处理图片...";
		$("span:contains('"+info[2]+"')").next().html("正在处理图片...")
	}else{
		html="进度：<strong>"+info[0]+"%</strong> ("+info[1]+")";
	}
	$(".name").html(info[2]);
	$(".progress").html(html);
}
function doyocms_upload_onCompleteData(info){
	var strs=info[0].split(",");
	var tt=info[0].substring(0,3);
	$(".info").empty();
	if(tt.indexOf('0,')==-1){
		$(".info").append(strs[0]);
	}else{
		$(window.parent.document).find("#<?php echo $inputid ?>").attr("value",'<?php echo $GLOBALS['WWW'];?>'+strs[1]);
	}
}
function doyocms_upload_err(err){
	alert(err);
}
</script>
<style type="text/css">
body{ margin:0; font-size:12px;}
ul,li,p{margin: 0; padding: 0; border:0;list-style: none;}
.swf{ float:left;}
.info{ float:left;font-size:12px; line-height:28px; padding-left:10px;}
.info li{height:28px; clear:both}
.info li span{width:100px;height:28px; overflow:hidden;}
.info p{ float:left;height:28px; overflow:hidden;}
.info .loading{padding:3px 5px 5px 0;}
.info .file{padding-right:5px;font-size:10px; line-height:13px; font-family:Verdana, Geneva, sans-serif;}
.info .cancel{width:28px;}
.info .cancel a{float:left;width:28px;height:28px; cursor:pointer; background:url(include/js/doyoupload/cancel.gif) no-repeat 0 6px;}
</style>
</head>

<body>
<div class="swf">
<object id="upload_swf" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="80" height="26"><param name="movie" value="include/js/doyoupload/doyo_upload.swf?date=<?php echo time(); ?>" /><param name="quality" value="high" /><param name="wmode" value="opaque" /><param name="swfversion" value="9.0.0" /><!--[if !IE]>--><embed src="include/js/doyoupload/doyo_upload.swf?date=<?php echo time(); ?>" name="upload_swf" quality="high" wmode="opaque"  swfversion="9.0.0" type="application/x-shockwave-flash"  width="80" height="26"></embed><!--<![endif]--></object>
</div>
<ul class="info"></ul>
<script type="text/javascript">
swfobject.registerObject("upload_swf");
</script>
</body>
</html>
