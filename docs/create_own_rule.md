# 3 Steps to Create Your Own Rector

First, make sure it's not covered by [any existing Rectors](/docs/rector_rules_overview.md).
Let's say we want to **change method calls from `set*` to `change*`**.

```diff
 $user = new User();
-$user->setPassword('123456');
+$user->changePassword('123456');
```

## 1. Create a New Rector and Implement Methods

Create a class that extends [`Rector\Core\Rector\AbstractRector`](/src/Rector/AbstractRector.php). It will inherit useful methods e.g. to check node type and name. See the source (or type `$this->` in an IDE) for a list of available methods.

```php
declare(strict_types=1);

namespace Utils\Rector\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class MyFirstRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        // what node types are we looking for?
        // pick any node from https://github.com/rectorphp/php-parser-nodes-docs/
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node - we can add "MethodCall" type here, because
     *                         only this node is in "getNodeTypes()"
     */
    public function refactor(Node $node): ?Node
    {
        // we only care about "set*" method names
        if (! $this->isName($node->name, 'set*')) {
            // return null to skip it
            return null;
        }

        $methodCallName = $this->getName($node->name);
        $newMethodCallName = Strings::replace($methodCallName, '#^set#', 'change');

        $node->name = new Identifier($newMethodCallName);

        // return $node if you modified it
        return $node;
    }

    /**
     * This method helps other to understand the rule and to generate documentation.
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change method calls from set* to change*.', [
                new CodeSample(
                    // code before
                    '$user->setPassword("123456");',
                    // code after
                    '$user->changePassword("123456");'
                ),
            ]
        );
    }
}
```


## File Structure

This is how the file structure for custom rule in your own project will look like:

```bash
/src/
    /YourCode.php
/utils
    /rector
        /src
            /Rector
                MyFirstRector.php
rector.php
composer.json
```

Writing test saves you lot of time in future debugging. Here is a file structure with tests:

```bash
/src/
    /YourCode.php
/utils
    /rector
        /src
            /Rector
                MyFirstRector.php
        /tests
            /Rector
                /MyFirstRector
                    /Fixture
                        test_fixture.php.inc
                    /config
                        config.php
                    MyFirstRectorTest.php
rector.php
composer.json
```

## Update `composer.json`

We also need to load Rector rules in `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Utils\\Rector\\": "utils/rector/src",
            "Utils\\Rector\\Tests\\": "utils/rector/tests"
        }
    }
}
```

After adding this to `composer.json`, be sure to reload Composer's class map:

```bash
composer dump-autoload
```

## 2. Register It

```php
<?php
// rector.php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Utils\Rector\Rector\MyFirstRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(MyFirstRector::class);
};
```

## 3. Let Rector Refactor Your Code

The `rector.php` configuration is loaded by default, so we can skip it.

```bash
# see the diff first
vendor/bin/rector process src --dry-run

# if it's ok, apply
vendor/bin/rector process src
```

That's it!

<br>

## Generating a Rector Rule

Do you want to save time with making rules and tests?

Use [the `generate` command](https://github.com/rectorphp/rector-generator).
