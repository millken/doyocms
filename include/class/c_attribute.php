<?php
class c_attribute extends syModel
{
	var $pk = "id";
	var $table = "attribute";
	var $verifier = array(
		"rules" => array(
			'name' => array(
				'notnull' => TRUE,
				'maxlength' => 50,
			),
		),
		"messages" => array(
			'name' => array(
				'notnull' => '名称不能为空',
				'maxlength' => '名称不能超过50字',
			),
		)
	);
	
}