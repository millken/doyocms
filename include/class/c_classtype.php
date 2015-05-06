<?php
class c_classtype extends syModel
{
	var $pk = "tid";
	var $table = "classtype";
	var $verifier = array(
		"rules" => array(
			'classname' => array(
				'notnull' => TRUE,
				'maxlength' => 50,
			),
			'molds' => array(
				'notnull' => TRUE,
				'isabcno' => TRUE,
				'maxlength' => 20,
			),
			'htmldir' => array(
				'isdir' => TRUE,
			),
			'htmlfile' => array(
				'isfile' => TRUE,
			),
		),
		"messages" => array(
			'classname' => array(
				'notnull' => '分类名称不能为空',
				'maxlength' => '分类名称不能大于50字',
			),
			'molds' => array(
				'notnull' => '请填写模块标识',
				'isabcno' => '模块标识必须为英文、数字、下划线组合，并且只能以英文开头',
				'maxlength' => '模块标识字数20以内',
			),
			'htmldir' => array(
				'isdir' => '生成目录只能为英文、数字、下划线、中划线和“/”组成',
			),
			'htmlfile' => array(
				'isfile' => '文件名只能为英文、数字、下划线、中划线组成',
			),
		)
	);
}