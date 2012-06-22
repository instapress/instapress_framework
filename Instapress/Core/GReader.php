<?php


class Instapress_Core_GReader
{
	private $urlAuth = "https://www.google.com/accounts/ClientLogin";
	private $urlAtom = "http://www.google.com/reader/atom";
	private $email = "";
	private $password ="";
	private $postRequest = "";

	/*
	 * constructor
	 */
	public function __construct($email, $password)
	{
		$this->email = $email;
		$this->password = $password;
		$login = array("service" => "reader",
			  "continue" => "http://www.google.com/",
			  "Email" => $email, 
			  "Passwd" => $password,
			  "source" => "tech");			  
		$postRequest = "";
		foreach($login as $field => $value)
		{
			$postRequest .= $field . "=" . $value . "&";
		}
		$this->postRequest = $postRequest;
	}

	/*
	 * @returns the cookie
	 */
	public function getCookie()
	{
		ob_start();
		$ch = curl_init($this->urlAuth);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $this->postRequest);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec ($ch);
		curl_close ($ch);
		$loginResult = ob_get_contents();
		ob_end_clean();
		if ($i = strstr($loginResult, "LSID"))
		{
			$SID = substr($loginResult, 0,
			(strlen($loginResult) - strlen($i)));
			$SID = rtrim(substr($SID, 4, (strlen($SID) - 4)));
		}
		$cookie = "SID=" . $SID ."; domain=.google.com; path=/; expires=1600000000";
		return $cookie;
	}

	/*
	 * @returns array of all items
	 */
	public function getItems()
	{
		$cookie = self::getCookie();
		$action = $this->urlAtom ."/user/-/state/com.google/reading-list";
		ob_start();
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $action);
		curl_setopt ($ch, CURLOPT_HTTPGET, true);
		curl_setopt ($ch, CURLOPT_COOKIE, $cookie);
		curl_exec ($ch);
		curl_close ($ch);
		$xml = ob_get_contents();
		ob_end_clean();
		return Helper::xml2array($xml);
	}
}