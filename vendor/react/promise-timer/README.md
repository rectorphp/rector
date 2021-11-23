# PromiseTimer

[![CI status](https://github.com/reactphp/promise-timer/workflows/CI/badge.svg)](https://github.com/reactphp/promise-timer/actions)

A trivial implementation of timeouts for `Promise`s, built on top of [ReactPHP](https://reactphp.org/).

**Table of contents**

* [Usage](#usage)
  * [timeout()](#timeout)
    * [Timeout cancellation](#timeout-cancellation)
    * [Cancellation handler](#cancellation-handler)
    * [Input cancellation](#input-cancellation)
    * [Output cancellation](#output-cancellation)
    * [Collections](#collections)
  * [resolve()](#resolve)
    * [Resolve cancellation](#resolve-cancellation)
  * [reject()](#reject)
    * [Reject cancellation](#reject-cancellation)
  * [TimeoutException](#timeoutexception)
* [Install](#install)
* [Tests](#tests)
* [License](#license)

## Usage

This lightweight library consists only of a few simple functions.
All functions reside under the `React\Promise\Timer` namespace.

The below examples assume you use an import statement similar to this:

```php
use React\Promise\Timer;

Timer\timeout(…);
```

Alternatively, you can also refer to them with their fully-qualified name:

```php
\React\Promise\Timer\timeout(…);
```

### timeout()

The `timeout(PromiseInterface $promise, $time, LoopInterface $loop = null)` function
can be used to *cancel* operations that take *too long*.
You need to pass in an input `$promise` that represents a pending operation and timeout parameters.
It returns a new `Promise` with the following resolution behavior:

* If the input `$promise` resolves before `$time` seconds, resolve the resulting promise with its fulfillment value.
* If the input `$promise` rejects before `$time` seconds, reject the resulting promise with its rejection value.
* If the input `$promise` does not settle before `$time` seconds, *cancel* the operation and reject the resulting promise with a [`TimeoutException`](#timeoutexception).

Internally, the given `$time` value will be used to start a timer that will
*cancel* the pending operation once it triggers.
This implies that if you pass a really small (or negative) value, it will still
start a timer and will thus trigger at the earliest possible time in the future.

If the input `$promise` is already settled, then the resulting promise will
resolve or reject immediately without starting a timer at all.

This function takes an optional `LoopInterface|null $loop` parameter that can be used to
pass the event loop instance to use. You can use a `null` value here in order to
use the [default loop](https://github.com/reactphp/event-loop#loop). This value
SHOULD NOT be given unless you're sure you want to explicitly use a given event
loop instance.

A common use case for handling only resolved values looks like this:

```php
$promise = accessSomeRemoteResource();
Timer\timeout($promise, 10.0)->then(function ($value) {
    // the operation finished within 10.0 seconds
});
```

A more complete example could look like this:

```php
$promise = accessSomeRemoteResource();
Timer\timeout($promise, 10.0)->then(
    function ($value) {
        // the operation finished within 10.0 seconds
    },
    function ($error) {
        if ($error instanceof Timer\TimeoutException) {
            // the operation has failed due to a timeout
        } else {
            // the input operation has failed due to some other error
        }
    }
);
```

Or if you're using [react/promise v2.2.0](https://github.com/reactphp/promise) or up:

```php
Timer\timeout($promise, 10.0)
    ->then(function ($value) {
        // the operation finished within 10.0 seconds
    })
    ->otherwise(function (Timer\TimeoutException $error) {
        // the operation has failed due to a timeout
    })
    ->otherwise(function ($error) {
        // the input operation has failed due to some other error
    })
;
```

#### Timeout cancellation

As discussed above, the [`timeout()`](#timeout) function will *cancel* the
underlying operation if it takes *too long*.
This means that you can be sure the resulting promise will then be rejected
with a [`TimeoutException`](#timeoutexception).

However, what happens to the underlying input `$promise` is a bit more tricky:
Once the timer fires, we will try to call
[`$promise->cancel()`](https://github.com/reactphp/promise#cancellablepromiseinterfacecancel)
on the input `$promise` which in turn invokes its [cancellation handler](#cancellation-handler).

This means that it's actually up the input `$promise` to handle
[cancellation support](https://github.com/reactphp/promise#cancellablepromiseinterface).

* A common use case involves cleaning up any resources like open network sockets or
  file handles or terminating external processes or timers.

* If the given input `$promise` does not support cancellation, then this is a NO-OP.
  This means that while the resulting promise will still be rejected, the underlying
  input `$promise` may still be pending and can hence continue consuming resources.

See the following chapter for more details on the cancellation handler.

#### Cancellation handler

For example, an implementation for the above operation could look like this:

```php
function accessSomeRemoteResource()
{
    return new Promise(
        function ($resolve, $reject) use (&$socket) {
            // this will be called once the promise is created
            // a common use case involves opening any resources and eventually resolving
            $socket = createSocket();
            $socket->on('data', function ($data) use ($resolve) {
                $resolve($data);
            });
        },
        function ($resolve, $reject) use (&$socket) {
            // this will be called once calling `cancel()` on this promise
            // a common use case involves cleaning any resources and then rejecting
            $socket->close();
            $reject(new \RuntimeException('Operation cancelled'));
        }
    );
}
```

In this example, calling `$promise->cancel()` will invoke the registered cancellation
handler which then closes the network socket and rejects the `Promise` instance.

If no cancellation handler is passed to the `Promise` constructor, then invoking
its `cancel()` method it is effectively a NO-OP.
This means that it may still be pending and can hence continue consuming resources.

For more details on the promise cancellation, please refer to the
[Promise documentation](https://github.com/reactphp/promise#cancellablepromiseinterface).

#### Input cancellation

Irrespective of the timeout handling, you can also explicitly `cancel()` the
input `$promise` at any time.
This means that the `timeout()` handling does not affect cancellation of the
input `$promise`, as demonstrated in the following example:

```php
$promise = accessSomeRemoteResource();
$timeout = Timer\timeout($promise, 10.0);

$promise->cancel();
```

The registered [cancellation handler](#cancellation-handler) is responsible for
handling the `cancel()` call:

* A described above, a common use involves resource cleanup and will then *reject*
  the `Promise`.
  If the input `$promise` is being rejected, then the timeout will be aborted
  and the resulting promise will also be rejected.
* If the input `$promise` is still pending, then the timout will continue
  running until the timer expires.
  The same happens if the input `$promise` does not register a
  [cancellation handler](#cancellation-handler). 

#### Output cancellation

Similarily, you can also explicitly `cancel()` the resulting promise like this:

```php
$promise = accessSomeRemoteResource();
$timeout = Timer\timeout($promise, 10.0);

$timeout->cancel();
```

Note how this looks very similar to the above [input cancellation](#input-cancellation)
example. Accordingly, it also behaves very similar.

Calling `cancel()` on the resulting promise will merely try
to `cancel()` the input `$promise`.
This means that we do not take over responsibility of the outcome and it's
entirely up to the input `$promise` to handle cancellation support.

The registered [cancellation handler](#cancellation-handler) is responsible for
handling the `cancel()` call:

* As described above, a common use involves resource cleanup and will then *reject*
  the `Promise`.
  If the input `$promise` is being rejected, then the timeout will be aborted
  and the resulting promise will also be rejected.
* If the input `$promise` is still pending, then the timout will continue
  running until the timer expires.
  The same happens if the input `$promise` does not register a
  [cancellation handler](#cancellation-handler). 

To re-iterate, note that calling `cancel()` on the resulting promise will merely
try to cancel the input `$promise` only.
It is then up to the cancellation handler of the input promise to settle the promise.
If the input promise is still pending when the timeout occurs, then the normal
[timeout cancellation](#timeout-cancellation) handling will trigger, effectively rejecting
the output promise with a [`TimeoutException`](#timeoutexception).

This is done for consistency with the [timeout cancellation](#timeout-cancellation)
handling and also because it is assumed this is often used like this:

```php
$timeout = Timer\timeout(accessSomeRemoteResource(), 10.0);

$timeout->cancel();
```

As described above, this example works as expected and cleans up any resources
allocated for the input `$promise`.

Note that if the given input `$promise` does not support cancellation, then this
is a NO-OP.
This means that while the resulting promise will still be rejected after the
timeout, the underlying input `$promise` may still be pending and can hence
continue consuming resources.

#### Collections

If you want to wait for multiple promises to resolve, you can use the normal promise primitives like this:

```php
$promises = array(
    accessSomeRemoteResource(),
    accessSomeRemoteResource(),
    accessSomeRemoteResource()
);

$promise = \React\Promise\all($promises);

Timer\timeout($promise, 10)->then(function ($values) {
    // *all* promises resolved
});
```

The applies to all promise collection primitives alike, i.e. `all()`, `race()`, `any()`, `some()` etc.

For more details on the promise primitives, please refer to the
[Promise documentation](https://github.com/reactphp/promise#functions).

### resolve()

The `resolve($time, LoopInterface $loop = null)` function can be used to create a new Promise that
resolves in `$time` seconds with the `$time` as the fulfillment value.

```php
Timer\resolve(1.5)->then(function ($time) {
    echo 'Thanks for waiting ' . $time . ' seconds' . PHP_EOL;
});
```

Internally, the given `$time` value will be used to start a timer that will
resolve the promise once it triggers.
This implies that if you pass a really small (or negative) value, it will still
start a timer and will thus trigger at the earliest possible time in the future.

This function takes an optional `LoopInterface|null $loop` parameter that can be used to
pass the event loop instance to use. You can use a `null` value here in order to
use the [default loop](https://github.com/reactphp/event-loop#loop). This value
SHOULD NOT be given unless you're sure you want to explicitly use a given event
loop instance.

#### Resolve cancellation

You can explicitly `cancel()` the resulting timer promise at any time:

```php
$timer = Timer\resolve(2.0);

$timer->cancel();
```

This will abort the timer and *reject* with a `RuntimeException`.

### reject()

The `reject($time, LoopInterface $loop = null)` function can be used to create a new Promise
which rejects in `$time` seconds with a `TimeoutException`.

```php
Timer\reject(2.0)->then(null, function (TimeoutException $e) {
    echo 'Rejected after ' . $e->getTimeout() . ' seconds ' . PHP_EOL;
});
```

Internally, the given `$time` value will be used to start a timer that will
reject the promise once it triggers.
This implies that if you pass a really small (or negative) value, it will still
start a timer and will thus trigger at the earliest possible time in the future.

This function takes an optional `LoopInterface|null $loop` parameter that can be used to
pass the event loop instance to use. You can use a `null` value here in order to
use the [default loop](https://github.com/reactphp/event-loop#loop). This value
SHOULD NOT be given unless you're sure you want to explicitly use a given event
loop instance.

This function complements the [`resolve()`](#resolve) function
and can be used as a basic building block for higher-level promise consumers.

#### Reject cancellation

You can explicitly `cancel()` the resulting timer promise at any time:

```php
$timer = Timer\reject(2.0);

$timer->cancel();
```

This will abort the timer and *reject* with a `RuntimeException`.

### TimeoutException

The `TimeoutException` extends PHP's built-in `RuntimeException`.

The `getTimeout()` method can be used to get the timeout value in seconds.

## Install

The recommended way to install this library is [through Composer](https://getcomposer.org).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This project follows [SemVer](https://semver.org/).
This will install the latest supported version:

```bash
$ composer require react/promise-timer:^1.7
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

This project aims to run on any platform and thus does not require any PHP
extensions and supports running on legacy PHP 5.3 through current PHP 8+ and
HHVM.
It's *highly recommended to use PHP 7+* for this project.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](https://getcomposer.org):

```bash
$ composer install
```

To run the test suite, go to the project root and run:

```bash
$ php vendor/bin/phpunit
```

## License

MIT, see [LICENSE file](LICENSE).
