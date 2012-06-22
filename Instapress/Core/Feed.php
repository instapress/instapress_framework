<?php

class Instapress_Core_Feed
{

	public function __construct()
	{
		require_once(LIB_PATH. 'Instapress/Core/Feedburner/Feedburner.php');
	}

	public function feedData($uri,$startDate,$endDate)
	{
		$fb =& new feedburner($uri);
		$date_str = date('Y-m-d',strtotime($startDate)).','.date('Y-m-d',strtotime($endDate));
	  
		$info = $fb->getFeedData(array('dates'=>$date_str));

		if($fb->isError())
		{
			echo $fb->getErrorMsg();
		}
		else
		{
			$entries = $info['entries'];
			if (count($entries) > 0)
			{
				$op = array();
				foreach($entries as $entry)
				{
					$op[] = $entry;
				}
			}
		}
		return $op;
	}
}
?>