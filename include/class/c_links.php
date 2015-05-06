<?php
class c_links extends syModel
{
	var $pk = "id";
	var $table = "links";
	var $verifier = array(
		"rules" => array(
			'name' => array(
				'notnull' => TRUE,
			),
		),
		"messages" => array(
			'name' => array(
				'notnull' => '链接名称不能为空',
			),
		)
	);
	
}