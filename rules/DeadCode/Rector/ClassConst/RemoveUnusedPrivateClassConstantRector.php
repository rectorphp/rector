<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\Rector\ClassConst;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassConst;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\EnumAnalyzer;
use RectorPrefix20220606\Rector\Core\NodeManipulator\ClassConstManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\Reflection\ReflectionResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\RemoveUnusedPrivateClassConstantRectorTest
 */
final class RemoveUnusedPrivateClassConstantRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassConstManipulator
     */
    private $classConstManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\EnumAnalyzer
     */
    private $enumAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ClassConstManipulator $classConstManipulator, EnumAnalyzer $enumAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->classConstManipulator = $classConstManipulator;
        $this->enumAnalyzer = $enumAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
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
    public function getNodeTypes() : array
    {
        return [ClassConst::class];
    }
    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClassConst($node)) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if ($this->classConstManipulator->hasClassConstFetch($node, $classReflection)) {
            return null;
        }
        $this->removeNode($node);
        return null;
    }
    private function shouldSkipClassConst(ClassConst $classConst) : bool
    {
        if (!$classConst->isPrivate()) {
            return \true;
        }
        if (\count($classConst->consts) !== 1) {
            return \true;
        }
        return $this->enumAnalyzer->isEnumClassConst($classConst);
    }
}
