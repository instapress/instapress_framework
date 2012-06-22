<?php


/**
    Class : DurationYear
    @author : Jayant
    usage : $objDuration = new Strategy_DurationYear('2011-12-31');
   
    Used as an object to calculate the duration, start date and end date
    A simple wrapper over Duration class which passes duration as 'Year' and durationValue as '1'
*/
class DurationYear extends Duration
{
    function __construct($startDate)
    {
        $dates = explode('-',$startDate);
        $year = $dates[0];
        parent::__construct($year, 'y');
    }
}

?>
