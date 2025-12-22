# Upgrading from Rector 2.2.14 to 2.3

* `FileWithoutNamespace` is deprecated, and replaced by `FileNode` that represents both namespaced and non-namespaced files and allow changes inside
* `beforeTraverse()` is now marked as `@final`, use `getNodeTypes()` with `FileNode::class` instead

**Before**

```php
use Rector\PhpParser\Node\FileWithoutNamespace;
use Rector\Rector\AbstractRector;

final class SomeRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [FileWithoutNamespace::class];
    }

    public function beforeTraverse(array $nodes): array
    {
        // some node hacking
    }

    /**
     * @param FileWithoutNamespace $node
     */
    public function refactor(Node $node): ?Node
    {
        // ...
    }

}
```

**After**

```php
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;

final class SomeRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [FileNode::class];
    }

    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->stmts as $stmt) {
            // check if has declare_strict already?
            // ...

            // create it
            $declareStrictTypes = $this->createDeclareStrictTypesNode();

            // add it
            $node->stmts = array_merge([$declareStrictTypes], $node->stmts);
        }

        return $node;
    }

}
```

<br>

The `FileNode` handles both namespaced and non-namespaced files. To handle the first stmts inside the file, you hook into 2 nodes:

```php
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use PhpParser\Node\Stmt\Namespace_;

final class SomeRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [FileNode::class, Namespace_::class];
    }

    /**
     * @param FileNode|Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof FileNode && $node->isNamespaced()) {
            // handled in the Namespace_ node
            return null;
        }

        foreach ($node->stmts as $stmt) {
            // modify stmts in desired way here
        }

        return $node;
    }

}
```

<br>

# Upgrading from Rector 1.x to 2.0

## PHP version requirements

Rector now uses PHP 7.4 or newer to run.

<br>

## Rector now uses PHP-Parser 5

See [upgrading guide](https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md) for PHP-Parser.

<br>

## Rector now uses PHPStan 2

See [upgrading guide](https://github.com/phpstan/phpstan-src/blob/2.0.x/UPGRADING.md) for PHPStan.

<br>

## Upgrade for custom Rules writers

### 1. `AbstractScopeAwareRector` is removed, use `AbstractRector` instead

The `Rector\Rector\AbstractScopeAwareRector` was too granular to fetch single helper object. It made creating new custom rules ambiguous, one layer more complex and confusing. This class has been removed in favor of standard `AbstractRector`. The `Scope` object can be fetched via `ScopeFetcher`.

**Before**

```php
use Rector\Rector\AbstractScopeAwareRector;

final class SimpleRector extends AbstractScopeAwareRector
{
    public function refactorWithScope(Node $node, Scope $scope): ?Node
    {
        // ...
    }
}
```

**After**

```php
use Rector\Rector\AbstractRector;
use Rector\PHPStan\ScopeFetcher;

final class SimpleRector extends AbstractRector
{
    public function refactor(Node $node): ?Node
    {
        if (...) {
            // this allow to fetch scope only when needed
            $scope = ScopeFetcher::fetch($node);
        }

        // ...
    }
}
```


### 2. `AbstractRector` get focused on code, the `getRuleDefinition()` is no longer required

Core rules need documentation, so people can read their feature and [search through](https://getrector.com/find-rule) them. Yet for writing custom rules and local rules, its not necessary. People often filled it empty, just to make Rector happy.

This is no longer needed. Now The `getRuleDefinition()` method has been removed:

```diff
 use Rector\Rector\AbstractRector;
-use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
-use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

 final class SimpleRector extends AbstractRector
 {
-    public function getRuleDefinition(): RuleDefinition
-    {
-        return new RuleDefinition('// @todo fill the description', [
-            new CodeSample(
-                <<<'CODE_SAMPLE'
-// @todo fill code before
-CODE_SAMPLE
-                ,
-                <<<'CODE_SAMPLE'
-// @todo fill code after
-CODE_SAMPLE
-            ),
-        ]);
-    }

     // valuable code here
 }
```

If you need description yourself to understand rule after many months, use the common place for documentation - docblock above class.


### 3. `SetListInterface` was removed

The deprecated `SetListInterface` was removed, if you created your own list just remove the Interface from it:

```diff
-use Rector\Set\Contract\SetListInterface;

-final class YourSetList implements SetListInterface
+final class YourSetList
```
