<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\Instanceof_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeCollector\BinaryOpConditionsCollector;
use Rector\NodeCollector\BinaryOpTreeRootLocator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Throwable was introduced in PHP 7.0 so to support older versions we need to also check for Exception.
 * @see https://www.php.net/manual/en/class.throwable.php
 * @see \Rector\Tests\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector\DowngradeInstanceofThrowableRectorTest
 */
final class DowngradeInstanceofThrowableRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\NodeCollector\BinaryOpConditionsCollector
     */
    private $binaryOpConditionsCollector;
    /**
     * @readonly
     * @var \Rector\NodeCollector\BinaryOpTreeRootLocator
     */
    private $binaryOpTreeRootLocator;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming, \Rector\NodeCollector\BinaryOpConditionsCollector $binaryOpConditionsCollector, \Rector\NodeCollector\BinaryOpTreeRootLocator $binaryOpTreeRootLocator)
    {
        $this->variableNaming = $variableNaming;
        $this->binaryOpConditionsCollector = $binaryOpConditionsCollector;
        $this->binaryOpTreeRootLocator = $binaryOpTreeRootLocator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add `instanceof Exception` check as a fallback to `instanceof Throwable` to support exception hierarchies in PHP 5', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return $e instanceof \Throwable;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return ($throwable = $e) instanceof \Throwable || $throwable instanceof \Exception;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Instanceof_::class];
    }
    /**
     * @param Instanceof_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!($node->class instanceof \PhpParser\Node\Name && $this->nodeNameResolver->isName($node->class, 'Throwable'))) {
            return null;
        }
        // Ensure the refactoring is idempotent.
        if ($this->isAlreadyTransformed($node)) {
            return null;
        }
        // Store the value into a temporary variable to prevent running possible side-effects twice
        // when the expression is e.g. function.
        $variable = $this->createVariable($node);
        $instanceof = new \PhpParser\Node\Expr\Instanceof_(new \PhpParser\Node\Expr\Assign($variable, $node->expr), $node->class);
        $exceptionFallbackCheck = $this->createFallbackCheck($variable);
        return new \PhpParser\Node\Expr\BinaryOp\BooleanOr($instanceof, $exceptionFallbackCheck);
    }
    private function createVariable(\PhpParser\Node\Expr\Instanceof_ $instanceof) : \PhpParser\Node\Expr\Variable
    {
        $currentStmt = $instanceof->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$currentStmt instanceof \PhpParser\Node\Stmt) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $scope = $currentStmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        return new \PhpParser\Node\Expr\Variable($this->variableNaming->createCountedValueName('throwable', $scope));
    }
    /**
     * Also checks similar manual transformations.
     */
    private function isAlreadyTransformed(\PhpParser\Node\Expr\Instanceof_ $instanceof) : bool
    {
        /** @var Node $parentNode */
        $parentNode = $instanceof->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return \false;
        }
        $hasVariableToFindInDisjunction = ($var = $instanceof->expr) instanceof \PhpParser\Node\Expr\Variable || $instanceof->expr instanceof \PhpParser\Node\Expr\Assign && ($var = $instanceof->expr->var) instanceof \PhpParser\Node\Expr\Variable;
        if (!$hasVariableToFindInDisjunction) {
            return \false;
        }
        $disjunctionTree = $this->binaryOpTreeRootLocator->findOperationRoot($instanceof, \PhpParser\Node\Expr\BinaryOp\BooleanOr::class);
        $disjuncts = $this->binaryOpConditionsCollector->findConditions($disjunctionTree, \PhpParser\Node\Expr\BinaryOp\BooleanOr::class);
        // If we transformed it ourselves, the second check can only be to the right
        // since it uses the assigned variable.
        if ($instanceof->expr instanceof \PhpParser\Node\Expr\Assign) {
            $index = \array_search($instanceof, $disjuncts, \true);
            if ($index !== \false) {
                $disjuncts = \array_slice($disjuncts, $index);
            }
        }
        $expectedDisjunct = $this->createFallbackCheck($var);
        return $this->nodeComparator->isNodeEqual($expectedDisjunct, $disjuncts);
    }
    private function createFallbackCheck(\PhpParser\Node\Expr\Variable $variable) : \PhpParser\Node\Expr\Instanceof_
    {
        return new \PhpParser\Node\Expr\Instanceof_($variable, new \PhpParser\Node\Name\FullyQualified('Exception'));
    }
}
