<?php		

class Instapress_Core_GoogleAuth
{
	public $auth;	
	
	public function login($email, $password)
	{
		$ch = $this->curl_init("https://www.google.com/accounts/ClientLogin");
		curl_setopt($ch, CURLOPT_POST, true);
		
		$data = array(
			'accountType' => 'GOOGLE',
			'Email' => $email,
			'Passwd' => $password,
			'service' => 'analytics',
			'source' => ''
		);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		$this->auth = '';
		if($info['http_code'] == 200) 
		{
			preg_match('/Auth=(.*)/', $output, $matches);
			if(isset($matches[1])) 
			{
				$this->auth = $matches[1];
			}
		}
		return $this->auth != '';	
	}
	
	protected function curl_init($url) 
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if($this->auth) 
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: GoogleLogin auth=$this->auth"));
		}
		
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		return $ch;		
	}
	
	public function call($url) 
  	{
		$headers = array("Authorization: GoogleLogin auth=$this->auth");				
		$ch = $this->curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		$return = false;
		
		if($info['http_code'] == 200) 
		{
			$return = $output;
		}
		elseif($info['http_code'] == 400) 
		{
			trigger_error('Badly formatted request to the Google Analytics API; check your profile id is in the format ga:12345, dates are correctly formatted and the dimensions and metrics are correct', E_USER_WARNING);
		}
		elseif($info['http_code'] == 401) 
		{
			trigger_error('Unauthorized request to the Google Analytics API', E_USER_WARNING);
		}
		else 
		{
			trigger_error("Unknown error when accessing the Google Analytics API, HTTP STATUS {$info['http_code']}", E_USER_WARNING);
		}
		return $return;		
	}	
			
	public function getAccountDetail()
	{
		$xml = $this->call('https://www.google.com/analytics/feeds/accounts/default');
		return Helper::xml2array($xml);	
	}
	
	public function ListAccountDetail()
	{
		$x = $this->getAccountDetail();
		$e = $x['feed']['entry'];
	
		$id = array();
		$cid = count($e);
		for ( $i = 0; $i<$cid; $i++)
		{
			$id[$i]['title'] = $e[$i]['title'];
			$id[$i]['webPropertyId'] = $e[$i]['dxp:property']['3_attr']['value'];
			$id[$i]['profileId'] = $e[$i]['dxp:property']['2_attr']['value'];			
		}	
		return $id;
	}
	
	public function getProfileId($webPropertyId)
	{
		$x = $this->getAccountDetail();
		$e = $x['feed']['entry'];
	
		$profileId=0;
		$id = array();
		$cid = count($e);
		for ( $i = 0; $i<$cid; $i++)
		{
			$id[$i]['webPropertyId'] = $e[$i]['dxp:property']['3_attr']['value'];
			$id[$i]['profileId'] = $e[$i]['dxp:property']['2_attr']['value'];
			
			if($id[$i]['webPropertyId'] == $webPropertyId)
			{
				return $id[$i]['profileId'];
			}
		}		
	}
}	

	
	
?>