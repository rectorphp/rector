<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\Identical;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\NetteCodeQuality\Tests\Rector\Identical\SubstrMinusToStringEndsWithRector\SubstrMinusToStringEndsWithRectorTest
 */
final class SubstrMinusToStringEndsWithRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change substr function with minus to Strings::endsWith()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
substr($var, -4) !== 'Test';
substr($var, -4) === 'Test';
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
! \Nette\Utils\Strings::endsWith($var, 'Test');
\Nette\Utils\Strings::endsWith($var, 'Test');
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! ($this->isFuncCallName($node->left, 'substr') || $this->isFuncCallName($node->right, 'substr'))) {
            return null;
        }

        $substr = $this->isFuncCallName($node->left, 'substr')
            ? $node->left
            : $node->right;

        if (! $substr->args[1]->value instanceof UnaryMinus) {
            return null;
        }

        $string = $this->isFuncCallName($node->left, 'substr')
            ? $node->right
            : $node->left;

        $replace = new StaticCall(new FullyQualified(Strings::class), 'endsWith', [$substr->args[0]->value, $string]);
        if ($node instanceof Identical) {
            return $replace;
        }

        return new BooleanNot($replace);
    }
}
