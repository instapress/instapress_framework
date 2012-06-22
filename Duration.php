<?php
/**
    Class : Duration
    @author : Jayant
    usage : $objDuration = new Strategy_Duration('2011-12-31','d');
   
    Used as an object to calculate the duration, start date and end date
*/

class Duration
{
   protected $startDate;
   protected $endDate;
   protected $duration; //[d]ay, [w]eek, [m]onth, [q]uarter, [y]ear
   protected $durationValue;
   protected $betweenClause;

   function __construct($startDate, $duration, $durationValue=1)
   {
        if(!$this->checkStartDate($startDate, $duration))
            throw new Exception(gettext("Date should be of format : 'yyyy-mm-dd' for day, 'yyyy-mm' for month and 'yyyy' for year"));

        $this->duration = $duration;
        $this->durationValue = $durationValue;
        $this->calculateEndDate();
   }

   /**
   *    Used to calculate the start date based on the duration passed
   */
   private function checkStartDate($startDate, $duration)
   {
        switch($duration)
        {
            // date should be of format 'yyyy-mm-dd';
            case 'd':
            case 'w':
                if($this->validateDay($startDate))
                    $this->startDate = $startDate;
                else
                    return false;
                break;

            // date should be of format 'yyyy-mm';
            case 'm':
            case 'q':
                if($this->validateMonth($startDate))
                    $this->startDate = $startDate.'-01';
                else    
                    return false;
                break;

            // date should be of format 'yyyy'
            case 'y':
                if($this->validateYear($startDate))
                    $this->startDate = $startDate.'-01-01';
                else    
                    return false;
                break;
        }
        return true;
   }

   // Function to check date
   private function validateDay($date)
   {
        $days = explode('-',$date);
        if(sizeof($days)!=3)
            return false;

        if(strlen($days[0]) != 4)
            return false;

        if(strlen($days[1]) != 2)
            return false;

        if(strlen($days[2]) != 2)
            return false;

        return true;
   }
   
   // function to check month
   private function validateMonth($date)
   {
        $days = explode('-',$date);
        if(sizeof($days)!=2)
            return false;

        if(strlen($days[0]) != 4)
            return false;

        if(strlen($days[1]) != 2)
            return false;

        return true;
   }
    
   //function to check year
   private function validateYear($date)
   {
        if( strpos($date,'-') )
			return FALSE;
        if(strlen($date) != 4)
            return false;

        return true;
   }
   
   //returns yearMonth from date passed
   public function getYearMonthFromDate($date)
   {
       $tm = strtotime($date);
       $ym = date('Ym', $tm);
       return $ym;
   }

