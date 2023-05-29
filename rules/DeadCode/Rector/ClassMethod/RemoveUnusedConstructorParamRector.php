<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Removing\NodeManipulator\ComplexNodeRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\RemoveUnusedConstructorParamRectorTest
 */
final class RemoveUnusedConstructorParamRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @readonly
     * @var \Rector\Removing\NodeManipulator\ComplexNodeRemover
     */
    private $complexNodeRemover;
    public function __construct(ParamAnalyzer $paramAnalyzer, ComplexNodeRemover $complexNodeRemover)
    {
        $this->paramAnalyzer = $paramAnalyzer;
        $this->complexNodeRemover = $complexNodeRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused parameter in constructor', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $hey;

    public function __construct($hey, $man)
    {
        $this->hey = $hey;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $hey;

    public function __construct($hey)
    {
        $this->hey = $hey;
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
        $constructorClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($constructorClassMethod->params === []) {
            return null;
        }
        if ($this->paramAnalyzer->hasPropertyPromotion($constructorClassMethod->params)) {
            return null;
        }
        if ($constructorClassMethod->isAbstract()) {
            return null;
        }
        $changedConstructorClassMethod = $this->processRemoveParams($constructorClassMethod);
        if (!$changedConstructorClassMethod instanceof ClassMethod) {
            return null;
        }
        return $node;
    }
    private function processRemoveParams(ClassMethod $classMethod) : ?ClassMethod
    {
        $paramKeysToBeRemoved = [];
        foreach ($classMethod->params as $key => $param) {
            if ($this->paramAnalyzer->isParamUsedInClassMethod($classMethod, $param)) {
                continue;
            }
            $paramKeysToBeRemoved[] = $key;
        }
        if ($paramKeysToBeRemoved === []) {
            return null;
        }
        $removedParamKeys = $this->complexNodeRemover->processRemoveParamWithKeys($classMethod, $paramKeysToBeRemoved);
        if ($removedParamKeys !== []) {
            return $classMethod;
        }
        return null;
    }
}
