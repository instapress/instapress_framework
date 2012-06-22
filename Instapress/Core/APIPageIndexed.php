<?php


class Instapress_Core_APIPageIndexed
{
	function __construct()
	{
	}	
	
	function yahooPageIndexed($searchWord)
	{
		$ch = curl_init("http://boss.yahooapis.com/ysearch/web/v1/".$searchWord."?appid=oggfUcjV34EGR5mlK0OmQss.4piO_ZR8YVH8v2qmLIjAI3yWwv.IL.7g7yL9JBDn.v9v140-&format=json");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		$arrData = json_decode($curlResult);		
		return($arrData);
	}
	
	function googlePageIndexed($url)
	{
		$url = "http://ajax.googleapis.com/ajax/services/search/web?q=".$url."&v=1.0";
		$resp = file_get_contents( $url );
		if( $resp === false )
		{
			return 0;
		}
		
		$resp = json_decode($resp,true);
		$googlePI = $resp['responseData']['cursor']['estimatedResultCount'];
		
		return $googlePI;
	}
	
	function msnPageIndexed($searchWord)
	{
		$ch = curl_init("http://api.bing.net/json.aspx?AppId=0A04C115B2F79A0F563CB7B60121DFA10DA2C607&Query=".$searchWord."&Sources=Web");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		$arrData = json_decode($curlResult);
		print_r($arrData);
		return($arrData);
	}
}