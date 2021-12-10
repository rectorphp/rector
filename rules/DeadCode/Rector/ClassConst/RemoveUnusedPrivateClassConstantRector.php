<?php

declare (strict_types=1);
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
final class RemoveUnusedPrivateClassConstantRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Core\NodeManipulator\ClassConstManipulator $classConstManipulator, \Rector\Core\NodeAnalyzer\EnumAnalyzer $enumAnalyzer)
    {
        $this->classConstManipulator = $classConstManipulator;
        $this->enumAnalyzer = $enumAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused class constants', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassConst::class];
    }
    /**
     * @param ClassConst $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipClassConst($node)) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        if ($this->classConstManipulator->hasClassConstFetch($node, $classReflection)) {
            return null;
        }
        $this->removeNode($node);
        return null;
    }
    private function shouldSkipClassConst(\PhpParser\Node\Stmt\ClassConst $classConst) : bool
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
