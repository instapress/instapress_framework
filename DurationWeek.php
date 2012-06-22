<?php


/**
    Class : DurationWeek
    @author : Jayant
    usage : $objDuration = new DurationWeek('2011-04-03'); 
   
    Used as an object to calculate the duration, start date and end date
    start date is set as the date for sunday.
    A simple wrapper over Duration class which passes duration as 'week' and durationValue as '1'
*/
class DurationWeek extends Duration
{
    function __construct($currentDate)
    {
        //Find last sunday of currentDate and 1st of month. 
        //which ever has a bigger time stamp send that as start date
        $dateArr = explode('-', $currentDate);
        $now = mktime(0,0,0,$dateArr[1],$dateArr[2],$dateArr[0]);
                
        $sunday = strtotime('last sunday', $now);
        $day1 = mktime(0,0,0,$dateArr[1],1,$dateArr[0]);
        
        $startDate = date('Y-m-d',$sunday);
        
        if(date('w', $now) == 0)
            $startDate = date('Y-m-d',$now);
        
        if($sunday < $day1)
            $startDate = date('Y-m-d',$day1);
        
        //echo "WEEK $startDate = $sunday/$day1\n";
        parent::__construct($startDate, 'w');
    }
}

?>
