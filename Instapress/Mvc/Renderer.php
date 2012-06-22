<?php
	class Instapress_Mvc_Renderer {
		protected $_modulesId = array();
		protected $_modulesHtml = array();
		protected $_parser = null;

		function __construct( $page ) {
			if( 'error' != strtolower( Instapress_Mvc_Page::$_pageName ) ) {
				$this->_parser = new Instapress_Core_Html_Dom( PAGES_PATH . Instapress_Mvc_Page::$_pageName );
				
				foreach( $this->_parser->find( 'ipml:module' ) as $e ) {
					 $this->_modulesId[] = $e->moduleId;
				}
				
				$this->_parseModules();
				$this->_replaceIpml();
			} else {
				$this->_parser = "ERROR 404";
			}
		}

		protected function _parseModules() {
			// Cache can be used here
			foreach( $this->_modulesId as $key => $value ) {
				$module = new Instapress_Mvc_Module( $value );
				$this->_moduleHtml[ $value ] = $module->moduleHtml;
			}
			
		}

		protected function _replaceIpml() {
			foreach( $this->_parser->find( 'ipml:module' ) as $e ) {
				$e->outertext = trim( $this->_moduleHtml[ $e->moduleId ] );
			}
		}

		public function render() {
			return $this->_parser;
		}
	}