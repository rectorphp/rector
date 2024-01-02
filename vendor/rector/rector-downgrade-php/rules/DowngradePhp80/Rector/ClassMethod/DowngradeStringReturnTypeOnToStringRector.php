<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\DowngradeStringReturnTypeOnToStringRectorTest
 */
final class DowngradeStringReturnTypeOnToStringRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ClassChildAnalyzer $classChildAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add "string" return on current __toString() method when parent method has string return on __toString() method', [new CodeSample(<<<'CODE_SAMPLE'
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
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $node->returnType = new Name('string');
        return $node;
    }
    private function shouldSkip(ClassMethod $classMethod) : bool
    {
        if ($classMethod->returnType instanceof Node) {
            return \true;
        }
        if (!$this->nodeNameResolver->isName($classMethod, '__toString')) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        $type = $this->classChildAnalyzer->resolveParentClassMethodReturnType($classReflection, '__toString');
        return $type instanceof MixedType;
    }
}
