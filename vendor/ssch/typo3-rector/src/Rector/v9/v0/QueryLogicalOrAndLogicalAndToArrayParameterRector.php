<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/m/typo3/book-extbasefluid/master/en-us/6-Persistence/3-implement-individual-database-queries.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\QueryLogicalOrAndLogicalAndToArrayParameterRector\QueryLogicalOrAndLogicalAndToArrayParameterRectorTest
 */
final class QueryLogicalOrAndLogicalAndToArrayParameterRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Persistence\\QueryInterface'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['logicalAnd', 'logicalOr'])) {
            return null;
        }
        if (\count($node->args) <= 1) {
            return null;
        }
        if ($node->args[0]->value instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $node->args = $this->nodeFactory->createArgs([$this->nodeFactory->createArray($node->args)]);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use array instead of multiple parameters for logicalOr and logicalAnd of Extbase Query class', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Persistence\Repository;

class ProductRepositoryLogicalAnd extends Repository
{
    public function findAllForList()
    {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            $query->lessThan('foo', 1),
            $query->lessThan('bar', 1)
        ));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Persistence\Repository;

class ProductRepositoryLogicalAnd extends Repository
{
    public function findAllForList()
    {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd([
            $query->lessThan('foo', 1),
            $query->lessThan('bar', 1)
        ]));
    }
}
CODE_SAMPLE
)]);
    }
}
