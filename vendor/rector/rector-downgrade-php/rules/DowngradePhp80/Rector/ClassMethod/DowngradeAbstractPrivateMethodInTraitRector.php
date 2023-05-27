<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector\DowngradeAbstractPrivateMethodInTraitRectorTest
 */
final class DowngradeAbstractPrivateMethodInTraitRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove "abstract" from private methods in traits and adds an empty function body', [new CodeSample(<<<'CODE_SAMPLE'
trait SomeTrait
{
    abstract private function someAbstractPrivateFunction();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
trait SomeTrait
{
    private function someAbstractPrivateFunction() {}
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        // remove abstract
        $node->flags -= Class_::MODIFIER_ABSTRACT;
        // Add empty array for stmts to generate empty function body
        $node->stmts = [];
        return $node;
    }
    private function shouldSkip(ClassMethod $classMethod) : bool
    {
        if (!$classMethod->isAbstract()) {
            return \true;
        }
        if (!$classMethod->isPrivate()) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        return !$classReflection->isTrait();
    }
}
