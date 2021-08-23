<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Mostly mimics source from
 * @changelog https://github.com/phpstan/phpstan-src/blob/master/src/Rules/ClassCaseSensitivityCheck.php
 *
 * @see \Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\FixClassCaseSensitivityNameRectorTest
 */
final class FixClassCaseSensitivityNameRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change miss-typed case sensitivity name to correct one', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $anotherClass = new anotherclass;
    }
}

final class AnotherClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $anotherClass = new AnotherClass;
    }
}

final class AnotherClass
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Name::class];
    }
    /**
     * @param Name $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $fullyQualifiedName = $this->resolveFullyQualifiedName($node);
        if (!$this->reflectionProvider->hasClass($fullyQualifiedName)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($fullyQualifiedName);
        if ($classReflection->isBuiltin()) {
            // skip built-in classes
            return null;
        }
        $realClassName = $classReflection->getName();
        if (\strtolower($realClassName) !== \strtolower($fullyQualifiedName)) {
            // skip class alias
            return null;
        }
        if ($realClassName === $fullyQualifiedName) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $hasFunction = $this->reflectionProvider->hasFunction(new \PhpParser\Node\Name\FullyQualified($fullyQualifiedName), $scope);
        if ($hasFunction) {
            return null;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // do not FQN use imports
        if ($parent instanceof \PhpParser\Node\Stmt\UseUse) {
            return new \PhpParser\Node\Name($realClassName);
        }
        return new \PhpParser\Node\Name\FullyQualified($realClassName);
    }
    private function resolveFullyQualifiedName(\PhpParser\Node\Name $name) : string
    {
        $parent = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // for some reason, Param gets already corrected name
        if (!$parent instanceof \PhpParser\Node\Param && !$parent instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return $this->getName($name);
        }
        $originalName = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME);
        if (!$originalName instanceof \PhpParser\Node\Name) {
            return $this->getName($name);
        }
        // replace parts from the old one
        $originalReversedParts = \array_reverse($originalName->parts);
        $resolvedReversedParts = \array_reverse($name->parts);
        $mergedReversedParts = $originalReversedParts + $resolvedReversedParts;
        $mergedParts = \array_reverse($mergedReversedParts);
        return \implode('\\', $mergedParts);
    }
}
