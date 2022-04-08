<?php

declare (strict_types=1);
namespace Rector\Visibility\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Visibility;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector\ExplicitPublicClassMethodRectorTest
 */
final class ExplicitPublicClassMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(\Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator)
    {
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add explicit public method visibility.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    function foo()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function foo()
    {
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        // already non-public
        if (!$node->isPublic()) {
            return null;
        }
        // explicitly public
        if ($this->visibilityManipulator->hasVisibility($node, \Rector\Core\ValueObject\Visibility::PUBLIC)) {
            return null;
        }
        $this->visibilityManipulator->makePublic($node);
        return $node;
    }
}
