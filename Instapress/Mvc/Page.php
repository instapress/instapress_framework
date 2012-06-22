<?php
class Instapress_Mvc_Page {

	static protected $_uri = null;
	static protected $_scheme = "http://";
	static protected $_subDomain = null;
	static protected $_hostName;
	static public $_pageVars = array();
	static protected $_pages = array();
	static public $_pageName = null;
	static protected $_uriComponent = null;
	static protected $_httpReferer = null;
	
	private static $instance;

	public static function getInstance() {
		if( !isset( self :: $instance ) ) {
			$c = __CLASS__;
			self :: $instance = new $c();
		}
		return self :: $instance;
	}

	private function __construct() {// Set _uri

		self::$_pageVars[ 'GETPOST' ] = array();
		self::$_pageVars[ 'GET' ] = array();
		self::$_pageVars[ 'POST' ] = array();
		self::$_pageVars[ 'FILES' ] = array();
		self::$_pageVars[ 'COOKIE' ] = array();
		self::$_pageVars[ 'SESSION' ] = array();
			
		$this -> setException( null, false );
		
		//Find url without query string
		$_domainURIwQ = $_SERVER['REQUEST_URI'];
		$_arrDomainURIwQ = explode('?', $_domainURIwQ);
		self :: $_uri = trim($_arrDomainURIwQ[0], "/");
		
		// Find sub domain
		$tmparray = explode( ".", str_replace( "www.", "", $_SERVER[ 'HTTP_HOST' ] ) );
		$count_tmparray = count( $tmparray );
		
		if(3 == $count_tmparray)
		{
			self :: $_subDomain = $tmparray[ 0 ];
			self :: $_hostName = $tmparray[1].".".$tmparray[2];
		}
		else
		{
			self :: $_hostName = $tmparray[$count_tmparray-2].".".$tmparray[$count_tmparray-1];
		}
		
		// Find _scheme
		self :: $_scheme = self :: getScheme();
		
		//parse page.XMl dom
		$this -> LoadPages();
		$this -> getPage();
		
		/*$this->dumper();
		exit;*/
	}

	protected function getScheme() {
		if( ( ( isset( $_SERVER[ 'HTTPS' ] ) )? $_SERVER[ 'HTTPS' ] : 'no' ) == 1 ) {/* Apache */
			return "https://";
		} elseif( ( ( isset( $_SERVER[ 'HTTPS' ] ) )? $_SERVER[ 'HTTPS' ] : 'no' ) == 'on' ) {/* IIS */
			return "https://";
		} elseif( ( ( isset( $_SERVER[ 'HTTPS' ] ) )? $_SERVER[ 'HTTPS' ] : 'no' ) == 443 ) {/* others */
			return "https://";
		} else {
			return "http://"; /* just using http */
		}
	}

	private function addPage( $uriComponent, $pageName )
	{
		if( array_key_exists( trim( $uriComponent, "/" ), self :: $_pages ) ) {
			echo $msg = "Sorry, you cant assign a same URL to multiple pages. This URL '" . $uriComponent . "' has already been assigned to Page '" . $pageName . "'.";// Throw Exception
		} else {
			self :: $_pages[ trim( $uriComponent, "/" ) ] = $pageName;
		}
	}

	private function getPage() {
		$found = false;
		$tempUri = self :: $_uri;
		$tempArr = explode( "/", $tempUri );
		$cnt = count( $tempArr );

		for( $i = 0; $i < $cnt; $i++ ) {
			if( array_key_exists( $tempUri, self :: $_pages ) ) 
			{
				self :: $_uriComponent = $tempUri;
				self::$_pageName = self :: $_pages[ $tempUri ];
				self :: getPageVars();
				$found = true;
				break;
			}
			else
			{
				array_pop( $tempArr );
				$tempUri = implode( "/", $tempArr );
			}
		}

		if( !$found ) {
			self::$_pageName = "Error";
		}
	}

