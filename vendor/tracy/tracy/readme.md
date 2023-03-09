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


Installation and requirements
-----------------------------

The recommended way to is via Composer:

```shell
composer require tracy/tracy
```

Alternatively, you can download the whole package or [tracy.phar](https://github.com/nette/tracy/releases) file.

| Tracy     | compatible with PHP | compatible with browsers
|-----------|---------------|----------
| Tracy 2.10| PHP 8.0 – 8.2 | Chrome 64+, Firefox 69+, Safari 15.4+ and iOS Safari 15.4+
| Tracy 2.9 | PHP 7.2 – 8.2 | Chrome 64+, Firefox 69+, Safari 13.1+ and iOS Safari 13.4+
| Tracy 2.8 | PHP 7.2 – 8.1 | Chrome 55+, Firefox 53+, Safari 11+ and iOS Safari 11+
| Tracy 2.7 | PHP 7.1 – 8.0 | Chrome 55+, Firefox 53+, MS Edge 16+, Safari 11+ and iOS Safari 11+
| Tracy 2.6 | PHP 7.1 – 8.0 | Chrome 49+, Firefox 45+, MS Edge 14+, Safari 10+ and iOS Safari 10.2+
| Tracy 2.5 | PHP 5.4 – 7.4 | Chrome 49+, Firefox 45+, MS Edge 12+, Safari 10+ and iOS Safari 10.2+
| Tracy 2.4 | PHP 5.4 – 7.2 | Chrome 29+, Firefox 28+, IE 11+ (except AJAX), MS Edge 12+, Safari 9+ and iOS Safari 9.2+


Usage
-----

Activating Tracy is easy. Simply add these two lines of code, preferably just after library loading (like `require 'vendor/autoload.php'`) and before any output is sent to browser:

```php
use Tracy\Debugger;

Debugger::enable();
```

The first thing you will notice on the website is a Debugger Bar.

(If you do not see anything, it means that Tracy is running in production mode. For security reasons, Tracy is visible only on localhost.
You may force Tracy to run in development mode by passing the `Debugger::Development` as the first parameter of `enable()` method.)

The `enable()` involves changing the error reporting level to E_ALL.


Debugger Bar
------------

The Debugger Bar is a floating panel. It is displayed in the bottom right corner of a page. You can move it using the mouse. It will remember its position after the page reloading.

[![Debugger-Bar](https://nette.github.io/tracy/images/tracy-bar.png)](https://nette.github.io/tracy/tracy-debug-bar.html)

You can add other useful panels to the Debugger Bar. You can find interesting ones in [addons](https://componette.org) or you can [create your own](https://tracy.nette.org/en/extensions).

If you do not want to show Debugger Bar, set:

```php
Debugger::$showBar = false;
```


Visualization of errors and exceptions
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

[![Notice rendered by Tracy](https://nette.github.io/tracy/images/tracy-notice2.png)](https://nette.github.io/tracy/tracy-debug-bar.html)

Or they may be displayed like errors:

```php
Debugger::$strictMode = true; // display all errors
Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // all errors except deprecated notices
```

[![Notice rendered by Tracy](https://nette.github.io/tracy/images/tracy-notice.png)](https://nette.github.io/tracy/tracy-notice.html)

In order to detect misspellings when assigning to an object, we use [trait Nette\SmartObject](https://doc.nette.org/en/3.0/smartobject).


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


Faster loading
--------------

The basic integration is straightforward, however if you have slow blocking scripts in web page, they can slow the Tracy loading.
The solution is to place `<?php Tracy\Debugger::renderLoader() ?>` into your template before
any scripts:

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


AJAX and redirected requests
----------------------------

Tracy is able to show Debug bar and Bluescreens for AJAX and redirected requests. Tracy keeps the data in a temporary files and uses the `tracy-session` cookie. Tracy can be configured to use a standard PHP session:

```php
Debugger::setSessionStorage(new Tracy\NativeSession);
Debugger::enable();
```

In case you use non-standard session handler, you can start Tracy immediately (in order to handle any errors), then initialize your session handler
and then inform Tracy that session is ready to use via `dispatch()`:

```php
Debugger::setSessionStorage(new Tracy\NativeSession);
Debugger::enable();

// initialize session handler
session_start();

Debugger::dispatch();
```


Opening files in the editor
---------------------------

When the error page is displayed, you can click on file names and they will open in your editor with the cursor on the corresponding line. Files can also be created (action `create file`) or bug fixed in them (action `fix it`). In order to do this, you need to [configure the browser and the system](https://tracy.nette.org/cs/open-files-in-ide).


Production mode and error logging
---------------------------------

As you can see, Tracy is quite eloquent. It is appreciated in a development environment, but on a production server, it would cause a disaster. Any debugging information cannot be listed there. Therefore Tracy has an environment autodetection and logging functionality. Instead of showing herself, Tracy stores information into a log file and shows the visitor a user-comprehensible server error message:

[![Server Error 500](https://nette.github.io/tracy/images/tracy-error2.png)](https://nette.github.io/tracy/tracy-production.html)

Production output mode suppresses all debugging information which is sent out via `dump()` or `Debugger::fireLog()`, and of course all error messages generated by PHP. So, even if you forget `dump($obj)` in the source code, you do not have to worry about it on your production server. Nothing will be seen.

The output mode is set by the first parameter of `Debugger::enable()`. You can specify either a constant `Debugger::Production` or `Debugger::Development`. Other option is to set it up in a way, that development mode will be on when the application is accessed from a defined IP address with a defined value of `tracy-debug` cookie. The syntax used to achieve this is `cookie-value@ip-address`.

If it is not specified, the default value `Debugger::Detect` is used. In this case, the system detects a server by IP address. The production mode is chosen if an application is accessed via a public IP address. A local IP address leads to development mode. It is not necessary to set the mode in most cases. The mode is correctly recognized when you are launching the application on your local server or in production.

In the production mode, Tracy automatically captures all errors and exceptions into a text log. Unless you specify otherwise, it will be stored in log/error.log. This error logging is extremely useful. Imagine, that all users of your application are actually betatesters. They are doing cutting-edge work for free when hunting bugs and you would be silly if you threw away their valuable reports to a recycle bin unnoticed.

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

A directory for errors logging can be set by the second parameter of the enable() method:

```php
Debugger::enable(Debugger::Detect, __DIR__ . '/mylog');
```

If you want Tracy to log PHP errors like `E_NOTICE` or `E_WARNING` with detailed information (HTML report), set `Debugger::$logSeverity`:

```php
Debugger::$logSeverity = E_NOTICE | E_WARNING;
```

For a real professional the error log is a crucial source of information and he or she wants to be notified about any new error immediately. Tracy helps him. She is capable of sending an email for every new error record. The variable $email identifies where to send these e-mails:

```php
Debugger::$email = 'admin@example.com';
```

If you use the Nette Framework, you can set this and others in the configuration file.

To protect your e-mail box from flood, Tracy sends **only one message** and creates a file `email-sent`. When a developer receives the e-mail notification, he checks the log, corrects his application and deletes the `email-sent` monitoring file. This activates the e-mail sending again.


Variable dumping
----------------

Every debugging developer is a good friend with the function `var_dump`, which lists all contents of any variable in detail. Unfortunately, its output is without HTML formatting and outputs the dump into a single line of HTML code, not to mention context escaping. It is necessary to replace the `var_dump` with a more handy function. That is just what `dump()` is.

```php
$arr = [10, 20.2, true, null, 'hello'];

dump($arr);
// or Tracy\Debugger::dump($arr);
```

generates the output:

![dump](https://nette.github.io/tracy/images/tracy-dump.png)

You can also change the nesting depth by `Debugger::$maxDepth` and displayed strings length by `Debugger::$maxLength`. Naturally, lower values accelerate Tracy rendering.

```php
Debugger::$maxDepth = 2; // default: 7
Debugger::$maxLength = 50; // default: 150
Debugger::$dumpTheme = 'dark'; // default: light
```

The `dump()` function can display useful location information:

```php
Debugger::$showLocation = true; // shows tooltip with path to the file, where the dump() was called, and tooltips for every dumped objects
Debugger::$showLocation = Tracy\Dumper::LOCATION_CLASS; // shows only tooltips for every dumped object containing path to the file
Debugger::$showLocation = false; // hides all location information
```

Very handy alternative to `dump()` is `dumpe()` (ie. dump and exit) and `bdump()`. This allows us to dump variables in Debugger Bar. This is useful, because dumps don't mess up the output and we can also add a title to the dump.

```php
bdump([2, 4, 6, 8], 'even numbers up to ten');
bdump([1, 3, 5, 7, 9], 'odd numbers up to ten');
```

![bar dump](https://nette.github.io/tracy/images/tracy-bardump.png)


Timing
------

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
