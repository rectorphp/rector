<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\NetteCodeQuality\Tests\Rector\FuncCall\SubstrMinusToStringEndsWithRector\SubstrMinusToStringEndsWithRectorTest
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
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
! \Nette\Utils\Strings::endsWith($var, 'Test');
CODE_SAMPLE
                ),

            ]);
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
        if (! $this->isName($node, 'substr')) {
            return null;
        }

        if (! $node->args[1]->value instanceof UnaryMinus) {
            return null;
        }

        /** @var Node $parent */
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof NotIdentical) {
            return null;
        }

        $string = $parent->left === $node
            ? $parent->right
            : $parent->left;

        if (! $string instanceof String_) {
            return null;
        }

        $this->addNodeBeforeNode(
            new BooleanNot(
                new StaticCall(new Name('\Nette\Utils\Strings'), 'endsWith', [$node->args[0]->value, $string])
            ),
            $parent
        );
        $this->removeNode($parent);

        return $node;
    }
}
