<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Instanceof_;

use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/stringable
 * @see \Rector\Tests\DowngradePhp80\Rector\Instanceof_\DowngradeInstanceofStringableRector\DowngradeInstanceofStringableRectorTest
 */
final class DowngradeInstanceofStringableRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('change instanceof Stringable to method_exists', [new CodeSample(<<<'CODE_SAMPLE'
$obj instanceof \Stringable;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
method_exists($obj, '__toString');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Instanceof_::class];
    }
    /**
     * @param Instanceof_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->class instanceof FullyQualified) {
            return null;
        }
        if (!$this->isName($node->class, 'Stringable')) {
            return null;
        }
        return $this->nodeFactory->createFuncCall('method_exists', [$node->expr, new String_('__toString')]);
    }
}
