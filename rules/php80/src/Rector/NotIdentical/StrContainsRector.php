<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\NotIdentical;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
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

    /**
     * @var string
     */
    private const PREG_MATCH_NAME = 'preg_match';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace strpos() !== false, strstr and preg_match() with str_contains()', [
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
        $funcCall = $this->matchPossibleFuncCallToFalseOrZero($node);
        if ($funcCall === null) {
            return null;
        }

        if ($this->isName($funcCall, self::PREG_MATCH_NAME)) {
            [$funcCall->args[0], $funcCall->args[1]] = [$funcCall->args[1], $funcCall->args[0]];

            $clearedDelimiter = $this->clearValueFromDelimiters($funcCall->args[1]->value);

            // unable to handle
            if ($clearedDelimiter === null) {
                return null;
            }

            $funcCall->args[1]->value = $clearedDelimiter;
        }

        $funcCall->name = new Name('str_contains');

        return $funcCall;
    }

    private function matchPossibleFuncCallToFalseOrZero(NotIdentical $notIdentical): ?FuncCall
    {
        if ($this->isNumber($notIdentical->left, 0)) {
            if (! $this->isFuncCallName($notIdentical->right, self::PREG_MATCH_NAME)) {
                return null;
            }

            return $notIdentical->right;
        }

        if ($this->isNumber($notIdentical->right, 0)) {
            if (! $this->isFuncCallName($notIdentical->left, self::PREG_MATCH_NAME)) {
                return null;
            }

            return $notIdentical->left;
        }

        return $this->matchNotIdenticalToFalse($notIdentical);
    }

    private function isNumber(Expr $expr, int $value): bool
    {
        if (! $expr instanceof LNumber) {
            return false;
        }

        return $expr->value === $value;
    }

    /**
     * Possibly extract to regex package or something
     */
    private function clearValueFromDelimiters(Expr $expr): ?Expr
    {
        // clears '#'. $value . '#'
        if ($expr instanceof Concat) {
            if (! $expr->right instanceof String_) {
                return null;
            }

            $rightValue = $this->getValue($expr->right);
            if (! $expr->left instanceof Concat) {
                return null;
            }

            /** @var Concat $leftConcat */
            $leftConcat = $expr->left;
            if (! $leftConcat->left instanceof String_) {
                return null;
            }

            $leftValue = $this->getValue($leftConcat->left);
            if ($leftValue === $rightValue) {
                return $leftConcat->right;
            }

            return null;
        }

        // clears '#content#'
        if ($expr instanceof String_) {
            $stringValue = $expr->value;

            $firstChar = $stringValue[0];
            $lastChar = $stringValue[strlen($stringValue) - 1];

            if ($firstChar === $lastChar) {
                $expr->value = Strings::substring($stringValue, 1, -1);
                return $expr;
            }

            return null;
        }

        // not supported yet
        return null;
    }

    private function matchNotIdenticalToFalse(NotIdentical $notIdentical): ?FuncCall
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
