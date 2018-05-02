<?php
	namespace Lib\Core;
	class Reptile extends \Threaded
	{
		private $url;
		#定义不允许爬取的后缀
		private $ext = ["jpg","gif","png","bmp","pdf","mp4","mp3","doc","rar","docx","xls","xlsx","zip","7z","apk"];
        private $Rex = "/\<\s?a[\s?\w=['|\"]?.*['|\"]?]?href=['|\"]?([^\"'><\s]*)['|\"]?[\s?\w=['|\"]?.*['|\"]?]?\s?\>[[\x80-\xff]?|.?]?\<\s?\/a\s?\>|\<\s?from[\s?\w=['|\"]?.*['|\"]?]?action=['|\"]?([^\"'><]*)['|\"]?[\s?\w=['|\"]?.*['|\"]?]?\s?\>.?\<\s?\/from\s?\>|\<\s?iframe[\s?\w=['|\"]?.*['|\"]?]?\s?src=[\"|']?([^\"'><]*)[\"|']?[\s?\w=['|\"]?.*['|\"]?]?\s?\>.?\<\s?\/iframe\s?\>/i";#定义正则表达式
        private $istrue = false;
        private $host;
        private $reptile_dir;

		public function __construct($url)
        {
			if(!empty($url))
			{
				$this->url = $url;
                $this->host = explode("/",$url)[2];
                $this->reptile_dir ="./data/{$this->host}/reptile/";
			}
		}

		public function run()
        {
            $do_while = true;
            do{
                $crawl = [];
                $crawled = [];
                $url_all = [];
                if(is_dir($this->reptile_dir))
                {
                    if(is_file($this->reptile_dir."crawled.txt"))
                    {
                        $crawled_data = array_filter(explode("\r\n",file_get_contents($this->reptile_dir."crawled.txt")));
                    }
                    else
                    {
                        $crawled_data = [];
                    }
                    foreach(scandir($this->reptile_dir) as $val)
                    {
                        if($val!="." && $val!=".." && $val != "crawled.txt")
                        {
                            if(!in_array($val,$crawled_data))
                            {
                                $url_all[$val] = array_filter(explode("\r\n",file_get_contents($this->reptile_dir.$val)));
                            }
                        }
                    }
                }
                else
                {
                    print "[+] {$this->host} A new crawl is under way\n";
                    $url_all[""] = [$this->url];
                }
                if(!empty($url_all))
                {
                    sleep(2);
                    foreach($url_all as $file_name=>$u)
                    {
                        $res = $this->worker->rolling_curl($u);
                        if($content = $this->is_status($res))
                        {
                            $this->crawl($content);
                        }
                        $this->put_contents("crawled.txt",$file_name);
                        print "[*] {$this->host} ".(count(scandir($this->reptile_dir))-3)." subdomains crawled\n";
                    }
                }
                $crawled_data = array_filter(explode("\r\n",file_get_contents($this->reptile_dir."crawled.txt")));
            }while($do_while);
            $this->istrue = true;
        }

        public function get_true()
        {
            return $this->istrue;
        }

        private function crawl($content)
        {
                foreach($content as $url => $val)
                {
                        if(preg_match_all($this->Rex,$val,$Rex_url))
                        {
                                if(count($Rex_url) == 4)
                                {
                                        unset($Rex_url[0]);
                                        $Crawled_url = $this->add_host($this->url,$this->array_not_empty($Rex_url));
                                        foreach($Crawled_url as $u)
                                        {
                                            $this->put_contents(explode("/", $u)[2].".txt",$u);
                                        }
                                        if($urls = $this->check_host(explode("/", $url)[2],$Crawled_url))
                                        {
                                            $this->put_contents(explode("/", $url)[2].".txt",$urls);
                                            print "[+] url:{$url}\n";
                                            print "[*] Currently crawls to ".count($urls)." records\n";
                                        }
                                }
                        }
                }
        }
        private function check_host($host,$crawled_url)
        {
                $urls = [];
                if(is_array($crawled_url))
                {
                        foreach($crawled_url as $curl)
                        {
                            if($host == explode("/", $curl)[2])
                            {
                                array_push($urls,$curl);
                            }
                        }
                }
                else
                {
                        if(strstr(explode("/", $crawled_url)[2],$host[1]))
                        {
                            $urls = $crawled_url;
                        }
                }
                return $urls;
        }
        private function add_host($host,$array)
        {
                $host = explode("/",$host);
                foreach($host as $key => $val)
                {
                        if($key>2)
                        {
                            unset($host[$key]);
                        }
                }
                $host = implode("/", $host);
                $url = [];
                foreach($array as $val)
                {
                        if(preg_match('/((http|https):\/\/({\d}{1,3}\.?){4})/i',$val))
                        {
                                array_push($url,$val);
                        }
                        if(strpos($val,"/") == 0)
                        {
                                $val = $host."/".$val;
                        }
                        if($count = strpos($val,"#"))
                        {
                                $val = substr($val,0,$count);
                        }
                        if(!strstr($val,"https") && !strstr($val,"http") && strpos($val,"/")!=0)
                        {
                                $val = $host."/".$val;
                        }
                        if(strstr($val,explode(".", $host)[1]))
                        {
                                if(!in_array($this->Get_suffix($val),$this->ext))
                                {
                                        array_push($url,$val);
                                }
                        }
                }
                return array_unique($url);
        }

        private function Get_suffix($url)
        {
                $suffix_arr = explode(".",$url);
                return end($suffix_arr);
        }

        private function array_not_empty($array)
        {
                $url = [];
                foreach ($array as $value)
                {
                        $url = array_merge($url,array_filter($value,function($value){
                                if(!strstr($value,"javascript") && !strstr($value,"360") && !strstr($value,"qq") && !strstr($value,"weibo") && !strstr($value,"163") && !strstr($value,"@") && !empty($value))
                                {
                                        return true;
                                }
                        }));
                }
                return $url;
        }

        private function is_status($info=[])
        {
                $content = [];
                foreach($info as $key=>$val)
                {
                    $content[$key] = $info[$key]["results"];
                }
                return $content;

        }
        #文件写入函数
        private function put_contents($filename,$contents)
        {
                if(!is_dir("./data"))
                {
                        mkdir("./data");
                }
                if(!is_dir("./data/{$this->host}"))
                {
                        mkdir("./data/{$this->host}");
                }
                $data_dir = "./data/{$this->host}/reptile";
                if(!is_dir($data_dir))
                {
                        mkdir($data_dir);
                }
                $data_file = $data_dir."/".$filename;
                if(is_array($contents))
                {
                        foreach($contents as $url)
                        {
                                $this->put_contents($filename,$url);
                        }
                }
                else
                {
                        if(is_file($data_file))
                        {
                                $content = !empty(file_get_contents($data_file))?explode("\r\n",file_get_contents($data_file)):'';
                                if(!in_array($contents,$content))
                                {
                                        file_put_contents($data_file,$contents."\r\n",FILE_APPEND);
                                }
                        }
                        else
                        {
                                file_put_contents($data_file,$contents."\r\n",FILE_APPEND);
                        }
                }
        }
	}
?>