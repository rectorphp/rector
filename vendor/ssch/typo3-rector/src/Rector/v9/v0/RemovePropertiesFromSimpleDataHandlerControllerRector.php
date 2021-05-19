<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Breaking-82629-TceDbOptionsPrErrAndUPTRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\RemovePropertiesFromSimpleDataHandlerControllerRector\RemovePropertiesFromSimpleDataHandlerControllerRectorTest
 */
final class RemovePropertiesFromSimpleDataHandlerControllerRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->var instanceof \PhpParser\Node\Expr\Variable) {
            $this->removeVariableNode($node);
            return null;
        }
        if ($node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $this->removePropertyFetchNode($node);
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove assignments or accessing of properties prErr and uPT from class SimpleDataHandlerController', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class MySimpleDataHandlerController extends SimpleDataHandlerController
{
    public function myMethod()
    {
        $pErr = $this->prErr;
        $this->prErr = true;
        $this->uPT = true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class MySimpleDataHandlerController extends SimpleDataHandlerController
{
    public function myMethod()
    {
    }
}
CODE_SAMPLE
)]);
    }
    private function removeVariableNode(\PhpParser\Node\Expr\Assign $assign) : void
    {
        $classNode = $assign->expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (null === $classNode) {
            return;
        }
        if (!$this->isObjectType($classNode, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Controller\\SimpleDataHandlerController'))) {
            return;
        }
        if (!$this->isName($assign->expr, 'uPT') && !$this->isName($assign->expr, 'prErr')) {
            return;
        }
        $this->removeNode($assign);
    }
    private function removePropertyFetchNode(\PhpParser\Node\Expr\Assign $assign) : void
    {
        $classNode = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (null === $classNode) {
            return;
        }
        if (!$this->isObjectType($classNode, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Controller\\SimpleDataHandlerController'))) {
            return;
        }
        if (!$this->isName($assign->var, 'uPT') && !$this->isName($assign->var, 'prErr')) {
            return;
        }
        $this->removeNode($assign);
    }
}
