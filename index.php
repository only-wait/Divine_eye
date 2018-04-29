<?php
	define("ROOT",str_replace('\\','/',__dir__));
	require_once ROOT."/Lib/Bugscan.php";
	ini_set('display_errors', 0);
	if($argc == 2)
	{
		Bugscan::start(explode(",", $argv[1]));
	}
	else
	{
		exit("[-] Usage:php index.php [url1,url2,...,urln]");
	}
?>