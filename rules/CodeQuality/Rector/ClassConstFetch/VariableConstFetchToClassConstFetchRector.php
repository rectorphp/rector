<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassConstFetch\VariableConstFetchToClassConstFetchRector\VariableConstFetchToClassConstFetchRectorTest
 */
final class VariableConstFetchToClassConstFetchRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change variable class constant fetch to direct class constant fetch', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(AnotherClass $anotherClass)
    {
        return $anotherClass::CONSTANT_NAME;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(AnotherClass $anotherClass)
    {
        return AnotherClass::CONSTANT_NAME;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?ClassConstFetch
    {
        if (!$node->class instanceof Variable) {
            return null;
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        $constantName = $this->getName($node->name);
        if (!is_string($constantName)) {
            return null;
        }
        $classObjectType = $this->nodeTypeResolver->getNativeType($node->class);
        if (!$classObjectType instanceof ObjectType) {
            return null;
        }
        if (!$classObjectType->hasConstant($constantName)->yes()) {
            return null;
        }
        $node->class = new FullyQualified($classObjectType->getClassName());
        return $node;
    }
}
