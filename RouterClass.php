<?php
    //Basic path router
    class Router
    {        
        private $routes = array();

        //Add routes with a callback to GET
        public function get($route, $callback)
        {
            $this->routes['GET']['/'.trim(strtolower($route), '/').'/'] = $callback;
        }
        
        //Add routes with a callback to POST
        public function post($route, $callback)
        {
            $this->routes['POST']['/'.trim(strtolower($route), '/').'/'] = $callback;
        }

        //Add routes with a callback to PUT
        public function put($route, $callback)
        {
            $this->routes['PUT']['/'.trim(strtolower($route), '/').'/'] = $callback;
        }

        //Add routes with a callback to DELETE
        public function delete($route, $callback)
        {
            $this->routes['DELETE']['/'.trim(strtolower($route), '/').'/'] = $callback;
        }

        //Call an anonymous function matching request_uri and request_method from $routes
        public function listen()
        {
            //format the input
            $method = $_SERVER['REQUEST_METHOD'];                
            $queryString = (isset($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'] : '';
            $path = '/'.trim(strtolower(str_replace($queryString,'',$_SERVER['REQUEST_URI'])), '/').'/';
                         
            //check if the array for that request method exists
            if(isset($this->routes[$method]))
            {                
                //find routes that match part of the url
                $routematches = array();
                foreach($this->routes[$method] as $route => $callback)
                {
                    $pattern = "/^".preg_quote($route,"/")."/i";

                    if(preg_match($pattern, $path)) 
                        $routematches[$route] = $callback;
                }

                //sort results on key length where largest is first
                uksort($routematches, function($a,$b)
                {
                    if(strlen($a) == strlen($b))
                    return 0;
                
                    return (strlen($a) > strlen($b)) ? -1 : 1;
                });    

                //pass left over route data to callback function
                if(count($routematches)>0)
                {
                    $matchKey = array_keys($routematches)[0];
                    
                    $matchCallback = array_shift($routematches);
                    
                    //trim route(only first occurrence) from path
                    $trimmed = trim(implode("", explode($matchKey, $path, 2)), '/');
                    //convert remaining string to an array split by '/'
                    $route_data = (!empty($trimmed)) ? explode("/", $trimmed) : array();

                    call_user_func($matchCallback, $route_data); 
                }
                else
                {
                    if(isset($this->routes[$method]['/'.'404'.'/']))
                        call_user_func($this->routes[$method]['/'.'404'.'/'], $route);
                    else
                    {
                        http_response_code(404);
                        echo 'Route not found.';
                    }
                }
            }
            else
            {
                http_response_code(404);
                echo 'Method not found.';
            }
        }
    }
?>
