<?php
    //Basic path router
    //For each call, return: content type, output, http status code
    class Router
    {
        private $routes = array();

        public function __construct()
        {
            //Set default 404 for GET
            $this->routes['GET']['404'] = function()
            {
                http_response_code(404);
                echo "Error 404: File not found";
            };

            //Set default 404 for POST
            $this->routes['POST']['404'] = function()
            {
                http_response_code(404);
                echo "Error 404: File not found";
            };        
        }
        
        //Add paths with a callback to GET
        public function get($route, $callback)
        {
            $this->routes['GET'][$route] = $callback;
        }
        
        //Add paths with a callback to POST
        public function post($route, $callback)
        {
            $this->routes['POST'][$route] = $callback;
        }

        public function put($route, $callback)
        {
            $this->routes['PUT'][$route] = $callback;
        }
        
        public function delete($route, $callback)
        {
            $this->routes['DELETE'][$route] = $callback;
        }

        //Call an anonymous function matching request_uri and request_method from $routes
        public function listen()
        {
            //format the input
            $method = $_SERVER['REQUEST_METHOD'];                
            $query = (isset($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'] : '';
            $uri = $_SERVER['REQUEST_URI'];
            $route = strtolower(str_replace($query,'',$uri));
            
            //if the route is not just '/'    
            if(strlen($route)>1)
            {
                //Erase 1st forward slash from path
                if($route[0]=="/")
                    $route = substr($route,1,strlen($route)-1);
                
                //Convert path to an array
                $route = explode("/", $route);
            }
            else
                $route = array('page','home');

            //Get the first node off of route (array pop)
            $node = array_shift($route);

            //Call and pass path data to anonymous functions
            if(isset($this->routes[$method][$node]))
            {
                call_user_func($this->routes[$method][$node], $route);                
            }
            else
            {
                if(isset($this->routes[$method]['404']))
                    call_user_func($this->routes[$method]['404'], $route);
            }
        }
    }
?>
