<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\Experimental;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Feature-84112-SymfonyDependencyInjectionForCoreAndExtbase.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\Experimental\OptionalConstructorToHardRequirementRector\OptionalConstructorToHardRequirementRectorTest
 */
final class OptionalConstructorToHardRequirementRector extends \Rector\Core\Rector\AbstractRector
{
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
        if (!\is_iterable($node->stmts)) {
            return null;
        }
        $paramsToCheck = [];
        foreach ($node->getParams() as $param) {
            if (null === $param->default) {
                continue;
            }
            if (null === $param->type) {
                continue;
            }
            if (!$this->valueResolver->isNull($param->default)) {
                continue;
            }
            if (!$param->type instanceof \PhpParser\Node\Name\FullyQualified) {
                continue;
            }
            $paramName = $this->nodeNameResolver->getName($param->var);
            if (null === $paramName) {
                continue;
            }
            $param->default = null;
            $paramsToCheck[$paramName] = $param;
        }
        $potentialStmtsToRemove = [];
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            if (!$stmt->expr->expr instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($stmt->expr->var);
            if ($stmt->expr->var instanceof \PhpParser\Node\Expr\Variable && null !== $variableName) {
                $potentialStmtsToRemove[$variableName] = $stmt;
            }
            if (!$stmt->expr->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            if (!$stmt->expr->expr->left instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!$this->isNames($stmt->expr->expr->left, \array_keys($paramsToCheck))) {
                continue;
            }
            if ($stmt->expr->expr->right instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
                // Reset param default value
                $paramDefault = $this->nodeNameResolver->getName($stmt->expr->expr->left);
                if (null === $paramDefault) {
                    continue;
                }
                $paramsToCheck[$this->nodeNameResolver->getName($stmt->expr->expr->left)]->default = $this->nodeFactory->createNull();
                continue;
            }
            $stmt->expr->expr = $stmt->expr->expr->left;
        }
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            if (!$stmt->expr->expr instanceof \PhpParser\Node\Expr\MethodCall) {
                continue;
            }
            if (!$this->isNames($stmt->expr->expr->var, \array_keys($potentialStmtsToRemove))) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($stmt->expr->expr->var);
            if (null === $variableName) {
                continue;
            }
            $this->removeNode($potentialStmtsToRemove[$variableName]);
        }
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Option constructor arguments to hard requirement', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Fluid\View\StandaloneView;
class MyClass
{
public function __construct(Dispatcher $dispatcher = null, StandaloneView $view = null, BackendUtility $backendUtility = null, string $test = null)
    {
        $dispatcher = $dispatcher ?? GeneralUtility::makeInstance(ObjectManager::class)->get(Dispatcher::class);
        $view = $view ?? GeneralUtility::makeInstance(StandaloneView::class);
        $backendUtility = $backendUtility ?? GeneralUtility::makeInstance(BackendUtility::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Fluid\View\StandaloneView;
class MyClass
{
public function __construct(Dispatcher $dispatcher, StandaloneView $view, BackendUtility $backendUtility, string $test = null)
    {
        $dispatcher = $dispatcher;
        $view = $view;
        $backendUtility = $backendUtility;
    }
}
CODE_SAMPLE
)]);
    }
}
