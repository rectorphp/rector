# How to Add Test for Rector Rule

## 1. Detect the Rector Rule

Run Rector only on 1 directory, or better 1 file.

```bash
vendor/bin/rector process compiler/src/PhpScoper/StaticEasyPrefixer.php
```

See "Applied rules" under the diff:

![Applied Rules](/docs/images/docs_applied_rules.png)

Our rule in this case is: `Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector`

## 2. Detect the Minimal File

Usually the Rector diff output is long and contains many other errors related to other rules. It's a mess, we can't use that for test fixture. We need to find **1 responsible line**.

The best way is to copy the file to local code, e.g. `app/SomeFile.php` a put only the broken line there.

In our case, all we need is:

```php
class StaticEasyPrefixer
{
}
```

Then run Rector again to confirm:

```bash
vendor/bin/rector process app/SomeFile.php
```

Do we have the same diff? Great!

## 3. Find the Rector Test Case

Now we need to find the test case. The test case name is rule + `Test` suffix.

`FinalizeClassesWithoutChildrenRector`

↓

`FinalizeClassesWithoutChildrenRectorTest` (test class)

↓

`FinalizeClassesWithoutChildrenRectorTest.php` (test file)

Right here:

![Rule Test Case](/docs/images/docs_rule_test_case.png)

## 4. Add Change or No-Change fixture

Next to test case, there is `/Fixture` directory, there we create our test fixture file, e.g. `add_final.php.inc`. The `.php.inc` is there on purpose, so the file is hidden from coding standard tools and static analysis.

There are 2 fixture formats.

### 1. The Code Should Change

```bash
<code before>
-----
<code after>
```

### 2. The Code Should not be Touched

```bash
<code before>
```

<br>

In this perticular case, the code should change - `final` should be added, so the test fixture would look like this:

```php
<?php

namespace Rector\SOLID\Tests\Rector\Class_\FinalizeClassesWithoutChildrenRector\Fixture;

class AddFinal
{
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\Class_\FinalizeClassesWithoutChildrenRector\Fixture;

final class AddFinal
{
}

?>
```

- The closing `?>` is there for slightly better PHPStorm.
- The PSR-4 namespace is there to make each class unique, because the test classes are loaded to analysis by reflection and must be unique
- The file name conventions => class is `add_final.php.inc` => `AddFinal` class

Run PHPUnit with the test file to confirm:

```bash
vendor/bin/phpunit rules/solid/tests/Rector/Class_/FinalizeClassesWithoutChildrenRector/FinalizeClassesWithoutChildrenRectorTest.php
```

To run only the single test fixture, add `--filter test#X`, where X is the order number of fixture.

```bash
vendor/bin/phpunit rules/solid/tests/Rector/Class_/FinalizeClassesWithoutChildrenRector/FinalizeClassesWithoutChildrenRectorTest.php --filter test#4
```

If PHPUnit fails, you've successfully added a test case! :)
