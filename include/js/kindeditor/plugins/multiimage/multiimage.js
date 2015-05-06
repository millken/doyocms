KindEditor.plugin('multiimage', function(K) {
	var editor = this, name = 'multiimage';
	editor.clickToolbar(name, function() {
		var dialog = K.dialog({
			width : 550,
			height : 300,
			title : '批量上传',
			body : '<div><iframe name="KindEditor_multiimage" id="KindEditor_multiimage" border="0" framespacing="0" frameborder="0" scrolling="no" width="550" height="300" src="?c=uploads&a=loadup&fileover=2&multi=1&isfiles=filedata&fileExt=*.jpg;*.jpeg;*.gif;*.png"></iframe></div>',
			closeBtn : {
				name : '关闭',
				click : function(e) {
					dialog.remove();
				}
			},
			yesBtn : {
				name : '全部插入',
				click : function(e) {
					imgs=$("#KindEditor_multiimage").contents().find("#onAll").html();
					if(imgs.length){
						editor.insertHtml(imgs);
						dialog.remove();
					}else{alert('文件上传未完成或没有找到文件');}
				}
			},
			noBtn : {
				name : '取消',
				click : function(e) {
					dialog.remove();
				}
			}
		});
	});
});
