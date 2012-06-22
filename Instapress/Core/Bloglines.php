<?php


class Instapress_Core_Bloglines
{
	var $searchBase;
	var $stats = array();
	var $raw;

	/**
	 * Constructor for the Bloglines Stats Fetcher
	 *
	 * @param apikey     API key provided by Bloglines
	 * @param site       URI of the site to get stats on
	 * @param cacheTime  (Optional) Length of time in seconds to cache results.
	 * @return           Boolean.  True if stats retrieved, false otherwise.
	 */
	function Bloglines($user, $apikey, $site)
	{
		// Compose base of search URI to Bloglines API
		$this->searchBase = "http://www.bloglines.com/search?format=publicapi&apiuser={$user}&apikey={$apikey}&t=f&q=";

		// Add stats from site
		return $this->addUrl($site);
	}
	 
	/**
	 * Allows additional URLs for a site to be added to the search results.
	 *
	 * @param site       URI of the site to get stats on
	 * @param cacheTime  (Optional) Length of time in seconds to cache results.
	 */
	function addUrl($site)
	{
		// Compose URI to Bloglines API
		$uri = $this->searchBase . urlencode($site);
		// Open uri or fail
		try
			{
				$raw = $contents = @file_get_contents($uri);
			}
		catch(Exception $e)
			{
				$this->error = 'Could not contact bloglines';
			}
		if(!$raw) return false;
		$this->raw = $raw;
		// Read output into an XML DOM
			try
			{
				$doc = new SimpleXMLElement($raw);
			}
			catch(Exception $e)
			{
				$this->error = $e;
			}

		// Locate all site elements in DOM
		if(!$doc)
			{
				trigger_error("Unable to parse Bloglines info for ${site}", E_USER_NOTICE);
				return false;
			}
		$siteElements = $doc->xpath('/publicapi/resultset[@set="main"]/result/site');
		if(!$siteElements) return false;
	
		// Iterate through sites in search results
		foreach ($siteElements as $siteElement)
		{
			$site = array();
			$site['subscribers'] = (string) $siteElement['nsubs'];
			$site['name'] = (string) $siteElement->name;
			$site['url'] = (string) $siteElement->url;
			$site['feedurl'] = (string) $siteElement->feedurl;
			 
			// Index by feed URL to prevent duplicates
			$this->stats[$site['feedurl']] = $site;
		}
	}
 
/**
 * Convenience function to return the total subscribers
 * to all feeds found by the search.
 */
	function totalSubscribers()
	{
		$total = 0;
		foreach($this->stats as $site)
		{
			$total = $total + $site['subscribers'];
		}
		return $total;
	}
}

	$bl =& new Instapress_Core_Bloglines('tech@instablogs.com','24899A0B6676AA1355922460F63401DA','http://www.bloglines.com/rss/about/news' );
	$max = $bl->totalSubscribers();
	echo "total subscribers : ".$max;
