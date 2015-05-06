<?php
class c_labelcus extends syModel
{
	var $pk = "id";
	var $table = "labelcus";
	var $verifier = array(
		"rules" => array(
			'name' => array(
				'notnull' => TRUE,
			),
		),
		"messages" => array(
			'name' => array(
				'notnull' => '调用名称',
			),
		)
	);
	
}