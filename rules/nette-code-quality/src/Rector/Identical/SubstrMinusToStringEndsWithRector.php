<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\Identical;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\NetteCodeQuality\Tests\Rector\Identical\SubstrMinusToStringEndsWithRector\SubstrMinusToStringEndsWithRectorTest
 */
final class SubstrMinusToStringEndsWithRector extends AbstractRector
{
    /**
     * @var string
     */
    private const SUBSTR = 'substr';

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
        if (! $this->isFuncCallName($node->left, self::SUBSTR) && ! $this->isFuncCallName($node->right, self::SUBSTR)) {
            return null;
        }

        $substr = $this->isFuncCallName($node->left, self::SUBSTR)
            ? $node->left
            : $node->right;

        if (! $substr->args[1]->value instanceof UnaryMinus) {
            return null;
        }

        /** @var UnaryMinus $unaryMinus */
        $unaryMinus = $substr->args[1]->value;
        if (! $unaryMinus->expr instanceof LNumber) {
            return null;
        }

        $string = $this->isFuncCallName($node->left, self::SUBSTR)
            ? $node->right
            : $node->left;

        $wordLength = $unaryMinus->expr->value;
        if ($string instanceof String_ && strlen($string->value) !== $wordLength) {
            return null;
        }

        $staticCall = $this->nodeFactory->createStaticCall(
            Strings::class,
            'endsWith',
            [$substr->args[0]->value, $string]
        );

        if ($node instanceof Identical) {
            return $staticCall;
        }

        return new BooleanNot($staticCall);
    }
}
