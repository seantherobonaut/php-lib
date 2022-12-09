<?php
    //Loads Class/Interface/Abastract from an array pulled pulled from a dependency file
	class DependencyManager
	{
		private $listFile = '';
		private $dependencies = array();
		private $searchPaths = array();

		public function __construct($listFile)
		{
			$this->listFile = $listFile;
		}

		//Load file required by Class/Interface/Abastract
		private function loadDependency($callName) 
		{		
			require $this->dependencies[$callName];	
		}

        //Add a directory to find loadable files in
		public function addSearchPath($path)
		{
            array_push($this->searchPaths, $path);		
		}

		//Register autoload function
		public function enable($state)
		{
			if($state==true)
			{
				if(!file_exists($this->listFile))
					$this->buildList();

				//Load dependency array from file, store it in the object
				require $this->listFile;
				$this->dependencies = $dependencyList;
				
				spl_autoload_register(array($this, 'loadDependency')); 
			}
			else
				spl_autoload_unregister(array($this, 'loadDependency'));
		}

		//Build an assoc array of dependencies and write it to a file
		public function buildList()
		{
			$regexNeedles = array("/^.+Interface\.php$/", "/^.+Abstract\.php$/", "/^.+Class\.php$/");
			
			//Get array of files by searching for regex matches
			$files = array();
			foreach($regexNeedles as $needle) 						
			{
				foreach($this->searchPaths as $path) 			
				{
					if(file_exists($path))
					{
						$resultArray = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)), $needle, RecursiveRegexIterator::GET_MATCH);
						foreach($resultArray as $row)
							array_push($files, str_replace("\\", "/", $row[0]));																	
					}		
                    else
                        trigger_error('directory '.$path.' for autoloader does not exist', E_USER_WARNING);
				}
			}

			//Make an assoc array of dependencies where each index matches their Class/Interface/Abastract name
			$dependencies = array();
			foreach($files as $value)
			{
				//Convert name of file to instance name for array indexing
				$includeName = str_replace('Class.php', '', basename($value));
				$includeName = str_replace('.php', '', basename($includeName));

				//TODO funnel all instances of the duplicated class file into crash array.
				if(empty($dependencies[$includeName]))					
					$dependencies[$includeName] = $value;
				else
					trigger_error("Duplicate instance of \"$includeName\" detected at ($value)", E_USER_ERROR);
			}

			//Convert array into php code string to be stored in a file
			$output = null;
			foreach($dependencies as $key => $value)   
				$output .= "        '$key' => '$value',\n";
			$output = substr($output, 0, -2); //erases the last newline and comma
			$output = "<?php\n    \$dependencyList = array\n    (\n$output\n    );\n?>\n";
				
			//Save output to file
			$listFile = fopen($this->listFile, 'w');
			fwrite($listFile, $output);
			fclose($listFile);
		}			
	}
?>
