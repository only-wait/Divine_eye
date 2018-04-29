<?php
	function __autoload($classname)
	{
		$classname = ROOT."/".str_replace("\\", "/", $classname).".class.php";
		if(is_file($classname))
		{
			require_once($classname);
		}
	}
?>