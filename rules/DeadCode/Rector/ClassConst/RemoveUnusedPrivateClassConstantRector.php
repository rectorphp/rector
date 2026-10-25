<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeManipulator\ClassConstManipulator;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\RemoveUnusedPrivateClassConstantRectorTest
 */
final class RemoveUnusedPrivateClassConstantRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ClassConstManipulator $classConstManipulator;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(ClassConstManipulator $classConstManipulator, ReflectionResolver $reflectionResolver)
    {
        $this->classConstManipulator = $classConstManipulator;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused class constants', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private const SOME_CONST = 'dead';

    public function run()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Enum_::class];
    }
    /**
     * @param Class_|Enum_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $unusedClassConstKeys = [];
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof ClassConst) {
                continue;
            }
            if ($this->shouldSkipClassConst($classReflection, $stmt)) {
                continue;
            }
            if ($this->classConstManipulator->hasClassConstFetch($stmt, $classReflection, $node)) {
                continue;
            }
            $unusedClassConstKeys[] = $key;
        }
        if ($unusedClassConstKeys === []) {
            return null;
        }
        // remove only after all constants are checked, so a constant used by another removed one is kept
        foreach ($unusedClassConstKeys as $unusedClassConstKey) {
            unset($node->stmts[$unusedClassConstKey]);
        }
        return $node;
    }
    private function shouldSkipClassConst(ClassReflection $classReflection, ClassConst $classConst): bool
    {
        if (!$classConst->isPrivate()) {
            return \true;
        }
        if (count($classConst->consts) !== 1) {
            return \true;
        }
        return $this->hasParentClassOfEnumSuffix($classReflection);
    }
    private function hasParentClassOfEnumSuffix(ClassReflection $classReflection): bool
    {
        $found = \false;
        foreach ($classReflection->getParentClassesNames() as $parentClassesName) {
            if (substr_compare($parentClassesName, 'Enum', -strlen('Enum')) === 0) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
