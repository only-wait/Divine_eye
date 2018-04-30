<?php
	define("ROOT",str_replace('\\','/',__dir__));
	require_once ROOT."/Lib/Bugscan.php";
	ini_set('display_errors', 0);
	if($argc == 3)
	{
		if(intval($argv[2])>0)
		{
			Bugscan::start(explode(",", $argv[1]),$argv[2]);
		}
		else
		{
			exit("[-] Threads can only enter numbers");
		}
	}
	else
	{
		exit("[-] Usage:php index.php [url1,url2,...,urln] threads_number");
	}
?>