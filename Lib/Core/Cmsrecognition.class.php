<?php
	namespace Lib\Core;
	class Cmsrecognition extends \Threaded
	{
		private $urls=[];
		private $cmsdic_url=[];
		private $cmsdic_root = "Cms-dictionary";
		private $cms_success = [];

		public function __construct($urls)
		{
			
		}
		#运行函数
		public function run($urls=[],$user_defined_dic=false)
		{
			if(!empty($user_defined_dic))
			{
				if(is_dir($user_defined_dic))
				{
					$this->cmsdic_root = $user_defined_dic;
				}
				else
				{
					exit("[-] {$user_defined_dic} is not a directory");
				}
			}
			if(!empty($urls) && is_array($urls))
			{
				$this->urls = $urls;
				$this->main();
			}
			else
			{
				return false;
			}
			return $this->cms_success;
		}

		#核心调用函数
		private function main()
		{
			$this->dic_foreach();
			foreach($this->urls as $url)
			{
				if($this->Request_http($url,true))
				{
					print $url."\n";
					$this->request_file($url);
				}
			}
		}
		#访问特有的一些文件
		private function request_file($url)
		{
			foreach($this->cmsdic_url as $cms_name => $dic)
			{
				foreach($dic as $file_name => $status)
				{
					if(intval($status)>0)
					{
						print $cms_name."\t{$status}\n";
						if($this->Request_http($url.$file_name,true) == $status)
						{
							array_push($this->cms_success,"[*] {$url} cms is {$cms_name}");
							return true;
						}
					}
					else
					{
						print $cms_name."\t{$status}\n";
						if(preg_match_all($status,$this->Request_http($url.$file_name),$string))
						{
							if($this->contain_str($string,$cms_name))
							{
								array_push($this->cms_success,"[*] {$url} cms is {$cms_name}");
								return true;
							}
						}
					}
				}
			}
		}
		#
		public function contain_str($str,$contain_str)
		{
			if(is_array($str))
			{
				foreach($str as $a)
				{
					return $this->contain_str($a,$contain_str);
				}
			}
			else
			{
				if(strstr($str,$contain_str))
				{
					return true;
				}
			}
		}
		#将cms字典目录遍历出来
		private function dic_foreach()
		{
			$cms_dir = scandir($this->cmsdic_root);
			foreach($cms_dir as $file)
			{
				if($file != "." && $file != ".." && strstr($file,".txt"))
				{
					$this->cmsdic_url[str_replace(".txt", "" , $file)] = array_filter(unserialize(file_get_contents($this->cmsdic_root."/{$file}")));
				}
			}
		}
	}
?>