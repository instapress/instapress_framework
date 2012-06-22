<?php


/**
    Class : DurationMonth
    @author : Jayant
    usage : $objDuration = new DurationMonth('2011-12-31');
 * OR
 *          $objDuration = new DurationMonth('201112');
   
    Used as an object to calculate the duration, start date and end date
    A simple wrapper over Duration class which passes duration as 'month' and durationValue as '1'
*/
class DurationMonth extends Duration
{
    function __construct($startDate)
    {
        if(stristr($startDate, '-'))
        {
            $dates = explode('-',$startDate);
            $month = $dates[0].'-'.$dates[1];
            parent::__construct($month, 'm');
        }
        else
        {
            $month = substr($startDate,0,4).'-'.substr($startDate,4,2);
            parent::__construct($month, 'm');
        }
    }
}

?>
