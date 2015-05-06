<?php
class c_order extends syModel
{
	var $pk = "id";
	var $table = "order";
	var $verifier = array(
		"rules" => array(
			'unote' => array(
				'maxlength' => 500,
			),
			'rnote' => array(
				'maxlength' => 500,
			),
		),
		"messages" => array(
			'unote' => array(
				'maxlength' => '备注不能超过500字',
			),
			'rnote' => array(
				'maxlength' => '备注不能超过500字',
			),
		)
	);
	
}