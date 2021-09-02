<?php

declare(strict_types=1);

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
final class DowngradeSessionStartArrayOptionsRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move array option of session_start($options) to before statement\'s ini_set()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
session_start([
    'cache_limiter' => 'private',
]);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
ini_set('session.cache_limiter', 'private');
session_start();
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $currentStatement = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);

        /** @var Array_ $options */
        $options = $node->args[0]->value;

        foreach ($options->items as $option) {
            if (! $option instanceof ArrayItem) {
                return null;
            }

            if (! $option->key instanceof String_) {
                return null;
            }

            if (! $this->valueResolver->isTrueOrFalse($option->value) && ! $option->value instanceof String_) {
                return null;
            }

            $sessionKey = new String_('session.' . $option->key->value);
            $funcName = new Name('ini_set');
            $iniSet = new FuncCall($funcName, [new Arg($sessionKey), new Arg($option->value)]);

            $this->nodesToAddCollector->addNodeBeforeNode(new Expression($iniSet), $currentStatement);
        }

        unset($node->args[0]);
        return $node;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isName($funcCall, 'session_start')) {
            return true;
        }

        if (! isset($funcCall->args[0])) {
            return true;
        }

        return ! $funcCall->args[0]->value instanceof Array_;
    }
}
