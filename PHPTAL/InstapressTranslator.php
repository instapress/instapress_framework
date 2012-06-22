<?php
	
	class PHPTAL_InstapressTranslator implements PHPTAL_TranslationService {

	    /**
	     * current execution context
	     */
	    protected $_context = null;

		/**
		 * @param string (name of the language)
		 * @return string (language you've just set)
		 *
		 * This method sets translation language.
		 * Name of the language is a dir name where you keep your translation files
		 */
		public function setLanguage() {
		}
		
		public function __construct( $context ) {
			$this->_context = $context;
		}

		/**
		 * @param string (translation file name)
		 * @return void
		 *
		 * You can separate translations in several files, and use only when needed.
		 * Use this method to specify witch translation file you want to
		 * use for current controller.
		 */
		public function useDomain( $domain ) {
		}

		/**
		 * Set an interpolation var.
		 * Replace all ${key}s with values in translated strings.
		 */
		public function setVar( $key, $value ) {
		}

		/**
		 * Translate a text and interpolate variables.
		 */
		public function translate( $key, $htmlescape=true ) {
			return $key;
			
			
			$value = $key;
			if( empty( $value ) ) {
				return $key;
			}
			while( preg_match( '/\${(.*?)\}/sm', $value, $m ) ) {
				list( $src, $var ) = $m;
				if( !array_key_exists( $var, $this->_context ) ) {
					$err = sprintf( 'Interpolation error, var "%s" not set', $var );
					throw new Exception( $err );
				}
				$value = str_replace( $src, $this->_context->$var, $value );
			}
			return gettext( $value );
		}

		/**
		 * Not implemented yet, default encoding is used
		 */
		public function setEncoding( $encoding ) {
		}
	}