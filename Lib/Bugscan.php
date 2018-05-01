<?php
	Bugscan::func_load('Global');
	class Bugscan{
		function __construct($url,$threads_number)
		{
			$pool = new \Pool($threads_number,"\Lib\Core\Request_http",array());
			if(is_array($url))
			{
				foreach($url as $u)
				{
					$pool->submit(new \Lib\Core\Reptile($u));
					#$pool->submit(new \Lib\Core\Cmsrecognition($u));
				}
			}
			$pool->shutdown();
			$pool->collect(function($work){
			    return $work->get_true();
			});
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