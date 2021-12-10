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
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\DowngradeSessionStartArrayOptionsRectorTest
 */
final class DowngradeSessionStartArrayOptionsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move array option of session_start($options) to before statement\'s ini_set()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $currentStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        /** @var Array_ $options */
        $options = $node->args[0]->value;
        foreach ($options->items as $option) {
            if (!$option instanceof \PhpParser\Node\Expr\ArrayItem) {
                return null;
            }
            if (!$option->key instanceof \PhpParser\Node\Scalar\String_) {
                return null;
            }
            if (!$this->valueResolver->isTrueOrFalse($option->value) && !$option->value instanceof \PhpParser\Node\Scalar\String_) {
                return null;
            }
            $sessionKey = new \PhpParser\Node\Scalar\String_('session.' . $option->key->value);
            $funcName = new \PhpParser\Node\Name('ini_set');
            $iniSet = new \PhpParser\Node\Expr\FuncCall($funcName, [new \PhpParser\Node\Arg($sessionKey), new \PhpParser\Node\Arg($option->value)]);
            $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression($iniSet), $currentStatement);
        }
        unset($node->args[0]);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        if (!$this->isName($funcCall, 'session_start')) {
            return \true;
        }
        if (!isset($funcCall->args[0])) {
            return \true;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return \true;
        }
        return !$funcCall->args[0]->value instanceof \PhpParser\Node\Expr\Array_;
    }
}
