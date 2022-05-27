<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\PropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ResolvedMethodReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
final class ExplicitMethodCallOverMagicGetSetRector extends \Rector\Core\Rector\AbstractScopeAwareRector
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
    public function refactorWithScope(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\Assign) {
            if ($node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return $this->refactorMagicSet($node->expr, $node->var, $scope);
            }
            return null;
        }
        if ($this->shouldSkipPropertyFetch($node)) {
            return null;
        }
        return $this->refactorPropertyFetch($node);
    }
    /**
     * @return string[]
     */
    public function resolvePossibleGetMethodNames(string $propertyName) : array
    {
        return ['get' . \ucfirst($propertyName), 'has' . \ucfirst($propertyName), 'is' . \ucfirst($propertyName)];
    }
    private function shouldSkipPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        $parent = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        return $parent->var === $propertyFetch;
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
        $possibleGetterMethodNames = $this->resolvePossibleGetMethodNames($propertyName);
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
    private function refactorMagicSet(\PhpParser\Node\Expr $expr, \PhpParser\Node\Expr\PropertyFetch $propertyFetch, \PHPStan\Analyser\Scope $scope)
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
        if ($this->hasNoParamOrVariadic($propertyCallerType, $setterMethodName, $scope)) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($propertyFetch->var, $setterMethodName, [$expr]);
    }
    private function hasNoParamOrVariadic(\PHPStan\Type\ObjectType $objectType, string $setterMethodName, \PHPStan\Analyser\Scope $scope) : bool
    {
        $methodReflection = $objectType->getMethod($setterMethodName, $scope);
        if (!$methodReflection instanceof \PHPStan\Reflection\ResolvedMethodReflection) {
            return \false;
        }
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        $parameters = $parametersAcceptor->getParameters();
        if (\count($parameters) !== 1) {
            return \true;
        }
        return $parameters[0]->isVariadic();
    }
}
