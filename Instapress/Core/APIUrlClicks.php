<?php

class Instapress_Core_APIUrlClicks
{
	function __construct($url)
	{
		$ch = curl_init("http://api.bit.ly/stats?version=2.0.1&format=json&shortUrl=$url&login=instablogs&apiKey=R_c934d002c0b5498556e4ee728cecf3b1");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$this->curlResult = curl_exec($ch);
		curl_close($ch);
			
		$dataArray = json_decode($this->curlResult);
		//print_r($dataArray);
		
		$this->clicks = $dataArray->results->clicks;
	}
	
	function numberOfClicks()
	{
		return $this->clicks;
	}
	
	
	
}