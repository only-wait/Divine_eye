<?php
	namespace Lib\Core;
	class Reptile extends \Threaded
	{
		private $urls=[];
		#定义不允许爬取的后缀
		private $ext = ["jpg","gif","png","bmp","pdf","mp4","mp3","doc","rar","docx","xls","xlsx","zip","7z","apk"];
        private $Rex = "/\<\s?a[\s?\w=['|\"]?.*['|\"]?]?href=['|\"]?([^\"'><\s]*)['|\"]?[\s?\w=['|\"]?.*['|\"]?]?\s?\>[[\x80-\xff]?|.?]?\<\s?\/a\s?\>|\<\s?from[\s?\w=['|\"]?.*['|\"]?]?action=['|\"]?([^\"'><]*)['|\"]?[\s?\w=['|\"]?.*['|\"]?]?\s?\>.?\<\s?\/from\s?\>|\<\s?iframe[\s?\w=['|\"]?.*['|\"]?]?\s?src=[\"|']?([^\"'><]*)[\"|']?[\s?\w=['|\"]?.*['|\"]?]?\s?\>.?\<\s?\/iframe\s?\>/i";#定义正则表达式
		public function __construct($urls=[])
        {
			if(!empty($urls))
			{
				$this->urls = $urls;
			}
		}

		public function run()
        {
            $do_while = [];
            do{
                $url_all = [];
                $files = [];
                foreach($this->urls as $key => $url)
                {
                    $url_all[$url]='';
                    if(is_file("./data/".explode("/",$url)[2]."/reptile/other.txt"))
                    {
                        print "[+] Gets the ".explode("/",$url)[2]." directory cache file in other.txt\n";
                        $files[$url] = fopen("./data/".explode("/",$url)[2]."/reptile/other.txt","r");
                    }
                    else
                    {
                        $url_all[$url][] = $url;
                    }
                }
                if(!empty($files))
                {
                    $istrue=false;
                    foreach($files as $url => $file)
                    {
                        $data_dir = "./data/".explode("/",$url)[2]."/reptile";
                        while(!feof($file)) {
                            if(flock($file, LOCK_EX)) {
                                $data = fgets($file);
                                $data = trim(str_replace(["\n","\r"],"",$data));
                                $Crawled_url = is_file($data_dir."/Crawled.txt")?array_filter(explode("\r\n",file_get_contents($data_dir."/Crawled.txt"))):[];
                                if(!in_array($data,$Crawled_url))
                                {
                                    if(count($url_all[$url]) <= (1000/count($url_all)))
                                    {
                                        $url_all[$url][] = $data;
                                        print "[+] Classifying {$data} as a {$url} class\n";
                                    }
                                }
                                else
                                {
                                    print "[-] {$data} crawled\n";
                                }
                                flock($file, LOCK_UN);
                            }
                        }
                        if($istrue)
                        {
                            break;
                        }
                        else
                        {
                            if(count($url_all,1) >= 1000)
                            {
                                $istrue = true;
                            }
                        }
                    }
                }
                if(!empty($url_all))
                {
                    foreach($url_all as $urlb => $urlc)
                    {
                        $res = $this->worker->rolling_curl($urlc);
                        if($content = $this->is_status($res))
                        {
                            $this->crawl($content,$urlb);
                        }
                        $this->put_contents("Crawled.txt",$urlc,$urlb);
                    }
                                
                }
                foreach($this->urls as $key => $url)
                {
                    if(is_file("./data/".explode("/",$url)[2]."/reptile/other.txt"))
                    {
                        $Crawl_url = array_filter(explode("\r\n",file_get_contents("./data/".explode("/",$url)[2]."/reptile/other.txt")));
                    }
                    if(is_file("./data/".explode("/",$url)[2]."/reptile/Crawled.txt"))
                    {
                        $Crawled_url = array_filter(explode("\r\n",file_get_contents("./data/".explode("/",$url)[2]."/reptile/Crawled.txt")));
                    }
                    if($Crawl_url == $Crawled_url)
                    {
                        $do_while[] = $url;
                    }
                }
                unset($url_all);
            }while(count($do_while)<count($this->urls));
        }

        private function crawl($content,$urla)
        {
                foreach($content as $url => $val)
                {
                        if(preg_match_all($this->Rex,$val,$Rex_url))
                        {
                                if(count($Rex_url) == 4)
                                {
                                        unset($Rex_url[0]);
                                        $Crawled_url = $this->add_host($url,$this->array_not_empty($Rex_url));
                                        $this->put_contents("other.txt",$Crawled_url,$urla);
                                        $urls = [];
                                        if($urls = $this->check_host(explode("/", $url)[2],$Crawled_url))
                                        {
                                                $this->put_contents(explode("/", $url)[2].".txt",$urls,$urla);
                                        }
                                        print "[+] url:{$url}\n";
                                        print "[*] Currently crawls to ".count($urls)." records\n";
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
                                if(!strstr($value,"javascript") && !strstr($value,"360") && !strstr($value,"qq") && !strstr($value,"weibo") && !strstr($value,"@") && !empty($value))
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
        private function put_contents($filename,$contents,$urla)
        {
                if(!is_dir("./data"))
                {
                        mkdir("./data");
                }
                if(!is_dir("./data/".explode("/",$urla)[2]))
                {
                        mkdir("./data/".explode("/",$urla)[2]);
                }
                $data_dir = "./data/".explode("/",$urla)[2]."/reptile";
                if(!is_dir($data_dir))
                {
                        mkdir($data_dir);
                }
                $data_file = $data_dir."/".$filename;
                if(is_array($contents))
                {
                        foreach($contents as $url)
                        {
                                $this->put_contents($filename,$url,$urla);
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