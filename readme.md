## Laravel Cronboy
![Travis](https://api.travis-ci.org/cronboy/cronboy.svg?branch=master)
![StyleCI](https://styleci.io/repos/72890665/shield?branch=master)
[![Latest Unstable Version](https://poser.pugx.org/cronboy/cronboy/v/unstable)](https://packagist.org/packages/cronboy/cronboy)
[![Total Downloads](https://poser.pugx.org/cronboy/cronboy/downloads)](https://packagist.org/packages/cronboy/cronboy)
[![Latest Stable Version](https://poser.pugx.org/cronboy/cronboy/v/stable)](https://packagist.org/packages/cronboy/cronboy)
[![composer.lock](https://poser.pugx.org/cronboy/cronboy/composerlock)](https://packagist.org/packages/cronboy/cronboy)
[![License](https://poser.pugx.org/cronboy/cronboy/license)](https://packagist.org/packages/cronboy/cronboy)

The idea to publish a service like Cronboy appeared when we have build web service that schedule jobs for our internal project. We notice that handling such kind of jobs is a general pattern and is boring to write those code from scratch for every project which need to execute  this type of work. Is it interesting for laravel web developers?  **Please leave some feadback, ideas, or questions to the github issues if you can.**

-

[Cronboy](http://cronboy.net) - is a distributed web service, that allows you to run scheduled jobs.

With Cronboy you can schedule a job execution in whatever time you want in the future. It creates, maintains, and reliably invokes scheduled work. Cronboy does not run any code. It only invokes code hosted elsewhere. Cronboy invokes jobs via HTTP/S endpoints or Messaging Queues. 

Cronboy run jobs on any schedule: now, later, or recurring. It monitors your jobs execution and keeps a history of each created job. It is disigned for high availability and reliability.

It is a cinch to create applications which are able to run scheduled jobs with Cronboy.


## # Examples

You can schedule a post request after an hour for _http:://your-domain/your-route-to-dispatch_

```php
<?php

Cronboy::call(
	'your-route-to-dispatch', ['id'=> 673212236], '+1 hour'
);
```

You can schedule a closure invocation after 13 minutes

```php
<?php

Cronboy::at(Carbon::now()->addMinutes(13))
	->dispatch(function(){
		logger('Task was added 13 minutes ago')
	});
```

Or you can schedule a Laravel Job Class after a week

```php
<?php	

Cronboy::aWeekLater()
	->dispatch(
		new SendEmail('test@test.com')
 	);
```

## # Features

* Run different types of jobs on any schedule
	* invoke endpoints via HTTP/S
	* invoke closure's 
	* invoke Laravel Job Classes
	* put Laravel Job Classes in a messaging queue
* Providing recurring jobs (in development)
* Ensurees jobs security and guarantees delivery

## # Requirements
* php >= 5.6
* laravel ~5.*
* carbon 1.*
* superclosure 2.*

## # Installation

Require this package in your composer.json and update composer. This will download the package.

`"cronboy/cronboy": "*"`

or run in the command line:

`composer require Cronboy/cronboy`

After updating composer open your `config/app.php` file and add the CronboyServiceProvider to providers array:

```php
<?php	

'providers' => [
	...
	Cronboy\Cronboy\CronboyServiceProvider::class,
]
``` 

You can use the facade if you want to. Add it to aliases array in `config/app.php` file:

```php
<?php	

'aliases' => [
	...
	'Cronboy' => Cronboy\Cronboy\Facades\Cronboy::class
]

```

## # Configuration

Now is time to privide required keys. First of all we need to publish cronboy configuration file:

```
php artisan vendor:publish --provider="Cronboy\Cronboy\CronboyServiceProvider" --tag=config
```

After that you will find configuration file `cronboy.php` in your application `config` folder. Copy and paste into keys from your [cronboy.net](http://cronboy.net) account

```php
<?php	

...
return [
	  ...
    'token'     => 'api token, for request authorization',
    'secret'    => 'secret key, for params signature',
    'id'        => 'application key, unique application identifier',
     ...
];
```

Now Laravel Cronboy Are Ready To Go!

## # Review
* [Usage]()
* [Scheduling and dispatching jobs]()
* [Schedule time] ()
* [Developing Mode] ()
* [Exceptions] ()
* [Jobs History] ()
* [When Cronboy decides that a Job was executed] ()

## # Usage

You can get a `cronboy` instance in multiple ways:

I. **IoC Container**

```php
<?php	

app(Cronboy\Cronboy\Cronboy::class)
	->afterOneMinute()
	->call('your-dispatch-task-route', []);
```

II. **Dependency injection**

```php
<?php	

public function scheduleATask(Cronboy\Cronboy\Cronboy $cronboy) {
	$cronboy->afterOneMinute()->call('your-dispatch-task-route', []);
}
```

III. **Laravel Facade**

```php
<?php	

Cronboy::afterOneMinute()
	->call('your-dispatch-task-route', []);
```

IV. **helper function**

```php
<?php	

cronboy()->afterOneMinute()
	->call('your-dispatch-task-route', []);
```

## # Scheduling and dispatching jobs

Laravel Cronboy provides to you scheduling for next types of jobs:

1. Endoint invocation via HTTP/S
2. Job as a Closure
3. Laravel Job Class
4. Qeueable Laravel Job Class

#### Endoint invocation via HTTP/S

`Cronboy::call($url, array $params, $time = null)`

Cronboy provides an easy way to invoke an endpoint via HTTP/S at any time you want just apply to `call` method

```php
<?php	

cronboy()->call(
	"/my-schedule-task-dispatch-route", ['message' = 'Hello World!'], '+1 hour'
)
```

After an hour your application will receive a post request with provided $params at _http://your-domain/my-schedule-task-dispatch-route_

> _your-domain_ will be resolved from `application identifier(APP_ID)` which you have been set in config/cronboy.php configuration file

In your defined route handler you can run any code you want to dispatch scheduled job:

```php
<?php	

...


Route::post("/my-schedule-task-dispatch-route", function () {
	$message = $request('message'); // $message will receive 'Hello World!'
	
	...
	
	return response()
		->json(
			[], 200
		);
});
```

**Schedule time**

You can set the time when job must be run passing it to `$time` argument  of the`call` method. It can be a string in valid [format] (http://php.net/manual/en/datetime.formats.php) or an instance of Carbon class.

**Changing the request Verb**


You can change the verb of http request using `via()` method. By default it will send a POST request.

> If you have been scheduled a post request, and the route is protected by VerifyCsrfToken you must add it to `$except` array of VerifyCsrfToken middleware.


Lets schedule a GET request, it is simple

```php
<?php	

Cronboy::via('GET')
	->call(
		"/my-schedule-task-dispatch-route", ['message' = 'Hello World!'], Carbon::now()->addMinutes(22)
	)
```

**Security**

For security reason Cronboy will sign request params with a secret key, provided by cronboy.io service. Secret key you have been set in `config/cronboy.php` file during configuration step. 

To enable params signature check you must assign **`Cronboy\Cronboy\Http\Middleware\VerifySignature`** middleware to route that will handle scheduled job. If the request was forged **`MismatchingSignatureException`** will be thrown.

**Mark a job as dispatched**

Cronboy decides that a job was dispatched successfully if your application response has a status of 200. In other way cronboy will retries to make a request until 200 will be received or the maximum retry number will be achieved. It will increase time interval between each retried request. After maximum retries (10 by default) the job status will be set to **"Failed"** 

#### Job as a Closure

`Cronboy::dispatch($closure, $time = null)`

Laravel Cronboy can invoke closure at any time you want. To schedule an closure use `dispatch()` method:

```php
<?php	

\Cronboy::dispatch(
	function(){
		logger("This callback was scheduled 2 weeks ago");
	}, 
	Carbon::now()->addWeek(2)
);
```

In example above a closure will be invoked after two weeks. The schedule time you can pass as the second argument of the `dispatch()` method.

When you are using `dispatch()` method the request signature will be checked by default.

If an exception will be thrown, cronboy will retry execution in the same way as was described early. 


#### Laravel Job Class

`Cronboy::dispatch($job, $time = null)`


Cronboy can dispatch [Laravel Job Classes](https://laravel.com/docs/5.3/queues#creating-jobs). Simply pass it to dispatch method as the first argument.

For example we want to send a reminder email for our application customer after a week. Imagine that we have a dedicated Laravel Job Class for this kind of work.


```php
<?php	


namespace App\Jobs;

use App\Podcast;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReminderEmail
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $email;

    /**
     * Create a new job instance.
     *
     * @param  App\Email  $email
     * @return void
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @param  Mailer $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        // Send email...
    }
}
```

It is nothing as easy as:


```php
<?php	

cronboy()
	->aWeekLater()
	->dispatch(
		new SendReminderEmail($email)
	);
```

**Put a Laravel Job Class into a queue**

If you are using queues, you can put Laravel Jobs into a queue at scheduled time. In order for this to work your Laravel Job should implement ShouldQueue interface. For details [Laravel Job Classes](https://laravel.com/docs/5.3/queues#creating-jobs) 

For example lets schedule a job that will be queued after 3 months.


```php
<?php	

cronboy()
	->inThreeMonths()
	->dispatch(
		new SendReminderEmail($email)->onQueue('reminder-emails')
	);
```


## # Schedule time

#### Timezone

You can specify a timezone. By default it will use timezone set for laravel application in `app/config.php`.

For example you can schedule a job that must be dispached a week later after now using America/Kentucky/Louisville timezone.


```php
<?php	

cronboy()
	->aWeekLater('America/Kentucky/Louisville')
	->dispatch(
		new SendReminderEmail($email)
	);

```

#### Helper functions to work with time

`Cronboy::at($time, $timezone=null)`

> You can pass schedule time using `at` method instead of pass it as an argument for `call` or `dispatch` method.
> 
	* $time - can be a string in valid [format] (http://php.net/manual/en/datetime.formats.php) or an instance of Carbon class
	* $timezone - any timezone in valid php format


```php
<?php	

# Call /test after 4 minutes
Cronboy::at('+4 minutes')->call('/test', []);

# dispatch a closure after 3 hours
Cronboy::at(Carbon::now()->addHours(3))
	->dispatch(
		function(){
			...
		}
);

```

**AfterTime Functions**


| List of available functions                                      |
--------------------------------------------------------------------
|`Cronboy::afterOneMinute($timezone=null)`                         | 
|`Cronboy::afterFiveMinutes($timezone=null)`|
|`Cronboy::afterTenMinutes($timezone=null)`|
|`Cronboy::afterHalfAnHour($timezone=null)`|
|`Cronboy::afterAnHour($timezone=null)`|
|`Cronboy::afterThreeHour($timezone=null)`|
|`Cronboy::aWeekLater($timezone=null)`|
|`Cronboy::afterTwoWeeks($timezone=null)`|
|`Cronboy::aMonthLater($timezone=null)`|
|`Cronboy::inThreeMonths($timezone=null)`|

**Examples**

```php
<?php	

# Call /test after 10 minutes
Cronboy::afterTenMinutes()->call('/test', []);

# dispatch a closure after 3 month
Cronboy::inThreeMonths("America/Ojinaga")->addHours(3))
	->dispatch(
		function(){
			...
		}
);

```

## # Developing Mode

For development you can use `debug()` method. It will call endpoint or dispatch a job directly without using a cronboy service.

```php
<?php	

cronboy('+1 hour')
	->debug()
	->call('/test-route', []);

```

## # Exceptions

Exception which can be thrown:


| Exception | Description|
------------|------------
|Cronboy\Cronboy\Client\Exceptions\InvalidApiTokenException| Api Token provided for cronboy service is invalid |
|Cronboy\Cronboy\Client\Exceptions\InvalidAppKeyException|Application identifier provided for cronboy service is invalid|
|Cronboy\Cronboy\Client\Exceptions\InvalidArgumentsException| Invalid data was provided for cronboy service |
|Cronboy\Cronboy\Client\Exceptions\InvalidCronboySaaSResponse| Invalid response  was received from cronboy service |
|Cronboy\Cronboy\Exceptions\ClosureUnserializationException| An error was occured during serialization/desirialization of clusure or job class |
|Cronboy\Cronboy\Exceptions\InvalidArgumentException| Invalid arguments was provided for schedule a job |
|Cronboy\Cronboy\Exceptions\InvalidScheduleTimeException| Invalid schedule time is provided |
|Cronboy\Cronboy\Exceptions\MismatchingSignatureException| Signature is wrong or is missing |


## # Jobs History

Cronboy Service will track and keep you scheduled jobs history. You can explore it in your cronboy account.


## # Contributors

Let people know how they can dive into the project, include important links to things like issue trackers, irc, twitter accounts if applicable.

## # License

This package is licensed under the MIT license.

