# LEGO FRAMEWORK
PHP Framework built from a combination of several libraries.

## Installation

```composer create-project akbaraditamasp/lego-framework:dev-main example-app```

Then run the following command in the terminal to start the localhost web server.

```php adel run```

## Routing
The route file is in `public/index.php`
   
Routing in Lego uses the [bramus/router](https://github.com/bramus/router) library. Below is a simple example.

```php
    $app->route("GET", "/", function() {
        return ["message" => "Hello World!"];
    });
```
The route handler can also reference a controller method.

```php
    $app->route("GET", "/", "Controller\\Welcome::index");
```

For more details, please visit the documentation from  [bramus/router](https://github.com/bramus/router).

## Controller
Controllers are the de facto way of handling HTTP requests in Lego. They enable you to clean up the routes file by moving all the inline route handlers to their dedicated controller files.

In Lego, the controllers are stored inside (but not limited to) the  `controllers/`  directory and each file represents a single controller. For example:

```php
    <?php
    namespace Controller;
    
    use Lego\App;
    
    class Welcome
    {
    	public static function index(App $app)
    	{
    		return ["message" => "Hello World!"];
    	}
    }
```

### Make Controller Command
You can make use of the following `php adel` command to create a new controller.

```php adel make:controller Post```

## Request
Request in Lego using library [sabre.io/http](https://github.com/sabre-io/http).

You can access the `request` object from the HTTP context passed to the route handler.

```php
    $app->route("GET", "/", function(App $app) {
	    $body = $app->request->getPostData();
	});
```

## Response
Response in Lego using library [sabre.io/http](https://github.com/sabre-io/http).

You can access the `response` object from the HTTP context passed to the route handler.

```php
    $app->route("GET", "/", function(App  $app) {
    	$app->response->setStatus(200);
    	$app->response->setHeader("Content-Type", "application/json");
    	$app->response->setBody(json_encode([
    		"message" => "Hello World!"
    	]));  
    
	    $app->finish();
    });
```
   
## File Uploads
Lego provides you an API for dealing with file uploads.
   
You can access the files using the `$app->request->getFiles()` method.

```php
    $app->route("POST", "/", function(App  $app) {
	    $image = $app->request->getFiles()["image"];
    
	    $image->move(__DIR__);
    });
```

## Validation
Lego uses the [rakit/validation](https://github.com/rakit/validation) library to do validation. For example:

```php
    $app->route("POST", "/", function(App $app) {
	    $app->validate([
		    'name' => 'required',
		    'email' => 'required|email',
		    'password' => 'required|min:6',
		    'confirm_password' => 'required|same:password',
		    'avatar' => 'required|uploaded_file:0,500K,png,jpeg',
		    'skills' => 'array',
		    'skills.*.id' => 'required|numeric',
		    'skills.*.percentage' => 'required|numeric',
	    ]);
    });
```

## Database
Lego uses [Eloquent](https://laravel.com/docs/9.x/eloquent) as an ORM.
   
You can set the database configuration in the `.env` file

### Creating Your First Model

```php adel make:model Post```
   
You can also generate the migration alongside the model by defining the `-m` flag.

```php adel make:model Post -m```
   
## Migrations
Lego uses [phinx](https://phinx.org/) as schema migrations.

You can create a new migration by running the following Adel command. The migration files are stored inside the `db/migrations` directory.

```php adel migration create Post```

### Run and Rollback
Once you have created the migration files you need, you can run the following Adel command to process migrations. For example:

```php adel migration migrate```

You can use Rollback command to undo previous migrations executed by Phinx.

```php adel migration rollback```

## Auth
By default Lego uses the User and UserLogin models for the authentication process.

You can generate an API token for a user using the `Auth::make`  method.

```php
    $app->route("POST", "/login", function(App $app) {
	    $user = User::find(1);
	    
	    return  Auth::make($user);
    });
```

You can use `$app->auth()`  method to guard the routes against the un-authenticated requests.

```php
    $app->route("GET", "/", function(App $app) {
	    $app->auth();
    
	    return ["message" => "Hello World!"];
    });
```

Or you want to make authentication optional

```php
    $app->route("GET", "/", function(App $app) {
        $app->auth(false);
    
        return ["message" => "Hello World!"];
    });
```

You can access user data via `$app->user`

```php
    $app->route("GET", "/", function(App $app) {
        $username = $app->user->username;
    
        return ["message" => "Hello World!"];
    });
```