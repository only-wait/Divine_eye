<?php
	namespace Lib\Core;
	class Portscan{
		private $host;
		private $ports = [21,22,23,25,79,80,110,135,137,138,139,143,443,445,1433,3306,3389];
		private $ports_msg = [21=>'Ftp',22=>'SsH',23=>'Telnet',25=>'Smtp',79=>'Finger',80=>'Http',110=>'Pop3',135=>'Location Service',137=>'Netbios-NS',138=>'Netbios-DGM',139=>'Netbios-SSN',143=>'IMAP',443=>'Https',445=>'Microsoft-DS',1433=>'MSSQL',3306=>'MYSQL',3389=>'Terminal Services'];
		private $scan_success=[];

		public function run($host)
		{
			$this->host = $host;
			foreach($this->ports as $port)
			{
				print "[+] Scanning port {$port}\n";
				$this->check_port($this->host,$port);
				$this->portscan_put_contents();
			}
		}

		private function check_port($ip, $port){
			$status = @fsockopen($ip, $port, $errno, $errstr, 1);
			if($status)
			{
				print "[*] Port {$port} opened serives {$this->ports_msg[$port]}\n";
				array_push($this->scan_success,"[*] Port {$port} opened serives {$this->ports_msg[$port]}");
			}
			else
			{
				print "[-] Port {$port} shut down serives {$this->ports_msg[$port]}\n";
			}
		}

		private function portscan_put_contents()
		{
			if(!empty($this->scan_success))
			{
				if(is_array($this->scan_success))
				{
					file_put_contents($this->host.".txt",implode("\r\n", $this->scan_success));
				}
			}
		}
	}
?>