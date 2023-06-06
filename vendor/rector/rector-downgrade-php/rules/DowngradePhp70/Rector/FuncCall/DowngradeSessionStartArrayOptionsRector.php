<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\DowngradeSessionStartArrayOptionsRectorTest
 */
final class DowngradeSessionStartArrayOptionsRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move array option of session_start($options) to before statement\'s ini_set()', [new CodeSample(<<<'CODE_SAMPLE'
session_start([
    'cache_limiter' => 'private',
]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
ini_set('session.cache_limiter', 'private');
session_start();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class, Return_::class, If_::class];
    }
    /**
     * @param Expression|Return_|If_ $node
     * @return Stmt[]|null
     */
    public function refactor(Node $node) : ?array
    {
        $funcCalls = $this->betterNodeFinder->findInstanceOf($node, FuncCall::class);
        $nodesToReturn = [];
        foreach ($funcCalls as $funcCall) {
            if ($this->shouldSkip($funcCall)) {
                continue;
            }
            $firstArg = $funcCall->getArgs()[0] ?? null;
            if (!$firstArg instanceof Arg) {
                continue;
            }
            /** @var Array_ $options */
            $options = $firstArg->value;
            foreach ($options->items as $option) {
                if (!$option instanceof ArrayItem) {
                    continue;
                }
                if (!$option->key instanceof String_) {
                    continue;
                }
                if (!$this->valueResolver->isTrueOrFalse($option->value) && !$option->value instanceof String_) {
                    continue;
                }
                $initSetFuncCall = $this->createInitSetFuncCall($option, $option->key);
                unset($funcCall->args[0]);
                $nodesToReturn[] = new Expression($initSetFuncCall);
            }
        }
        if ($nodesToReturn !== []) {
            return \array_merge($nodesToReturn, [$node]);
        }
        return null;
    }
    private function shouldSkip(FuncCall $funcCall) : bool
    {
        if (!$this->isName($funcCall, 'session_start')) {
            return \true;
        }
        $args = $funcCall->getArgs();
        if (!isset($args[0])) {
            return \true;
        }
        return !$args[0]->value instanceof Array_;
    }
    private function createInitSetFuncCall(ArrayItem $arrayItem, String_ $string) : FuncCall
    {
        $sessionKey = new String_('session.' . $string->value);
        $funcName = new Name('ini_set');
        return new FuncCall($funcName, [new Arg($sessionKey), new Arg($arrayItem->value)]);
    }
}
