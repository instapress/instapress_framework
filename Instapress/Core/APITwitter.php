<?php

class Instapress_Core_APITwitter
{
	private $_followers = 0;
	private $_following = 0;
	private $_listed = 0;
	private $_tweets = 0;
	private $_favorites = 0;
	private $_userName = '';

	public function __construct( $twitterUserName ) 
	{
		$this->_userName = $twitterUserName;
		if( $twitterUserName ) 
		{
			$this->getTwitterData();
			$this->getTwitterListed();
		}
	}

	public function getFollowers() 
	{
		return $this->_followers;
	}

	public function getFollowings() 
	{
		return $this->_following;
	}

	public function getListed() 
	{
		return $this->_listed;
	}
	
	public function getTweets() 
	{
		return $this->_tweets;
	}
	
	public function getFavorites() 
	{
		return $this->_favorites;
	}

	public function getTwitterData() 
	{
		if( !$this->_userName ) 
		{
			throw new Exception( 'Twitter screen name not found!' );
		}

		$resp = @file_get_contents( 'http://api.twitter.com/1/users/show.json?screen_name=' . $this->_userName );
		if( $resp === false ) 
		{
			throw new Exception( 'Incorrect twitter account name!' );
		}
		$res = json_decode( $resp, true );
		
		$this->_followers = $res["followers_count"];
		$this->_following = $res["friends_count"];
		// $this->_listed = 0;
		$this->_tweets = $res["statuses_count"];
		$this->_favorites = $res["favourites_count"];			        
	}
	
	public function getTwitterListed() 
	{
		if( !$this->_userName ) 
		{
			throw new Exception( 'Twitter screen name not found!' );
		}
		
		$resp = @file_get_contents( 'http://twitter.com/' . $this->_userName );	
		$match = explode( '<span id="lists_count" class="stats_count numeric">', $resp );
		$match = explode( "</span>", $match[1] );
		$this->_listed = $match[0];
	}
}

