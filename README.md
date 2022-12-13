# **A useful collection of php code**
> **Note:** Each of these tools can be used independently of each other. 

## **Debugger** - *catch errors/exceptions*

The debugger uses `set_exception_handler(CALLBACK)` and `set_error_handler(CALLBACK)` to catch all errors and exceptions. This includes errors and exceptions caused by problematic custom callback functions passed to the debugger's addHandler() method. **Php's default error reporting is turned off** to hide sensitve server details from spilling out to the client.

Additionally, `ob_start()` is used in conjuction with `ob_end_clean()` to catch and purge all output from reaching the client if a critical error/exception happens. This is done to prevent half rendered web pages, or other weird issues.

**The `array()` passed to your handler callback function contains:**
* [ "time" ] = (float) EPOC time with milliseconds follow the decimal
* [ "type" ] = Title of the error/exception
* [ "file" ] = Exact path to the problem file
* [ "line" ] = Line number in the file
* [ "msg" ] = Output of the error/exception message
* [ "backtrace" ] = The output of `debug_backtrace()`

*Debugger setup example:*
```php
require 'php-lib/DebuggerClass.php';
$debug = new Debugger(); 

//Optionally add a callback that runs when issues happen
$debug->addHandler(function($error_data)
{
    //Customize how you want the issue handled
    error_log("Something went wrong.\n", 3, "logfile.log");

    if($error_data['type'] != 'E_USER_ERROR')
    {
        echo '<pre>';
        print_r($error_data);
        echo '</pre>';
    }
});

//Enable (true) or disable (false) error/exception handling
$debug->enable(true);
```
## **Dependency Manager** - *autoload your classes*

The dependency manager works by using `spl_autoload_register(CALLBACK)` to task another function with loading the file that contains your class/abstract/interface. It only needs to do this once, instead of every time. 

The path for your class file is loaded (***require 'file.php'***) from an associative array declared in a separate file. This 'dependency' file contains an array that is built by recursively scanning directories that you specify containing class/abstract/interface filess. To make the dependency manager work properly, the rules below MUST be followed:<br>

&nbsp;&nbsp;&nbsp;&nbsp;**1.** Each class/abstract/interface resides in its own file<br>
&nbsp;&nbsp;&nbsp;&nbsp;**2.** Each file MUST be named exactly like this:<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;**=>**&nbsp;**"Name + Class/Abstract/Interface.php"**<br>
&nbsp;&nbsp;&nbsp;&nbsp;**3.** Each dependency name MUST match the file prefix it resides in<br>
&nbsp;&nbsp;&nbsp;&nbsp;**4.** Do NOT allow recursive searching conflicting directories

Example: **"AwesomeClass.php"**<br>
*contains:* `class Awesome{...}`

> **BIG NOTE:** *for performance, the dependency file is created ***only*** once*<br>
* This means that if you ever add, delete, rename, or move an existing class/abstract/interface file, you must delete the dependency list file so a new one can be generated.

This entire tool came about to find a way around performance hit caused by frequent use of php's default `require_once`. While `require_once` is safer than `require`, it can gradually slow down your code every time goes to check if that file has been loaded already. This performance hit be lessened in the most recent versions of php, but I'm skeptical. With my tool, you don't have to `require` or `require_once` any of your files other than the dependency manager itself. Anytime you create an object that ***hasn't*** had its class file loaded yet, the dependency manager simply `require`'s the php file from dependency array index in memory that matches the name of your class. Make sure that file is updated though, or it will yell at you.<br>

*Dependency Manager setup example:*
```php
require 'php-lib/DependencyManagerClass.php';

//Point constructor to the list file
$loader = new DependencyManager('app/dependency_list.php');

//Add directories the loader can create a dependency list from
$loader->addSearchPath('directory_with_class_files');

//Enable (true) or disable (false) the autoloader
$loader->enable(true);
```

## **DBConn & DBquery** - *talk to databases easily*

The DBconn and DBquery classes are essentially *private* encapsulated forms of **PDO** and **PDOStatement** respectively with more functionality and safety built in. I recommend storing an object instance of the `DBconn` class in a global variable since it only needs to be made once. Running `DBconn->getQuery()` gets you a reusable `DBQuery` object that again, encapsulates a **PDOStatement**. 

By default the error mode is set to trigger warnings. Adding your own custom callback functions automatically sets the error mode to silent, and passes all warnings in the form of a string to your handlers. All methods that provide additional functionality have debugging features built in. When something goes wrong, they will either trigger warnings, or pass an **$error** string to your handlers. 

> All of DBconn's methods can be called out of order, but I recommend setting up your error handlers first. Even if the database connection fails, you can still catch those errors before they turn into unhandled warnings.

### <u>**DBconn**</u>
*Database connection setup example:*
```php
require 'php-lib/DBconnClass.php';
require 'php-lib/DBQueryClass.php';

//Create new instance
$db_conn = new DBconn;

//Optionally add a callback that replaces default warnings
$db_conn->addHandler(function($error)
{
    //Customize how you want the issue handled
    error_log($error."\n", 3, 'logs/database.log');
});

//Connect to a database
$db_conn->connect('host', 'dbname', 'username', 'password');
```
***Additional methods included with `DBconn`***
```php
//Get the name of the currently connected database (string)
$db_conn->getDBname();

//Get a list of tables contained in the database (array)
$db_conn->getTables()

//Check if a table exists in the database (true/false)
$db_conn->table_exists($tableName);

//Get a list of the table's columns (array)
$db_conn->getColumns($tableName);
```

### <u>**DBquery**</u>
The `DBquery` class is the bread and butter of database interactions because it encapsulates the all powerful **PDOStatement**. The functions runQuery(), rowCount(), fetch(), and fetchAll() all include default and custom error handling just like DBconn's methods. 
> If your query has bound parameters, their arguments **MUST** be passed in through an array.

*Basic query setup example:*
```php
//Get an encapsulated prepared query from DBConn
$query = $conn->getQuery("SELECT * FROM `users` WHERE id=?;");

//Run the query with an array containing arguments
$query->runQuery(array(1));

//Get results using fetch() or fetchAll() (assoc array)
$result = $query->fetchAll();

echo var_dump($result);
```

## **UTILS** - *various useful functions*

Instead of classes, this is just 1 file that has some useful functions. Presently, there's a single function with many more planned, and well on the way. So far, we have `append_file()`.

### **append_file(** $file, $data **)**
This function does exactly what it hints it. It appends data to a file, but it makes sure the directory exists first before trying. This avoids warnings and errors while allowing you to write to files just a single line without having to worry.

*Basic append_file() example:*
```php
append_file("logs/debug.log", "Hello world!\n");
```
