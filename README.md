# PHP-Lib
This repo provides various useful snippets of php code

**Example usage of the dependency manager**
```php
require './php-lib/DependencyManager/DependencyManagerClass.php';

//Create a new instance that points to the file holding a record of your dependencies
$autoLoader = new DependencyManager('./dependencies.php');
//Specify where the loader needs to scan when creating a record of dependencies
$autoLoader->setSearchPaths(array('./php-lib','./public'));
//Enable (true) or disable (false) the autoloader
$autoLoader->enableLoader(true);
```

**Example usage of the debugger**
```php
$debug = new Debugger;

//Add handlers that run when notices/warnings/errors/exceptions happen
$debug->addHandler(new HandlerWeb(array(E_ERROR, E_WARNING, E_NOTICE, 'exception')));
$debug->addHandler(new HandlerLog('./logs/debug.log'));

//Enable or disable error/exception handling
$debug->handleErrors(true);
$debug->handleExceptions(true);
```

**Example usage of the database manager**
```php
$conn = new DBconn;

//Optionally add a logging object (normal warnings show when absent)
$conn->addLogger(new DBLogger('./logs/database.log'));
//Connect to the database
$conn->connect('host', 'dbname', 'user', 'password');

//Get a wrapped PDO statement from DBConn
$query = $conn->getQuery("SELECT * FROM `users` WHERE id=?;");
//Run the query with ability to pass arguments
$query->runQuery(array(1));
//Get results using fetch() or fetchAll()
$result = $query->fetchAll();

print_r($result);
```
