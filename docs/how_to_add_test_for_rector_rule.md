# How to Add Test for Rector Rule

## 1. Create an environment for testing

When using Rector to update your own code, you will typically be using release repository installed by composer, however, when adding tests, you will need to use the development repository as shown:

- Fork https://github.com/rectorphp/rector-src
- Clone it locally
- Install dependencies by executing `composer install`
- Tests your installation by executing `composer fix-cs` and `composer phpstan`
- Create a new branch for your test
- Add your test as described below. Note that the rector binary is located at `bin/rector` instead of the typical `vendor/bin/rector`
- Push the branch
- Create a new pull request to https://github.com/rectorphp/rector-src

<br>

## 2. Detect the Rector Rule

Run Rector only on 1 directory, or better 1 file.

```bash
bin/rector process /some-file.php
```

See "Applied rules" under the diff:

![Applied Rules](/docs/images/docs_applied_rules.png)

Our rule in this example is: `Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector`

This rule's job is to add `final` to every class that has no children and is not a Doctrine entity = everywhere it can without breaking our code.

<br>

## 3. Detect the Minimal File

Usually, the Rector diff output is long and contains many other errors related to other rules. It's a mess; we can't use that for a test fixture. We need to find **1 responsible line**.

The best way is to copy the file to local code, e.g. `app/SomeFile.php` and put only the broken line there.

In our case, all we need is:

```php
class StaticEasyPrefixer
{
}
```

Then rerun Rector to confirm:

```bash
bin/rector process app/SomeFile.php
```

Do we have the same diff? Great!

<br>

## 4. Find the Rector Test Case

Now we need to find the test case. The test case name is rule + `Test` suffix.

`FinalizeClassesWithoutChildrenRector`

↓

`FinalizeClassesWithoutChildrenRectorTest` (test class)

Right here:

![Rule Test Case](/docs/images/docs_rule_test_case.png)

<br>

## 5. Add Change or No-Change Test Fixture File

Next to the test case, there is `/Fixture` directory. It contains many test fixture files that verified the Rector rule work correctly in all possible cases.

Do you see *test fixture file* first time? It's a file with real-life PHP code that test 1 specific case that rule should cover or avoid. E.g., one test fixture file can contain a Doctrine entity that cannot be final and should be skipped by this rule. By convention, the first fixture file has the name `fixture.php.inc`.

In the `/Fixture` directory, we create our test fixture file, e.g., `add_final.php.inc`. The `.php.inc` is there on purpose, so the file is hidden from coding standard tools and static analysis.

<br>

There are 2 fixture formats:

### A. The Code Should Change

```bash
<code before>
-----
<code after>
```

### B. The Code Should Be Skipped

```bash
<code before>
```

<br>

In this particular case, the code should change - `final` should be added so that the test fixture would look like this:

```php
<?php

namespace Rector\Tests\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector\Fixture;

class AddFinal
{
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector\Fixture;

final class AddFinal
{
}

?>
```

- The closing `?>` is there for slightly better PHPStorm.
- The PSR-4 namespace is there to make each class unique because the test classes are loaded to an analysis by reflection and must be unique
- The file name conventions => class is `add_final.php.inc` => `AddFinal` class

Run PHPUnit with the test file to confirm:

```bash
vendor/bin/phpunit rules-tests/Privatization/Rector/Class_/FinalizeClassesWithoutChildrenRector/FinalizeClassesWithoutChildrenRectorTest.php
```

To run only the single test fixture, add `--filter test#X`, where X is the fixture's order number.

```bash
vendor/bin/phpunit rules-tests/Privatization/Rector/Class_/FinalizeClassesWithoutChildrenRector/FinalizeClassesWithoutChildrenRectorTest.php --filter test#4
```

If PHPUnit fails, you've successfully added a test case! :)

<br>

Thank you
