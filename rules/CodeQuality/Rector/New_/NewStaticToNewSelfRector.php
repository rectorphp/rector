<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/phpstan/phpstan-src/blob/699c420f8193da66927e54494a0afa0c323c6458/src/Rules/Classes/NewStaticRule.php
 *
 * @see \Rector\Tests\CodeQuality\Rector\New_\NewStaticToNewSelfRector\NewStaticToNewSelfRectorTest
 */
final class NewStaticToNewSelfRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change unsafe new static() to new self()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function build()
    {
        return new static();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
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
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        if (!$class->isFinal()) {
            return null;
        }
        if (!$this->isName($node->class, ObjectReference::STATIC)) {
            return null;
        }
        $node->class = new Name(ObjectReference::SELF);
        return $node;
    }
}
