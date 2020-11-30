<?php

declare(strict_types=1);

namespace Rector\Php56\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Pow;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php56\Tests\Rector\FuncCall\PowToExpRector\PowToExpRectorTest
 */
final class PowToExpRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes pow(val, val2) to ** (exp) parameter',
            [new CodeSample('pow(1, 2);', '1**2;')]
        );
    }

    /**
     * @return string[]
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
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::EXP_OPERATOR)) {
            return null;
        }

        if (! $this->isName($node, 'pow')) {
            return null;
        }

        if (count($node->args) !== 2) {
            return null;
        }

        $firstArgument = $node->args[0]->value;
        $secondArgument = $node->args[1]->value;

        return new Pow($firstArgument, $secondArgument);
    }
}
