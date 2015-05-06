$(function(){
	$('#attribute span').click(function(){
		$(this).siblings().removeClass("c");
		$(this).toggleClass("c");
		if($(this).find("input[name=aprice]").attr("checked")==true){
			$(this).find("input[type=checkbox]").removeAttr("checked");
		}else{
			$(this).parent().find("input[type=checkbox]").removeAttr("checked");
			$(this).find("input[type=checkbox]").attr("checked","true");
		}
		price_p=parseFloat($("#price").val());
		$("#attribute span input[name='aprice']:checked").each(function(){
			price_p=price_p+parseFloat($(this).val());
		});
		$('.price').text((price_p).toFixed(2));
	});
	
	alls=$("#imgto .imgc ul li").length;
	goimages_li(1,1);
	timer=setInterval('goimages(-1,'+alls+',1)',5000);
	$('#imgto .imgc ul li').click(function () {
		ns=$('#imgto .imgc ul li').index($(this));
		goimages(ns,alls,0);
	});
	$('#imgto .imgl').click(function () {
		goimages_ul(1)
	});
	$('#imgto .imgr').click(function () {
		goimages_ul(2)
	});
	$('#imgto').mouseover(function () {
		clearInterval(timer);
	});
	$('#imgto').mouseout(function () {
		timer=setInterval('goimages(-1,'+alls+',1)',5000);
	});
})
function goimages_ul(up) {
	var left = $('#imgto .imgc ul').position();
	left=left.left;
	allleft=Math.ceil($("#imgto .imgc ul li").length/4);
	if(up==1){
		if(-left==(allleft-1)*260){toleft=0;}else{toleft=left-260;}
		$("#imgto .imgc ul").animate({left: toleft+'px'}, "slow");
	}else{
		if(left==0){toleft=-(allleft-1)*260;}else{toleft=left+260;}
		$("#imgto .imgc ul").animate({left: toleft+'px'}, "slow");
	}
}
function goimages(n,alls,aut) {
	if(n==-1){
		li=$('#imgto .imgc ul .the').next();
		n=$('#imgto .imgc ul li').index(li);
		if(n==-1){n=0;}
	}
	$('#imgto .imgc ul li').removeClass('the');
	$('#imgto .imgc ul li:eq('+n+')').addClass('the');
	goimages_li(n,aut);
	$('#imgto .big').fadeOut(500,function(){
		$('#imgto .big').html($('#imgto .imgc ul li:eq('+n+')').html());
	});
	$('#imgto .big').fadeIn(500);
}
function goimages_li(n,aut) {
	if(aut==1){
		if(!(n%4)){
		  goimages_ul(1);
		}
	}
	$('#imgto .imgc ul li').fadeTo(150, 0.5,function(){
		$('#imgto .imgc ul .the').fadeTo(150,1);
	});
}
function cartbox(id,gobak){
	winbox('<p class="t"><span onclick="closebox()">关闭</span>提示信息</p><p class="c">正在提交数据，请稍后...</p>',300);
	$.ajax({
		type: "POST",
		url: site_dir+"index.php?c=pay&a=cartadd&id="+id,
		async: false,
		cache: false,
		data: $('#goods').serialize(),
		success: function(msg){
			if(msg=='ok'){
				mycart_info('mycart_info','member/ajax_cart.html');
				winbox('<p class="t"><span onclick="closebox()">关闭</span>已成功加入购物车</p><p class="g"><a href="'+site_dir+'index.php?c=pay&cart=1">去购物车结算</a> <a href="#" onclick="closebox()">继续选购</a></p>',300);
			}else{
				var strs=msg.split(",");
				if(strs[0]=='err'){
					closebox();
					alert(strs[1]);
				}else{
					winbox('<p class="t"><span onclick="closebox()">关闭</span>您还没有登陆，请登陆后操作</p><p class="g"><a href="'+site_dir+'index.php?c=member&a=login&url='+gobak+'">立即登陆</a> <a href="'+site_dir+'index.php?c=member&a=reg&url='+gobak+'">注册会员</a></p>',300);
				}
			}
		}
	});
}