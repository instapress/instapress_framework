<?php


class Instapress_Core_Locale
{
	// holds single instance
	private static $hinst = null;
	
	private $_tldLocaleArray = array( "ar" => "es_AR",
				                      "br" => "pt_BR",
				                      "ca" => "en_CA",
				                      "cl" => "es_CL",
				                      "co" => "es_CO",
				                      "mx" => "es_MX",
				                      "pe" => "es_PE",                      
				                      "ve" => "es_VE",
				                      "at" => "de_AT",
				                      "dk" => "da_DK",
				                      "fi" => "fi_FI",
						              "fr" => "fr_FR",    
								      "de" => "de_DE",
								      "ie" => "en_IE",
								      "it" => "it_IT",
								      "nl" => "nl_NL",
								      "no" => "nb_NO",
								      "ru" => "ru_RU",
								      "es" => "es_ES",
								      "se" => "sv_SE",
								      "ch" => "fr_CH",
								      "tr" => "tr_TR",
								      "uk" => "en_GB",
								      "au" => "en_AU",
				                      "cn" => "zh_CN",
				                      "hk" => "zh_HK",
				                      "in" => "hi_IN",
				                      "id" => "id_ID",
				                      "jp" => "ja_JP",
				                      "kr" => "ko_KR",		                    
				                      "my" => "ms_MY",
				                      "nz" => "en_NZ",
				                      "ph" =>"en_PH",
			                          "pt" =>"pt_BR",                                                                       
				                      "sg" => "zh_SG",
				                      "tw" => "zh_TW",
				                      "th" => "th_TH",
				                      "vn" => "vi_VN"
				                     );  
		                      
	
	private function __construct()
	{
    }
	
	public static function getInstance()
	{
		if( !self::$hinst )
		{
			self::$hinst = new self();
		}
		return self::$hinst;
	}
	
	public function getTldLocaleArray()
	{
		return $this->_tldLocaleArray;
	}
	
	public function setLocale( $_locale , $_languageDomain)
	{
		$language = $_locale.".utf8";
		putenv("LANGUAGE = $language");
		setlocale(LC_ALL, $language);
	     
		bindtextdomain($_languageDomain, LIB_PATH. 'Instapress/Core/Locale');
		bind_textdomain_codeset($_languageDomain, 'UTF-8');
		textdomain($_languageDomain);
	}
       
	
	/*
	echo _("hello");	
	print(money_format('%i',736));
	
	//date_default_timezone_set($locale);
	echo strftime("%A %e %B %Y", strtotime("2008-12-03"));
	*/
	
}
