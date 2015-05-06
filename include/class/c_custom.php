<?php
class c_custom extends syModel
{
	var $pk = "id";
	var $table = "custom";
	var $verifier = array(
		"rules" => array(
			'template' => array(
				'notnull' => TRUE,
			),
			'dir' => array(
				'isdir' => TRUE,
			),
			'file' => array(
				'notnull' => TRUE,
				'isdirfile' => TRUE,
			),
		),
		"messages" => array(
			'template' => array(
				'notnull' => '请输入页面模板',
			),
			'dir' => array(
				'isdir' => '目录只能包括英文、数字、下划线、中划线和/',
			),
			'file' => array(
				'notnull' => '请输入文件名',
				'isdirfile' => '文件名只能包括英文、数字、下划线、中划线和点',
			),
		)
	);
	
}