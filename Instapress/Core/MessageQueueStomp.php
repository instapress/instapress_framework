<?php


class Instapress_Core_MessageQueueStomp
{
	private $_objStomp = null;
	private $_frame = null;

	public function __construct()
	{
		try 
		{
		    $this->_objStomp = new Stomp('tcp://localhost:61613');
		} 
		catch(StompException $e) 
		{
		    throw new Exception("ActiveMq is not running.");
		}
	}
	
	function __destruct()
	{
		unset($this->_objStomp);
	}
	
	public function sendMessage($queueName, $queueMessage)
	{
		$this->_objStomp->send($queueName, $queueMessage, array('persistent' => 'true'));
	}
	
	public function subscribeQueue($queueName)
	{
		$this->_objStomp->subscribe($queueName);
	}
	
	public function receiveMessage()
	{
		$this->_frame = $this->_objStomp->readFrame();
		
		if ( $this->_frame != null)
		{
			$queueData = $this->_frame->body;	
			return $queueData;
		}
		return "";
	}
	
	public function removeMessage()
	{
		$this->_objStomp->ack($this->_frame);
	}
}