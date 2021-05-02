<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\Expression;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Expression\DowngradeSessionStartArrayOptionsRector\DowngradeSessionStartArrayOptionsRectorTest
 */
final class DowngradeSessionStartArrayOptionsRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
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
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        return $node;
    }
}
