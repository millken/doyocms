<?php
class c_ads extends syModel
{
	var $pk = "id";
	var $table = "ads";
	var $verifier = array(
		"rules" => array(
			'taid' => array(
				'notnull' => TRUE,
			),
			'name' => array(
				'notnull' => TRUE,
			),
		),
		"messages" => array(
			'taid' => array(
				'notnull' => '所属广告位不能为空',
			),
			'name' => array(
				'notnull' => '广告名称不能为空',
			),
		)
	);
	
}