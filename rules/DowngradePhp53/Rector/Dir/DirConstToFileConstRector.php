<?php

declare(strict_types=1);

namespace Rector\DowngradePhp53\Rector\Dir;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://github.com/nikic/PHP-Backporter/blob/master/lib/PHPBackporter/Converter/Dir.php
 *
 * @see \Rector\Tests\DowngradePhp53\Rector\Dir\DirConstToFileConstRector\DirConstToFileConstRectorTest
 */
final class DirConstToFileConstRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor __DIR__ to dirname(__FILE__)', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return __DIR__;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return dirname(__FILE__);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Scalar\MagicConst\Dir::class];
    }

    /**
     * @param \PhpParser\Node\Scalar\MagicConst\Dir $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->nodeFactory->createFuncCall('dirname', [new Node\Scalar\MagicConst\File()]);
    }
}
