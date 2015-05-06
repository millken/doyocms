<?php
function verification($type){
	switch ($type){
		case 1:
			return  '<p></p><a href="http://bbs.wdoyo.com/f-2-1.html" target="_blank">求助问答</a><a href="http://wdoyo.cn" target="_blank">域名主机</a>';
		break;
		case 2:
			return ' | <a href="http://wdoyo.com/help/" target="_blank">帮助</a> | <a href="http://bbs.wdoyo.com/f-2-1.html" target="_blank">问题反馈</a> | <a href="http://wdoyo.cn" target="_blank">域名主机</a>';
		break;
		case 3:
			return '<tr><td width="80">友情提示：</td><td class="tdleft">如不会使用本系统或不会制作模板等，可选择官方服务，为您提供全程无忧服务，详询官方网站客服。<a href="http://wdoyo.com" target="_blank">http://wdoyo.com</a></td></tr><tr><td width="80">系统介绍：</td><td class="tdleft">DOYO通用建站程序</td></tr>  <tr><td width="80">系统版本：</td><td class="tdleft">DOYO <strong>'.syExt('version').'</strong> - '.syExt('update').'</td></tr><tr><td width="80">官方网址：</td><td class="tdleft"><a href="http://wdoyo.com" target="_blank">http://wdoyo.com</a></td></tr><tr><td>快捷服务：</td><td class="tdleft"><a href="http://wdoyo.com/download" target="_blank">系统下载</a> | <a href="http://wdoyo.com/help" target="_blank">使用帮助</a> | <a href="http://wdoyo.com/service" target="_blank">商业服务</a> | <a href="http://wdoyo.cn" target="_blank">域名空间</a> | <a href="http://bbs.wdoyo.com/f-38-1.html" target="_blank">网页模板</a> | <a href="http://bbs.wdoyo.com/f-2-1.html" target="_blank" class="c">求助问答</a></td></tr><tr><td>Email：</td><td class="tdleft">hi@wdoyo.com</td></tr>';
		break;
		case 4:
			return '&nbsp;&nbsp;<a href="http://wdoyo.com" target="_blank">DoYo</a> 通用建站程序 版本'.syExt('version').' - '.syExt('update').' Powered by <a href="http://wdoyo.com" target="_blank">wdoyo.com</a> © 2010-2099';
		break;
		case 5:
			return '<a href="http://wdoyo.com" target="_blank">系统更新</a> | <a href="http://wdoyo.com/help/" target="_blank">使用帮助</a> | <a href="http://wdoyo.cn" target="_blank">域名主机</a>';
		break;
		case 6:
		return 'href="http://wdoyo.com" class="v" target="_blank"';
		break;
		case 7:
		return ' log';
		break;
		case 8:
		return '轻松建站';
		break;
	}
}
?>