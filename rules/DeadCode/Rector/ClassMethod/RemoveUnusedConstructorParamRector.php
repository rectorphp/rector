<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
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
    public function __construct(\Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer)
    {
        $this->paramAnalyzer = $paramAnalyzer;
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
        foreach ($node->params as $param) {
            if ($this->paramAnalyzer->isParamUsedInClassMethod($node, $param)) {
                continue;
            }
            $this->nodeRemover->removeParam($node, $param);
        }
        return null;
    }
}
