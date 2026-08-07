<?php

declare (strict_types=1);
namespace Rector\DowngradePhp84\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/exit-as-function
 *
 * @see \Rector\Tests\DowngradePhp84\Rector\FuncCall\DowngradeExitNamedArgumentRector\DowngradeExitNamedArgumentRectorTest
 */
final class DowngradeExitNamedArgumentRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove named argument from exit() and die()', [new CodeSample(<<<'CODE_SAMPLE'
exit(status: 1);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
exit(1);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isNames($node, ['exit', 'die'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (count($args) !== 1) {
            return null;
        }
        $statusArg = $args[0];
        if (!$statusArg instanceof Arg) {
            return null;
        }
        if (!$statusArg->name instanceof Identifier) {
            return null;
        }
        $statusArg->name = null;
        return $node;
    }
}
