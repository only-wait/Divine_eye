<?php
	$content = [];
	unset($argv[0]);
	if(count($argv) >= 2)
	{
		$filename = $argv[$argc-2];
		unset($argv[$argc-2]);
	}
	if(count($argv) >= 1 || count($argv) == 0)
	{
		$action = !empty($argv[$argc-1])?$argv[$argc-1]:"select";
		unset($argv[$argc-1]);
	}
	if(count($argv) >= 2 && count($argv)%2==0)
	{
		if(!empty($argv))
		{
			$count = count($argv);
			$i=1;
			while($i<$count)
			{
				if(isset($argv[$i+1]))
				{
					$content[$argv[$i]] = $argv[$i+1];
				}
				$i += 2;
			}
		}
	}
	else
	{
		exit("[-] Usage:php index.php key value file_name [select|update|add] \n[-] the argument must be an even number\n");
	}
	if($action == "add")
	{
		if(!empty($content))
		{
			$sercontent = serialize($content);
			file_put_contents($filename,$sercontent);
		}
	}
	elseif($action == "update")
	{
		if(is_file($filename))
		{
			$unsercontent = unserialize(file_get_contents($filename));
			$conten = [];
			foreach($content as $key => $val)
			{
				if(!array_key_exists($key,$unsercontent))
				{
					$conten[$key] = $content[$key];
				}
			}
			$content = array_merge($unsercontent,$conten);
			$sercontent = serialize($content);
			file_put_contents($filename,$sercontent);
		}
		else
		{
			exit("[-] {$filename} does not exist");
		}
	}
	elseif($action == "select")
	{
		$unsercontent = unserialize(file_get_contents($filename));
		print_r($unsercontent);
	}
	else
	{
		exit("[-] {$action} is Unrecognized command");
	}
	
?>