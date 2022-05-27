<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedParamInRequiredAutowireRector\RemoveUnusedParamInRequiredAutowireRectorTest
 */
final class RemoveUnusedParamInRequiredAutowireRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(\Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused parameter in required autowire method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Contracts\Service\Attribute\Required;

final class SomeService
{
    private $visibilityManipulator;

    #[Required]
    public function autowire(VisibilityManipulator $visibilityManipulator)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Contracts\Service\Attribute\Required;

final class SomeService
{
    private $visibilityManipulator;

    #[Required]
    public function autowire()
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $params = $node->params;
        if ($params === []) {
            return null;
        }
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstanceOf((array) $node->getStmts(), \PhpParser\Node\Expr\Variable::class);
        $hasRemovedParam = \false;
        foreach ($params as $param) {
            $paramVar = $param->var;
            foreach ($variables as $variable) {
                if ($this->nodeComparator->areNodesEqual($variable, $paramVar)) {
                    continue 2;
                }
            }
            $this->removeNode($param);
            $hasRemovedParam = \true;
        }
        if (!$hasRemovedParam) {
            return null;
        }
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $hasRequiredAnnotation = $phpDocInfo->hasByName('required');
        $hasRequiredAttribute = $this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'Symfony\\Contracts\\Service\\Attribute\\Required');
        return !$hasRequiredAnnotation && !$hasRequiredAttribute;
    }
}
