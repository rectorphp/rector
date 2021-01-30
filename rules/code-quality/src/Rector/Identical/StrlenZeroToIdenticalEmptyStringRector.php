<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\StrlenZeroToIdenticalEmptyStringRectorTest
 */
final class StrlenZeroToIdenticalEmptyStringRector extends AbstractRector
{
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

            ]);
    }

    /**
     * @return string[]
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
        $variable = null;
        if ($node->left instanceof FuncCall) {
            if (! $this->isName($node->left, 'strlen')) {
                return null;
            }

            if (! $this->valueResolver->isValue($node->right, 0)) {
                return null;
            }

            $variable = $node->left->args[0]->value;
        } elseif ($node->right instanceof FuncCall) {
            if (! $this->isName($node->right, 'strlen')) {
                return null;
            }

            if (! $this->valueResolver->isValue($node->left, 0)) {
                return null;
            }

            $variable = $node->right->args[0]->value;
        } else {
            return null;
        }

        /** @var Expr $variable */
        return new Identical($variable, new String_(''));
    }
}
