<?php


class Instapress_Core_Readability {

	function __construct() {
		require_once( LIB_PATH. 'Instapress/Core/Readability/Readability.php' );
	}

	public function getURLContent( $url ) {

		// (change this URL to whatever you'd like to test)
		// $url ='http://googleblog.blogspot.com/2010/10/wind-cries-transmission.html';// $URL;//
		$html = @file_get_contents($url);

		// PHP Readability works with UTF-8 encoded content.
		// If $html is not UTF-8 encoded, use iconv() or
		// mb_convert_encoding() to convert to UTF-8.

		// give it to Readability
		$readability = new Readability( $html, $url );

		// print debug output?
		// useful to compare against Arc90's original JS version -
		// simply click the bookmarklet with FireBug's
		// console window open
		$readability->debug = false;

		// convert links to footnotes?
		$readability->convertLinksToFootnotes = false;

		// process it
		$result = $readability->init();

		// does it look like we found what we wanted?
		if( $result ) {
			//  echo "== Title ===============================\n";
			//echo $readability->getTitle()->textContent, "\n\n";
			//echo "== Body ===============================\n";
			$content = $readability->getTitle()->textContent . "<br>" . $readability->getContent()->innerHTML;
			// if we've got Tidy, let's clean it up for output
			if( function_exists( 'tidy_parse_string' ) ) {
				$tidy = tidy_parse_string( $content, array( 'indent' => true, 'show-body-only' => true ), 'UTF8' );
				$tidy->cleanRepair();
				$content = $tidy->value;
			}
			// echo"content ". $content;
			return $content;
		} else {
			return false;
		}
	}


	public function getURLContentArray( $url ) {
		$urlDetailsArr = array();
		// (change this URL to whatever you'd like to test)
		// $url ='http://googleblog.blogspot.com/2010/10/wind-cries-transmission.html';// $URL;//
		$html = @file_get_contents($url);

		// PHP Readability works with UTF-8 encoded content.
		// If $html is not UTF-8 encoded, use iconv() or
		// mb_convert_encoding() to convert to UTF-8.

		// give it to Readability
		$readability = new Readability( $html, $url );

		// print debug output?
		// useful to compare against Arc90's original JS version -
		// simply click the bookmarklet with FireBug's
		// console window open
		$readability->debug = false;

		// convert links to footnotes?
		$readability->convertLinksToFootnotes = false;

		// process it
		$result = $readability->init();

		// does it look like we found what we wanted?
		if( $result ) {
			// Get Title and Inner html of the article.
			$urlDetailsArr['title'] = $readability->getTitle()->textContent;
			$innerHtml = $readability->getContent()->innerHTML;
			$urlDetailsArr['innerContent'] = $innerHtml;
			// Get All Images from content
			$matches = array();
			preg_match_all('#<img[^>]+src=[\'"]([^\'"]+)[\'"]#', $innerHtml, $matches);
			$urlDetailsArr['images'] = $matches[1];

			return $urlDetailsArr;
		} else {
			return false;
		}
	}
}