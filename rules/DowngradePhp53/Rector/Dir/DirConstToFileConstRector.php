<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp53\Rector\Dir;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\MagicConst\Dir;
use RectorPrefix20220606\PhpParser\Node\Scalar\MagicConst\File;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/nikic/PHP-Backporter/blob/master/lib/PHPBackporter/Converter/Dir.php
 *
 * @see \Rector\Tests\DowngradePhp53\Rector\Dir\DirConstToFileConstRector\DirConstToFileConstRectorTest
 */
final class DirConstToFileConstRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor __DIR__ to dirname(__FILE__)', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return __DIR__;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return dirname(__FILE__);
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
        return [Dir::class];
    }
    /**
     * @param Dir $node
     */
    public function refactor(Node $node) : FuncCall
    {
        return $this->nodeFactory->createFuncCall('dirname', [new File()]);
    }
}
