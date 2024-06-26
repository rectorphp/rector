<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Enum\ObjectReference;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\New_\NewStaticToNewSelfRector\NewStaticToNewSelfRectorTest
 */
final class NewStaticToNewSelfRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change unsafe new static() to new self()', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function build()
    {
        return new static();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function build()
    {
        return new self();
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
        if (!$node->isFinal()) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$hasChanged) : ?New_ {
            if (!$node instanceof New_) {
                return null;
            }
            if (!$this->isName($node->class, ObjectReference::STATIC)) {
                return null;
            }
            $hasChanged = \true;
            $node->class = new Name(ObjectReference::SELF);
            return $node;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
