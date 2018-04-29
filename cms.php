<?php
	require_once "./Lib/Core/Cmsrecognition.class.php";
	$cms = new Cmsrecognition;
	print_r($cms->run(["http://www.lenr.com.cn","http://www.only-wait.cn","http://localhost/emlog/","http://localhost/phpcms/","http://localhost/dz/","http://sublimetext.iaixue.com","http://www.gzsec.org","http://we.dengsoft.com"]));
?>