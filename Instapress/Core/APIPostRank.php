<?php


class Instapress_Core_APIPostRank
{
	function __construct($domain)
	{
		$ch = curl_init("http://api.postrank.com/v2/feed/info?appkey=$domain&id=$domain&format=json");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$this->curlResult = curl_exec($ch);
		curl_close($ch);
			
		$dataArray = json_decode($this->curlResult);
		//print_r($dataArray);echo "<br><hr>";
		if(!$dataArray->error)
		{
			echo "feedHash=".$this->feedHash = $dataArray->id;echo "<br>";echo "<br>";
			echo "".$this->error = False;echo "<br>";echo "<br>";
		}
		else
		{
			echo "".$this->error = $dataArray->error;echo "<br>";echo "<br>";
			echo "feedHash=".$this->feedHash = False;echo "<br>";echo "<br>";
		}
	}
	
	function getFeedHash()
	{
		return $this->feedHash;
	}
	
	function getErrorMsg()
	{
		return $this->error;
	}
	
	
	function getFilteredPosts($feedHash, $doamin, $level)
	{
		$ch = curl_init("http://api.postrank.com/v2/feed/$feedHash?format=json&appkey=$doamin&level=$level");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$this->curlResult = curl_exec($ch);
		curl_close($ch);
			
		$dataArray = json_decode($this->curlResult);
		//print_r($dataArray);
		return $dataArray;
	}
	
	
	function getTopPosts($feedHash, $doamin)
	{
		
		
		$ch = curl_init("http://api.postrank.com/v2/feed/$feedHash/topposts?appkey=$doamin&format=json");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$this->curlResult = curl_exec($ch);
		curl_close($ch);
			
		$dataArray = json_decode($this->curlResult);
		//print_r($dataArray);
		return $dataArray;
	}
}