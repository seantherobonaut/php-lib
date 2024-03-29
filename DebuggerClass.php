<?php
    //This class catches and manages errors and exceptions 
    class Debugger
    {
        private $data = array();
        private $handlers = array();

        public function __construct()
        {
            //turn off all error reporting
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            
            //begin output capture
            ob_start();
        }

        //Adds a new callback function to handle error data
        public function addHandler($handler)
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

            //log to php console
            if($this->data['type']=='E_ERROR' || $this->data['type']=='E_USER_ERROR' || $this->data['type']=='EXCEPTION')
                http_response_code(500);

            error_log($this->data['type'].' '.$this->data['msg'].' -> '.$this->data['file'].'@line:'.$this->data['line']);
        }

        //Capture unhandled errors, warnings, notices
        public function error_handler($errorNo, $message, $file, $line)
        {
            //erase all previous output if errors exist
            if($errorNo == E_ERROR || $errorNo == E_USER_ERROR) 
                ob_end_clean();

            $this->newEvent(array_search($errorNo, get_defined_constants(true)['Core']), $file, $line, $message);
            $this->runHandlers();

            //kill code if errors exist (or execution continues)
            if($errorNo == E_ERROR || $errorNo == E_USER_ERROR) 
                exit();
        }

        //Capture unhandled exceptions
        public function exception_handler($exception)
        {
            ob_end_clean();

            $this->newEvent('EXCEPTION', $exception->getFile(), $exception->getLine(), $exception->getMessage());
            $this->runHandlers();

            exit();
        }

        //Enable or disable error/exception handlers
        public function enable($state)
        {
            if($state===true)
            {
                set_error_handler(array($this, 'error_handler'), E_ALL); 
                set_exception_handler(array($this, 'exception_handler')); 
            }
            else
            {
                if($state===false)
                {
                    restore_error_handler();
                    restore_exception_handler();
                }
            }
        }

        //Call all user functions and pass error data to them
        private function runHandlers()
        {   
            //catch errors from buggy handlers and log them
            set_error_handler(function($errorNo, $message, $file, $line)
            {
                http_response_code(500);
                $this->newEvent(array_search($errorNo, get_defined_constants(true)['Core']), $file, $line, $message);
                error_log(date('[Y-n-d G:i:s e]').' - '.$this->data['type'].' '.$this->data['msg'].' -> '.$this->data['file'].'@line:'.$this->data['line']."\n", 3, 'errors.log');
                exit('An internal error has occurred. Please try again later.<br>');
            }, E_ALL);

            //catch unhandled exceptions from buggy handlers and log them
            try
            {
                //call each handler function
                foreach($this->handlers as $key)
                    call_user_func($key, $this->data);                
            }
            catch(Exception $e)
            {
                $this->newEvent('EXCEPTION', $e->getFile(), $e->getLine(), $e->getMessage());
                error_log(date('[Y-n-d G:i:s e]').' - '.$this->data['type'].' '.$this->data['msg'].' -> '.$this->data['file'].'@line:'.$this->data['line']."\n", 3, 'errors.log');
                exit('An internal error has occurred. Please try again later.<br>');
            }
            
            //set handler back to previous state
            set_error_handler(array($this, 'error_handler'), E_ALL);         
        }
    }
?>
