<?php

declare (strict_types=1);
namespace Rector\Php53\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector\DirNameFileConstantToDirConstantRectorTest
 */
final class DirNameFileConstantToDirConstantRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert `dirname(__FILE__)` to `__DIR__`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return dirname(__FILE__);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return __DIR__;
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'dirname')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->args) !== 1) {
            return null;
        }
        if (!isset($node->getArgs()[0])) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        if (!$firstArg->value instanceof File) {
            return null;
        }
        return new Dir();
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DIR_CONSTANT;
    }
}
