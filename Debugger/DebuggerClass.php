<?php
    //This class catches and manages errors and exceptions 
    class Debugger
    {
        private $data = array();
        private $handlers = array();

        public function __construct()
        {
            ob_start();
        }

        //Add a new handler to deal with gathered data
        public function addHandler(DebugHandlerInterface $handler)
        {
            array_push($this->handlers, $handler);
        }

        //Unified method to accept notices, warnings, errors, and exceptions
        private function newEvent($type, $file, $line, $msg)
        {
            $this->data['time'] = microtime(true);
            $this->data['type'] = $type;
            $this->data['file'] = $file;
            $this->data['line'] = $line;
            $this->data['msg'] = $msg;
            $this->data['backtrace'] = debug_backtrace();
        }

        //Run all handlers of the data
        private function runHandlers()
        {
            foreach($this->handlers as $key)
                $key->run($this->data);
        }

        //Capture unhandled errors and warnings
        public function error_handler($errorNo, $message, $file, $line)
        {
            //If E_USER_ERROR, erase all previous output
            if($errorNo == 256) 
                ob_end_clean();

            $this->newEvent($errorNo, $file, $line, $message);
            $this->runHandlers();

            //If error = E_USER_ERROR, kill code
            if($errorNo == 256) 
                exit();
        }

        //Capture unhandled exceptions
        public function exception_handler($exception)
        {
            ob_end_clean();
            
            $this->newEvent('exception', $exception->getFile(), $exception->getLine(), $exception->getMessage());
            $this->runHandlers();

            exit();
        }

        //Register the error handler
        public function handleErrors($state)
        {
            if($state==true)
                set_error_handler(array($this, 'error_handler'), E_ALL); 
            else
                restore_error_handler();
        }

        //Register the exception handler
        public function handleExceptions($state)
        {
            if($state==true)
                set_exception_handler(array($this, 'exception_handler')); 
            else
                restore_exception_handler();
        }
    }
?>
