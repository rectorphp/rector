<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/is-countable
 *
 * @see \Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector\DowngradeIsCountableRectorTest
 */
final class DowngradeIsCountableRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade is_countable() to former version', [new CodeSample(<<<'CODE_SAMPLE'
$items = [];
return is_countable($items);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$items = [];
return is_array($items) || $items instanceof Countable;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'is_countable')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof Arg) {
            return null;
        }
        $isArrayFuncCall = $this->nodeFactory->createFuncCall('is_array', $node->args);
        $instanceof = new Instanceof_($node->args[0]->value, new FullyQualified('Countable'));
        return new BooleanOr($isArrayFuncCall, $instanceof);
    }
}
