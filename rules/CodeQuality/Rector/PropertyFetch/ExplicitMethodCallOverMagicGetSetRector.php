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
final class ExplicitMethodCallOverMagicGetSetRector extends AbstractScopeAwareRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace magic property fetch using __get() and __set() with existing method get*()/set*() calls', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [PropertyFetch::class, Assign::class];
    }
    /**
     * @param PropertyFetch|Assign $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($node instanceof Assign) {
            if ($node->var instanceof PropertyFetch) {
                return $this->refactorMagicSet($node->expr, $node->var, $scope);
            }
            return null;
        }
        if ($this->shouldSkipPropertyFetch($node)) {
            return null;
        }
        return $this->refactorPropertyFetch($node, $scope);
    }
    /**
     * @return string[]
     */
    public function resolvePossibleGetMethodNames(string $propertyName) : array
    {
        return ['get' . \ucfirst($propertyName), 'has' . \ucfirst($propertyName), 'is' . \ucfirst($propertyName)];
    }
    private function shouldSkipPropertyFetch(PropertyFetch $propertyFetch) : bool
    {
        $parent = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof Assign) {
            return \false;
        }
        return $parent->var === $propertyFetch;
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function refactorPropertyFetch(PropertyFetch $propertyFetch, Scope $scope)
    {
        $callerType = $this->getType($propertyFetch->var);
        if (!$callerType instanceof ObjectType) {
            return null;
        }
        // has magic methods?
        if (!$callerType->hasMethod(MethodName::__GET)->yes()) {
            return null;
        }
        $propertyName = $this->getName($propertyFetch->name);
        if ($propertyName === null) {
            return null;
        }
        if (!$callerType->hasProperty($propertyName)->yes()) {
            return null;
        }
        $propertyReflection = $callerType->getProperty($propertyName, $scope);
        $propertyType = $propertyReflection->getReadableType();
        $possibleGetterMethodNames = $this->resolvePossibleGetMethodNames($propertyName);
        foreach ($possibleGetterMethodNames as $possibleGetterMethodName) {
            if (!$callerType->hasMethod($possibleGetterMethodName)->yes()) {
                continue;
            }
            $methodReflection = $callerType->getMethod($possibleGetterMethodName, $scope);
            $variant = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
            $returnType = $variant->getReturnType();
            if (!$propertyType->isSuperTypeOf($returnType)->yes()) {
                continue;
            }
            return $this->nodeFactory->createMethodCall($propertyFetch->var, $possibleGetterMethodName);
        }
        return null;
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function refactorMagicSet(Expr $expr, PropertyFetch $propertyFetch, Scope $scope)
    {
        $propertyCallerType = $this->getType($propertyFetch->var);
        if (!$propertyCallerType instanceof ObjectType) {
            return null;
        }
        if (!$propertyCallerType->hasMethod(MethodName::__SET)->yes()) {
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
    private function hasNoParamOrVariadic(ObjectType $objectType, string $setterMethodName, Scope $scope) : bool
    {
        $methodReflection = $objectType->getMethod($setterMethodName, $scope);
        if (!$methodReflection instanceof ResolvedMethodReflection) {
            return \false;
        }
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        $parameters = $parametersAcceptor->getParameters();
        if (\count($parameters) !== 1) {
            return \true;
        }
        return $parameters[0]->isVariadic();
    }
}
