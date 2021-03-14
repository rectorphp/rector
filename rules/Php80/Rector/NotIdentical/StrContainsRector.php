<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\NotIdentical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://externals.io/message/108562
 * @see https://github.com/php/php-src/pull/5179
 *
 * @see \Rector\Tests\Php80\Rector\NotIdentical\StrContainsRector\StrContainsRectorTest
 */
final class StrContainsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const OLD_STR_NAMES = ['strpos', 'strstr'];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace strpos() !== false and strstr()  with str_contains()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return strpos('abc', 'a') !== false;
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}
CODE_SAMPLE
            ),
            ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical::class|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $funcCall = $this->matchFuncCall($node);

        if (! $funcCall instanceof FuncCall) {
            return null;
        }

        $funcCall->name = new Name('str_contains');

        if ($node instanceof Identical) {
            return new BooleanNot($funcCall);
        }

        return $funcCall;
    }

    private function matchFuncCall(Expr $expr): ?Expr
    {
        if ($this->valueResolver->isFalse($expr->left)) {
            if (! $this->nodeNameResolver->isFuncCallNames($expr->right, self::OLD_STR_NAMES)) {
                return null;
            }

            return $expr->right;
        }

        if ($this->valueResolver->isFalse($expr->right)) {
            if (! $this->nodeNameResolver->isFuncCallNames($expr->left, self::OLD_STR_NAMES)) {
                return null;
            }

            return $expr->left;
        }

        return null;
    }
}
