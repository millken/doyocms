<?php
require("../../../config.php");
require(DOYO_PATH."/sys.php");

$v = syClass('tenpay',null,'tenpay.php');
$g = $v->verify_get();
if($g) {
	$v->success($g);
	echo 'success';
}else{
	echo "fail";
}
?>