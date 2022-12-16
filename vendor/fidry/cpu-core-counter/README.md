# CPU Core Counter

This package is a tiny utility to get the number of CPU cores.

```sh
composer require fidry/cpu-core-counter
```


## Usage

```php
use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;

$counter = new CpuCoreCounter();

try {
    $counter->getCount();   // e.g. 8
} catch (NumberOfCpuCoreNotFound) {
    return 1;   // Fallback value
}

// An alternative form where we not want to catch the exception:

$counter = new CpuCoreCounter([
    ...CpuCoreCounter::getDefaultFinders(),
    new DummyCpuCoreFinder(1),  // Fallback value
]);

$counter->getCount();   // e.g. 8

```


## Advanced usage

### Changing the finders

When creating `CpuCoreCounter`, you may want to change the order of the finders
used or disable a specific finder. You can easily do so by passing the finders
you want

```php
// Remove WindowsWmicFinder 
$finders = array_filter(
    CpuCoreCounter::getDefaultFinders(),
    static fn (CpuCoreFinder $finder) => !($finder instanceof WindowsWmicFinder)
);

$cores = (new CpuCoreCounter($finders))->getCount();
```

```php
// Use CPUInfo first & don't use Nproc
$finders = [
    new CpuInfoFinder(),
    new WindowsWmicFinder(),
    new HwLogicalFinder(),
];

$cores = (new CpuCoreCounter($finders))->getCount();
```

### Choosing only logical or physical finders

`FinderRegistry` provides two helpful entries:

- `::getDefaultLogicalFinders()`: gives an ordered list of finders that will
  look for the _logical_ CPU cores count
- `::getDefaultPhysicalFinders()`: gives an ordered list of finders that will
  look for the _physical_ CPU cores count

By default when using `CpuCoreCounter`, it will use the logical finders since
it is more likely what you are looking for and is what is used by PHP source to
build the PHP binary.


### Checks what finders find what on your system

You have two commands available that provides insight about what the finders
can find:

```
$ make diagnosis                                    # From this repository
$ ./vendor/fidry/cpu-core-counter/bin/diagnose.php  # From the library
```

And:
```
$ make execute                                     # From this repository
$ ./vendor/fidry/cpu-core-counter/bin/execute.php  # From the library
```


## Backward Compatibility Promise (BCP)

The policy is for the major part following the same as [Symfony's one][symfony-bc-policy].
Note that the code marked as `@private` or `@internal` are excluded from the BCP.

The following elements are also excluded:

- The `diagnose` and `execute` commands: those are for debugging/inspection purposes only
- `FinderRegistry::get*Finders()`: new finders may be added or the order of finders changed at any time


## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).

[symfony-bc-policy]: https://symfony.com/doc/current/contributing/code/bc.html
