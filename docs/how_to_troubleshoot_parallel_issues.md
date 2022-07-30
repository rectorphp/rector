# How to Troubleshoot Parallel Issues

You may get some parallel errors and ask how you can identify if there is something in your code or a real parallel issue than can be fixed by configuration. They may be displayed as:

```bash
$ ./vendor/bin/rector process
...
[ERROR] Could not process "/app/vendor/rector/rector/vendor/symplify/easy-parallel/src/ValueObject/ParallelProcess.php" file, due to:  "Child process timed out after 120 seconds". On line: 103
[ERROR] Could not process some files, due to: "Reached system errors count limit of 50, exiting...".
```

When that happens, the first good approach is to disable parallel processing. The output of parallel failures can hide some fatal errors from the source codebase. You can do so by:

```php
$rectorConfig->disableParallel();
```

After that, if rector processing works fine, that is an indication that you might need to adjust your parallel process to some balanced load, depending on the resources you have to process rector.


If we check [parallel() method defaults](https://github.com/rectorphp/rector-src/blob/main/packages/Config/RectorConfig.php#L53):
```php
public function parallel(int $seconds = 120, int $maxNumberOfProcess = 16, int $jobSize = 20) : void
```

You might find it helpful to keep it aligned with what you have at your disposal:

- timeout `$seconds` can be increased if you find your codebase has a job size with classes that require more time to process, and then you need more time to process
- `$maxNumberOfProcess` may be decreased not to overload your system, and keeping some processes free and avoid side impacts of busy systems not being able to process files properly. For example: if you have four processes, you can use `$maxNumberOfProcess = 2` to keep two processes free and limit the system load.
- `$jobSize` might be decreased if your files have too many lines of code, which would cause more time needed to process, which also will load the processes, etc

Happy coding!
