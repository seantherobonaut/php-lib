<?php
    //Connect to database and provide wrapped PDO objects
    class DBconn 
    {
        private $dbname = null;
        private $conn = null;
        private $handlers = array();

        //Add a handler for database reporting to process to use
        public function addHandler($handler)
        {
            array_push($this->handlers, $handler);

            //suppress warnings if loggers are present to handle them
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

                //Set the errormode if handlers are present
                if(empty($this->handlers))
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);                    
                else
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);                    
            }
            catch(PDOException $e)
            {
                //If no handlers present, issue standard warning
                if(empty($this->handlers))
                    trigger_error($e->getMessage(), E_USER_WARNING);
                else
                    foreach($this->handlers as $key)
                        call_user_func($key, $e->getMessage());
            }
        }

        //Return a new DBquery object containing a PDO statement, or an empty DBquery object if no connetion is detected
        public function getQuery($sql)
        {       
            $query = new DBquery;
            
            //If credentials are valid, set the queries connection and pass on any handlers
            if($this->conn)
                $query->setConnection($this->conn->prepare($sql));

            //Add handlers to DBquery object
            foreach($this->handlers as $key)
                $query->addHandler($key);

            return $query;
        }  

        //Return the name of the currently connected database
        public function getDBname()
        {
            return $this->dbname;
        }        
    }
?>
