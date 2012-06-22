<?php

	define( "EXCL", true );
	define( "INCL", false );
	define( "BEFORE", true );
	define( "AFTER", false );

	class Instapress_Core_Document {

		private $_documentHtml = '';

		function __construct( $html ) {
			$this->_documentHtml = $html;
		}

		function returnBetween( $start, $stop, $type = EXCL ) {
			$string = $this->_documentHtml;
			$temp = $this->splitString( $string, $start, AFTER, $type );
			return $this->splitString( $temp, $stop, BEFORE, $type );
		}

		private function splitString( $string, $delineator, $desired, $type ) {
			# Case insensitive parse, convert string and delineator to lower case
			$lcStr = strtolower( $string );
			$marker = strtolower( $delineator );

			# Return text BEFORE the delineator
			if( $desired == BEFORE ) {
				if( $type == EXCL ) {  // Return text ESCL of the delineator
					$splitHere = strpos( $lcStr, $marker );
				} else {               // Return text INCL of the delineator
					$splitHere = strpos( $lcStr, $marker ) + strlen( $marker );
				}

				$parsedString = substr( $string, 0, $splitHere );
			} else {			# Return text AFTER the delineator
				if( $type == EXCL ) {    // Return text ESCL of the delineator
					$splitHere = strpos( $lcStr, $marker ) + strlen( $marker );
				} else {               // Return text INCL of the delineator
					$splitHere = strpos( $lcStr, $marker );
				}
				$parsedString =  substr( $string, $splitHere, strlen( $string ) );
			}
			return $parsedString;
		}
	}