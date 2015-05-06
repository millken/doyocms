<?php
require("config.php");
$doyoConfig['view']['config']['template_dir'] = APP_PATH.'/template/'.$doyoConfig['ext']['view_themes'];
require(DOYO_PATH."/sys.php");
spRun();