<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\NodeFactory;
final class ArrayCollectionAssignFactory
{
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function createFromPropertyName(string $toManyPropertyName) : Expression
    {
        $propertyFetch = $this->nodeFactory->createPropertyFetch('this', $toManyPropertyName);
        $new = new New_(new FullyQualified('Doctrine\\Common\\Collections\\ArrayCollection'));
        $assign = new Assign($propertyFetch, $new);
        return new Expression($assign);
    }
}
