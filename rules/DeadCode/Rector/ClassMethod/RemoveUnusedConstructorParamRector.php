<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\DeadCode\NodeManipulator\ClassMethodParamRemover;
use Rector\NodeAnalyzer\ParamAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\RemoveUnusedConstructorParamRectorTest
 */
final class RemoveUnusedConstructorParamRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ParamAnalyzer $paramAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ClassMethodParamRemover $classMethodParamRemover;
    public function __construct(ParamAnalyzer $paramAnalyzer, ReflectionResolver $reflectionResolver, ClassMethodParamRemover $classMethodParamRemover)
    {
        $this->paramAnalyzer = $paramAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->classMethodParamRemover = $classMethodParamRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused parameter in constructor', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $hey;

    public function __construct($hey, $man)
    {
        $this->hey = $hey;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $hey;

    public function __construct($hey)
    {
        $this->hey = $hey;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $constructorClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($constructorClassMethod->params === []) {
            return null;
        }
        if ($this->paramAnalyzer->hasPropertyPromotion($constructorClassMethod->params)) {
            return null;
        }
        if ($constructorClassMethod->isAbstract()) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $interfaces = $classReflection->getInterfaces();
        foreach ($interfaces as $interface) {
            if ($interface->hasNativeMethod(MethodName::CONSTRUCT)) {
                return null;
            }
        }
        $changedConstructorClassMethod = $this->classMethodParamRemover->processRemoveParams($constructorClassMethod);
        if (!$changedConstructorClassMethod instanceof ClassMethod) {
            return null;
        }
        return $node;
    }
}
