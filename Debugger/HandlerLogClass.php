<?php        
    //Outputs a log file to a provided destination using a custom format and timestamp
    class HandlerLog extends Logger implements DebugHandlerInterface
    {
        public function __construct($fileName)
        {
            parent::__construct($fileName);
        }

        //Trigger the output
        public function run(Array $data)
        {
            $timestamp = TimeStamp::getStamp($data['time']);

            if($data['type'] != 'exception')
                $output =  ' - Code:'.$data['type'].' '.$data['msg'].' -> '.$data['file'].'@line:'.$data['line'];
            else
                $output =  ' - Exception: '.$data['msg'].' -> '.$data['file'].'@line:'.$data['line'];

            $this->pushLog($timestamp.$output);
        }
    }
?>
