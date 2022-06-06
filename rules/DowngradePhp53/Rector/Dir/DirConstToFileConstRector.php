<?php

declare (strict_types=1);
namespace Rector\DowngradePhp53\Rector\Dir;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/nikic/PHP-Backporter/blob/master/lib/PHPBackporter/Converter/Dir.php
 *
 * @see \Rector\Tests\DowngradePhp53\Rector\Dir\DirConstToFileConstRector\DirConstToFileConstRectorTest
 */
final class DirConstToFileConstRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor __DIR__ to dirname(__FILE__)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Scalar\MagicConst\Dir::class];
    }
    /**
     * @param Dir $node
     */
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Expr\FuncCall
    {
        return $this->nodeFactory->createFuncCall('dirname', [new \PhpParser\Node\Scalar\MagicConst\File()]);
    }
}
