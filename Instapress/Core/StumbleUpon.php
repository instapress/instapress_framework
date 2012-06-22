<?php

class Instapress_Core_StumbleUpon
{
	private $_views = 0;
	private $_reviews = 0;
	private $_submit = 0;
	private $_siteUrl = '';

	public function __construct( $url = '' ) {
		$this->_siteUrl = $url;
		if( $url ) {
			$this->getInfo();
		}
	}

	public function getViews() {
		return $this->_views;
	}

	public function getReviews() {
		return $this->_reviews;
	}

	public function getSubmitBy() {
		return $this->_submit;
	}

	public function getInfo() {
		if( !$this->_siteUrl ) {
			throw new Exception( 'Empty URL found!' );
		}

		$resp = @file_get_contents( 'http://www.stumbleupon.com/url/' . $this->_siteUrl );
		if( $resp === false ) {
			throw new Exception( 'Unregistered URL!' );
		}

		// Submitted By -

		$sub = explode( '<div class="colRight">', $resp );
		$sub = $sub[1];
		$sub = explode( '<p>', $sub );
		$sub = $sub[1];
		$sub = trim( $sub );
		$sub = str_replace( '<br />', '', $sub );
		preg_match_all('#<a href=(.+?)</a>#is', $sub, $matches);
		$matches = $matches[1];
			
		$subm = $matches[0];
		$subm = explode( ' ', $subm );
		$sb = '';
		for( $i = 0; $i < (count( $subm )); $i++) {
			if( strpos( $subm[$i], 'title' ) !== false ) {
				$sb = $subm[$i];
				$sb = explode( '>', $sb );
				$sb = $sb[1];
			}
		}

		// Views and Reviews-

		$resp = explode( '<ul class="listStumble noBorder spotlight">', $resp );
		$resp = $resp[1];
		$resp = explode( '</ul> <!-- end listStumble in header -->', $resp );
		$resp = trim( $resp[0] );
		$resp = str_replace( '<br />', '', $resp );

		preg_match_all('#<span>(.+?)</span>#is', $resp, $matches);

		$views = str_replace( ',', '', $matches[1][0] );
		$views = str_replace( 'K', '000', $matches[1][0] );

		preg_match_all('#<a href=(.+?)</a>#is', $resp, $matches);

		$reviews = '';

		for( $i = 0; $i < count( $matches[1] ); $i++ ) {
			if( strpos( $matches[1][$i], 'reviews' ) !== false ) {
				$reviews = $matches[1][$i];
				$reviews = explode( '>', $reviews );
				$reviews = explode( ' ', $reviews[1] );
				$reviews = $reviews[0];
			}
		}

		$this->_views = $views;        echo "<br />";
		$this->_reviews = $reviews;    echo "<br />";
		$this->_submit = $sb;          echo "<br />";
	}
}

try {
	//$su = new Instapress_Core_StumbleUpon( 'http://stopdroplol.com/posts/blank-title--9312' );
	}
catch( Exception $ex ) {
	echo $ex->getMessage();
	}
