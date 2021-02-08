<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\NotIdentical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://externals.io/message/108562
 * @see https://github.com/php/php-src/pull/5179
 *
 * @see \Rector\Php80\Tests\Rector\NotIdentical\StrContainsRector\StrContainsRectorTest
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
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [NotIdentical::class];
    }

    /**
     * @param NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $funcCall = $this->matchNotIdenticalToFalse($node);
        if (! $funcCall instanceof FuncCall) {
            return null;
        }

        $funcCall->name = new Name('str_contains');

        return $funcCall;
    }

    /**
     * @return FuncCall|null
     */
    private function matchNotIdenticalToFalse(NotIdentical $notIdentical): ?Expr
    {
        if ($this->valueResolver->isFalse($notIdentical->left)) {
            if (! $this->nodeNameResolver->isFuncCallNames($notIdentical->right, self::OLD_STR_NAMES)) {
                return null;
            }

            return $notIdentical->right;
        }

        if ($this->valueResolver->isFalse($notIdentical->right)) {
            if (! $this->nodeNameResolver->isFuncCallNames($notIdentical->left, self::OLD_STR_NAMES)) {
                return null;
            }

            return $notIdentical->left;
        }

        return null;
    }
}
