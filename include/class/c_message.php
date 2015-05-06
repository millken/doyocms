<?php
class c_message extends syModel
{
	var $pk = "id";
	var $table = "message";
	var $verifier = array(
		"rules" => array(
			'tid' => array(
				'notnull' => TRUE,
			),
			'title' => array(
				'notnull' => TRUE,
			),
		),
		"messages" => array(
			'tid' => array(
				'notnull' => '请选择栏目',
			),
			'title' => array(
				'notnull' => '标题不能为空',
			),
		)
	);
}