<?php
	Bugscan::func_load('Global');
	class Bugscan{
		function __construct($url,$threads_number)
		{
			$pool = new \Pool($threads_number,"\Lib\Core\Request_http",array());
			$pool->submit(new \Lib\Core\Reptile($url));
			$pool->shutdown();
		}
		static public function start($url,$threads_number)
		{
			$threads_number = (intval($threads_number)>0)?intval($threads_number):1;
			new Bugscan($url,$threads_number);
		}

		static public function func_load($funcname)
		{
			if(is_file(ROOT."/Lib/Func/{$funcname}.php"))
			{
				require_once(ROOT."/Lib/Func/{$funcname}.php");
			}
		}
		
	}
?>