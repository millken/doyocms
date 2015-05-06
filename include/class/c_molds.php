<?php
class c_molds extends syModel
{
	var $pk = "mid";
	var $table = "molds";
	var $verifier = array(
		"rules" => array(
			'molds' => array(
				'notnull' => TRUE,
				'isabcno' => TRUE,
				'maxlength' => 20,
			),
			'moldname' => array(
				'notnull' => TRUE,
				'maxlength' => 20,
			),
			't_index' => array(
				'notnull' => TRUE,
			),
			't_list' => array(
				'notnull' => TRUE,
			),
			't_listimg' => array(
				'notnull' => TRUE,
			),
			't_listb' => array(
				'notnull' => TRUE,
			),
			't_content' => array(
				'notnull' => TRUE,
			),
		),
		"messages" => array(
			'molds' => array(
				'notnull' => '请填写模块标识',
				'isabcno' => '模块标识必须为英文、数字、下划线组合，并且只能以英文开头',
				'maxlength' => '模块标识字数20以内',
			),
			'moldname' => array(
				'notnull' => '请填写模块名称',
				'maxlength' => '模块名称字数20以内',
			),
			't_index' => array(
				'notnull' => '模板文件名不能为空',
			),
			't_list' => array(
				'notnull' => '模板文件名不能为空',
			),
			't_listimg' => array(
				'notnull' => '模板文件名不能为空',
			),
			't_listb' => array(
				'notnull' => '模板文件名不能为空',
			),
			't_content' => array(
				'notnull' => '模板文件名不能为空',
			),
		)
	);
}