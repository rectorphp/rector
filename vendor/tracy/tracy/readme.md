[Tracy](https://tracy.nette.org) - PHP debugger
==============================================

[![Downloads this Month](https://img.shields.io/packagist/dm/tracy/tracy.svg)](https://packagist.org/packages/tracy/tracy)
[![Tests](https://github.com/nette/tracy/workflows/Tests/badge.svg?branch=master)](https://github.com/nette/tracy/actions)
[![Build Status Windows](https://ci.appveyor.com/api/projects/status/github/nette/tracy?branch=master&svg=true)](https://ci.appveyor.com/project/dg/tracy/branch/master)
[![Latest Stable Version](https://poser.pugx.org/tracy/tracy/v/stable)](https://github.com/nette/tracy/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/tracy/blob/master/license.md)


Introduction
------------

Tracy library is a useful helper for everyday PHP programmers. It helps you to:

- quickly detect and correct errors
- log errors
- dump variables
- measure execution time of scripts/queries
- see memory consumption


PHP is a perfect language for making hardly detectable errors because it gives great flexibility to programmers. Tracy\Debugger is more valuable because of that. It is an ultimate tool among the diagnostic ones.

If you are meeting Tracy for the first time, believe me, your life starts to be divided into one before the Tracy and the one with her. Welcome to the good part!

Documentation can be found on the [website](https://tracy.nette.org).


[Support Tracy](https://github.com/sponsors/dg)
-----------------------------------------------

Do you like Tracy? Are you looking forward to the new features?

[![Buy me a coffee](https://files.nette.org/icons/donation-3.svg)](https://github.com/sponsors/dg)

Thank you!


Installation and Requirements
-----------------------------

The recommended way to is via Composer:

```shell
composer require tracy/tracy
```

Alternatively, you can download the whole package or [tracy.phar](https://github.com/nette/tracy/releases) file.

| Tracy     | compatible with PHP | compatible with browsers
|-----------|---------------|----------
| Tracy 2.10| PHP 8.0 – 8.3 | Chrome 64+, Firefox 69+, Safari 15.4+ and iOS Safari 15.4+
| Tracy 2.9 | PHP 7.2 – 8.2 | Chrome 64+, Firefox 69+, Safari 13.1+ and iOS Safari 13.4+


Usage
-----

Tracy is activated by calling the `Tracy\Debugger::enable()' method as soon as possible at the beginning of the program, before any output is sent:

```php
use Tracy\Debugger;

require 'vendor/autoload.php'; // alternatively tracy.phar

Debugger::enable();
```

The first thing you'll notice on the page is the Tracy Bar in the bottom right corner. If you don't see it, it may mean that Tracy is running in production mode.
This is because Tracy is only visible on localhost for security reasons. To test if it works, you can temporarily put it into development mode using the `Debugger::enable(Debugger::Development)` parameter.


Tracy Bar
---------

The Tracy Bar is a floating panel. It is displayed in the bottom right corner of a page. You can move it using the mouse. It will remember its position after the page reloading.

[![Debugger-Bar](https://nette.github.io/tracy/images/tracy-bar.webp)](https://nette.github.io/tracy/tracy-debug-bar.html)

You can add other useful panels to the Tracy Bar. You can find interesting ones in [addons](https://componette.org) or you can [create your own](https://tracy.nette.org/en/extensions).

If you do not want to show Tracy Bar, set:

```php
Debugger::$showBar = false;
```


Visualization of Errors and Exceptions
--------------------------------------

Surely, you know how PHP reports errors: there is something like this in the page source code:

```pre
<b>Parse error</b>:  syntax error, unexpected '}' in <b>HomepagePresenter.php</b> on line <b>15</b>
```

or uncaught exception:

```pre
<b>Fatal error</b>:  Uncaught Nette\MemberAccessException: Call to undefined method Nette\Application\UI\Form::addTest()? in /sandbox/vendor/nette/utils/src/Utils/ObjectMixin.php:100
Stack trace:
#0 /sandbox/vendor/nette/utils/src/Utils/Object.php(75): Nette\Utils\ObjectMixin::call(Object(Nette\Application\UI\Form), 'addTest', Array)
#1 /sandbox/app/forms/SignFormFactory.php(32): Nette\Object-&gt;__call('addTest', Array)
#2 /sandbox/app/presenters/SignPresenter.php(21): App\Forms\SignFormFactory-&gt;create()
#3 /sandbox/vendor/nette/component-model/src/ComponentModel/Container.php(181): App\Presenters\SignPresenter-&gt;createComponentSignInForm('signInForm')
#4 /sandbox/vendor/nette/component-model/src/ComponentModel/Container.php(139): Nette\ComponentModel\Container-&gt;createComponent('signInForm')
#5 /sandbox/temp/cache/latte/15206b353f351f6bfca2c36cc.php(17): Nette\ComponentModel\Co in <b>/sandbox/vendor/nette/utils/src/Utils/ObjectMixin.php</b> on line <b>100</b><br />
```

It is not so easy to navigate through this output. If you enable Tracy, both errors and exceptions are displayed in a completely different form:

[![Uncaught exception rendered by Tracy](https://nette.github.io/tracy/images/tracy-exception.png)](https://nette.github.io/tracy/tracy-exception.html)

The error message literally screams. You can see a part of the source code with the highlighted line where the error occurred. A message clearly explains an error. The entire site is [interactive, try it](https://nette.github.io/tracy/tracy-exception.html).

And you know what? Fatal errors are captured and displayed in the same way. No need to install any extension (click for live example):

[![Fatal error rendered by Tracy](https://nette.github.io/tracy/images/tracy-error.png)](https://nette.github.io/tracy/tracy-error.html)

Errors like a typo in a variable name or an attempt to open a nonexistent file generate reports of E_NOTICE or E_WARNING level. These can be easily overlooked and/or can be completely hidden in a web page graphic layout. Let Tracy manage them:

[![Notice rendered by Tracy](https://nette.github.io/tracy/images/tracy-notice2.webp)](https://nette.github.io/tracy/tracy-debug-bar.html)

Or they may be displayed like errors:

```php
Debugger::$strictMode = true; // display all errors
Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // all errors except deprecated notices
```

[![Notice rendered by Tracy](https://nette.github.io/tracy/images/tracy-notice.png)](https://nette.github.io/tracy/tracy-notice.html)

Note: Tracy when activated changes the error reporting level to E_ALL. If you want to change this, do so after calling `enable()`.


Development vs Production Mode
------------------------------

As you can see, Tracy is quite talkative, which can be appreciated in the development environment, while on the production server it would cause a disaster. That's because no debugging information should be displayed there. Tracy therefore has **environment auto-detection** and if the example is run on a live server, the error will be logged instead of displayed, and the visitor will only see a user-friendly message:

[![Server Error 500](https://nette.github.io/tracy/images/tracy-error2.png)](https://nette.github.io/tracy/tracy-production.html)

Production mode suppresses the display of all debugging information sent out using [dump() |dumper], and of course also all error messages generated by PHP. So if you have forgotten some `dump($obj)` in the code, you don't have to worry, nothing will be displayed on the production server.

How does mode auto-detection work? The mode is development if the application is running on localhost (i.e., IP address `127.0.0.1` or `::1`) and there is no proxy (i.e., its HTTP header). Otherwise, it runs in production mode.

If you want to enable development mode in other cases, for example for developers accessing from a specific IP address, you can specify it as a parameter of the `enable()` method:

```php
Debugger::enable('23.75.345.200'); // you can also provide an array of IP addresses
```

We definitely recommend combining the IP address with a cookie. Store a secret token, e.g., `secret1234`, in the `tracy-debug` cookie, and in this way, activate the development mode only for developers accessing from a specific IP address who have the mentioned token in the cookie:

```php
Debugger::enable('secret1234@23.75.345.200');
```

You can also directly set the development/production mode using the `Debugger::Development` or `Debugger::Production` constants as a parameter of the `enable()` method.

(If you use the Nette Framework, take a look at how to set the mode for it, and it will then also be used for Tracy.)


Error Logging
-------------

In production mode, Tracy automatically logs all errors and exceptions to a text log. In order for logging to take place, you need to set the absolute path to the log directory to the `$logDirectory` variable or pass it as the second parameter to `enable()` method:

```php
Debugger::$logDirectory = __DIR__ . '/log';
```

Error logging is extremely useful. Imagine that all users of your application are actually beta testers who do top-notch work in finding errors for free, and you would be foolish to throw their valuable reports away unnoticed into the trash bin.

If you need to log your own messages or caught exceptions, use the method `log()`:

```php
Debugger::log('Unexpected error'); // text message

try {
	criticalOperation();
} catch (Exception $e) {
	Debugger::log($e); // log exception
	// or
	Debugger::log($e, Debugger::ERROR); // also sends an email notification
}
```

If you want Tracy to log PHP errors like `E_NOTICE` or `E_WARNING` with detailed information (HTML report), set `Debugger::$logSeverity`:

```php
Debugger::$logSeverity = E_NOTICE | E_WARNING;
```

For a real professional the error log is a crucial source of information and he or she wants to be notified about any new error immediately. Tracy helps him. She is capable of sending an email for every new error record. The variable $email identifies where to send these e-mails:

```php
Debugger::$email = 'admin@example.com';
```

(If you use the Nette Framework, you can set this and others in the configuration file.)

To protect your e-mail box from flood, Tracy sends **only one message** and creates a file `email-sent`. When a developer receives the e-mail notification, he checks the log, corrects his application and deletes the `email-sent` monitoring file. This activates the e-mail sending again.


Opening Files in the Editor
---------------------------

When the error page is displayed, you can click on file names and they will open in your editor with the cursor on the corresponding line. Files can also be created (action `create file`) or bug fixed in them (action `fix it`). In order to do this, you need to [configure the browser and the system](https://tracy.nette.org/cs/open-files-in-ide).


Variable Dumping
----------------

Every debugging developer is a good friend with the function `var_dump`, which lists all contents of any variable in detail. Unfortunately, its output is without HTML formatting and outputs the dump into a single line of HTML code, not to mention context escaping. It is necessary to replace the `var_dump` with a more handy function. That is just what `dump()` is.

```php
$arr = [10, 20.2, true, null, 'hello'];

dump($arr);
// or Debugger::dump($arr);
```

generates the output:

![dump](https://nette.github.io/tracy/images/dump-basic.webp)

You can change the default light theme to dark:

```php
Debugger::$dumpTheme = 'dark';
```

![dump](https://nette.github.io/tracy/images/dump-dark.webp)

You can also change the nesting depth by `Debugger::$maxDepth` and displayed strings length by `Debugger::$maxLength`. Naturally, lower values accelerate Tracy rendering.

```php
Debugger::$maxDepth = 2; // default: 3
Debugger::$maxLength = 50; // default: 150
Debugger::$dumpTheme = 'dark'; // default: light
```

The `dump()` function can display other useful information. `Tracy\Dumper::LOCATION_SOURCE` adds a tooltip with path to the file, where the function was called. `Tracy\Dumper::LOCATION_LINK` adds a link to the file. `Tracy\Dumper::LOCATION_CLASS` adds a tooltip to every dumped object containing path to the file, in which the object's class is defined. All these constants can be set in `Debugger::$showLocation` variable before calling the `dump()`. You can set multiple values at once using the `|` operator.

```php
Debugger::$showLocation = Tracy\Dumper::LOCATION_SOURCE; // Shows path to where the dump() was called
Debugger::$showLocation = Tracy\Dumper::LOCATION_CLASS | Tracy\Dumper::LOCATION_LINK; // Shows both paths to the classes and link to where the dump() was called
Debugger::$showLocation = false; // Hides additional location information
Debugger::$showLocation = true; // Shows all additional location information
```

Very handy alternative to `dump()` is `dumpe()` (ie. dump and exit) and `bdump()`. This allows us to dump variables in Tracy Bar. This is useful, because dumps don't mess up the output and we can also add a title to the dump.

```php
bdump([2, 4, 6, 8], 'even numbers up to ten');
bdump([1, 3, 5, 7, 9], 'odd numbers up to ten');
```

![bar dump](https://nette.github.io/tracy/images/bardump-en.webp)


Stopwatch
---------

Another useful tool is the debugger stopwatch with a precision of microseconds:

```php
Debugger::timer();

// sweet dreams my cherrie
sleep(2);

$elapsed = Debugger::timer();
// $elapsed = 2
```

Multiple measurements at once can be achieved by an optional parameter.

```php
Debugger::timer('page-generating');
// some code

Debugger::timer('rss-generating');
// some code

$rssElapsed = Debugger::timer('rss-generating');
$pageElapsed = Debugger::timer('page-generating');
```

```php
Debugger::timer(); // runs the timer

... // some time-consuming operation

echo Debugger::timer(); // elapsed time in seconds
```


Custom Logger
-------------

We can create a custom logger to log errors, uncatched exceptions, and also be called by `Tracy\Debugger::log()`. Logger implements the interface Tracy\ILogger.

```php
use Tracy\ILogger;

class SlackLogger implements ILogger
{
	public function log($value, $priority = ILogger::INFO)
	{
		// sends a request to Slack
	}
}
```

And then we activate it:

```php
Tracy\Debugger::setLogger(new SlackLogger);
```

If we use the full Nette Framework, we can set it in the NEON configuration file:

```neon
services:
	tracy.logger: SlackLogger
```


Monolog Integration
-------------------

This package provides a PSR-3 adapter, allowing for integration of [monolog/monolog](https://github.com/Seldaek/monolog).

```php
$monolog = new Monolog\Logger('main-channel');
$monolog->pushHandler(new Monolog\Handler\StreamHandler($logFilePath, Monolog\Logger::DEBUG));

$tracyLogger = new Tracy\Bridges\Psr\PsrToTracyLoggerAdapter($monolog);
Debugger::setLogger($tracyLogger);
Debugger::enable();

Debugger::log('info'); // writes: [<TIMESTAMP>] main-channel.INFO: info [] []
Debugger::log('warning', Debugger::WARNING); // writes: [<TIMESTAMP>] main-channel.WARNING: warning [] []
```


Faster Loading
--------------

The basic integration is straightforward, however if you have slow blocking scripts in web page, they can slow the Tracy loading.
The solution is to place `<?php Tracy\Debugger::renderLoader() ?>` into your template before any scripts:

```html
<!DOCTYPE html>
<html>
<head>
	<title>...<title>
	<?php Tracy\Debugger::renderLoader() ?>
	<link rel="stylesheet" href="assets/style.css">
	<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
</head>
```


AJAX and Redirected Requests
----------------------------

Tracy can display Debug bar and Bluescreens for AJAX requests and redirects. Tracy creates its own sessions, stores data in its own temporary files, and uses a `tracy-session` cookie.

Tracy can also be configured to use a native PHP session, which is started before Tracy is turned on:

```php
session_start();
Debugger::setSessionStorage(new Tracy\NativeSession);
Debugger::enable();
```

In case starting a session requires more complex initialization, you can start Tracy immediately (so that it can handle any errors that occur) and then initialize the session handler and finally inform Tracy that the session is ready to be used using the `dispatch()` function:

```php
Debugger::setSessionStorage(new Tracy\NativeSession);
Debugger::enable();

// followed by session initialization
// and start the session
session_start();

Debugger::dispatch();
```

The `setSessionStorage()` function has existed since version 2.9, before that Tracy always used the native PHP session.


Content Security Policy
-----------------------

If your site uses Content Security Policy, you'll need to add `'nonce-<value>'` and `'strict-dynamic'` to `script-src` for Tracy to work properly. Some 3rd plugins may require additional directives.
Nonce is not supported in the `style-src` directive, if you use this directive you need to add `'unsafe-inline'`, but this should be avoided in production mode.

Configuration example for [Nette Framework](https://nette.org):

```neon
http:
	csp:
		script-src: [nonce, strict-dynamic]
```

Example in pure PHP:

```php
$nonce = base64_encode(random_bytes(20));
header("Content-Security-Policy: script-src 'nonce-$nonce' 'strict-dynamic';");
```


nginx
-----

If Tracy does not work on nginx, it is probably misconfigured. If there is something like

```nginx
try_files $uri $uri/ /index.php;
```

change it to

```nginx
try_files $uri $uri/ /index.php$is_args$args;
```


Integrations
------------

This is a list of unofficial integrations to other frameworks and CMS:

- [Drupal 7](http://drupal.org/project/traced)
- Laravel framework: [recca0120/laravel-tracy](https://github.com/recca0120/laravel-tracy), [whipsterCZ/laravel-tracy](https://github.com/whipsterCZ/laravel-tracy)
- [OpenCart](https://github.com/BurdaPraha/oc_tracy)
- [ProcessWire CMS/CMF](https://github.com/adrianbj/TracyDebugger)
- [Slim Framework](https://github.com/runcmf/runtracy)
- Symfony framework: [kutny/tracy-bundle](https://github.com/kutny/tracy-bundle), [VasekPurchart/Tracy-Blue-Screen-Bundle](https://github.com/VasekPurchart/Tracy-Blue-Screen-Bundle)
- [Wordpress](https://github.com/ktstudio/WP-Tracy)

... feel free to be famous, create an integration for your favourite platform!
