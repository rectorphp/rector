# 4 Steps Create Own Rector

First, make sure your needs is not covered by [any existing Rectors](/docs/AllRectorsOverview.md).

Let's say you want to prefix private method call names starting with "internal" with `_`. Not very practical, but it will show the Rector flow.

## 1. New Class

Create class that extends [`Rector\Rector\AbstractRector`](/src/Rector/AbstractRector.php). It has many useful methods for checking node type, name, add nodes or remove one. Just run `$this->` and let PHPStorm show you all possible methods.

There 3 methods to implement:

```php
<?php declare(strict_types=1);

namespace App\Rector;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

final class MyFirstRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        // what does this do?
        // minimalistic before/after sample - to explain in code
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        // what node types we look for?
        // String_? FuncCall?
        // pick any from https://github.com/nikic/PHP-Parser/tree/master/lib/PhpParser/Node
        return [];
    }

    public function refactor(Node $node): ?Node
    {
        // what will happen with the node?
        // common work flow:
        // - should skip? → return null;
        // - modify it? → do it, then return $node;
        // - remove/add nodes elsewhere? → do it, then return null;
    }
}
```

## 2. Add Methods

```php
<?php declare(strict_types=1);

namespace App\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class MyFirstRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add "_" to private method calls that start with "internal"', [
            new CodeSample('$this->internalMethod();', '$this->_internalMethod();')
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node - we can add "MethodCall" type here, because only this node is in "getNodeTypes()"
     */
    public function refactor(Node $node): ?Node
    {
        // we only care about "internal*" method names
        if (! $this->nameStartsWith($node, 'internal')) {
            return null;
        }

        $node->name = new Identifier('_' . $this->getName($node));

        return $node;
    }
}
```

## 3. Register it `rector.yml`

```diff
 services:
+    App\Rector\MyFirstRector: ~
```

## 4. Let Rector Refactor Your Code

```bash
vendor/bin/rector process src --dry-run

# apply it
vendor/bin/rector process src
```

If you use `rector.yml` from another directory or another name, set it with `--config` option:

```bash
vendor/bin/rector process src --config ../custom-rector.yml
```

That's it!
