<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\NotIdentical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace strpos() !== false and strstr()  with str_contains()', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return strpos('abc', 'a') !== false;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}
PHP
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
        if ($funcCall === null) {
            return null;
        }

        $funcCall->name = new Name('str_contains');

        return $funcCall;
    }

    private function matchNotIdenticalToFalse(NotIdentical $notIdentical): ?Expr
    {
        if ($this->isFalse($notIdentical->left)) {
            if (! $this->isFuncCallNames($notIdentical->right, self::OLD_STR_NAMES)) {
                return null;
            }

            return $notIdentical->right;
        }

        if ($this->isFalse($notIdentical->right)) {
            if (! $this->isFuncCallNames($notIdentical->left, self::OLD_STR_NAMES)) {
                return null;
            }

            return $notIdentical->left;
        }

        return null;
    }
}
