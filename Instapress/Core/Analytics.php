<?php

class Instapress_Core_Analytics
{
	private $_objAnalyticsAnalytics = null;

	public function __construct()
	{
		$gaAccountName = 'email';
		$gaAccountPassword = 'password';
		
		define('ga_email', $gaAccountName);
		define('ga_password', $gaAccountPassword);
		require_once(LIB_PATH. 'Instapress/Core/Google/gapi.php');
		$this->_objAnalyticsAnalytics = new gapi(ga_email, ga_password);
	}

	/*
	 *  Site Data
	 */
	public function getSiteData($profileId,$startDate,$endDate)
	{
		$this->_objAnalyticsAnalytics->requestReportData($profileId,array('visitCount'),
		array('visits','pageviews','uniquePageviews','bounces','timeOnSite','exits','entrances','newVisits'),
							'-uniquePageviews',null,$startDate,$endDate);
		// visits
		$visits = $this->_objAnalyticsAnalytics->getvisits();
		//Pageviews
		$pageviews = $this->_objAnalyticsAnalytics->getPageviews();
		// uniquePageviews
		$uniquePageviews = $this->_objAnalyticsAnalytics->getuniquePageviews();
		// Bounces
		$bounces = $this->_objAnalyticsAnalytics->getbounces();
		// timeOnSite
		$time = $this->_objAnalyticsAnalytics->gettimeOnSite();
		// exits
		$exits = $this->_objAnalyticsAnalytics->getexits();
		// entrances
		$entrances = $this->_objAnalyticsAnalytics->getentrances();
		// newVisits
		$newVisits = $this->_objAnalyticsAnalytics->getnewVisits();
		/*
		 *  Calculations for "Bounce Rate","Exit Rate","Avg time On Site","Pages/Visits","%newVisits".
		 */
		// BounceRate
		if( $entrances > 0 )
		{
			$br=round( ( ( $bounces/$entrances )*100 ), 2 );
		}
		else
		{
			$br = 0;
		}

		// Avg time On Site
		if( $exits > 0 )
		{
			$secs = round( $time/$exits );
		}
		else
		{
			$secs = 0;
		}

		// Pages/Visits
		if( $visits > 0 )
		{
			$ppv = round( ( $pageviews/$visits ), 2 );
		}
		else
		{
			$ppv = 0;
		}
			
		// % New Visits
		if( $visits > 0 )
		{
			$nv=round( ( ( $newVisits/$visits )*100 ), 2 );
		}
		else
		{
			$nv = 0;
		}

		/*
		 *  array of site data
		 */
		$siteInfo = array();
		$siteInfo['visits'] = $visits;
		$siteInfo['pageViews'] = $pageviews;
		$siteInfo['uniquePageviews'] = $uniquePageviews;
		$siteInfo['bounceRate'] = $br;
		$siteInfo['avgTime'] = $secs;
		$siteInfo['pagesPerVisit'] = $ppv;
		$siteInfo['newVisitsPercentage'] = $nv;

		return $siteInfo;
	}

	/*
	 * total no. of visitors
	 * return visitor's count
	 */
	public function getVisitors($profileId,$startDate,$endDate)
	{
		$this->_objAnalytics->requestReportData($profileId,array('date'),array('visitors'),'date',null,$startDate,$endDate);

		$visitorCount = array();
		foreach($this->_objAnalytics->getResults() as $result)
		{
			$d = $result->__tostring();
			$yr = substr($d,0,4);
			$mnth = substr($d,4,2);
			$day = substr($d,6,2);
			$date = $yr."-".$mnth."-".$day;
			$visitors = $result->getvisitors();
				
			$visitorCount[$date] = array( "visitors"=>$visitors);
		}
		return $visitorCount;
	}
	
	/*
	 * Data from Google Analytoics on the basis of visitor Type
	 * Visitor Type have only two values : "New Visitor" and "Returning Visitor".
	 */
	public function getVisitorsTypeData($profileId,$startDate,$endDate,$visitorType="Returning Visitor")
	{
		$filter = 'visitorType == '.$visitorType;
		$this->_objAnalyticsAnalytics->requestReportData( $profileId, array('date','visitorType'),
		array('visits','pageviews','uniquePageviews','bounces','timeOnPage','exits','entrances'),
														   'date',$filter,$startDate,$endDate);

		$visits = $this->_objAnalyticsAnalytics->getvisits();
		$pageviews = $this->_objAnalyticsAnalytics->getpageviews();
		$uniquePageviews = $this->_objAnalyticsAnalytics->getuniquePageviews();
		$bounce = $this->_objAnalyticsAnalytics->getbounces();
		$time = $this->_objAnalyticsAnalytics->gettimeOnPage();
		$exits = $this->_objAnalyticsAnalytics->getexits();
		$entrances = $this->_objAnalyticsAnalytics->getentrances();

		// Average Time
		if( $exits > 0 )
		{
			$avg = $time/($exits);
			$avgT = round($avg);
			$secs = $avgT;
		}
		else
		{
			$secs = 0;
		}

		// Bounce Rate
		if( $entrances > 0 )
		{
			$br = round( ( ( $bounce/$entrances )*100 ), 2 );
		}
		else
		{
			$br = 0;
		}

		// Pages per Visits
		if( $visits>0 )
		{
			$pagesPerVisit = round( ( $pageviews/$visits ), 2 );
		}
		else
		{
			$pagesPerVisit = 0;
		}
		$visitor = array();

		$visitor["visits"] = $visits;
		$visitor["pageViews"] = $pageviews;
		$visitor["uniquePageviews"] = $uniquePageviews;
		$visitor["avgTime"] = $secs;
		$visitor["bounceRate"] = $br;
		$visitor["pagesPerVisits"] = $pagesPerVisit;

		return $visitor;
	}

