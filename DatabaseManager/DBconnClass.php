<?php
    //Connect to database and provide wrapped PDO objects
    class DBconn 
    {
        private $dbname = null;
        private $conn = null;
        private $loggers = array();

        //Add a logger for database reporting to process to use
        public function addLogger(DBLoggerInterface $logger)
        {
            array_push($this->loggers, $logger);
            if($this->conn != null)
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        }

        //Create new PDO statement through constructor, if not valid a warning is triggered
        public function connect($host, $dbname, $user, $pass)
        {
            try
            {
                $this->dbname = $dbname;

                //Make a new database connection
                $this->conn = new PDO('mysql:host='.$host.';dbname='.$dbname, $user, $pass);

                //Set the errormode for database operations
                if(empty($this->loggers))
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);                    
                else
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);                    
            }
            catch(PDOException $e)
            {
                foreach($this->loggers as $key)
                    $key->run($e->getMessage());

                //If no loggers present, issue standard warning
                if(empty($this->loggers))
                    trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        //Return a new DBquery object containing a PDO statement, or an empty DBquery object if no connetion is detected
        public function getQuery($sql)
        {       
            $query = new DBquery;
            
            //If credentials are valid, set the queries connection and pass on any loggers
            if($this->conn)
            {
                $query->setConnection($this->conn->prepare($sql));
                
                //Add loggers to DBquery object
                foreach($this->loggers as $key)
                    $query->addLogger($key);
            }                

            return $query;
        }  

        //Return the name of the currently connected database
        public function getDBname()
        {
            return $this->dbname;
        }        
    }
?>
