<?php
/*
* Main class
* @package contextvoice
* @subpackage Main
*/
class contextvoice{
  private $apikey;

  /**
   * Default constructor
   *
   * You can use the service anonymously by not specifying an API key but you will be limited to only using the getUrlReactions method, and a call rate limit of 500 calls per day.
   *
   * Instantiate as
   *    $myvar = new contextvoice('[my api key]');
   * or
   *    $myvar = new contextvoice(); //anonymous access
   *
   * @param string $apikey
   */
  public function __construct($apikey=false){
    $this->apikey = $apikey;
  }

  /**
   * Submit a single url to contextvoice for tracking
   *
   * @param string $url
   * Must be a fully qualified url e.g. http://www.example.com/page.htm
   * @return cv_Response
   */
  function addUrl($url){
    $api = "http://api.contextvoice.com/1.1/resources/";
    $uri = $api."?apikey={$this->apikey}&format=json";
    $data = array("url" => $url);
    $response = $this->sendRequest($uri, 'POST', $data);
    return $response;
  }

  /**
  * Submit a number of urls to contextvoice for tracking.
  *
  * $urls can be either a comma delimited list or an array.
  * each URL must be a fully qualified url e.g. <i>http://www.example.com/page.htm</i>
  *
  * @param mixed $urls
  * This must be either a comma-separated list(string) or an array of urls
  * @return cv_Response
  */
  function addUrls($urls){
    $api = "http://api.contextvoice.com/1.1/resources/batch/";
    $uri = $api."?apikey={$this->apikey}&format=json";
    //allow urls to be passed as array or comma delimted string
    if(is_array($urls)){
      $urlData = implode(",", $urls);
    }else{
      $urlData = $urls;
    }
    $data = array("urls" => $urlData);
    $response = $this->sendRequest($uri, 'POST', $data);
    return $response;
  }
  /**
  * Get info about a single url from contextvoice.
  *
  * The url must have first been registered via the addUrl or addUrls methods.
  *
  * @param string $url
  * Must be a fully qualified url e.g. http://www.example.com/page.htm
  * @return cv_Response
  */
  function getUrlInfo($url){
    $api = "http://api.contextvoice.com/1.1/resources/";
    $uri = $api."?url={$url}&apikey={$this->apikey}&format=json";
    $response = $this->sendRequest($uri);
    return $response;
  }
  /**
  * Get a set of reactions about a single url from contextvoice.
  *
  * The url must have first been registered via the addUrl or addUrls methods.
  *
  * @param string $url
  * Must be a fully qualified url e.g. http://www.example.com/page.htm
  * @param string $since
  * [optional] Any php parseable date string e.g. "15 May 2009"
  * @param string $include
  * [optional] Comma-separated list of sources e.g. "twitter,digg,friendfeed"
  * @param string $exclude
  * [optional] Comma-separated list of sources e.g. "flickr,hackernews,slashdot"
  * @param bool $filter
  * [optional] if true, attempts to remove "retweets" from the results.
  * @param string $order
  * [optional] "asc" or "desc" orders the results by the date that they were added to the contextvoice indexes
  * @param int $page
  * [optional] the page of results to return defaults to 1
  * @param int $perpage
  * [optional] the number of results to return per page, defaults to 25
  * @param bool $threaded
  * [optional] return the threaded conversation (retwitts will be treated as children of the original twitt), defaults to false
  * @return cv_Response
  */
  function getUrlReactions($url, $perpage=false, $since=false, $include=false, $exclude=false, $filter=false, $order=false, $page=false, $threaded=false ){
  	if( $perpage === false ) {
  		$count = $this->getUrlReactions( $url, 1 );
  		$perpage = $count->total;
  	}
    if($this->apikey==false){
      $api = "http://externalapi.contextvoice.com/1.1/reactions/";
      $uri = $api."?url={$url}&format=json";
    }else{
      $api = "http://api.contextvoice.com/1.1/reactions/";
      $uri = $api."?url={$url}&apikey={$this->apikey}&format=json";
      
    }
    if($order!=false){
      $uri = $uri."&order={$order}";
    }
    if($perpage!=false){
      $uri = $uri."&perpage={$perpage}";
    }
    if($page!=false){
      $uri = $uri."&page={$page}";
    }
    if($exclude!=false){
      $uri = $uri."&exclude[generator]={$exclude}";
    }
    if($include!=false){
      $uri = $uri."&include[generator]={$include}";
    }
    if($filter!=false){
      $uri = $uri."&filter=remove-retwitts";
    }
    if($threaded!=false){
      $uri = $uri."&threaded=true";
    }
    if($since!=false){
      $date = date_parse($since);
      if($date['error_count']==0){
        $uri = $uri."&since=".date("U", strtotime($since));
      }
    }
    
    $response = $this->sendRequest($uri);
    
    return $response;
  }

  /**
  * create and excute a CURL request
  * @ignore
  */
  private function sendRequest($uri, $method ='GET', $data ='')
  {
  	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

    if('POST' == ($method = strtoupper($method)))
    {
   
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    else if('GET' != $method)
    {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, 21600);

    $data = curl_exec($ch);
    curl_close($ch);

    return json_decode($data);
  }
}
?>