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
final class StrContainsRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private const OLD_STR_NAMES = ['strpos', 'strstr'];
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STR_CONTAINS;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace strpos() !== false and strstr()  with str_contains()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node) : ?Node
    {
        $funcCall = $this->matchIdenticalOrNotIdenticalToFalse($node);
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        $funcCall->name = new Name('str_contains');
        if ($node instanceof Identical) {
            return new BooleanNot($funcCall);
        }
        return $funcCall;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $expr
     */
    private function matchIdenticalOrNotIdenticalToFalse($expr) : ?FuncCall
    {
        if ($this->valueResolver->isFalse($expr->left)) {
            if (!$expr->right instanceof FuncCall) {
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
            if (!$expr->left instanceof FuncCall) {
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
