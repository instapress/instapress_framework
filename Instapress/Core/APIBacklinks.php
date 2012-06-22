<?php
class Instapress_Core_APIBacklinks
{
	function __construct()
	{		
	}
		
	function arrDataBackLinks($request)
	{
		#20110405: Mayank Gupta do some modification regard yahoo api implementation for backlinks. 
		//http://search.yahooapis.com/SiteExplorerService/V1/inlinkData?appid=YahooDemo&query=http://search.yahoo.com please refer link.
		//$ch = curl_init("http://search.yahooapis.com/SiteExplorerService/V1/inlinkData?appid=qCxdqWXIkY2K1saUIDtrW.PxBqW6uLvioMpUHHg7".$request);
		//echo "http://search.yahooapis.com/SiteExplorerService/V1/inlinkData?appid=qCxdqWXIkY2K1saUIDtrW.PxBqW6uLvioMpUHHg7&query=".$request."&output=json";
		$ch = curl_init("http://search.yahooapis.com/SiteExplorerService/V1/inlinkData?appid=qCxdqWXIkY2K1saUIDtrW.PxBqW6uLvioMpUHHg7&query=".$request."&output=json");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		$arrData = json_decode($curlResult);
		return($arrData);
	}
}