<?php
class compete
{
	var $api_key = 'aq46y23kfatfd6t8z76vtha6';
	var $query_base = 'http://api.compete.com/fast-cgi/MI?';
	var $version = 3;
	var $size = NULL;
	
	var $results;
	
	function query_compete ($url)
	{
		$data = simplexml_load_file($this->construct_query($url));
		
		if($data == FALSE)
		{
			return FALSE;
		}
		else
		{
			$this->results = $data;
			return TRUE;
		}
	}
	
	function construct_query ($url)
	{
		$query = $this->query_base;
		$query .= 'd=' . $url;
		$query .= '&ver=' . $this->version;
		$query .= '&apikey=' . $this->api_key;
		if ($this->size != NULL)
		{
			$query .= '&size=' . $this->size;
		}		
		return $query;
	}
	
	function get_trust ()
	{
		$results['val'] = trim((string)$this->results->dmn->trust->val);
		$results['link'] = trim((string)$this->results->dmn->trust->link);
		$results['icon'] = trim((string)$this->results->dmn->trust->icon);
		return($results);
	}
	
	function get_traffic ()
	{
		/* $results['year'] = trim((string)$this->results->dmn->metrics->val->yr);
		$results['month'] = trim((string)$this->results->dmn->metrics->val->mth); */
		$results['ranking'] = trim((string)$this->results->dmn->metrics->val->uv->ranking);
		
		/* $results['count'] = trim((string)$this->results->dmn->metrics->val->uv->count);
		$results['count_int'] = (int)implode('', explode(',', $results['count']));
		
		$results['link'] = trim((string)$this->results->dmn->metrics->link);
		$results['icon'] = trim((string)$this->results->dmn->metrics->icon); */
		$results['ranking'] = str_replace(",","", $results['ranking']);
		return($results);
	}
	
	function get_deals ()
	{
		$results['val'] = (int)$this->results->dmn->deals->val;
		$results['link'] = trim((string)$this->results->dmn->deals->link);
		$results['icon'] = trim((string)$this->results->dmn->deals->icon);
		return $results;
	}
}
?>