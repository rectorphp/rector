<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\DeadCode\NodeManipulator\ClassMethodParamRemover;
use Rector\DeadCode\NodeManipulator\VariadicFunctionLikeDetector;
use Rector\NodeAnalyzer\MagicClassMethodAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector\RemoveUnusedPublicMethodParameterRectorTest
 */
final class RemoveUnusedPublicMethodParameterRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\VariadicFunctionLikeDetector
     */
    private $variadicFunctionLikeDetector;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\ClassMethodParamRemover
     */
    private $classMethodParamRemover;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\MagicClassMethodAnalyzer
     */
    private $magicClassMethodAnalyzer;
    public function __construct(VariadicFunctionLikeDetector $variadicFunctionLikeDetector, ClassMethodParamRemover $classMethodParamRemover, MagicClassMethodAnalyzer $magicClassMethodAnalyzer)
    {
        $this->variadicFunctionLikeDetector = $variadicFunctionLikeDetector;
        $this->classMethodParamRemover = $classMethodParamRemover;
        $this->magicClassMethodAnalyzer = $magicClassMethodAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused parameter in public method on final class without extends and interface', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($a, $b)
    {
        echo $a;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($a)
    {
        echo $a;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        // may has child, or override parent that needs follow signature
        if (!$node->isFinal() || $node->extends instanceof FullyQualified || $node->implements !== []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($classMethod->params === []) {
                continue;
            }
            if ($this->magicClassMethodAnalyzer->isUnsafeOverridden($classMethod)) {
                continue;
            }
            if ($this->variadicFunctionLikeDetector->isVariadic($classMethod)) {
                continue;
            }
            $changedMethod = $this->classMethodParamRemover->processRemoveParams($classMethod);
            if (!$changedMethod instanceof ClassMethod) {
                continue;
            }
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
