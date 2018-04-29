<?php
	Bugscan::func_load('Global');
	class Bugscan{
		function __construct($url)
		{
			$pool = new \Pool(127,"\Lib\Core\Request_http",array());
			$pool->submit(new \Lib\Core\Reptile($url));
			$pool->shutdown();
		}
		static public function start($url)
		{
			new Bugscan($url);
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