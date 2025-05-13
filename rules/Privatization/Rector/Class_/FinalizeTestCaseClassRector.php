<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Php81\NodeManipulator\AttributeGroupNewLiner;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Class_\FinalizeTestCaseClassRector\FinalizeTestCaseClassRectorTest
 */
final class FinalizeTestCaseClassRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private AttributeGroupNewLiner $attributeGroupNewLiner;
    public function __construct(ReflectionProvider $reflectionProvider, VisibilityManipulator $visibilityManipulator, AttributeGroupNewLiner $attributeGroupNewLiner)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->attributeGroupNewLiner = $attributeGroupNewLiner;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make PHPUnit test case final', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        // skip obvious cases
        if ($node->isAbstract() || $node->isAnonymous() || $node->isFinal()) {
            return null;
        }
        $className = $this->getName($node);
        if (!\is_string($className)) {
            return null;
        }
        if (\substr_compare($className, 'TestCase', -\strlen('TestCase')) === 0) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->is('PHPUnit\\Framework\\TestCase')) {
            return null;
        }
        if ($node->attrGroups !== []) {
            $this->attributeGroupNewLiner->newLine($this->file, $node);
        }
        $this->visibilityManipulator->makeFinal($node);
        return $node;
    }
}
