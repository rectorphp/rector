# Auto Import Names

Rector works with all class names as fully qualified by default, so it knows the exact types. In most coding standard, that's not desired behavior, because short version with `use` statement is preferred:

```diff
-$object = new \App\Some\Namespace\SomeClass();
+use App\Some\Namespace\SomeClass;
+$object = new SomeClass();
```


To import FQN like these, configure `rector.php` with:

```php
$parameters->set(Option::AUTO_IMPORT_NAMES, true);
```

<br>

If you enable this feature, the class names in docblocks are imported as well:

```diff
+use App\Some\Namespace\SomeClass;
-/** @var \App\Some\Namespace\SomeClass $someClass */
+/** @var SomeClass $someClass */
 $someClass = ...;
```

Do you want to skip them?

```php
$parameters->set(Option::IMPORT_DOC_BLOCKS, false);
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
$parameters->set(Option::IMPORT_SHORT_CLASSES, false);
```

<br>

If you have set Option::AUTO_IMPORT_NAMES to true, rector is applying this to every analyzed file, even if no real change by a rector was applied to the file.
The reason is that a so-called post rector is responsible for this, namely the NameImportingPostRector.
If you like to apply the Option::AUTO_IMPORT_NAMES only for real changed files, you can configure this.

```php
$parameters->set(Option::APPLY_AUTO_IMPORT_NAMES_ON_CHANGED_FILES_ONLY, true);
```

## How to Remove Unused Imports?

To remove imports, use [ECS](github.com/symplify/easy-coding-standard) with [`NoUnusedImportsFixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.18/doc/rules/import/no_unused_imports.rst) rule:

```php
// ecs.php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NoUnusedImportsFixer::class);
};
```

Run it:

```bash
vendor/bin/ecs check src --fix
```
