<?php

class Instapress_Core_Log
{
	private $_logMessage = '';
	private $_logClassObject = null;
	private $_logFile = 'error.log';
	private $_logTable = 'error_log';
	private $_dbUserName = 'root';
	private $_dbPassword = 'instablogs';
	private $_dbHost = 'localhost';
	private $_logDbName = 'log_db';
	private $_logTableName = 'log_table';
	
	public $writeLog = true;
	
	public function __construct( $logType = 'file', $conf = null, $ident = 'ident' )
	{
		require_once( 'Log.php' );
		
		switch( strtolower( trim( $logType ) ) ) {
			case "firebug":
				$this->_logClassObject = &Log::singleton( 'firebug', '', $ident );
				break;
				
			case "database":
				require_once( "DB.php" );
				$dsnString = 'mysql://' . $this->_dbUserName . ':' . $this->_dbPassword . '@' . $this->_dbHost . '/' . $this->_logDbName;
				$conf = array( 'dsn' => $dsnString );
				$this->_logClassObject = &Log::singleton( 'sql', $this->_logTableName, $ident, $conf );
				break;
			
			default:
				$this->_logFile = LIB_PATH . '../logs/error.log';
				if( is_array( $conf ) ) {
					$this->_conf = $conf;
				} else {
					$this->_conf = array( 'mode' => 0600, 'timeFormat' => '%X %x' );
				}
				$this->_logClassObject = &Log::singleton( 'file', $this->_logFile, $ident, $conf );
		}
	}

	public function log( $logMessage, $filePath = "", $lineNo = "") 
	{
		if($this->writeLog == true)
		{
			$logMessage = "Error : ". $logMessage . ", File : " . $filePath . ", on Line : " . $lineNo;
			$this->_logClassObject->log( $logMessage );
		}
	}
}	