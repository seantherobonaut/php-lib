<?php
    //Saves log files to specific locations
    class Logger implements LogInterface
    {
        private $logPath = null;

        public function __construct($file)
        {
            $this->setLocation($file);
        }

        //Set the location of your log file
        public function setLocation($file)
        {
            $this->logPath = $file;
        }

        //Check the destination of the file and create a folder if one does not exist
        private function checkPath($file)
        {
            $path = str_replace(basename($file), '', $file);
            if(!file_exists($path))
                mkdir($path);
        }

        //Append entries to existing log file or create a new one
        public function pushLog($data)
        {
            $this->checkPath($this->logPath);

            $logFile = fopen($this->logPath, 'a');
            fwrite($logFile, $data."\n");
            fclose($logFile);
        }        
    }    
?>
