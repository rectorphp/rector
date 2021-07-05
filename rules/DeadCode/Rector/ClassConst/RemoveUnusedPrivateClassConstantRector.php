<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\NodeAnalyzer\EnumAnalyzer;
use Rector\Core\NodeManipulator\ClassConstManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\RemoveUnusedPrivateClassConstantRectorTest
 */
final class RemoveUnusedPrivateClassConstantRector extends AbstractRector
{
    public function __construct(
        private ClassConstManipulator $classConstManipulator,
        private EnumAnalyzer $enumAnalyzer,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused class constants', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private const SOME_CONST = 'dead';

    public function run()
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipClassConst($node)) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        if ($this->classConstManipulator->hasClassConstFetch($node, $classReflection)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function shouldSkipClassConst(ClassConst $classConst): bool
    {
        if (! $classConst->isPrivate()) {
            return true;
        }

        if (count($classConst->consts) !== 1) {
            return true;
        }

        return $this->enumAnalyzer->isEnumClassConst($classConst);
    }
}
