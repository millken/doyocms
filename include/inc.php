<?php
return array(
	'mode' => 'debug', //debug 开启php错误提示
	'sp_core_path' => DOYO_PATH, 
	'sp_drivers_path' => DOYO_PATH, 
	'sp_include_path' => array( DOYO_PATH.'/ext' ),
	
	'auto_load_controller' => array('syArgs'), 
	'auto_load_model' => array('syPager','syVerifier','syCache'), 
	'allow_trace_onrelease' => FALSE, 
	
	'sp_error_show_source' => 5, 
	'sp_error_throw_exception' => FALSE, 
	'allow_trace_onrelease' => FALSE, 
	'notice_php' => DOYO_PATH."/notice.php", 
	
	'inst_class' => array(), 
	'import_file' => array(), 
	'sp_access_store' => array(), 
	'view_registered_functions' => array(), 

	'default_controller' => 'index', 
	'default_action' => 'index',  
	'url_controller' => 'c',  
	'url_action' => 'a',  

	'auto_session' => TRUE, 
	'dispatcher_error' => "syError('route Error');", 
	'auto_sp_run' => FALSE, 
	
	'sp_cache' => DOYO_PATH.'/cache/tmp',
	'sp_session' => DOYO_PATH.'/cache/ses',
	'sp_app_id' => 'sy',
	'controller_path' => APP_PATH.'/source', 
	'model_path' => DOYO_PATH.'/class', 

	'url' => array( 
		'url_path_info' => FALSE, 
		'url_path_base' => '', 
	),
	
	'db' => array(
		'driver' => 'mysql',
		'persistent' => FALSE,
	),
	'db_driver_path' => '',
	'db_spdb_full_tblname' => FALSE,
	
	'view' => array( 
		'enabled' => TRUE, 
		'config' =>array(
			'template_dir' => APP_PATH.'/template',
			'template_tpl' => DOYO_PATH.'/cache/tpl',
		),
		'engine_name' => 'templatedoyo', 
		'engine_path' => DOYO_PATH.'/Template.php', 
	),

		
	'html' => array( 
		'enabled' => TRUE, 
		'safe_check_file_exists' => FALSE, 
	),
	
	'lang' => array(), 
		
	'include_path' => array(
		DOYO_PATH.'/fun',
	),
	
	'vercode' => 1,
	
	'rewrite' => array( 
		'rewrite_open' => 0,
		'rewrite_dir' => '/',
		'rewrite_article' => '{article}/{file}{page}.html',
		'rewrite_article_type' => '{article}/{type}/{tid}-{page}.html',
		'rewrite_product' => '{product}/{file}{page}.html',
		'rewrite_product_type' => '{product}/{type}/{tid}-{page}.html',
		'rewrite_channel' => '{channel}/{molds}/{file}{page}.html',
		'rewrite_channel_type' => '{channel}/{type}/{tid}-{page}.html',
		'rewrite_message_type' => '{message}/{type}/{tid}-{page}.html',
		'rewrite_special' => '{special}/{sid}-{page}.html',
		'rewrite_labelcus_custom' => '{file}.html',
	),
	
	'logistics' => array('快递'=>0,'EMS'=>0,),
);
