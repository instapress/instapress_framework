<?php


/**
    Class : DurationAll
    @author : Jayant
    usage : $objDuration = new Strategy_DurationAll();
   
    Used as an object to calculate the duration, start date and end date is not required
    A simple wrapper over Duration class which passes duration as 'All' and durationValue as '1'
*/
class DurationAll extends Duration
{
    function __construct()
    {
        $year = date('Y');
        parent::__construct($year, 'All');
    }
}

?>
