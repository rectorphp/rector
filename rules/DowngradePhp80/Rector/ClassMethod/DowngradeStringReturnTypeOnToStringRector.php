<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\DowngradeStringReturnTypeOnToStringRectorTest
 */
final class DowngradeStringReturnTypeOnToStringRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer $classChildAnalyzer, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add "string" return on current __toString() method when parent method has string return on __toString() method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
abstract class ParentClass
{
    public function __toString(): string
    {
        return 'value';
    }
}

class ChildClass extends ParentClass
{
    public function __toString()
    {
        return 'value';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
abstract class ParentClass
{
    public function __toString(): string
    {
        return 'value';
    }
}

class ChildClass extends ParentClass
{
    public function __toString(): string
    {
        return 'value';
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $node->returnType = new \PhpParser\Node\Name('string');
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$this->nodeNameResolver->isName($classMethod, '__toString')) {
            return \true;
        }
        if ($classMethod->returnType instanceof \PhpParser\Node) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \true;
        }
        $type = $this->classChildAnalyzer->resolveParentClassMethodReturnType($classReflection, '__toString');
        return $type instanceof \PHPStan\Type\MixedType;
    }
}
