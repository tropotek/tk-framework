
## Introduction

TODO: move this to the Bs lib

Some notes about creating the new Tk4 symfony based framework.


### System Bootstrap steps

1. Setup Config and app settings
1. Create `Db` object
1. Create `Session`
1. Create `Request`
1. Create our system cache
1. Compile routes and cache them
1. Create `RequestContext` and `UrlMatcher` for routing
1. Create the `EventDispatcher` and add system events
1. Create the `ControllerResolver` and `ArgumentResolver`
1. Create the `FrontController` (`HttpKernel`) with the above required objects
2. Now we can execute the `FrontController` by calling ->handle($request); and ->send() to send the response to the client.


### System, Factory, Config Objects

Create 3 Main Singleton Files:

 - `\Tk\Config`:    For system config values only, should only hold native types
 - `\Tk\Factory`:   This object will create and store all global object instances for the Framework/Application
 - `\Tk\System`:    For all system info ie: getVersion(), isCli(), getScriptDuration(), ...

These 3 Objects are singletons and can be extended, However the first call to thier instance() method
will determin what will be returned throughout the system for that load.
EG:
```php
<?php
   $config = \Tk\Config::instance();            // First call means that \Tk\Config is stored as the instance
   // ...
   echo get_class(\App\Config::instance());
   // Would output 'Tk\Config'
?>
```
To get an instance of \App\Config as the instance you must check that you have 
instansiated the call before any calls to \Tk\Config:

```php
<?php
   $config = \App\Config::instance();            // First call means that \App\Config is stored as the instance
   // ...
   echo get_class(\Tk\Config::instance());
   // Would output 'App\Config'
?>
```


This is the default setup if you do not want to override any of the system objects
```php
<?php
   \Tk\Config::instance();
   \Bs\Factory::instance();
   $system = \Bs\System::instance();
   
   // ...
   echo get_class($system->getConfig());    // 'Tk\Config'
   echo get_class($system->getFactory());   // 'Tk\Factory'
   echo get_class($system);                 // 'Tk\System'
   echo get_class($system->getSystem());    // 'Tk\System'
   
?>
```

To initiate the system class be sure to instantiate any objects that you want to override before calling `\Tk\System::instance()` 
as this will auto instantiate the Factory and Config instances if none exists.
```php
<?php
   \App\Config::instance();
   \App\Factory::instance();
   $system = \App\System::instance();
   
   // ...
   echo get_class($system->getConfig());    // 'App\Config'
   echo get_class($system->getFactory());   // 'App\Factory'
   echo get_class($system);                 // 'App\System'
   echo get_class($system->getSystem());    // 'App\System'
   
?>
```

All objects using the `\Tk\Traits\SingletonTrait` wok in this manner and is handy for framework objects
that you would like to override later in the Application layer.


