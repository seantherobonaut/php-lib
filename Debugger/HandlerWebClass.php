<?php
    //Prints HTML messages to client with an option to blacklist certain error types
    class HandlerWeb implements DebugHandlerInterface
    {
        private $blacklist = null;

        public function __construct(Array $blacklist = array())
        {            
            $this->blacklist = $blacklist;
        }

        //Trigger output
        public function run(Array $data)
        {
            if(in_array($data['type'], $this->blacklist))                
                echo 'Something went wrong. Please try again later.<br>';
            else
            {
                $timestamp = TimeStamp::getStamp($data['time']);

                if($data['type'] != 'exception')
                {
                    if($data['type']==E_USER_ERROR || $data['type']==E_ERROR)
                        http_response_code(500);

                    echo $timestamp.' - Code:'.$data['type'].' '.$data['msg'].' -> '.$data['file'].'@line:'.$data['line'];
                }
                else
                {
                    http_response_code(500);
                    echo $timestamp.' - Exception: '.$data['msg'].' -> '.$data['file'].'@line:'.$data['line'];
                }
            }
        }
    }
?>
