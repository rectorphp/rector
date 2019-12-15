<?php

declare(strict_types=1);

namespace Rector\Php53\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see http://php.net/manual/en/language.operators.comparison.php#language.operators.comparison.ternary
 * @see https://stackoverflow.com/a/1993455/1348344
 * @see \Rector\Php53\Tests\Rector\Ternary\TernaryToElvisRector\TernaryToElvisRectorTest
 */
final class TernaryToElvisRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use ?: instead of ?, where useful', [
            new CodeSample(
                <<<'PHP'
function elvis()
{
    $value = $a ? $a : false;
}
PHP
                ,
                <<<'PHP'
function elvis()
{
    $value = $a ?: false;
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
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->areNodesEqual($node->cond, $node->if)) {
            return null;
        }

        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $node->if = null;

        return $node;
    }
}
