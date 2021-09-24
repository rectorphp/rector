<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\NotIdentical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://externals.io/message/108562 https://github.com/php/php-src/pull/5179
 *
 * @see \Rector\Tests\Php80\Rector\NotIdentical\StrContainsRector\StrContainsRectorTest
 */
final class StrContainsRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private const OLD_STR_NAMES = ['strpos', 'strstr'];
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::STR_CONTAINS;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace strpos() !== false and strstr()  with str_contains()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return strpos('abc', 'a') !== false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class, \PhpParser\Node\Expr\BinaryOp\NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $funcCall = $this->matchIdenticalOrNotIdenticalToFalse($node);
        if (!$funcCall instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        $funcCall->name = new \PhpParser\Node\Name('str_contains');
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return new \PhpParser\Node\Expr\BooleanNot($funcCall);
        }
        return $funcCall;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $expr
     */
    private function matchIdenticalOrNotIdenticalToFalse($expr) : ?\PhpParser\Node\Expr\FuncCall
    {
        if ($this->valueResolver->isFalse($expr->left)) {
            if (!$expr->right instanceof \PhpParser\Node\Expr\FuncCall) {
                return null;
            }
            if (!$this->isNames($expr->right, self::OLD_STR_NAMES)) {
                return null;
            }
            /** @var FuncCall $funcCall */
            $funcCall = $expr->right;
            return $funcCall;
        }
        if ($this->valueResolver->isFalse($expr->right)) {
            if (!$expr->left instanceof \PhpParser\Node\Expr\FuncCall) {
                return null;
            }
            if (!$this->isNames($expr->left, self::OLD_STR_NAMES)) {
                return null;
            }
            /** @var FuncCall $funcCall */
            $funcCall = $expr->left;
            return $funcCall;
        }
        return null;
    }
}
