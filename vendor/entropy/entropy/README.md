# Entropy

A tiny framework with a dependency injection container and a console runner. No configuration, no YAML, no magic strings - just classes.

Requires PHP 8.3+.

<br>

## Install

```bash
composer require entropy/entropy
```

<br>

## 1. Dependency Injection

The container autowires services through reflection. Point it at a directory and every class in it becomes an available service.

```php
use Entropy\Container\Container;

$container = new Container();
$container->autodiscover(__DIR__ . '/src');

$someService = $container->make(SomeService::class);
```

<br>

Need a custom factory? Register it manually:

```php
$container->service(PDO::class, function (Container $container): PDO {
    return new PDO('sqlite::memory:');
});
```

<br>

Need every implementation of an interface? Ask for the contract:

```php
$listeners = $container->findByContract(EventListenerInterface::class);
```

<br>

The container detects circular dependencies and tells you exactly where the cycle is, e.g. `A -> B -> C -> A`.

<br>

## 2. Console

Implement `CommandInterface` and the command is wired up automatically:

```php
use Entropy\Console\Contract\CommandInterface;
use Entropy\Console\Enum\ExitCode;
use Entropy\Console\Output\OutputPrinter;

final readonly class HelloCommand implements CommandInterface
{
    public function __construct(
        private OutputPrinter $outputPrinter
    ) {
    }

    public function getName(): string
    {
        return 'hello';
    }

    public function getDescription(): string
    {
        return 'Say hello';
    }

    /**
     * @param string[] $names Names to greet.
     * @param bool $loud Shout instead of speak.
     */
    public function run(array $names, bool $loud = false): int
    {
        foreach ($names as $name) {
            $greeting = $loud ? "HELLO {$name}!" : "Hello {$name}";
            $this->outputPrinter->green($greeting);
        }

        return ExitCode::SUCCESS;
    }
}
```

<br>

The `run()` method signature *is* the command definition. Parameter types, default values, and docblocks become CLI arguments, options, and help text. No attributes to add, no input objects to wire.

<br>

Boot the application from your binary:

```php
use Entropy\Console\ConsoleApplication;
use Entropy\Container\Container;

$container = new Container();
$container->autodiscover(__DIR__ . '/src');

$consoleApplication = $container->make(ConsoleApplication::class);
exit($consoleApplication->run($argv));
```

<br>

That's it. Run it:

```bash
bin/console hello Alice Bob --loud
```

<br>

By convention, the first `string` or `array` parameter becomes a positional argument; everything else becomes a `--option`. To force the first parameter to be an option instead, mark it with `@option` in the docblock:

```php
/**
 * @option $source
 * @param string $source The source path
 */
public function run(string $source, bool $verbose = false): int
{
    // ...
}
```

```bash
bin/console hello --source=src/
```

<br>

Typo a command name and the fuzzy matcher will pick the closest one. Pass `--help` for global help, or `<command> --help` for per-command help built from your docblocks.

<br>

Happy coding!
