<?php


/**
    Class : DurationDay
    @author : Jayant
    usage : $objDuration = new Strategy_DurationDay('2011-12-31');
   
    Used as an object to calculate the duration, start date and end date
    A simple wrapper over Duration class which passes duration as 'day' and durationValue as '1'
*/
class DurationDay extends Duration
{
    function __construct($startDate)
    {
        parent::__construct($startDate, 'd');
    }
}

?>
