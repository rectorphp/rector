# Auto Import Names

Rector works with all class names as fully qualified by default, so it knows the exact types. In most coding standard, that's not desired behavior, because short version with `use` statement is preferred:

```diff
+use App\Some\Namespace\SomeClass;

-/** @var \App\Some\Namespace\SomeClass $object */
+/** @var SomeClass $object */

-$object = new \App\Some\Namespace\SomeClass();
+$object = new SomeClass();
```


To import FQN like these, configure `rector.php` with:

```php
$rectorConfig->importNames();
```

<br>

Single short classes are imported too:

```diff
+use DateTime;
-$someClass = \DateTime();
+$someClass = DateTime();
```

Do you want to keep those?

```php
$rectorConfig->importShortClasses(false);
```

## How to Remove Unused Imports?

To remove imports, use [ECS](https://github.com/symplify/easy-coding-standard) with [`NoUnusedImportsFixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.18/doc/rules/import/no_unused_imports.rst) rule:

```php
// ecs.php
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rule(NoUnusedImportsFixer::class);
};
```

Run it:

```bash
vendor/bin/ecs check src --fix
```

<br>

Happy coding!
