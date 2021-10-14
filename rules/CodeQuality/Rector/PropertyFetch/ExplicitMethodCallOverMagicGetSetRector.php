<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\PropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://github.com/symplify/phpstan-rules/blob/main/docs/rules_overview.md#explicitmethodcallovermagicgetsetrule
 *
 * @inspired by \Rector\Transform\Rector\Assign\GetAndSetToMethodCallRector
 * @phpstan-rule https://github.com/symplify/phpstan-rules/blob/main/src/Rules/Explicit/ExplicitMethodCallOverMagicGetSetRule.php
 *
 * @see \Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\ExplicitMethodCallOverMagicGetSetRectorTest
 */
final class ExplicitMethodCallOverMagicGetSetRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace magic property fetch using __get() and __set() with existing method get*()/set*() calls',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class MagicCallsObject
{
    // adds magic __get() and __set() methods
    use \Nette\SmartObject;

    private $name;

    public function getName()
    {
        return $this->name;
    }
}

class SomeClass
{
    public function run(MagicObject $magicObject)
    {
        return $magicObject->name;
    }
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
class MagicCallsObject
{
    // adds magic __get() and __set() methods
    use \Nette\SmartObject;

    private $name;

    public function getName()
    {
        return $this->name;
    }
}

class SomeClass
{
    public function run(MagicObject $magicObject)
    {
        return $magicObject->getName();
    }
}
CODE_SAMPLE
                ),

            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [PropertyFetch::class, Assign::class];
    }

    /**
     * @param PropertyFetch|Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Assign) {
            if ($node->var instanceof PropertyFetch) {
                return $this->refactorMagicSet($node->expr, $node->var);
            }

            return null;
        }

        if ($this->shouldSkipPropertyFetch($node)) {
            return null;
        }

        return $this->refactorPropertyFetch($node);
    }

    private function shouldSkipPropertyFetch(PropertyFetch $propertyFetch): bool
    {
        $parentAssign = $this->betterNodeFinder->findParentType($propertyFetch, Assign::class);
        if (! $parentAssign instanceof Assign) {
            return false;
        }

        return (bool) $this->betterNodeFinder->findFirst(
            $parentAssign->var,
            fn (Node $subNode): bool => $subNode === $propertyFetch
        );
    }

    private function refactorPropertyFetch(PropertyFetch $propertyFetch): MethodCall|null
    {
        $callerType = $this->getType($propertyFetch->var);
        if (! $callerType instanceof ObjectType) {
            return null;
        }

        // has magic methods?
        if (! $callerType->hasMethod(MethodName::__GET)->yes()) {
            return null;
        }

        $propertyName = $this->getName($propertyFetch->name);
        if ($propertyName === null) {
            return null;
        }

        $possibleGetterMethodNames = [];
        $possibleGetterMethodNames[] = 'get' . ucfirst($propertyName);
        $possibleGetterMethodNames[] = 'has' . ucfirst($propertyName);
        $possibleGetterMethodNames[] = 'is' . ucfirst($propertyName);

        foreach ($possibleGetterMethodNames as $possibleGetterMethodName) {
            if (! $callerType->hasMethod($possibleGetterMethodName)->yes()) {
                continue;
            }

            return $this->nodeFactory->createMethodCall($propertyFetch->var, $possibleGetterMethodName);
        }

        return null;
    }

    private function refactorMagicSet(Expr $expr, PropertyFetch $propertyFetch): MethodCall|null
    {
        $propertyCallerType = $this->getType($propertyFetch->var);
        if (! $propertyCallerType instanceof ObjectType) {
            return null;
        }

        if (! $propertyCallerType->hasMethod(MethodName::__SET)->yes()) {
            return null;
        }

        $propertyName = $this->getName($propertyFetch->name);
        if ($propertyName === null) {
            return null;
        }

        $setterMethodName = 'set' . ucfirst($propertyName);
        if (! $propertyCallerType->hasMethod($setterMethodName)->yes()) {
            return null;
        }

        return $this->nodeFactory->createMethodCall($propertyFetch->var, $setterMethodName, [$expr]);
    }
}
