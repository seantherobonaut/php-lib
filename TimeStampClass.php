<?php
    //Create comprehensive text timestamps
    class TimeStamp
    {
        //Get get epoc time with milliseconds
        public static function getTime()
        {
            return microtime(true);
        }

        //Return formatted stamp with date and time
        public static function getStamp($epoch)
        {
            //if Epoch time is float, remove decimals and add them to the output timestamp
            $sec = 0;
            $ms = 0;

            if($epoch > 0)
            {    
                $sec = floor($epoch);        
                $ms = $epoch - floor($epoch);
            }
            else
            {
                $sec = ceil($epoch);
                $ms = $epoch - ceil($epoch);
            }

            //Create new timestamp using rounded seconds from EPOCH time
            $time = new DateTime;
            $time->setTimeStamp($sec);

            $date = $time->format('n').'-'.$time->format('d').'-'.$time->format('Y');
            $time = $time->format('G').':'.$time->format('i').':'.$time->format('s');

            if($ms > 0)
                $time = $time.':'.sprintf('%03d', (1000*$ms));

            return '['.$date.']'.'['.$time.']';
        }

        //Return the difference between timestamps in seconds (requires a specific format Ex: [5-14-2018][14:13:08:71])
        public static function compareStamps($startTime, $endTime)
        {
            $val1 = self::getStampEpoch($startTime);
            $val2 = self::getStampEpoch($endTime);

            return round(($val2 - $val1),4);
        }

        //Accepts a valid stamp as a string, and returns the time since EPOCH + MS
        public static function getStampEpoch($stamp)
        {
            $stampArray = explode("][", $stamp);
            $stampArray[0] = str_replace('[', '', $stampArray[0]);
            $stampArray[1] = str_replace(']', '', $stampArray[1]);

            $date = explode('-', $stampArray[0]);
            $time = explode(':', $stampArray[1]);

            //if there is no $time[3], then just return epoc time without decimal
            if(!empty($time[3]))
                return mktime($time[0], $time[1], $time[2], $date[0], $date[1], $date[2]).'.'.$time[3];    
            else
                return mktime($time[0], $time[1], $time[2], $date[0], $date[1], $date[2]);    
        }
    }
?>
