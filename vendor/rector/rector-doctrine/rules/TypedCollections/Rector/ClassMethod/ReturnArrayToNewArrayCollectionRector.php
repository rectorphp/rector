<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\ReturnArrayToNewArrayCollectionRector\ReturnArrayToNewArrayCollectionRectorTest
 */
final class ReturnArrayToNewArrayCollectionRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change return [] to return new ArrayCollection([]) in a method, that returns Collection type', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ReturnArrayItems
{
    public function getItems(): Collection
    {
        $items = [1, 2, 3];
        $items[] = 4;

        return $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class ReturnArrayItems
{
    public function getItems(): Collection
    {
        $items = [1, 2, 3];
        $items[] = 4;

        return new ArrayCollection($items);
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
        if (!$node->returnType instanceof Name) {
            return null;
        }
        if (!$this->isName($node->returnType, DoctrineClass::COLLECTION)) {
            return null;
        }
        // update all return [] to return new ArrayCollection([])
        $hasChanged = \false;
        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use(&$hasChanged) : ?Node {
            if (!$node instanceof Return_) {
                return null;
            }
            if (!$node->expr instanceof Expr) {
                return null;
            }
            $exprType = $this->getType($node->expr);
            if (!$exprType->isArray()->yes()) {
                return null;
            }
            $node->expr = new New_(new FullyQualified(DoctrineClass::ARRAY_COLLECTION), [new Arg($node->expr)]);
            $hasChanged = \true;
            return $node;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
