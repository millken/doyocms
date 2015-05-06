<?php
class c_special extends syModel
{
	var $pk = "sid";
	var $table = "special";
	var $verifier = array(
		"rules" => array(
			'name' => array(
				'notnull' => TRUE,
				'maxlength' => 50,
			),
			'molds' => array(
				'notnull' => TRUE,
				'isabcno' => TRUE,
				'maxlength' => 20,
			),
		),
		"messages" => array(
			'name' => array(
				'notnull' => '专题名称不能为空',
				'maxlength' => '专题名称不能大于50字',
			),
			'molds' => array(
				'notnull' => '请填写模块标识',
				'isabcno' => '模块标识必须为英文和数字，并且只能以英文开头',
				'maxlength' => '模块标识字数20以内',
			),
		)
	);
}