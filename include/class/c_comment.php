<?php
class c_comment extends syModel
{
	var $pk = "cid";
	var $table = "comment";
	var $verifier = array(
		"rules" => array(
			'aid' => array(
				'notnull' => TRUE,
			),
			'molds' => array(
				'notnull' => TRUE,
			),
			'body' => array(
				'maxlength' => 500,
			),
			'reply' => array(
				'maxlength' => 500,
			),
		),
		"messages" => array(
			'aid' => array(
				'notnull' => '所属内容不能为空',
			),
			'molds' => array(
				'notnull' => '所属模块不能为空',
			),
			'body' => array(
				'maxlength' => '评论内容不能超过500字',
			),
			'reply' => array(
				'maxlength' => '回复内容不能超过500字',
			),
		)
	);
	
}