	/*
	 *  Permalink Data
	 */
	public function getTopContent( $profileId,$startDate,$endDate, $start, $qty )
	{
		$this->_objAnalyticsAnalytics->requestReportData( $profileId, array('pagePath'),
		array('pageviews','uniquePageviews','bounces','timeOnPage','exits','entrances'),
												          '-pageviews',null,$startDate,$endDate, $start, $qty);
		$topContents = array();
		foreach($this->_objAnalyticsAnalytics->getResults() as $result)
		{
			$pagePath = $result->__tostring();
			$pageviews = $result->getpageviews();
			$uniquePageviews = $result->getuniquePageviews();
			$bounces = $result->getbounces();
			$time = $result->gettimeOnPage();
			$exits = $result->getexits();
			$entrances = $result->getentrances();

			// Avg Time on site in seconds
			if( $exits > 0 )
			{
				$secs = round( $time/$exits );
			}
			else
			{
				$secs = 0;
			}

			// Bounce Rate
			if( $entrance > 0 )
			{
				$br = round( ( ( $bounces/$entrances )*100 ), 2 );
			}
			else
			{
				$br = 0;
			}
				
			$topContents[] = array( "pagePath" => $pagePath,
									"pageViews" => $pageviews,
									"uniquePageviews" => $uniquePageviews,
  									"avgTime" => $secs,
  									"bounceRate" => $br );
		}
		return $topContents;
	}



	/*
	 * Traffic Information =============================================
	 */

	public function getTrafficDetail($profileId,$startDate,$endDate, $start, $qty )
	{
		$this->_objAnalyticsAnalytics->requestReportData( $profileId,array('source','medium'),
		array('visits','pageviews','bounces','timeOnSite','exits','entrances'),
														  '-Visits',null,$startDate,$endDate,$start, $qty);

		$trafficDetails = array();
		foreach($this->_objAnalyticsAnalytics->getResults() as $result)
		{
			// source/medium
			$source_medium = $result->__tostring();
			$sm = explode(" ", $source_medium);
			$source = $sm[0];
			$medium = $sm[1];
				
			// visits
			$visits = $result->getvisits();
			// page views
			$pageviews = $result->getpageviews();
			//bounces
			$bounces = $result->getbounces();
			// time on page
			$time = $result->gettimeOnSite();
			// exits
			$exits = $result->getexits();
			// entrances
			$entrances = $result->getentrances();

			// Average Time
			if($exits>0)
			{
				$avg = $time/($exits);
				$avgT = round($avg);
				$secs = $avgT;
			}
			else
			{
				$secs = 0;
			}

			// Bounce Rate
			if($entrances>0)
			{
				$bouncerate = round((($bounces/$entrances)*100),2);
			}
			else
			{
				$bouncerate = 0;
			}

			// Pages per Visits
			if($visits>0)
			{
				$pagesPerVisit = round(($pageviews/$visits),2);
			}
			else
			{
				$pagesPerVisit = 0;
			}
	
			
			$trafficDetails[] = array("source" => $source,
									"medium" => $medium,
									"pageViews" => $pageviews,
									"visits" => $visits,
  									"pagesPerVisits" => $pagesPerVisit,
  									"avgTime" => $secs,
									"bounceRate" => $bouncerate );
		}
		return $trafficDetails;
	}

	public function getTotalResults()
	{
		return $this->_objAnalyticsAnalytics->getTotalResults();
	}

	function getDistributionChannelType( $medium, $source )
	{
		$feed_data = array("netvibes.com","bloglines.com","feedly.com","feedburner","twitterfeed");

		$social_net = array("twitter.com","facebook.com","linkedin.com","orkut.com","myspace.com","friendster.com",
		  					"bebo.com","hi5.com","netlog.com","tagged.com","meebo.com","tumblr.com","care2.com","ning.com","yammer.com");

		$social_news = array("digg.com","stumbleupon.com","reddit.com","instadaily.com","2leep.com","instablogs.com",
							"mixx.com","buzz.yahoo.com","fark.com","slashdot.org","friendfeed.com","shoutwire.com",
							"delicious.com","newsvine.com","clipmarks.com");
		
		$crossMediums = array( '(none)' => "direct", "referral" => "referrals", "feed" => "referrals",
								"twitter" => "referrals", "organic" => "search" );

		$medium = isset( $crossMediums[ $medium ] ) ? $crossMediums[ $medium ] : $medium;

		$medium = in_array($source,$social_net) ? "social_networks" : $medium;

		$medium = in_array($source,$social_news) ? "social_news" : $medium;

		$medium = in_array($source,$feed_data) ? "feed" : $medium;
		
		$arrMedium = array ('direct','search','social_news','social_networks','referrals','feed');
		
		$medium = in_array($medium,$arrMedium)?$medium : "referrals";
		
		$distributionChannelType = $medium;
		
		return $distributionChannelType;
	}
}
?>
