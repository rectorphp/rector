<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Reflection\ClassReflection;
use Rector\PHPStan\ScopeFetcher;
use Rector\Privatization\Guard\OverrideByParentClassGuard;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\ClassConst\PrivatizeFinalClassConstantRector\PrivatizeFinalClassConstantRectorTest
 */
final class PrivatizeFinalClassConstantRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private OverrideByParentClassGuard $overrideByParentClassGuard;
    public function __construct(VisibilityManipulator $visibilityManipulator, OverrideByParentClassGuard $overrideByParentClassGuard)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->overrideByParentClassGuard = $overrideByParentClassGuard;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change protected constant to private if possible', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    protected const SOME_CONSTANT = 'some-value';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private const SOME_CONSTANT = 'some-value';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->isFinal()) {
            return null;
        }
        if (!$this->overrideByParentClassGuard->isLegal($node)) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getConstants() as $classConst) {
            if ($this->shouldSkipClassConst($classConst)) {
                continue;
            }
            if ($this->isDefinedInParentOrInterface($classConst, $classReflection)) {
                continue;
            }
            $this->visibilityManipulator->makePrivate($classConst);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipClassConst(ClassConst $classConst): bool
    {
        // Skip if multiple constants are defined in one line to avoid partial visibility complexity
        if (count($classConst->consts) !== 1) {
            return \true;
        }
        return !$classConst->isProtected();
    }
    private function isDefinedInParentOrInterface(ClassConst $classConst, ClassReflection $classReflection): bool
    {
        $constantName = $this->getName($classConst);
        if ($constantName === null) {
            return \true;
        }
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            // Skip the class itself
            if ($ancestorClassReflection->getName() === $classReflection->getName()) {
                continue;
            }
            if ($ancestorClassReflection->hasConstant($constantName)) {
                return \true;
            }
        }
        return \false;
    }
}
