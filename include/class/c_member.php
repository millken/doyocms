<?php
class c_member extends syModel
{
	var $pk = "id";
	var $table = "member";
	var $verifier = array(
		"rules" => array(
			'user' => array(
				'notnull' => TRUE,
				'isabcnocn' => TRUE,
				'maxlength' => 20,
			),
			'pass' => array(
				'notnull' => TRUE,
			),
		),
		"messages" => array(
			'user' => array(
				'notnull' => '用户名不能为空',
				'isabcnocn' => '用户名只能包含数字,英文,中文,下划线',
				'maxlength' => '用户名不能大于20字',
			),
			'pass' => array(
				'notnull' => '请填写登录密码',
			),
		)
	);
}