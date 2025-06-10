<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\CollectionParamTypeSetterToCollectionPropertyRector\CollectionParamTypeSetterToCollectionPropertyRectorTest
 */
final class CollectionParamTypeSetterToCollectionPropertyRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add property collection type based on param type setter', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SetFirstParameterArray
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function setItems(array $items)
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SetFirstParameterArray
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function setItems(\Doctrine\Common\Collections\Collection $items)
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?ClassMethod
    {
        if ($node->isAbstract()) {
            return null;
        }
        if (\count($node->getParams()) !== 1) {
            return null;
        }
        // has first stmts assign to a property?
        $firstStmt = $node->stmts[0] ?? null;
        if (!$firstStmt instanceof Stmt) {
            return null;
        }
        if (!$firstStmt instanceof Expression) {
            return null;
        }
        if (!$firstStmt->expr instanceof Assign) {
            return null;
        }
        if (!$this->isAssignToPropertyFetchCollection($firstStmt->expr)) {
            return null;
        }
        // skip correct type
        $firstParam = $node->getParams()[0];
        if ($firstParam->type instanceof Name && $this->isName($firstParam->type, DoctrineClass::COLLECTION)) {
            // remove default param, as no longer needed
            if ($firstParam->default instanceof Expr) {
                $firstParam->default = null;
                return $node;
            }
            return null;
        }
        $firstParam->type = new FullyQualified(DoctrineClass::COLLECTION);
        // remove default param, as no longer needed
        if ($firstParam->default instanceof Expr) {
            $firstParam->default = null;
        }
        return $node;
    }
    private function isAssignToPropertyFetchCollection(Assign $assign) : bool
    {
        if (!$assign->var instanceof PropertyFetch) {
            return \false;
        }
        if ($assign->expr instanceof New_) {
            return \false;
        }
        $propertyFetchType = $this->getType($assign->var);
        if (!$propertyFetchType instanceof ObjectType) {
            return \false;
        }
        return $propertyFetchType->getClassName() === DoctrineClass::COLLECTION;
    }
}
