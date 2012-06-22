<?php


class Instapress_Core_Faviki
{
	private $URL = "http://www.faviki.com/api/save/";
	private $api_key = "";
	private $uri = "";	//"http://en.wikipedia.org/wiki/RDFa";
	private $title = "";	//"Wikipedia page on RDFa";
	private $quote = "";	//"RDFa is a set of extensions to XHTML being proposed by W3C.";
	private $note = "";	//"RDFa rules";
	private $tags = "";	//"RDFa;Resource Description Framework;Semantic Web;XHTML";     // tags1; tags2;....(seprate by ' ; ')
	private $private = ""; // 0 or 1 bye default 0
	private $lang = "";  // language of tags en|de|es|fi|fr|it|ja|nl|...
	
	/*
	 * constructor
	 */
	public function __construct( $api_key, $url, $title, $quote, $note, $tags, $private = null, $lang = null )
	{
		$this->api_key = $api_key;
		$this->url = $url;
		$this->title = $title;
		$this->quote = $quote;
		$this->note = $note;
		$this->tags = $tags;
		$this->private = $private;
		$this->lang = $lang;
	}
	
	/*
	 * return the responce of the request
	 */
	public function getResponce()
	{
		$t = str_replace(" ","%20",$this->title );									
		$qt = str_replace( " ", "%20", $this->quote );					
		$nt = str_replace( " ", "%20", $this->note );					
		$tg = str_replace( " ", "%20", $this->tags );					
		$p = $this->private == ""||0 ? 0 : 1;										
		$ln = $this->lang == "" ? "en" : $this->lang;	
		
		$postRequest = "api_key=".$this->api_key."&url=".$this->url."&title=".$t."&quote=".$qt."&note=".$nt."&tags=".$tg;
		
		$ch = curl_init($this->URL);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $postRequest);
		curl_exec ($ch);
		curl_close ($ch);
		$abc = ob_get_contents();	
		$status = explode( 'status="', $abc );
		$status = explode( '"', $status[1] );
		$status = $status[0];
		return $status;
		//echo "responce status = ".$status;
	}
}
		