   // Function to calculate end date based on duration & durationValue
   private function calculateEndDate()
   {
        switch($this->duration)
        {
            case 'd':
                $days = $this->durationValue;
                $this->endDate = Helper::add_date($this->startDate,$days);
                $this->betweenClause = " `date` between '$this->startDate' and '$this->endDate' ";
                break;
            //for week start date is sunday or 1st of month & end date is either saturday or 31/30 th of month
            case 'w':
                $dateArr = explode('-',$this->startDate);
                $startTime = mktime(0,0,0,$dateArr[1],$dateArr[2],$dateArr[0]);
                $saturday = strtotime('next saturday',$startTime);
                $dayLast = mktime(0,0,-1,$dateArr[1]+1,1,$dateArr[0]);
                $this->endDate = date('Y-m-d',$saturday);
                if($dayLast < $saturday)
                    $this->endDate = date('Y-m-d',$dayLast);
                
                //$days = $this->durationValue*7;
                $this->betweenClause = " `date` between '$this->startDate' and '$this->endDate' ";
                break;
            case 'm':
                $months = $this->durationValue;

                $this->endDate = Helper::add_date($this->startDate, 0, $months);
                //echo '<br/>'.$this->startDate.':::';echo $this->endDate; // echo '-----';echo Helper::add_date($this->startDate, 0, $months);
                @$startYearMonth = date('Ym', $this->startDate);
                @$endYearMonth = date('Ym', $this->endDate);

                //$this->endDate = Helper::add_date($this->startDate, 0, $months);
                $dateArr = explode('-',$this->startDate);
                $this->endDate = date('Y-m-d',mktime(0,0,-1,$dateArr[1]+$this->durationValue,1,$dateArr[0]));
                $startYearMonth = date('Ym', $this->makeTime($this->startDate));
                $endYearMonth = date('Ym', $this->makeTime($this->endDate));

                $this->betweenClause = " `yearMonth` between '{$this->getYearMonthFromDate($this->startDate)}' and '{$this->getYearMonthFromDate($this->endDate)}' ";
                break;
            case 'q':
                $months = $this->durationValue*3;
                $this->endDate = Helper::add_date($this->startDate,0, $months);
                $this->betweenClause = " `yearMonth` between '{$this->getYearMonthFromDate($this->startDate)}' and '{$this->getYearMonthFromDate($this->endDate)}' ";
                break;
            case 'y':
                $year = $this->durationValue;
                $this->endDate = Helper::add_date($this->startDate,0,0, $year);
                $this->betweenClause = " `yearMonth` between '{$this->getYearMonthFromDate($this->startDate)}' and '{$this->getYearMonthFromDate($this->endDate)}' ";
                break;
            case 'All':
                $this->startDate = '1990-01-01';
                $this->endDate = date('Y-m-d',time());
                $this->betweenClause = '';
                break;
            default : // by default the next day is the end date
                $this->endDate = Helper::add_date($this->startDate,1);
                $this->betweenClause = " `date` between '$this->startDate' and '$this->endDate' ";
        }
   }

   // return the duration
   public function getDuration()
   {
        return $this->duration;
   }
   
   // return the durationValue
   public function getDurationValue()
   {
        return $this->durationValue;
   }
    
   // return next year from current yearll
   public function getNextYear()
   {
        $nextYear = date('Y-01-01',strtotime(Helper::add_date($this->startDate,0,0,1)));
        return $nextYear;
   }

   // return next month from current month
   public function getNextMonth()
   {
        $nextMonth = date('Y-m-01',strtotime(Helper::add_date($this->startDate,0,1)));
        return $nextMonth;
   }
   
   private function makeTime($date)
   {
       $dateArr = explode('-', $date);
       $time = mktime(0,0,0,$dateArr[1],$dateArr[2],$dateArr[0]);
       return $time;
   }
   
   // return the week no for this month for this date
   public function getWeekNo()
   {
        $i=0;
        $week=0;
        $dateArr = explode('-',$this->startDate);
        if (date("N", mktime(0, 0, 0, $dateArr[1], 1, $dateArr[0])) <= "6") $week++;
        while ($i <= $dateArr[2]) {
            if (date("N", mktime(0, 0, 0, $dateArr[1], $i, $dateArr[0])) == 7) $week++;
            $i++;
            //echo "$this->startDate $i => $week : $dateArr[2] == ".date("N",mktime(0,0,0,$dateArr[1],$i,$dateArr[2]))."\n";
        }
        return $week;
   }

   // return the start date
   public function getStartDate()
   {
        return $this->startDate;
   }

   // return the end date
   public function getEndDate()
   {
        return $this->endDate;
   }
   
   // return the between clause according to date or yearmonth column name
   public function getBetweenClause()
   {
       return $this->betweenClause;
   }   
   
   public function getDayNo($date)
   {
       $dayArr = explode('-',$date);
       $dayno = gregoriantojd($dayArr[1], $dayArr[2], $dayArr[0]);
       return $dayno;
   }
   
   public function getDateFromDayNo($dayNo)
   {
       $cal = cal_from_jd($dayNo,  CAL_GREGORIAN);
       $dt = $cal['year'].'-'.$cal['month'].'-'.$cal['day'];
       return $dt;
   }
}
?>
