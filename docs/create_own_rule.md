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
namespace Utils\Rector;

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
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        // what node types are we looking for?
        // pick any node from https://github.com/rectorphp/rector/blob/master/docs/nodes_overview.md
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
     * From this method documentation is generated.
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

This is how the file structure should look like:

```bash
/src/YourCode.php
/utils/Rector/MyFirstRector.php
rector.php
composer.json
```

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
            "Utils\\": "utils"
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
use Utils\Rector\MyFirstRector;

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

Use [the `create` command](/docs/rector_recipe.md).
