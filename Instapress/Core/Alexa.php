<?php


define ('ALEXA_ERROR_NODATA', 0);

class Instapress_Core_Alexa {
	
	public function __construct(){
				
			}
		
	/**
	 * Returns the Alexa rank for a website.
	 */
	function getRank($domain) {
		// Make the HTTP request for the page holding Alexa data.
		$ret = $this->httpRequest('http://alexa.com/data/details/traffic_details?url='.$domain);
		if ($ret === false) return (int)ALEXA_ERROR_NODATA;
	
		$ret = explode('<img src="/images/icons/globe-sm.jpg" alt="Global" style="margin-bottom:-2px;"/>', $ret);
	
		if (count($ret) < 2) return (int)ALEXA_ERROR_NODATA;
		
		$ret = explode( '<', $ret[1] );
		
		$ret = str_replace( ',', '', $ret[0] );
		
		/* $ret = array_shift(explode('<br',$ret[1]));
		
		// Remove decoy digits from the HTML.
		$scrambles = $this->getScrambleList();
		foreach ($scrambles as $scramble) {
			$ret = preg_replace("/<[A-Z0-9]*[^<>]*class=[\"']".$scramble."[\"'][^<>]*?>[0-9]*<\\/[^<>]*?>/si", "", $ret);
		}
		
		// Remove any remnant HTML and extraneous non-numeric characters.
		$ret = preg_replace(array("'<[\/\!]*?[^<>]*?>'si", "'[^0-9]*'s"), array("", ""), $ret); */
		
		return (int)trim( $ret );
	}
	
	/**
	 * Alexa tries to scramble numbers to stop screen scrapers by mixing in SPAN elements with
	 * classes that have a display:none attribute (e.g.: <span class="d342">40</span> whereby CSS
	 * class d342 has attribute "display:none").  This locates those fake digits and removes them.
	 */
	function getScrambleList() {
		$ret = $this->httpRequest('http://client.alexa.com/common/css/scramble.css');
		preg_match_all('/\.([A-Z0-9]+)\s*{\s*display\s*:\s*none[\s;]*}/si', $ret, $matches);
		return $matches[1];
	}
	
	/**
	 * Perform an HTTP request for the specified URL, passing header information
	 * to make us appear as a human.  Requires curl installation on server.
	 */
	function httpRequest($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.8.1.4) Gecko/20070515 Firefox/2.0.0.4");
		$curl_ret = curl_exec($ch);
		curl_close($ch);
		
		return $curl_ret;
	}
}
	
	
	
