<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Use_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TraitUseAdaptation\Alias;
use PhpParser\Node\Stmt\Use_;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector\SeparateMultiUseImportsRectorTest
 */
final class SeparateMultiUseImportsRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Split multi use imports and trait statements to standalone lines', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FileWithoutNamespace::class, Namespace_::class, Class_::class];
    }
    /**
     * @param FileWithoutNamespace|Namespace_|Class_ $node
     * @return \Rector\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_|\PhpParser\Node\Stmt\Class_|null
     */
    public function refactor(Node $node)
    {
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if ($stmt instanceof Use_) {
                $refactorUseImport = $this->refactorUseImport($stmt);
                if ($refactorUseImport !== null) {
                    unset($node->stmts[$key]);
                    \array_splice($node->stmts, $key, 0, $refactorUseImport);
                    $hasChanged = \true;
                }
                continue;
            }
            if ($stmt instanceof TraitUse) {
                $refactorTraitUse = $this->refactorTraitUse($stmt);
                if ($refactorTraitUse !== null) {
                    unset($node->stmts[$key]);
                    \array_splice($node->stmts, $key, 0, $refactorTraitUse);
                    $hasChanged = \true;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @return Use_[]|null $use
     */
    private function refactorUseImport(Use_ $use) : ?array
    {
        if (\count($use->uses) < 2) {
            return null;
        }
        $uses = [];
        foreach ($use->uses as $singleUse) {
            $uses[] = new Use_([$singleUse]);
        }
        return $uses;
    }
    /**
     * @return TraitUse[]|null
     */
    private function refactorTraitUse(TraitUse $traitUse) : ?array
    {
        if (\count($traitUse->traits) < 2) {
            return null;
        }
        $traitUses = [];
        foreach ($traitUse->traits as $singleTraitUse) {
            $adaptation = [];
            foreach ($traitUse->adaptations as $traitAdaptation) {
                if ($traitAdaptation instanceof Alias && $traitAdaptation->trait && $traitAdaptation->trait instanceof Name && $traitAdaptation->trait->toString() === $singleTraitUse->toString()) {
                    $adaptation[] = $traitAdaptation;
                }
            }
            $traitUses[] = new TraitUse([$singleTraitUse], $adaptation);
        }
        return $traitUses;
    }
}
