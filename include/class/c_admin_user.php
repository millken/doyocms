<?php
class c_admin_user extends syModel
{
	var $pk = "auid";
	var $table = "admin_user";
	var $verifier = array(
		"rules" => array(
			'auser' => array(
				'notnull' => TRUE,
				'isabcnocn' => TRUE,
				'maxlength' => 20,
			),
			'apass' => array(
				'notnull' => TRUE,
			),
		),
		"messages" => array(
			'auser' => array(
				'notnull' => '登录名称不能为空',
				'isabcnocn' => '登录名称只能为数字,英文,中文,下划线',
				'maxlength' => '登录名称不能大于20字',
			),
			'apass' => array(
				'notnull' => '请填写登录密码',
			),
		)
	);
}