<?php
    //Wrapper around PDO statement for more controll and less unhandled errors
    class DBquery
    {   
        private $statement=null;
        private $handlers = array();
        
        //Add a handler for database reporting to process to use
        public function addHandler($handler)
        {
            array_push($this->handlers, $handler);
        }

        //Accept and store PDO statement for use in queries
        public function setConnection(PDOStatement $statement)
        {
            $this->statement = $statement;
        }

        //Pass values (if any) to PDO statement and run the query if statement exists
        public function runQuery(Array $valuesArray = null)
        {
            if($this->statement)
            {
                $this->statement->closeCursor();

                if(!$valuesArray)
                    $this->statement->execute();
                else
                    $this->statement->execute($valuesArray); 

                //Check and log any errors
                if($this->statement->errorCode() != 0)
                {
                    $errorCode = $this->statement->errorCode();
                    $error = $this->statement->errorInfo();

                    //Call handlers, if empty, then PDO will trigger warnings for us
                    foreach($this->handlers as $key)
                        call_user_func($key, $errorCode.' '.$error[2]);
                }
            }
            else
            {
                if(empty($this->handlers))
                    trigger_error('DBQuery is missing a PDO statment!', E_USER_WARNING);
                else
                    foreach($this->handlers as $key)
                        call_user_func($key, 'DBQuery is missing a PDO statment!');
            }
        }

        //Return the number of results from the last query
        public function rowCount()
        {
            if($this->statement)
                return $this->statement->rowCount();
            else
            {
                if(empty($this->handlers))
                    trigger_error('DBQuery is missing a PDO statment!', E_USER_WARNING);
                else
                    foreach($this->handlers as $key)
                        call_user_func($key, 'DBQuery is missing a PDO statment!');

                return 0;
            }
        }

        //Return each result or an empty array
        public function fetch()
        {
            if($this->statement)
                return $this->statement->fetch(PDO::FETCH_ASSOC);
            else
            {
                if(empty($this->handlers))
                    trigger_error('DBQuery is missing a PDO statment!', E_USER_WARNING);
                else
                    foreach($this->handlers as $key)
                        call_user_func($key, 'DBQuery is missing a PDO statment!');

                return Array();
            }
        }

        //Return entire result set or an empty array 
        public function fetchAll()
        {
            if($this->statement)
                return $this->statement->fetchAll(PDO::FETCH_ASSOC);                  
            else
            {
                if(empty($this->handlers))
                    trigger_error('DBQuery is missing a PDO statment!', E_USER_WARNING);
                else
                    foreach($this->handlers as $key)
                        call_user_func($key, 'DBQuery is missing a PDO statment!');

                return Array();
            }
        }
    }
?>
