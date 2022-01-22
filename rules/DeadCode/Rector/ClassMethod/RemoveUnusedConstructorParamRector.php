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
final class RemoveUnusedConstructorParamRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer, \Rector\Removing\NodeManipulator\ComplexNodeRemover $complexNodeRemover)
    {
        $this->paramAnalyzer = $paramAnalyzer;
        $this->complexNodeRemover = $complexNodeRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused parameter in constructor', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return null;
        }
        if ($node->params === []) {
            return null;
        }
        if ($this->paramAnalyzer->hasPropertyPromotion($node->params)) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        if ($node->isAbstract()) {
            return null;
        }
        return $this->processRemoveParams($node);
    }
    private function processRemoveParams(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $paramKeysToBeRemoved = [];
        foreach ($classMethod->params as $key => $param) {
            if ($this->paramAnalyzer->isParamUsedInClassMethod($classMethod, $param)) {
                continue;
            }
            $paramKeysToBeRemoved[] = $key;
        }
        $removedParamKeys = $this->complexNodeRemover->processRemoveParamWithKeys($classMethod->params, $paramKeysToBeRemoved);
        if ($removedParamKeys === []) {
            return null;
        }
        return $classMethod;
    }
}
