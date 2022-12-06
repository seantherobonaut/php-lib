<?php
    //Outputs a log file to a provided destination using a custom format and timestamp
    class DBLogger extends Logger implements DBLoggerInterface
    {
        public function __construct($fileName)
        {
            parent::__construct($fileName);
        }

        //Trigger the output
        public function run($log)
        {
            $timestamp = TimeStamp::getStamp(TimeStamp::getTime());

            $this->pushLog($timestamp.' - '.$log);
        }
    }
?>
