<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\DeadCode\NodeManipulator\LivingCodeManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\RemoveDeadStmtRectorTest
 */
final class RemoveDeadStmtRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\DeadCode\NodeManipulator\LivingCodeManipulator
     */
    private $livingCodeManipulator;
    /**
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\DeadCode\NodeManipulator\LivingCodeManipulator $livingCodeManipulator, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->livingCodeManipulator = $livingCodeManipulator;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes dead code statements', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$value = 5;
$value;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = 5;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Expression::class];
    }
    /**
     * @param Expression $node
     * @return Node[]|Node|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if ($this->hasGetMagic($node)) {
            return null;
        }
        $livingCode = $this->livingCodeManipulator->keepLivingCodeFromExpr($node->expr);
        if ($livingCode === []) {
            return $this->removeNodeAndKeepComments($node);
        }
        if ($livingCode === [$node->expr]) {
            return null;
        }
        $firstExpr = \array_shift($livingCode);
        $node->expr = $firstExpr;
        $newNodes = [];
        foreach ($livingCode as $singleLivingCode) {
            $newNodes[] = new \PhpParser\Node\Stmt\Expression($singleLivingCode);
        }
        $newNodes[] = $node;
        return $newNodes;
    }
    private function hasGetMagic(\PhpParser\Node\Stmt\Expression $expression) : bool
    {
        if (!$this->propertyFetchAnalyzer->isPropertyFetch($expression->expr)) {
            return \false;
        }
        /** @var PropertyFetch|StaticPropertyFetch $propertyFetch */
        $propertyFetch = $expression->expr;
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetch);
        /**
         *  property not found assume has class has __get method
         *  that can call non-defined property, that can have some special handling, eg: throw on special case
         */
        return !$phpPropertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection;
    }
    private function removeNodeAndKeepComments(\PhpParser\Node\Stmt\Expression $expression) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($expression);
        if ($expression->getComments() !== []) {
            $nop = new \PhpParser\Node\Stmt\Nop();
            $nop->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $phpDocInfo);
            $this->phpDocInfoFactory->createFromNode($nop);
            return $nop;
        }
        $this->removeNode($expression);
        return null;
    }
}
