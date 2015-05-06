<?php
require("config.php");
$doyoConfig['launch'] = array('router_prefilter' => array(array('syauser','check'),),);
$doyoConfig['ext']['view_admin']= 'admin';
$doyoConfig['view']['config']['template_dir'] = APP_PATH.'/source/admin/template';
$doyoConfig['controller_path'] = APP_PATH.'/source/admin';

require(DOYO_PATH."/sys.php");
import(APP_PATH.'/include/fun/fun_admin.php');
spRun();