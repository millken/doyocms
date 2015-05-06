function labels(t,c,w,o){
	$(t).nextAll(c).addClass("current");
	$(t).prevAll(c).addClass("current");
	$(t).removeClass("current");
	$("."+w).addClass("none");
	$("#"+o).removeClass("none");
}
function winbox(c,w){
	$("#winbox").remove();
	if(w=='')w=400;
	$("body").append('<div id="winbox" style="width:'+w+'px;">'+c+'</div>');
	winbox_tl();
	$(window).bind("resize scroll", function(){
		winbox_tl()
	});
}
function winbox_tl(){
	$t=Math.round(($(window).height()-$("#winbox").height())/2);
	$l=Math.round(($(window).width()-$("#winbox").width())/2);
	$("#winbox").css("top",$t+$(document).scrollTop());
	$("#winbox").css("left",$l+$(document).scrollLeft());
}
function closebox(){
	$("#winbox").remove();
}
function member_login(id,template){
	$.ajax({
		url: site_dir+'index.php?c=ajax&a=member_login',type: 'post',
		cache: false,
		data: "template="+template,
		success: function(html){
			$("#"+id).html(html);
		}
	});
}
function ajax_comment(id,molds,aid,page,template){
	$.ajax({
		url: site_dir+'index.php?c=ajax&a=comment',type: 'post',
		cache: false,
		data: "id="+id+"&template="+template+"&molds="+molds+"&aid="+aid+"&comment_page="+page,
		success: function(html){
			$("#"+id).html(html);
		}
	});
}
function ajax_record(id,aid,page,template){
	$.ajax({
		url: site_dir+'index.php?c=ajax&a=record',type: 'post',
		cache: false,
		data: "template="+template+"&aid="+aid+"&record_page="+page,
		success: function(html){
			$("#"+id).html(html);
		}
	});
}
function mycart_info(id,template){
	$.ajax({
		url: site_dir+'index.php?c=ajax&a=mycart',type: 'post',
		cache: false,
		data: "template="+template,
		success: function(html){
			mycart_total();
			$("#"+id).html(html);
		}
	});
}
function mycartdel(id){
	if(confirm('确认删除购物车中的本商品吗？')==true) {
		$.ajax({
			type: "POST",
			url: site_dir+"index.php?c=pay&a=cartdel",
			async: false,
			cache: false,
			data: "id="+id,
			success: function(msg){
				if(msg=='ok'){
					mycart_total();
					$("#cart"+id).remove();
				}else{
					alert('操作失败，请稍后再试。');
				}
			}
		});
	}
}
function mycart_total(){
	$.ajax({
		url: site_dir+'index.php?c=ajax',type: 'post',
		cache: false,
		data: "a=mycart_total",
		success: function(html){
			$("#mycart_total").html(html);
		}
	});
}