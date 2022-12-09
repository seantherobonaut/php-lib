<?php
    //Wrapper around PDO statement for more controll and less unhandled errors
    class DBquery
    {   
        private $statement=null;
        private $loggers = array();

        //Add a logger for database reporting to process to use
        public function addLogger(DBLoggerInterface $logger)
        {
            array_push($this->loggers, $logger);
        }

        //Accept and store PDO statement for use in queries
        public function setConnection(PDOStatement $statement)
        {
            $this->statement = $statement;
        }

        //Pass values (if any) to PDO statement and run the query if statement exists
        public function runQuery($valuesArray = null)
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

                    foreach($this->loggers as $key)
                        $key->run($errorCode.' '.$error[2]); 
                }
            }
        }

        //Return the number of results from the last query
        public function rowCount()
        {
            if($this->statement)
                return $this->statement->rowCount();
            else
                return 0;
        }

        //Return each result or an empty array
        public function fetch()
        {
            if($this->statement)
                return $this->statement->fetch(PDO::FETCH_ASSOC);
            else
                return Array();
        }

        //Return entire result set or an empty array 
        public function fetchAll()
        {
            if($this->statement)
                return $this->statement->fetchAll(PDO::FETCH_ASSOC);                  
            else
                return Array();
        }
    }
?>
