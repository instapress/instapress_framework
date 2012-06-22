<?php


class Instapress_Core_Geoip
{
	// holds single instance
	private static $hinst = null;
	
	private $_countryName = '';
	private $_countryCode = '';
	private $_countryCode3 = '';
	private $_region = '';
	private $_city = '';
	private $_postalCode = '';
	private $_latitude = '';
	private $_longitude = '';
	private $_dma_code = '';
	private $_area_code = '';
	
	private function __construct()
	{
    }
	
	public static function getInstance()
	{
		if( !self::$hinst ){
			self::$hinst = new self();
		}		
		return self::$hinst;
	}
	
	public function processGeoip()
	{
		require_once(LIB_PATH. 'Instapress/Core/Geoip/geoipcity.inc');

		$gi = geoip_open(LIB_PATH. 'Instapress/Core/Geoip/GeoLiteCity.dat', GEOIP_STANDARD);
		
		$ip = Helper::getRealIpAddr();
		
		$record = geoip_record_by_addr($gi, $ip);
		
		if($record)
		{
			$this->_countryName = $record->country_name;
			$this->_countryCode = $record->country_code;
			$this->_countryCode3 = $record->country_code3;
			$this->_region = $record->region;
			$this->_city = $record->city;
			$this->_postalCode = $record->postal_code;
			$this->_latitude = $record->latitude;
			$this->_longitude = $record->longitude;
			$this->_dma_code = $record->dma_code;
			$this->_area_code = $record->area_code;
		}
		
		geoip_close($gi);		
	}	
	
	public function getCountryName()
	{
		return $this->_countryName;
	}
	
	public function getCountryCode()
	{
		return $this->_countryCode;
	}
	
	public function getCountryCode3()
	{
		return $this->_countryCode3;
	}
	
	public function getRegion()
	{
		return $this->_region;
	}
	
	public function getCity()
	{
		return $this->_city;
	}
	
	public function getPostalCode()
	{
		return $this->_postalCode;
	}
	
	public function getLatitude()
	{
		return $this->_longitude;
	}
	
	public function getLongitude()
	{
		return $this->_longitude;
	}
	
	public function getDmaCode()
	{
		return $this->_dma_code;
	}
	
	public function getAreaCode()
	{
		return $this->_area_code;
	}


}