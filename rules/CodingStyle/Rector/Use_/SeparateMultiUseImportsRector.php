<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector\SeparateMultiUseImportsRectorTest
 */
final class SeparateMultiUseImportsRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Split multi use imports and trait statements to standalone lines', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use A, B;

class SomeClass
{
    use SomeTrait, AnotherTrait;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use A;
use B;

class SomeClass
{
    use SomeTrait;
    use AnotherTrait;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Use_::class, \PhpParser\Node\Stmt\TraitUse::class];
    }
    /**
     * @param Use_|TraitUse $node
     * @return Use_[]|TraitUse[]|null
     */
    public function refactor(\PhpParser\Node $node) : ?array
    {
        if ($node instanceof \PhpParser\Node\Stmt\Use_) {
            return $this->refactorUseImport($node);
        }
        return $this->refactorTraitUse($node);
    }
    /**
     * @return Use_[]|null $use
     */
    private function refactorUseImport(\PhpParser\Node\Stmt\Use_ $use) : ?array
    {
        if (\count($use->uses) < 2) {
            return null;
        }
        $uses = [];
        foreach ($use->uses as $singleUse) {
            $uses[] = new \PhpParser\Node\Stmt\Use_([$singleUse]);
        }
        return $uses;
    }
    /**
     * @return TraitUse[]|null
     */
    private function refactorTraitUse(\PhpParser\Node\Stmt\TraitUse $traitUse) : ?array
    {
        if (\count($traitUse->traits) < 2) {
            return null;
        }
        $traitUses = [];
        foreach ($traitUse->traits as $singleTraitUse) {
            $traitUses[] = new \PhpParser\Node\Stmt\TraitUse([$singleTraitUse]);
        }
        return $traitUses;
    }
}
