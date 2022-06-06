<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
final class ArrayCollectionAssignFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
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
