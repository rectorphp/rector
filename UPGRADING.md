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
