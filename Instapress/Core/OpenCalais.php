<?php

class Instapress_Core_OpenCalais
{
	private $_apiKey = "key";
	private $_requestURL = "http://api.opencalais.com/enlighten/rest/";
	private $_contentType = "TEXT/RAW";
	private $_outputFormat = "Application/json";
	private $_paramsXML = "";
	private $_response=array();

	private $_topics = array();
	private $_socialTags = array();
	private $_entities = array();
	private $_events_n_facts = array();

	function __construct()
	{}

	public function getGroupedTags() {
		$tags = array();
		$entities = $this->getEntities();
	
		foreach( $entities as $group => $tagData ) {
			$tags[ $group ] = array();
			foreach( $tagData as $tag ) {
				$newTag[ 'name' ] = $tag[ 'name' ];
				$newTag[ 'expandableData' ] = $tag;
	
				if( isset( $newTag[ 'expandableData' ][ 'resolutions' ] ) ) {
					foreach( $newTag[ 'expandableData' ][ 'resolutions' ] as $resolutionKey => $resolutionValue ) {
						foreach( $newTag[ 'expandableData' ][ 'resolutions' ][ $resolutionKey ] as $innerResolutionKey => $innerResolutionValue ) {
							if( $innerResolutionKey == 'id' || $innerResolutionKey == 'name' ) {
								continue;
							} else {
								$newTag[ 'expandableData' ][ $innerResolutionKey ] = $innerResolutionValue;
							}
						}
					}
					unset( $newTag[ 'expandableData' ][ 'resolutions' ] );
				}
	
				unset( $newTag[ 'expandableData' ][ 'name' ] );
				unset( $newTag[ 'expandableData' ][ 'relevance' ] );
	
				foreach( $newTag[ 'expandableData' ] as $key => $value ) {
					if( $value == 'N/A' ) {
						unset( $newTag[ 'expandableData' ][ $key ] );
					}
				}
				$tags[ $group ][] = $newTag;
			}
		}
		
		$eventAndFacts = $this->getEventsAndFacts();
	
		$tags[ 'EventsAndFacts' ] = array();
		foreach( $eventAndFacts as $eventName => $metaData ) {
			$expandableData = isset( $metaData[ 0 ] ) ? $metaData[ 0 ] : array();
			$newTag = array();
			$newTag[ 'name' ] = $eventName;
			$newTag[ 'expandableData' ] = $expandableData;
			$tags[ 'EventsAndFacts' ][] = $newTag;
		}
		
		$socialTags = $this->getSocialTags();
		$tags[ 'SocialTags' ] = array();
		foreach( $socialTags as $tagIndex => $data ) {
			$newTag = array();
			$newTag[ 'name' ] = $data[ 'name' ];
			$newTag[ 'expandableData' ] = array();
			$tags[ 'SocialTags' ][] = $newTag;
		}

		return $tags;
	}
	
	private function getParamsXML()
	{
		$this->_paramsXML = "<c:params xmlns:c=\"http://s.opencalais.com/1/pred/\" ";
		$this->_paramsXML.= "xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"> ";
		$this->_paramsXML.= "<c:processingDirectives c:contentType=\"".$this->_contentType."\" ";
		$this->_paramsXML.= "c:outputFormat=\"".$this->_outputFormat."\" ";
		$this->_paramsXML.= "c:enableMetadataType=\"GenericRelations,SocialTags\"";
		$this->_paramsXML.= "></c:processingDirectives> ";
		$this->_paramsXML.= "<c:userDirectives c:allowDistribution=\"false\" ";
		$this->_paramsXML.= "c:allowSearch=\"false\" c:externalID=\" \" ";
		$this->_paramsXML.= "c:submitter=\"InstaPress\"></c:userDirectives> ";
		$this->_paramsXML.= "<c:externalMetadata><c:Caller>Instablogs</c:Caller>";
		$this->_paramsXML.= "</c:externalMetadata></c:params>";
	}

	private function resolveReferences()
	{
		foreach($this->_response as $key => $value)
		{
			foreach($value as $key1 => $value1)
			{
				$ref = 'd.opencalais.com';
				if(is_string($value1))
				{
					if(strpos($value1, $ref))
					{
						if(array_key_exists($value1,$this->_response))
						$this->_response[$key][$key1] = $this->_response[$value1]['name'];
					}
				}
				elseif(is_array($value1))
				{
					foreach($value1 as $key2 => $value2)
					{
						if(is_string($value2))
						{
							if(strpos($value2, $ref))
							{
								if(array_key_exists($value2,$this->_response))
								$this->_response[$key][$key1][$key2] = $this->_response[$value2]['name'];
							}
						}
					}
				}
			}
		}
	}

