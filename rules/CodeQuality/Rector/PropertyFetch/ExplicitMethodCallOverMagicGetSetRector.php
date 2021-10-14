<?php

declare (strict_types=1);
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
final class ExplicitMethodCallOverMagicGetSetRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace magic property fetch using __get() and __set() with existing method get*()/set*() calls', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param PropertyFetch|Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\Assign) {
            if ($node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return $this->refactorMagicSet($node->expr, $node->var);
            }
            return null;
        }
        if ($this->shouldSkipPropertyFetch($node)) {
            return null;
        }
        return $this->refactorPropertyFetch($node);
    }
    private function shouldSkipPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        $parentAssign = $this->betterNodeFinder->findParentType($propertyFetch, \PhpParser\Node\Expr\Assign::class);
        if (!$parentAssign instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirst($parentAssign->var, function (\PhpParser\Node $subNode) use($propertyFetch) : bool {
            return $subNode === $propertyFetch;
        });
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function refactorPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        $callerType = $this->getType($propertyFetch->var);
        if (!$callerType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        // has magic methods?
        if (!$callerType->hasMethod(\Rector\Core\ValueObject\MethodName::__GET)->yes()) {
            return null;
        }
        $propertyName = $this->getName($propertyFetch->name);
        if ($propertyName === null) {
            return null;
        }
        $possibleGetterMethodNames = [];
        $possibleGetterMethodNames[] = 'get' . \ucfirst($propertyName);
        $possibleGetterMethodNames[] = 'has' . \ucfirst($propertyName);
        $possibleGetterMethodNames[] = 'is' . \ucfirst($propertyName);
        foreach ($possibleGetterMethodNames as $possibleGetterMethodName) {
            if (!$callerType->hasMethod($possibleGetterMethodName)->yes()) {
                continue;
            }
            return $this->nodeFactory->createMethodCall($propertyFetch->var, $possibleGetterMethodName);
        }
        return null;
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function refactorMagicSet(\PhpParser\Node\Expr $expr, \PhpParser\Node\Expr\PropertyFetch $propertyFetch)
    {
        $propertyCallerType = $this->getType($propertyFetch->var);
        if (!$propertyCallerType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        if (!$propertyCallerType->hasMethod(\Rector\Core\ValueObject\MethodName::__SET)->yes()) {
            return null;
        }
        $propertyName = $this->getName($propertyFetch->name);
        if ($propertyName === null) {
            return null;
        }
        $setterMethodName = 'set' . \ucfirst($propertyName);
        if (!$propertyCallerType->hasMethod($setterMethodName)->yes()) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($propertyFetch->var, $setterMethodName, [$expr]);
    }
}
