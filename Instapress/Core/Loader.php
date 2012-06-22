<?php


	class Instapress_Core_Loader {
	 
		static function Lib( $class ) {
			$filename = $class . '.php';
			$filename = str_replace( "_", "/", $filename );
	
			$file = LIB_PATH . $filename;
	
			if( !file_exists( $file ) ) {
				self::ComponentClass( $class );
			} else {
				require_once $file;
			}
		}
	
		static function ComponentClass( $class ) {
			$filename = $class . '.php';
			$filename = str_replace( "_", "/", $filename );

			$file = COMPONENT_PATH . $filename;

			if( !file_exists( $file ) ) {
				@include_once $filename;
				if( !class_exists( $class ) ) {
					throw new Exception("Class $class was not found!");
				}
			} else {
				require_once $file;
			}
		}
	}