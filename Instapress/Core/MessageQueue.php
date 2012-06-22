<?php


class Instapress_Core_MessageQueue
{
	private function __construct()
	{
	}
	
	public static function GetInstance($type='mysql')
	{
		switch($type)
		{
			case 'stomp':
				$objMQ = new Instapress_Core_MessageQueueStomp();
				return $objMQ;
				break;
			default:
				$objMQ = new Queue_MessageQueue();
				return $objMQ;
		}		
	}
}