	private function getPageVars() {
			
		//abcpage.com/add-asset/number/2/page/5/author/naveen/
		//archives/2010/page/2/auythor/3654376
		//arrchives = 20110
		//page =2
		//author = 3654376	
			
		if( self :: $_uri !== self :: $_uriComponent )
		{
			$thatString = self :: $_uri;

			
			if( $thatString !== '' ) {
				$tmpArr = explode( "/", $thatString );
					
				if( count( $tmpArr ) % 2 ) {
					self :: $_pageVars[ 'GETPOST' ][ 'ORPHAN' ] = self :: $_pageVars[ 'GET' ][ 'ORPHAN' ] = array_pop( $tmpArr );
				}
					
				for( $i = 0; $i < count( $tmpArr ); $i += 2 ) {
					self::$_pageVars[ 'GETPOST' ][ $tmpArr[ $i ] ] = $tmpArr[ $i + 1 ];
				}
			}
		}	
			
		if( isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
			self :: $_httpReferer = $_SERVER[ 'HTTP_REFERER' ];
		}
		
		// Now extract values from get, post, session, cookie etc
			
		foreach( $_GET as $key => $value ) {
			self :: $_pageVars[ 'GET' ][ $key ] = $value;
			self :: $_pageVars[ 'GETPOST' ][ $key ] = $value;
		}
			
		foreach( $_POST as $key => $value ) {
			self :: $_pageVars[ 'POST' ][ $key ] = $value;
			self :: $_pageVars[ 'GETPOST' ][ $key ] = $value;
		}
			
		self :: $_pageVars[ 'FILES' ] = $_FILES;
		
		self :: $_pageVars[ 'COOKIE' ] = $_COOKIE;
		
		if( isset( $_SESSION ) ) {
			self :: $_pageVars[ 'SESSION' ] = $_SESSION;			
		}
	}

	protected function LoadPages() {
		$xmlDoc = simplexml_load_file( CONFIG_PATH . 'pages.xml', 'SimpleXMLElement', LIBXML_NOCDATA );
			
		foreach( $xmlDoc as $element ) {
			$this -> addPage( $element -> UriComponent, $element -> PageName );
		}
	}

	public function dumper() {
		echo "<hr>:: <b>VARDUMPER STARTS</b> :: <hr>";
		echo "URI: ->  " . self :: $_uri;
		echo "<hr>";
		echo "SUBDOMAIN: ->  " . self :: $_subDomain;
		echo "<hr>";
		echo "HOST NAME: ->  " . self :: $_hostName;
		echo "<hr>";
			
		if( !empty(self :: $_pageVars[ 'GETPOST' ] ))
		{
			echo "GETPOST PAGE VARS ARRAY: ->  <br>";

			foreach( self :: $_pageVars[ 'GETPOST' ] as $key => $value ) {
				echo $key . " => " . $value . "<br>";
			}

			echo "<hr>";
		}
			
		if( !empty(self :: $_pageVars[ 'GET' ] ))
		{
			echo "GET PAGE VARS ARRAY: ->  <br>";

			foreach( self :: $_pageVars[ 'GET' ] as $key => $value ) {
				echo $key . " => " . $value . "<br>";
			}

			echo "<hr>";
		}

		if( !empty(self :: $_pageVars[ 'POST' ] ))
		{
			echo "POST PAGE VARS ARRAY: ->  <br>";

			foreach( self :: $_pageVars[ 'POST' ] as $key => $value ) {
				echo $key . " => " . $value . "<br>";
			}
			echo "<hr>";
		}

		echo "COOKIE PAGE VARS ARRAY: ->  <br>";

		foreach( self :: $_pageVars[ 'COOKIE' ] as $key => $value ) {
			echo $key . " => " . $value . "<br>";
		}

		echo "<hr>";

		echo "FILES PAGE VARS ARRAY: ->  <br>";

		foreach( self :: $_pageVars[ 'FILES' ] as $key => $value ) {
			echo $key . " => ";
			print_r( $value );
			echo "<br>";
		}

		echo "<hr>";
			
		if( !empty( $_SESSION ) ) {
			echo "SESSION PAGE VARS ARRAY: ->  <br>";

			foreach( self :: $_pageVars[ 'SESSION' ] as $key => $value ) {
				echo $key . " => " . $value . "<br>";
			}

			echo "<hr>";
		}

		echo "PAGES ARRAY: ->    <br>";
			
		foreach( self :: $_pages as $key => $value ) {
			echo $key . " => " . $value . "<br>";
		}

		echo "<hr>";
		echo "PAGR NAME: ->  " . self :: $_pageName;
		echo "<hr>";
		echo "URI COMPONENT: ->  " . self :: $_uriComponent;
		echo "<hr>:: <b>VARDUMPER Ends</b> :: <hr>";
			
	}

	public function setException( $msg, $domain = 'none', $raise = false ) {
		self :: $_pageVars[ 'EXCEPTION' ][ 'raised' ] = ( $raise == false ? 'no':'yes' );
			
		if( $raise ) {
			self :: $_pageVars[ 'EXCEPTION' ][ "{$domain}" ] = $msg;
		}
	}
}