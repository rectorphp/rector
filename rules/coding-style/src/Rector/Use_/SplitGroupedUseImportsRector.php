<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\CodingStyle\Tests\Rector\Use_\SplitGroupedUseImportsRector\SplitGroupedUseImportsRectorTest
 */
final class SplitGroupedUseImportsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Split grouped use imports and trait statements to standalone lines',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use A, B;

class SomeClass
{
    use SomeTrait, AnotherTrait;
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
use A;
use B;

class SomeClass
{
    use SomeTrait;
    use AnotherTrait;
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Use_::class, TraitUse::class];
    }

    /**
     * @param Use_|TraitUse $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Use_) {
            $this->refactorUseImport($node);
        }

        if ($node instanceof TraitUse) {
            $this->refactorTraitUse($node);
        }

        return null;
    }

    private function refactorUseImport(Use_ $use): void
    {
        if (count($use->uses) < 2) {
            return;
        }

        foreach ($use->uses as $singleUse) {
            $separatedUse = new Use_([$singleUse]);
            $this->addNodeAfterNode($separatedUse, $use);
        }

        $this->removeNode($use);
    }

    private function refactorTraitUse(TraitUse $traitUse): void
    {
        if (count($traitUse->traits) < 2) {
            return;
        }

        foreach ($traitUse->traits as $singleTraitUse) {
            $separatedTraitUse = new TraitUse([$singleTraitUse]);
            $this->addNodeAfterNode($separatedTraitUse, $traitUse);
        }

        $this->removeNode($traitUse);
    }
}
