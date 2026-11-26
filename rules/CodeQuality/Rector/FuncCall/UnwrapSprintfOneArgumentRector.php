<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector\UnwrapSprintfOneArgumentRectorTest
 */
final class UnwrapSprintfOneArgumentRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Unwrap `sprintf()` with one argument', [new CodeSample(<<<'CODE_SAMPLE'
echo sprintf('value');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo 'value';
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'sprintf')) {
            return null;
        }
        if (count($node->getArgs()) !== 1) {
            return null;
        }
        // unpacked argument is not resolved by getArg()
        $firstArg = $node->getArg('format', 0);
        if (!$firstArg instanceof Arg) {
            return null;
        }
        // only a literal without "%" prints itself, e.g. "%%" prints "%" and "%s" throws
        if (!$firstArg->value instanceof String_) {
            return null;
        }
        if (strpos($firstArg->value->value, '%') !== \false) {
            return null;
        }
        return $firstArg->value;
    }
}
