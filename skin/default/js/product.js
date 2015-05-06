(function () {
    $.fn.infiniteCarousel = function () {
        function repeat(str, n) {
            return new Array( n + 1 ).join(str);
        }
        return this.each(function () {
            var $wrapper = $('> div', this).css('overflow', 'hidden'),
                $slider = $wrapper.find('> ul').width(9999),
                $items = $slider.find('> li'),
                $single = $items.filter(':first')
                
                singleWidth = $single.outerWidth(),
                visible = Math.ceil($wrapper.innerWidth() / singleWidth),
                currentPage = 1,
                pages = Math.ceil($items.length / visible);

            if ($items.length % visible != 0) {
                $slider.append(repeat('<li class="empty" />', visible - ($items.length % visible)));
                $items = $slider.find('> li');
            }

            $items.filter(':first').before($items.slice(-visible).clone().addClass('cloned'));
            $items.filter(':last').after($items.slice(0, visible).clone().addClass('cloned'));
            $items = $slider.find('> li');
            $wrapper.scrollLeft(singleWidth * visible);

            function gotoPage(page) {
                var dir = page < currentPage ? -1 : 1,
                    n = Math.abs(currentPage - page),
                    left = singleWidth * dir * visible * n;
                
                $wrapper.filter(':not(:animated)').animate({
                    scrollLeft : '+=' + left
                }, 500, function () {
                    if (page > pages) {
                        $wrapper.scrollLeft(singleWidth * visible);
                        page = 1;
                    } else if (page == 0) {
                        page = pages;
                        $wrapper.scrollLeft(singleWidth * visible * pages);
                    }
                    
                    currentPage = page;
                });
            }
            $wrapper.after('<a href="#" class="arrow back"></a><a href="#" class="arrow forward"></a>');
            $('a.back', this).click(function () {
                gotoPage(currentPage - 1);
                return false;
            });
            
            $('a.forward', this).click(function () {
                gotoPage(currentPage + 1);
                return false;
            });
            
            $(this).bind('goto', function (event, page) {
                gotoPage(page);
            });
            $(this).bind('next', function () {
                gotoPage(currentPage + 1);
            });
        });
    };
})(jQuery);

$(document).ready(function () {
    var autoscrolling = true; 
    $('.product').infiniteCarousel().mouseover(function () {
        autoscrolling = false;
    }).mouseout(function () {
        autoscrolling = true;
    });
    
    setInterval(function () {
        if (autoscrolling) {
            $('.product').trigger('next');
        }
    }, 5000);
	
	$("#newsmenua").click(function(){
		$("#newsa").css("display","block");
		$("#newsb").css("display","none");
		$(".newsmenu").removeClass("newsmenu_a");
	});
	$("#newsmenub").click(function(){
		$("#newsb").css("display","block");
		$("#newsa").css("display","none");
		$(".newsmenu").addClass("newsmenu_a");
	});
});

$(function(){
     var len  = $("#top_banner .num > li").length;
	 var index = 0;
	 var adTimer;
	 $("#top_banner .num li").mouseover(function(){
		index  =   $("#top_banner .num li").index(this);
		showImg_top(index);
	 }).eq(0).mouseover();
	 //滑入 停止动画，滑出开始动画.
	 $('#top_banner').hover(function(){
			 clearInterval(adTimer);
		 },function(){
			 adTimer = setInterval(function(){
			    showImg_top(index)
				index++;
				if(index==len){index=0;}
			  } , 3000);
	 }).trigger("mouseleave");
	 
     var len2  = $("#new_banner .num > li").length;
	 var index2 = 0;
	 var adTimer2;
	$("#new_banner .num li").mouseover(function(){
		index2  =   $("#new_banner .num li").index(this);
		showImg_news(index2);
	 }).eq(0).mouseover();
	 //滑入 停止动画，滑出开始动画.
	 $('#new_banner').hover(function(){
			 clearInterval(adTimer2);
		 },function(){
			 adTimer2 = setInterval(function(){
			    showImg_news(index2)
				index2++;
				if(index2==len2){index2=0;}
			  } , 3000);
	 }).trigger("mouseleave");
})
// 通过控制top ，来显示不同的幻灯片
function showImg_top(index){
    var adHeight = $("#top_banner").height();
	$("#top_banner .slider").stop(true,false).animate({top : -adHeight*index},1000);
	$("#top_banner .num li").removeClass("on")
	.eq(index).addClass("on");
}
function showImg_news(index){
    var adHeight = $("#new_banner").height();
	$("#new_banner .slider").stop(true,false).animate({top : -adHeight*index},1000);
	$("#new_banner .num li").removeClass("on")
	.eq(index).addClass("on");
}