	private function parseResponse()
	{
		foreach($this->_response as $key => $value)
		{
			if(array_key_exists ('_typeGroup' , $this->_response[$key]))
			{
				if($this->_response[$key]['_typeGroup'] == 'topics')
				{
					$this->_topics[] = $value;
				}

				if($this->_response[$key]['_typeGroup'] == 'socialTag')
				{
					$this->_socialTags[] = $value;
				}

				if($this->_response[$key]['_typeGroup'] == 'entities')
				{
					$this->_entities[] = $value;
				}

				if($this->_response[$key]['_typeGroup'] == 'relations')
				{
					$this->_events_n_facts[] = $value;
				}
			}
		}
	}

	public function getSuggestions($content)
	{
		$this->getParamsXML();

		$data = "licenseID=".urlencode($this->_apiKey);
		$data .= "&paramsXML=".urlencode($this->_paramsXML);
		$data .= "&content=".urlencode($content);

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->_requestURL);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlHandle, CURLOPT_HEADER, 0);
		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curlHandle, CURLOPT_POST, 1);
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, 500);
		$this->_response = curl_exec($curlHandle);
		curl_close($curlHandle);

		//describe( $this->_response, true );
		
		$this->_response = json_decode( $this->_response, TRUE );
		$this->_response = $this->_response ? $this->_response : array();
		
		$this->resolveReferences();
		$this->parseResponse();
	}

	private static function sortTopics($a, $b)
	{
		if ($a['score'] == $b['score'])
		{
			return 0;
		}
		return ($a['score'] > $b['score']) ? -1 : 1;
	}

	public function getTopics()
	{
		$i = 0;
		$topics = array();
		foreach($this->_topics as $key => $value)
		{
			$topics[$i]['name'] = $this->_topics[$key]['categoryName'];
			$topics[$i]['score'] = $this->_topics[$key]['score'];
			$i++;
		}

		usort($topics, 'Instapress_Core_OpenCalais::sortTopics');
		return $topics;
	}

	private static function sortSocialTags($a, $b)
	{
		if ($a['importance'] == $b['importance'])
		{
			return 0;
		}
		return ($a['importance'] > $b['importance']) ? -1 : 1;
	}

	public function getSocialTags()
	{
		$i = 0;
		$socialTags = array();
		foreach($this->_socialTags as $key => $value)
		{
			$socialTags[$i]['name'] = $this->_socialTags[$key]['name'];
			$socialTags[$i]['importance'] = $this->_socialTags[$key]['importance'];
			$i++;
		}

		usort($socialTags, 'Instapress_Core_OpenCalais::sortSocialTags');
		return $socialTags;
	}

	private static function sortEntitiesAndRelations($a, $b)
	{
		return strtolower($a)>strtolower($b);
	}

	public function getEntities()
	{
		$entities = array();
		$i=0;
		foreach($this->_entities as $key1 => $value1)
		{
			$temp_type="";
			foreach($value1 as $key => $value)
			{
				if('_type' == $key)
				{
					if(!array_key_exists($value, $entities))
					{
						$entities[$value] = array();
						$j=0;
					}
					else
					$j = count($entities[$value]);
					$temp_type = $value;
					continue;
				}

				if('_typeGroup' == $key or '_typeReference' == $key or 'instances' == $key)
				{
					continue;
				}
				else
				{
					$entities[$temp_type][$j][$key] = $value;
				}
			}
			$i++;
		}

		uksort($entities, "Instapress_Core_OpenCalais::sortEntitiesAndRelations");
		return $entities;
	}

	public function getEventsAndFacts()
	{
		$events_facts = array();
		$i=0;
		foreach($this->_events_n_facts as $key1 => $value1)
		{
			$temp_type="";
			foreach($value1 as $key => $value)
			{
				if('_type' == $key)
				{
					if(!array_key_exists($value, $events_facts))
					{
						$events_facts[$value] = array();
						$j=0;
					}
					else
					$j = count($events_facts[$value]);
					$temp_type = $value;
					continue;
				}
					
				if('_typeGroup' == $key or '_typeReference' == $key or 'instances' == $key)
				{
					continue;
				}
				else
				{
					$events_facts[$temp_type][$j][$key] = $value;
				}
			}
			$i++;
		}

		uksort($events_facts, "Instapress_Core_OpenCalais::sortEntitiesAndRelations");
		return $events_facts;
	}
}
?>
