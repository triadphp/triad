# Triad PHP Framework

Triad PHP Framework is PHP 5.3 framework handling (HTTP or other) requests to your application that
results in response - json, php serialized, raw, template engine - smarty or custom.

This framework was done with simplicity in mind - basically it handles requests and handling exceptions. 
Custom classes (database or template engine) can be easily implemented in application 
and this framework is not trying to implement custom database or template engine class - 
instead, use the one you like the most! 

Router can handle simple requests or MVP application at full - and you can easily create inline requests 
in your application (this Framework is HMVP - check
[HMVC](http://en.wikipedia.org/wiki/Hierarchical_model%E2%80%93view%E2%80%93controller) as reference) - even
to remote server. 

# Prerequisites
- PHP 5.3 or better (for namespace support)

# Requests and responses
Responses is dictionary with values that contain own serializing methods `outputBody` and `get`. 
Build in responses are 
- `\Triad\Responses\JsonResponse`
- `\Triad\Responses\PhpSerializeResponse`
- `\Triad\Responses\RawResponse`
- `\Triad\Responses\RedirectResponse`

Request `\Triad\Request` consists of 
- `method` - `create`, `read`, `update`, `delete`
- `path` (string with full path /site-path)
- `params` (dictionary array with params)

Request can be easily defined or created from http request 
```php
// this will be default response type, but methods inside application can override it
$response = new \Triad\Responses\JsonResponse(); 

// define
$request = \Triad\Request::factory("/users/get", array("params" => 1), $response);

// create from current http request
$request = \Triad\Requests\HttpRequest::fromServerRequest($response, array(
    // allow changeing response type to php or json http://localhost/?response_format=php
    "format_override" => true, 
    
    // enables callback for jsonp requests http://localhost/?callback=myfunction
    "enable_json_callback" => true 
));
```

Request is then called against application in order to execute it and get response
```php
$response = $request->execute($application)->response;
```

And response can be outputed with output buffer (php print) or returned
```php
$response->outputBody(); // output
var_dump($response->get()); // return
```

### Summary
Internal requests in same application are called as easy as 
```php
$created = \Triad\Request::factory("/users/create", array("email" => "john@doe.com"))
           ->execute($this->application)
           ->response
           ->get();
```

Requests to remote application running on remote http server are done using `\Triad\RemoteApplication` as  
```php
$remoteServer = \Triad\RemoteApplication::factory(array(
   "url" => "http://server02",
   "base_path" => "/", 
   "client_secret" => "" // if remote application client_secret set in config
));

$userData = \Triad\Request::factory("/users/get", array("id" => 1))
            ->execute($remoteServer)
            ->response
            ->get();
```

# Create application
To create a new application, implement own Application class that extends `\Triad\Application` 
```php
class Application extends \Triad\Application
{
    public function init() {
        // initialize database or other services used in application
        // $this->database = new \Triad\Database($this->configuration["database"]["dns"]);
            
        // set up routes
        $router = new \Triad\Router();
        
        // simple route that matches /increment-[number] 
        $router->add("#^/increment-(?P<number_to_increment>\d+)#", array($this, "myCustomHandler"), true); 
        $this->setRouter($router);
    }

    public function myCustomHandler(Application $application, Request $request, $params = array()) {
        $request->response["number"] = $params["number_to_increment"] + 1;
    }
    
    /**
     * Do something with exception occured during application execute
     * @param \Exception $e
     * @param \Triad\Request $request
     */
    public function handleException(\Exception $e, Request $request) {
        return parent::handleException($e, $request);
    }
}

$config = \Triad\Config::factory(__DIR__ . "/config.php");

$application = new Application($config);
$application->setEnvironment($config["environment"]);
```

`config.php` containing your app settings 
```php
<?php
return array(
    "base_path" => "/", 
    "environment" => "development", 
    "client_secret" => ""
    
    // define custom service settings  
    "database" => array
    (
        "dns" => "mysql:host=127.0.0.1;dbname=database;charset=UTF8"
    ),
);
```

### Full examples
Check examples of full applications that follow MVP, PHP namespaces and dependency injection design patterns. 
[Examples](https://github.com/triadphp/examples)

## Author
- [Marek Vavrecan](mailto:vavrecan@gmail.com)

## License
- [GNU General Public License, version 3](http://www.gnu.org/licenses/gpl-3.0.html)
