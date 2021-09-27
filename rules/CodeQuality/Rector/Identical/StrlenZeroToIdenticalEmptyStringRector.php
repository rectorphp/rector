<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\StrlenZeroToIdenticalEmptyStringRectorTest
 */
final class StrlenZeroToIdenticalEmptyStringRector extends AbstractRector
{
    public function __construct(
        private ArgsAnalyzer $argsAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes strlen comparison to 0 to direct empty string compare',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $empty = strlen($value) === 0;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $empty = $value === '';
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Identical::class];
    }

    /**
     * @param Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->left instanceof FuncCall) {
            return $this->processLeftIdentical($node, $node->left);
        }

        if ($node->right instanceof FuncCall) {
            return $this->processRightIdentical($node, $node->right);
        }

        return null;
    }

    private function processLeftIdentical(Identical $identical, FuncCall $funcCall): ?Identical
    {
        if (! $this->isName($funcCall, 'strlen')) {
            return null;
        }

        if (! $this->valueResolver->isValue($identical->right, 0)) {
            return null;
        }

        if (! $this->argsAnalyzer->isArgInstanceInArgsPosition($funcCall->args, 0)) {
            return null;
        }

        /** @var Arg $firstArg */
        $firstArg = $funcCall->args[0];
        /** @var Expr $variable */
        $variable = $firstArg->value;

        return new Identical($variable, new String_(''));
    }

    private function processRightIdentical(Identical $identical, FuncCall $funcCall): ?Identical
    {
        if (! $this->isName($funcCall, 'strlen')) {
            return null;
        }

        if (! $this->valueResolver->isValue($identical->left, 0)) {
            return null;
        }

        if (! $this->argsAnalyzer->isArgInstanceInArgsPosition($funcCall->args, 0)) {
            return null;
        }

        /** @var Arg $firstArg */
        $firstArg = $funcCall->args[0];
        /** @var Expr $variable */
        $variable = $firstArg->value;

        return new Identical($variable, new String_(''));
    }
}
