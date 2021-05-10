## Upgrading from 1.x

The default behaviour has changed from always restarting if Xdebug is loaded, to only restarting if
Xdebug is _active_. This has been introduced to support Xdebug3
[modes](https://xdebug.org/docs/all_settings#mode). Xdebug is considered active if it is loaded, and
for Xdebug3, if it is running in a mode other than `xdebug.mode=off`.

* Break: removed optional `$colorOption` constructor param and passthru fallback.

    Just use `new XdebugHandler('myapp')` to instantiate the class and color support will be
detectable in the restarted process.

* Added: `isXdebugActive()` method to determine if Xdebug is still running in the restart.

    Returns true if Xdebug is loaded and is running in an active mode (if it supports modes).
Returns false if Xdebug is not loaded, or it is running with `xdebug.mode=off`.

### Extending classes

* Break: renamed `requiresRestart` param from `$isLoaded` to `$default`.

    This reflects the new default behaviour which is to restart only if Xdebug is active.

* Break: changed `restart` param `$command` from a string to an array.

    Only important if you modified the string. `$command` is now an array of unescaped arguments.

* Added: Process utility class to the API.

    This class was previously annotated as @internal and has been refactored due to recent changes.
