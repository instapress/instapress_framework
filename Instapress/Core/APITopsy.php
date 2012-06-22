<?php

class Instapress_Core_APITopsy
{
	function __construct()
	{
			
	}
	
	function authorInformation($authorName)
	{
		$ch = curl_init("http://otter.topsy.com/authorinfo.json?url=http://twitter.com/$authorName");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function authorSearch($string)
	{
		$ch = curl_init("http://otter.topsy.com/authorsearch.json?q=$string");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function authorLinkPosts($authorName)
	{
		$ch = curl_init("http://otter.topsy.com/linkposts.json?url=http://twitter.com/$authorName");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function authorLinkPostCount($authorName)
	{
		$ch = curl_init("http://otter.topsy.com/linkpostcount.json?url=http://twitter.com/$authorName");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function authorProfileSearch($authorSearch)
	{
		$ch = curl_init("http://otter.topsy.com/profilesearch.json?q=$authorSearch");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function relatedUrl($url)
	{
		$ch = curl_init("http://otter.topsy.com/related.json?url=$url");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function authorSearchResult($authorName)
	{
		$ch = curl_init("http://otter.topsy.com/search.json?q=$authorName");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function authorProfileSearchCount($authorSearch)
	{
		$ch = curl_init("http://otter.topsy.com/searchcount.json?q=$authorSearch");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function urlPostStats($url)
	{
		#20110405: Mayank Gupta make change in api for access tweet count please refer http://code.google.com/p/otterapi/wiki/Resources#/stats.
		//$ch = curl_init("http://otter.topsy.com/stats.json?q=$url");
		$ch = curl_init("http://otter.topsy.com/stats.json?url=$url");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);	
		return $dataArray;		
	}
	
	function urlPostTags($url)
	{
		$ch = curl_init("http://otter.topsy.com/tags.json?url=$url");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function trackbacks($url)
	{
		$ch = curl_init("http://otter.topsy.com/trackbacks.json?url=$url");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function trendingTerms($term)
	{
		$ch = curl_init("http://otter.topsy.com/trending.json?q=$term");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	
	function urlInfo($url)
	{
		$ch = curl_init("http://otter.topsy.com/urlinfo.json?url=$url");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);		
		return $dataArray;		
	}
	/*
	 * twitter followers
	 *
	
	function getTwitterFollower($twitterUserName)
	{
		$resp = @file_get_contents( 'http://twitter.com/users/show/' . $twitterUserName );
		$match = explode( '<followers_count>', $resp );
		$match = explode('</followers_count>', $match[1] );
		$followerCount = $match[0];
		return $followerCount;		
	}
	*/	
}
	

