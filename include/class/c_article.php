<?php
class c_article extends syModel
{
	var $pk = "id";
	var $table = "article";
	var $verifier = array(
		"rules" => array(
			'tid' => array(
				'notnull' => TRUE,
			),
			'title' => array(
				'notnull' => TRUE,
			),
			'mgold' => array(
				'isgold' => TRUE,
			),
			'htmlfile' => array(
				'isfile' => TRUE,
			),
		),
		"messages" => array(
			'tid' => array(
				'notnull' => '请选择栏目',
			),
			'title' => array(
				'notnull' => '标题不能为空',
			),
			'mgold' => array(
				'isgold' => '请输入正确的价格，只能包含0-9数字及小数点',
			),
			'htmlfile' => array(
				'isfile' => '文件名只能为英文、数字、下划线、中划线组成',
			),
		)
	);
}