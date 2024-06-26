<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php74\Rector\FuncCall\HebrevcToNl2brHebrevRector\HebrevcToNl2brHebrevRectorTest
 */
final class HebrevcToNl2brHebrevRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_HEBREVC;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change hebrevc($str) to nl2br(hebrev($str))', [new CodeSample(<<<'CODE_SAMPLE'
hebrevc($str);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
nl2br(hebrev($str));
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
    public function refactor(Node $node) : ?FuncCall
    {
        if (!$this->isName($node, 'hebrevc')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $node->name = new Name('hebrev');
        return new FuncCall(new Name('nl2br'), [new Arg($node)]);
    }
}
