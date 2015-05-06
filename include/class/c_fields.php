<?php
class c_fields extends syModel
{
	var $pk = "fid";
	var $table = "fields";
	var $verifier = array(
		"rules" => array(
			'molds' => array(
				'notnull' => TRUE,
				'isabcno' => TRUE,
				'maxlength' => 20,
			),
			'fieldsname' => array(
				'notnull' => TRUE,
				'maxlength' => 30,
			),
			'fields' => array(
				'notnull' => TRUE,
				'isabcno' => TRUE,
				'maxlength' => 20,
			),
			'fieldstype' => array(
				'notnull' => TRUE,
				'isabcno' => TRUE,
			),
		),
		"messages" => array(
			'molds' => array(
				'notnull' => '请填写模块标识',
				'isabcno' => '模块标识必须为英文、数字、下划线组合，并且只能以英文开头',
				'maxlength' => '模块标识字数20以内',
			),
			'fieldsname' => array(
				'notnull' => '请填写字段名称',
				'maxlength' => '字段名称字数30以内',
			),
			'fields' => array(
				'notnull' => '请填写字段标识',
				'isabcno' => '字段标识必须为英文、数字、下划线组合，并且只能以英文开头',
				'maxlength' => '字段标识字数20以内',
			),
			'fieldstype' => array(
				'notnull' => '字段类型不能为空',
				'isabcno' => '字段类型必须为英文、数字、下划线组合，并且只能以英文开头',
			),
		)
	);
}