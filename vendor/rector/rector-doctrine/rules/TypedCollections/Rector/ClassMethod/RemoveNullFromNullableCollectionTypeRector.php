<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\RemoveNullFromNullableCollectionTypeRector\RemoveNullFromNullableCollectionTypeRectorTest
 */
final class RemoveNullFromNullableCollectionTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove null from a nullable Collection, as empty ArrayCollection is preferred instead to keep property type strict and always a collection', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private $items;

    public function setItems(?Collection $items): void
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private $items;

    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (\count($node->params) !== 1) {
            return null;
        }
        if ($this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->params as $param) {
            if (!$param->type instanceof NullableType) {
                continue;
            }
            $realType = $param->type->type;
            if (!$this->isName($realType, DoctrineClass::COLLECTION)) {
                continue;
            }
            $param->type = $realType;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
